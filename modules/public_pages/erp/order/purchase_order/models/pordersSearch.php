<?php

/**
 *  Purchase Order Search
 *
 *  Default options for purchase order search
 *
 *  @package purchase_orders
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class pordersSearch extends BaseSearch
{

    protected $fields = array();

    public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
    {
        $search = new pordersSearch($defaults);

        // Get relevant module preferences
        $system_prefs = SystemPreferences::instance();
        $viewAll = $system_prefs->getPreferenceValue('show-all-orders', 'purchase_order');

        // Search by Raised_By
        if ($viewAll == 'on') {
            $search->addSearchField('order', 'order_is', 'porder_status', array(
                'Raised by me',
                'Other Orders'
            ));
        } else {
            $search->addSearchField('order', 'order_is', 'porder_status', array(
                'Raised by me'
            ));
        }
        
        // Search by Supplier
        $search->addSearchField('plmaster_id', 'Supplier', 'select', 0, 'basic');
        $supplier = DataObjectFactory::Factory('PLSupplier');
        $options = array(
            '0' => 'All'
        );

        // Search by Order Number
        $search->addSearchField('order_number', 'order_number', 'equal', '', 'basic');

        // Search by Order Number
        $search->addSearchField('lines', 'Show Lines', 'show', '', 'basic', false);

        $suppliers = $supplier->getAll(null, false, true, '', '');
        $options += $suppliers;
        $search->setOptions('plmaster_id', $options);

        // Search by Project
        $search->addSearchField('project_id', 'Project', 'select', '', 'advanced');
        $project = DataObjectFactory::Factory('Project');
        $options = array(
            '' => 'All'
        );
        $projects = $project->getAll();
        $options += $projects;
        $search->setOptions('project_id', $options);

        // Search by Description
        $search->addSearchField('description', 'Description Contains', 'contains', '', 'advanced');

        // Search by Order Date
        $search->addSearchField('order_date', 'order_date_after', 'after', '', 'advanced');

        // Search by Due Date
        $search->addSearchField('due_date', 'due_date_before', 'before', '', 'advanced');

        // Search by Transaction Type
        $search->addSearchField('type', 'type', 'select', '', 'advanced');
        $options = array(
            '' => 'All',
            'O' => 'Order',
            'R' => 'Requisition'
        );
        $search->setOptions('type', $options);

        // Search by Status
        $search->addSearchField('status', 'status', 'multi_select', ['N', 'O', 'A', 'H', 'R', 'P'], 'advanced');
        $porder = DataObjectFactory::Factory('POrder');
        $options = array_merge(array(
            '' => 'All'
        ), $porder->getEnumOptions('status'));
        $search->setOptions('status', $options);
        $search->setSearchData($search_data, $errors);
        return $search;
    }

    public static function receivedOrders($search_data = null, &$errors = array(), $defaults = null)
    {
        $search = new pordersSearch($defaults);

        // Search by Supplier
        $search->addSearchField('plmaster_id', 'Supplier', 'select', 0);
        $supplier = DataObjectFactory::Factory('PLSupplier');
        $options = array(
            '0' => 'Select Supplier'
        );
        $suppliers = $supplier->getAll(null, false, true, '');
        $options += $suppliers;
        $search->setOptions('plmaster_id', $options);

        // Search by Stock Item
        $search->addSearchField('stitem', 'Stock Item begins with', 'begins');

        // Search by Order Number
        $search->addSearchField('order_id', 'order_number', 'select', 0);
        $orderlines = DataObjectFactory::Factory('POrder');
        $cc = new ConstraintChain();
        $cc->add(new Constraint('status', 'in', "('R','P')"));
        $orderlines->orderby = 'order_number';
        $options = array(
            '0' => 'All'
        );
        $orderlines = $orderlines->getAll($cc);
        $options += $orderlines;
        $search->setOptions('order_id', $options);

        // Restrict Search by Received Status
        $search->addSearchField('status', '', 'hidden', '', 'hidden');

        $cc = new ConstraintChain();
        $cc->add(new Constraint('status', 'in', "('A', 'R')"));
        $cc->add(new Constraint('invoice_id', 'is', 'NULL'));
        $search->setConstraint('status', $cc);

        $search->setSearchData($search_data, $errors, 'receivedOrders');
        return $search;
    }

    public static function accrual($search_data = null, &$errors = array(), $defaults = null)
    {
        $search = new pordersSearch($defaults);

        // Search by Supplier
        $search->addSearchField('plmaster_id', 'Supplier', 'select', 0, 'advanced');
        $supplier = DataObjectFactory::Factory('PLSupplier');
        $options = array(
            '0' => 'All'
        );
        $suppliers = $supplier->getAll(null, false, true, '', '');
        $options += $suppliers;
        $search->setOptions('plmaster_id', $options);

        // Search by Stock Item
        $search->addSearchField('stitem_id', 'Stock Item', 'select', 0, 'advanced');
        $stitems = DataObjectFactory::Factory('STItem');
        $options = array(
            '0' => 'All'
        );
        $stitems = $stitems->getAll();
        $options += $stitems;
        $search->setOptions('stitem_id', $options);

        // Search by Despatch Number
        $search->addSearchField('gr_number', 'goods_received_number', 'equal', '', 'advanced');

        // Search by Order Number
        $search->addSearchField('order_number', 'order_number', 'equal', '', 'advanced');

        // Search by Received Date
        $search->addSearchField('received_date', 'delivery_date_between', 'between', '', 'advanced');

        // Search by Status
        $search->addSearchField('status', 'status', 'hidden', 'R', 'hidden');

        $search->setSearchData($search_data, $errors, 'accrual');
        return $search;
    }

    public static function grn_write_off($search_data = null, &$errors = array(), $defaults = null)
    {
        $search = new pordersSearch($defaults);

        // Search by Supplier
        $search->addSearchField('plmaster_id', 'Supplier', 'select', 0, 'advanced');
        $supplier = DataObjectFactory::Factory('PLSupplier');
        $options = array(
            '0' => 'All'
        );
        $suppliers = $supplier->getAll(null, false, true, '', '');
        $options += $suppliers;
        $search->setOptions('plmaster_id', $options);

        // Search by Stock Item
        $search->addSearchField('stitem_id', 'Stock Item', 'select', 0, 'advanced');
        $stitems = DataObjectFactory::Factory('STItem');
        $options = array(
            '0' => 'All'
        );
        $stitems = $stitems->getAll();
        $options += $stitems;
        $search->setOptions('stitem_id', $options);

        // Search by Despatch Number
        $search->addSearchField('gr_number', 'goods_received_number', 'equal', '', 'advanced');

        // Search by Order Number
        $search->addSearchField('order_number', 'order_number', 'equal', '', 'advanced');

        // Search by Received Date
        $search->addSearchField('received_date', 'delivery_date_between', 'between', '', 'advanced');

        // Search by Status
        $grn = DataObjectFactory::Factory('POReceivedLine');

        $search->addSearchField('status', 'status', 'hidden', $grn->accrualStatus(), 'hidden');

        // Ignore any received/accrued lines that have been invoiced
        // NB: received line status only set to invoiced when invoice is posted
        $search->addSearchField('invoice_id', '', 'hidden', '', 'hidden');
        $cc = new ConstraintChain();
        $cc->add(new Constraint('invoice_id', 'is', 'NULL'));
        $search->setConstraint('invoice_id', $cc);

        $search->setSearchData($search_data, $errors, 'grn_write_off');
        return $search;
    }
}

?>
