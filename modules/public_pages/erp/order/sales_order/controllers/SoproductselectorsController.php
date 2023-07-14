<?php
/**
 *	Sales Order Product Selector Controller
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class SoproductselectorsController extends SelectorController
{
    protected $version = '$Revision: 1.11 $';

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);
    }

    /**
     * Delete selector
     *
     * Delete the current level(selector) and all child
     * selectors and any associated component relationships.
     *
     * Called via ajax
     */
    public function deleteSelector()
    {
        // Only POST requests with an XHR header are allowed
        $this->checkRequest(['post'], true);

        if (! isset($this->_data) || ! $this->loadData()) {
            // either no id has been provided
            // or the data for the supplied id does not exist
            $this->dataError();
            sendBack();
        }
        $current = $this->_uses[$this->modeltype];

        // Get all children of the current selector
        $ancestor_sql = "
            WITH RECURSIVE tree AS (
                SELECT id, ARRAY[]::BIGINT[] AS ancestors
                FROM so_product_selector WHERE parent_id IS NULL AND usercompanyid = ?

                UNION ALL

                SELECT so_product_selector.id, tree.ancestors || so_product_selector.parent_id
                FROM so_product_selector, tree
                WHERE so_product_selector.parent_id = tree.id
            )
            SELECT * FROM tree WHERE ? = ANY(tree.ancestors)";

        $db = &DB::Instance();
        $result = $db->getAll($ancestor_sql, [
            EGS_COMPANY_ID,
            $current->id
        ]);

        $result_ids = [];
        foreach ($result as $selector) {
            $result_ids[] = $selector['id'];
        }

        // Delete child selectors (component links removed on cascade)
        if (!empty($result_ids)) {
            $selectors = new DataObjectCollection(new SelectorObject('so_product_selector'));
            $selector_filter = new SearchHandler($selectors, false);
            $selector_filter->addConstraint(new Constraint('id', 'IN', '(' . implode(',', $result_ids) . ')'));
            $selectors->delete($selector_filter);
        }

        // Delete current selector
        parent::delete($this->_templateobject);

        // Send to index if this was a top level, otherwise view the parent
        $action = 'index';
        $args = [];
        if ($current->parent_id !== null) {
            $action = 'view';
            $args = [
                'id' => $current->parent_id
            ];
        }

        // Return JSON response
        sendTo($this->name, $action, $this->_modules, $args);
    }
}
