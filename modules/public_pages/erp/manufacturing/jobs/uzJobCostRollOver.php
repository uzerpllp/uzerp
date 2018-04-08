<?php
class uzJobCostRollOver extends uzExclusiveJob
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
        $stitem_ids = array_keys(STItem::nonObsoleteItems());

        foreach ($stitem_ids as $stitem_id) {
            $stitem = new STItem();

            if (! $stitem->load($stitem_id) || ! $stitem->rollOver()) {
                $errors[] = 'failed to roll cost ' . $stitem_id;
                continue;
            }
        }

        if (count($errors) == 0) {
            if ((! MFStructure::globalRollOver()) || (! MFOperation::globalRollOver()) || (! MFOutsideOperation::globalRollOver())) {
                $errors[] = 'Could not roll-over stock items';
                $db->FailTrans();
            }
        }

        $db->CompleteTrans();
        $message = uzJobMessages::Factory($this->user, $this->egs_co);
        if (count($errors) == 0) {
            $message->send($job_id, 'Stock items cost roll-over complete');
        } else {
            $message->send($job_id, 'Stock items cost roll-over failed');
        }
    }
}
