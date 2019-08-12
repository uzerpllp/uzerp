<?php
class uzJobRecalcLatestCosts extends uzExclusiveJob
{

    /**
     *
     * {@inheritDoc}
     * @see uzJob::perform()
     */
    public function perform($job_id=null)
    {

        $db = DB::Instance();
        $db->StartTrans();
        $errors = array();
        $stitems_done = array();
        $stitem_ids = array_keys(STItem::nonObsoleteItems());
        $max_depth = 5;
        $max_parents = 5;

        foreach ($stitem_ids as $stitem_id) {
            if (in_array($stitem_id, $stitems_done)) {
                continue;
            }

            //echo '...'.$stitem_id."\r\n";

            $stitem = new STItem();
            if (! $stitem->load($stitem_id)) {
                continue;
            }

            $parent = null;
            $num_parents = 0;

            do {
                if ($parent) {
                    $stitem = $parent;
                }

                $parent = null;
                $parents = $stitem->getParents();

                if (count($parents) > 0) {
                    list ($parent) = $parents;
                }

                $num_parents ++;
            } while (($parent) && ($num_parents <= $max_parents));

            $tree_array = $stitem->getTreeArray($max_depth);
            // Gets child nodes first

            $array_iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($tree_array), 2);
            foreach ($array_iterator as $id => $children) {
                if (in_array($id, $stitems_done)) {
                    continue;
                }
                $stitem = new STItem();

                if (! $stitem->load($id)) {
                    continue;
                }

                $stitems_done[] = $id;
                $old_costs = array(
                    $stitem->latest_cost,
                    $stitem->latest_mat,
                    $stitem->latest_lab,
                    $stitem->latest_osc,
                    $stitem->latest_ohd
                );
                $stitem->calcLatestCost();
                $new_costs = array(
                    $stitem->latest_cost,
                    $stitem->latest_mat,
                    $stitem->latest_lab,
                    $stitem->latest_osc,
                    $stitem->latest_ohd
                );

                $equal_costs = true;
                $total_costs = count($old_costs);

                for ($i = 0; $i < $total_costs; $i ++) {
                    if (bccomp($old_costs[$i], $new_costs[$i], $stitem->cost_decimals) != 0) {
                        $equal_costs = false;
                        break;
                    }
                }

                if ($equal_costs) {
                    continue;
                }

                if ((! $stitem->saveCosts()) || (! STCost::saveItemCost($stitem))) {
                    $errors[] = 'failed to save cost ' . $stitem->id;
                    continue;
                }
            }
        }

        $db->CompleteTrans();
        $message = uzJobMessages::Factory($this->user, $this->egs_co);
        if (count($errors) == 0) {
            $message->send($job_id, 'Stock items latest cost calculation complete');
        } else {
            $message->send($job_id, 'Stock items latest cost calculation failed', 'error');
        }
    }
}
