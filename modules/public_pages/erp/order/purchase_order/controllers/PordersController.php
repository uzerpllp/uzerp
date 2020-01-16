<?php

/**
 *	uzERP Purchase Orders Controller
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
class PordersController extends printController
{

    protected $version = '$Revision: 1.127 $';

    use getSalesOrderOptions;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->uses(DataObjectFactory::Factory('POrderLine'), FALSE);

        $this->_templateobject = DataObjectFactory::Factory('POrder');

        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $this->view->set('clickaction', 'view');

        $errors = array();

        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['plmaster_id'])) {
            $s_data['plmaster_id'] = $this->_data['plmaster_id'];
        }

        if (isset($this->_data['status'])) {
            $s_data['status'] = $this->_data['status'];
        }

        $user = getCurrentUser();

        $s_data['raised_by'] = array(
            'raised_by' => $user->username,
            'authorised_by' => $user->username
        );

        $this->setSearch('pordersSearch', 'useDefault', $s_data);

        parent::index(new POrderCollection($this->_templateobject));

        $today = fix_date(date(DATE_FORMAT));
        $this->view->set('today', $today);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        foreach ($this->_templateobject->getEnumOptions('type') as $key => $description) {
            if ($key != 'R') {
                $actions['new' . $description] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'new',
                        'type' => $key
                    ),
                    'tag' => 'new ' . $description
                );
            }
        }

        $actions['availabilityByItems'] = array(
            'tag' => 'view_items_on_order',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'viewByItems'
            )
        );

        if ($this->search->getValue('lines')) {
            $this->_templateName = $this->getTemplateName('revieworders');
        }

        $actions['print'] = array(
            'tag' => 'print_orders',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'filename' => 'OrderList_' . fix_date(date(DATE_FORMAT)),
                'printaction' => 'printOrderList',
                'type' => 'new'
            )
        );

        $actions['invoice'] = array(
            'tag' => 'create_invoice_from_GRN',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'createinvoice'
            )
        );

        $sidebar->addList('Actions', $actions);

        $sidebarlist = array();
        $sidebarlist['printoverdue'] = array(
            'tag' => 'Overdue Orders',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'filename' => 'OrderList_' . date('Y-m-d'),
                'printaction' => 'printOrderList',
                'type' => 'overdue'
            )
        );
        $sidebarlist['printoverduelines'] = array(
            'tag' => 'Overdue Orders (with lines)',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'filename' => 'OrderList_' . date('Y-m-d'),
                'printaction' => 'printOrderList',
                'type' => 'overdue',
                'lines' => 'Y'
            )
        );
        $sidebarlist['printoutstanding'] = array(
            'tag' => 'Outstanding Orders',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'filename' => 'OrderList_' . date('Y-m-d'),
                'printaction' => 'printOrderList',
                'type' => 'outstanding'
            )
        );
        $sidebarlist['printoutstandinglines'] = array(
            'tag' => 'Outstanding Orders (with lines)',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'filename' => 'OrderList_' . date('Y-m-d'),
                'printaction' => 'printOrderList',
                'type' => 'outstanding',
                'lines' => 'Y'
            )
        );
        $sidebar->addList('Reports', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function clone_order()
    {
        $flash = Flash::Instance();

        $errors = array();

        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $order = $this->_uses[$this->modeltype];

        if (! $order->isLoaded()) {
            $flash->addError('Error loading order details');
            sendBack();
        }

        $data[$this->modeltype] = array();

        foreach ($order->getFields() as $fieldname => $field) {
            switch ($fieldname) {
                case $order->idField:
                case 'created':
                case 'createdby':
                case 'lastupdated':
                case 'alteredby':
                case 'order_number':
                case 'order_date':
                case 'due_date':
                case 'raised_by':
                case 'authorised_by':
                case 'date_authorised':
                case 'owner':
                    break;
                case 'status':
                    $data[$this->modeltype][$fieldname] = $order->newStatus();
                    break;
                default:
                    $data[$this->modeltype][$fieldname] = $order->$fieldname;
            }
        }

        $line_count = 0;

        foreach ($order->lines as $orderline) {
            $modelname = get_class($orderline);
            foreach ($orderline->getFields() as $fieldname => $field) {
                switch ($fieldname) {
                    case $orderline->idField:
                    case 'created':
                    case 'createdby':
                    case 'lastupdated':
                    case 'alteredby':
                    case 'gr_note':
                    case 'del_qty':
                    case 'due_delivery_date':
                    case 'actual_delivery_date':
                        break;
                    case 'order_id':
                        $data[$modelname][$fieldname][$line_count] = '';
                        break;
                    case 'status':
                        $data[$modelname][$fieldname][$line_count] = $orderline->newStatus();
                        break;
                    case 'productline_id':
                        if (! is_null($orderline->productline_id)) {
                            $productline = DataObjectFactory::Factory('POProductLine');

                            $productline->load($orderline->productline_id);

                            if (! $productline->isLoaded() || (! is_null($productline->end_date) && $productline->end_date < un_fix_date(date(DATE_FORMAT)))) {
                                $flash->addWarning('Selected Product is no longer valid on line ' . $orderline->line_number);
                                $orderline->description .= ' ** Selected Product is no longer valid';
                                $data[$modelname]['description'][$line_count] .= ' ** Selected Product is no longer valid';
                            }
                        }
                    default:
                        $data[$modelname][$fieldname][$line_count] = $orderline->$fieldname;
                }
            }
            $line_count ++;
        }

        if (! empty($this->_data['type'])) {
            $data[$this->modeltype]['type'] = $this->_data['type'];
        }

        $result = $order->save_model($data);

        if ($result !== FALSE) {
            sendTo($this->name, 'view', $this->_modules, array(
                $order->idField => $result['internal_id']
            ));
        }

        sendBack();
    }

    public function delete()
    {
        $flash = Flash::Instance();

        parent::delete('POrder');

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function _new()
    {
        parent::_new();

        // Get the Order Object - if loaded, this is an edit
        $porder = $this->_uses[$this->modeltype];

        // get supplier list
        if ($porder->isLoaded() && $porder->net_value != 0) {
            $suppliers = array(
                $porder->plmaster_id => $porder->supplier
            );
        } else {
            $suppliers = $this->getOptions($this->_templateobject, 'plmaster_id', 'getOptions', 'getOptions', array(
                'use_collection' => true
            ));

            if (! $porder->isLoaded()) {
                if (isset($this->_data['type'])) {
                    $porder->type = $this->_data['type'];
                } else {
                    $porder->type = 'R';
                }
            }
        }

        // get the default/current selected supplier
        if (isset($this->_data['plmaster_id'])) {
            // this is set if there has been error and we are redisplaying the screen
            $defaultsupplier = $this->_data['plmaster_id'];
        } else {
            if (! $porder->isLoaded()) {
                $defaultsupplier = $this->getDefaultValue($this->modeltype, 'plmaster_id', '');
            } else {
                $defaultsupplier = $porder->plmaster_id;
            }
        }

        if (empty($defaultsupplier)) {
            $defaultsupplier = key($suppliers);
        }

        if (! $porder->isLoaded()) {
            $porder->plmaster_id = $defaultsupplier;
            // get delivery term for default supplier
            $this->view->set('customer_term', $this->getDeliveryTerm($defaultsupplier));
        }

        $this->view->set('selected_supplier', $defaultsupplier);

        $this->view->set('trans_type', $porder->getEnum('type', $porder->type));

        if ($porder->isLoaded()) {
            $this->view->set('default_receive_action', $porder->receive_action);

            $deleted_value = 0;

            $deleted_lines = array();

            if (isset($this->_data['POrderLine']['deleted_lines'])) {

                $deleted_lines = $this->_data['POrderLine']['deleted_lines'];

                foreach ($deleted_lines as $value) {
                    $deleted_value += $value;
                }
            }
            $this->view->set('order_total', bcsub($porder->net_value, $deleted_value));
        } else {
            $this->view->set('default_receive_action', $this->getReceiveAction($defaultsupplier));
            $this->view->set('order_total', '0.00');
            if (is_null($porder->type)) {
                $porder->type = 'R';
            }
        }

        // get Purchase Order Notes for default supplier or first in supplier
        $this->getNotes($defaultsupplier);

        $receive_actions = WHAction::getReceiveActions();
        $this->view->set('receive_actions', $receive_actions);

        if (! is_null($porder->type)) {
            $this->view->set('page_title', $this->getPageName($porder->getFormatted('type')));
        }

        // This bit allows for projects and tasks
        if (! $porder->isLoaded() && ! empty($this->_data['project_id'])) {
            $porder->project_id = $this->_data['project_id'];
        }

        // We only want non-archived projects
        $projects = Project::getLiveProjects();
        $this->view->set('projects', $projects);

        // Now get tasks for the selected project
        $this->view->set('tasks', $this->getTaskList($porder->project_id));

        // PO to SO link
        if (! $porder->isLoaded() && ! empty($this->_data['sales_order_id'])) {
            $porder->sales_order_id = $this->_data['sales_order_id'];
        }

        $this->view->set('sales_orders', $this->getSalesOrders($sales_order_id));

        // Use SO Delivery Address
        if (! $porder->isLoaded() && ! empty($this->_data['use_sorder_delivery'])) {
            $porder->use_sorder_delivery = $this->_data['use_sorder_delivery'];
        }
    }

    public function save()
    {
        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $flash = Flash::Instance();

        $data = $this->_data;

        $errors = array();

        $header = $data[$this->modeltype];

        if (isset($header['id']) && $header['id'] != '') {
            $action = 'updated';
        } else {
            $action = 'added';
        }

        $trans_type = $data['trans_type'];

        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($header['plmaster_id']);

        if ($supplier) {
            $header['currency_id'] = $supplier->currency_id;
            $header['payment_term_id'] = $supplier->payment_term_id;
            $header['tax_status_id'] = $supplier->tax_status_id;
        }

        if (! empty($header['receive_action'])) {

            $transferrules = new WHTransferruleCollection();

            $locations = $transferrules->getToLocations($header['receive_action'], array());

            if (count($locations) == 1) {

                $location = DataObjectFactory::Factory('WHLocation');

                $location->load(current(array_keys($locations)));

                if ($location) {

                    $store = DataObjectFactory::Factory('WHStore');

                    $store->load($location->whstore_id);

                    if ($store) {
                        $header['del_address_id'] = $store->address_id;
                    }
                }
            }
        } else {
            $header['del_address_id'] = '';
        }

        if (empty($header['del_address_id'])) {
            $company = DataObjectFactory::Factory('Systemcompany');
            $company->load(EGS_COMPANY_ID);

            $companyAddress = $company->getCompanyAddress();

            $header['del_address_id'] = $companyAddress->id;
        }

        $order = POrder::Factory($header, $errors);

        if ($order && count($errors) == 0) {
            $result = $order->save($errors);
        } else {
            $flash->addErrors($errors);
            $result = false;
        }

        if ($result !== FALSE) {
            $flash = Flash::Instance();
            $flash->addMessage($order->getEnum('type', $order->type) . ' ' . $action . ' successfully');
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $order->id
            ));
        }

        $flash->addError('Error saving ' . $trans_type);
        $this->refresh();
    }

    public function view()
    {
        $order = $this->order_details();

        $id = $order->{$order->idField};
        $type = $order->getFormatted('type');

        $grns = $order->getGoodsReceivedNumbers();

        $this->view->set('grns', $grns);

        // Create a summary array of the Order Lines Statuses
        $linestatuses = $order->getLineStatuses();
        $linestatus = $linestatuses['count'];

        $this->view->set('linevalue', $linestatuses['value']);
        $this->view->set('use_sorder_delivery', $order->use_sorder_delivery);

        $porderline = DataObjectFactory::Factory('POrderLine');
        $this->view->set('porderlines', $order->lines);

        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($order->plmaster_id);

        // Return list of users who can authorise
        $po_obj = new DataObject('po_auth_summary');
        $po_obj->idField = 'username';
        $po_obj->identifierField = 'username';

        $cc = new ConstraintChain();

        $cc->add(new Constraint('order_number', '=', $order->order_number));

        $this->view->set('authorised_users', $po_obj->getAll($cc));

        // Get current username
        $user = getCurrentUser();

        $can_authorise = $this->authRequisition($order);

        // TODO: to make this generic, should do something like
        // $can_edit = ($order->isAccessAllowed() || $can_authorise);
        // which covers the following condition
        // However, still need to get the list of allowed users!
        // How to do this without duplicating effort!

        // Should only be allowed to edit if
        // 1) current user raised the order
        // 2) current user is the owner
        // 3) current user has authority
        // 4) current user role is allowed to edit
        $can_edit = false;

        foreach ($order->getAccessFields() as $fieldname) {
            if ($order->$fieldname == $user->username) {
                $can_edit = true;
                break;
            }
        }

        $can_edit = $can_edit ? $can_edit : $can_authorise;

        $this->view->set('can_edit', $can_edit);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'purchase_ledger',
                'controller' => 'PLSuppliers',
                'action' => 'index'
            ),
            'tag' => 'view all suppliers'
        );

        foreach ($order->getEnumOptions('type') as $key => $description) {
            if ($key != 'R') {
                $actions['new' . $description] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'new',
                        'type' => $key
                    ),
                    'tag' => 'new ' . $description
                );
            }
        }

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view requisitions/orders'
        );

        $sidebar->addList('Actions', $actions);

        $actions = array();

        foreach ($order->getEnumOptions('type') as $key => $description) {
            if ($key != 'R') {
                $actions['new' . $description] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'new',
                        'plmaster_id' => $order->plmaster_id,
                        'type' => $key
                    ),
                    'tag' => 'new ' . $description
                );
            }
        }

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index',
                'plmaster_id' => $order->plmaster_id
            ),
            'tag' => 'view requisitions/orders'
        );

        $sidebar->addList($supplier->name, $actions);

        $actions = array();

        foreach ($order->getEnumOptions('type') as $key => $description) {
            if ($key != 'R') {
                $actions['clone' . $description] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'clone_order',
                        'id' => $order->id,
                        'type' => $key
                    ),
                    'tag' => 'Save as new ' . $description
                );
            }
        }

        if ($order->type == 'R' && ! $order->cancelled()) {
            $actions['viewprofile'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'profile',
                    'id' => $id
                ),
                'tag' => 'view requisition profile'
            );
        }

        if (($order->isNew() || $order->orderSent()) && $order->type == 'O') {
            $actions['printAcknowledgement'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printorder',
                    'filename' => 'PO' . $order->order_number,
                    'id' => $id
                ),
                'tag' => 'print Order'
            );
            // Print a PO Schedule by specifying a 'report definition'
            $actions['printSchedule'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printorder',
                    'report' => 'PurchaseOrderSchedule',
                    'filename' => 'PO' . $order->order_number,
                    'id' => $id
                ),
                'tag' => 'print Schedule'
            );
        }

        if ($order->status == $order->isNew() && $can_edit) {
            $this->view->set('linevalue', $linestatuses['value']);
            $actions['editOrder'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'edit',
                    'id' => $id
                ),
                'tag' => 'Edit ' . $type
            );

            $available_wo_purchase = POrderLine::getWorkOrdersNeedingPurchase(['plmaster_id' => $supplier->id]);
            if (count($available_wo_purchase) != 0) {

            $actions['add_wo_purchase'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'porderlines',
                    'action' => 'new_wopurchase',
                    'order_id' => $id
                ),
                'tag' => 'Add_Work_Order_Purchase'
            );
        }
            $actions['add_lines'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'porderlines',
                    'action' => 'new',
                    'order_id' => $id
                ),
                'tag' => 'Add_Lines'
            );
        }

        if (($order->awaitingDelivery() && $order->allLinesAwaitingDelivery($linestatus)) || ($linestatuses['linecount'] == 0 && ! $order->isNew())) {
            $actions['resetorder'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'resetstatus',
                    'id' => $id
                ),
                'tag' => 'Reset Order'
            );
        }

        if ($order->orderSent()) {
            $actions['orderacknowledged'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'orderAcknowledged',
                    'id' => $id
                ),
                'tag' => 'Order Acknowledgement received'
            );
        }

        if ($order->type == 'R' && $order->lines->count() > 0 && $can_authorise && ! $order->cancelled()) {
            $actions['authorise'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'authoriseOrder',
                    'id' => $id,
                ),
                'tag' => "Authorise {$type}",
                'class' => 'confirm',
                'data_attr' => ['data_uz-confirm-message' => "Authorise {$type}?|This cannot be undone."]
            );
        }

        if ($order->type == 'R' && $order->allLinesNew($linestatus)) {
            $actions['cancel_order'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'cancel_order',
                    'type' => 'confirm',
                    'id' => $id
                ),
                'tag' => 'Cancel ' . $type,
                'class' => 'confirm',
                'data_attr' => ['data_uz-confirm-message' => "Cancel {$type}?|This cannot be undone."]
            );
        }

        if ($order->someLinesReceived($linestatus)) {
            // If there is at least one received line
            // then the order can be invoiced
            $actions['createInvoice'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'createinvoice',
                    'order_id' => $id,
                    'plmaster_id' => $order->plmaster_id,
                    'order_number' => $order->order_number
                ),
                'tag' => 'Create Invoice'
            );
        }

        if ($order->partReceived() || $order->Received() || $order->Invoiced()) {
            $actions['viewgrn'] = array(
                'link' => array(
                    'module' => 'goodsreceived',
                    'controller' => 'poreceivedlines',
                    'action' => 'index',
                    'order_number' => $order->order_number
                ),
                'tag' => 'View Goods Received Note'
            );
        }

        $invoice_list = $order->getInvoices();

        if (count($invoice_list) == 1) {
            $actions['viewinvoice'] = array(
                'link' => array(
                    'module' => 'purchase_invoicing',
                    'controller' => 'Pinvoices',
                    'action' => 'view',
                    'id' => key($invoice_list)
                ),
                'tag' => 'View Invoice'
            );
        }

        if (count($invoice_list) > 1) {
            $actions['viewinvoice'] = array(
                'link' => array(
                    'module' => 'purchase_invoicing',
                    'controller' => 'Pinvoices',
                    'action' => 'index',
                    'purchase_order_number' => $order->order_number
                ),
                'tag' => 'View Invoices'
            );
        }

        $sidebar->addList('This ' . $type, $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        if ($order->type == 'R') {
            $this->view->set('page_title', 'Purchase Requisition');
        }
    }

    public function profile()
    {
        $order = $this->order_details();
        $id = $order->{$order->idField};

        // Create a summary array of the Order Lines Statuses
        $linestatuses = $order->getLineStatuses();
        $linestatus = $linestatuses['count'];

        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($order->plmaster_id);

        $address = DataObjectFactory::Factory('Companyaddress');

        if ($order->del_address_id) {
            $address = $address->load($order->del_address_id);
        }
        $this->view->set('delivery_address', $address);

        // Return list of users who can authorise
        $po_obj = new DataObject('po_auth_summary');

        $po_obj->idField = 'username';
        $po_obj->identifierField = 'username';

        $cc = new ConstraintChain();

        $cc->add(new Constraint('order_number', '=', $order->order_number));

        $this->view->set('authorised_users', $po_obj->getAll($cc));

        // return lines grouped by glaccount_id and glcentre_id
        // return a list of users who can authorise each individual line
        $po_linesum_obj = new DataObject('po_linesum');

        $po_linesum_obj->idField = 'id';
        $po_linesum_obj->identifierField = 'order_number';

        $po_linesum_obj->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
        $po_linesum_obj->belongsTo('GLCentre', 'glcentre_id', 'glcentre');

        $po_linesum_col = new DataObjectCollection($po_linesum_obj);

        $sh = new SearchHandler($po_linesum_col, false);

        $sh->setFields(array(
            'id',
            'order_number',
            'glaccount_id',
            'glcentre_id',
            'value'
        ));

        $sh->addConstraint(new Constraint('order_number', '=', $order->order_number));

        $po_linesum_col->load($sh);

        $this->view->set('po_linesum', $po_linesum_col);

        $authorisers = array();

        foreach ($po_linesum_col as $key => $value) {

            $temp_arr = array();

            $authlist = new POAuthLimitCollection();

            $authlist->getAuthList($value->glaccount_id, $value->glcentre_id, $value->value);

            foreach ($authlist as $key1 => $value1) {
                $temp_arr[] = $value1->username;
            }

            $authorisers[$value->id] = implode(', ', $temp_arr);
        }

        $this->view->set('line_authorisers', $authorisers);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'purchase_ledger',
                'controller' => 'PLSuppliers',
                'action' => 'index'
            ),
            'tag' => 'view all suppliers'
        );

        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'new order'
        );

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view all requisitions/orders'
        );

        $sidebar->addList('Actions', $actions);

        $actions = array();

        $actions['viewcustomer'] = array(
            'link' => array(
                'module' => 'purchase_ledger',
                'controller' => 'PLSuppliers',
                'action' => 'view',
                'id' => $order->plmaster_id
            ),
            'tag' => 'view supplier'
        );

        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'plmaster_id' => $order->plmaster_id
            ),
            'tag' => 'new order'
        );

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index',
                'plmaster_id' => $order->plmaster_id
            ),
            'tag' => 'view requisitions/orders'
        );

        if ($order->type == 'R') {
            $actions['viewdetails'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'view',
                    'id' => $id
                ),
                'tag' => 'view requisition'
            );
        }

        // Get current username
        $user = getCurrentUser();

        // Should only be allowed to edit if you are the raiser, or you have authority
        if ($order->status == 'N' && ($this->authRequisition($order) == true || $order->raised_by == $user->username)) {
            $actions['editOrder'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'edit',
                    'id' => $id
                ),
                'tag' => 'Edit ' . $order->getField('type')->formatted
            );
        }
        if ($order->type == 'R' && $order->lines->count() > 0 && $this->authRequisition($order) == true) {
            $actions['authorise'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'authoriseOrder',
                    'id' => $id
                ),
                'tag' => 'Authorise Order'
            );
        }

        $sidebar->addList($supplier->name, $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function accrual()
    {
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['plmaster_id'])) {
            $s_data['plmaster_id'] = $this->_data['plmaster_id'];
        }

        if (isset($this->_data['order_number'])) {
            $s_data['order_number'] = $this->_data['order_number'];
        }

        if (! empty($this->_data['invoice_number'])) {
            $s_data['invoice_number'] = $this->_data['invoice_number'];
        }

        $this->setSearch('pordersSearch', 'accrual', $s_data);

        $this->view->set('clickaction', 'view');

        $poreceivedline = DataObjectFactory::Factory('POReceivedLine');

        if (isset($this->_data['selectAll'])) {
            if ($this->_data['selectAll'] == 'Select All') {
                foreach ($_SESSION['accrual']['data'] as $key => $value) {
                    $_SESSION['accrual']['data'][$key] = 'true';
                }

                $_SESSION['accrual']['count'] = count($_SESSION['accrual']['data']);

                $_SESSION['accrual']['text'] = 'Clear All';
            } else {
                foreach ($_SESSION['accrual']['data'] as $key => $value) {
                    $_SESSION['accrual']['data'][$key] = 'false';
                }

                $_SESSION['accrual']['count'] = 0;

                $_SESSION['accrual']['text'] = 'Select All';
            }
        } elseif (! isset($this->_data['page']) && ! isset($this->_data['orderby'])) {
            $_SESSION['accrual']['data'] = array();

            $cc = new ConstraintChain();
            $cc = $this->search->toConstraintChain();

            foreach ($poreceivedline->getAll($cc) as $key => $value) {
                $_SESSION['accrual']['data'][$key] = 'false';
            }

            $_SESSION['accrual']['count'] = 0;

            $_SESSION['accrual']['text'] = 'Select All';
        }

        $poreceivedline->setDefaultDisplayFields(array(
            'gr_number',
            'order_number',
            'delivery_note',
            'supplier',
            'received_date',
            'received_qty',
            'uom_name',
            'item_description',
            'net_value'
        ));

        parent::index(new POReceivedLineCollection($poreceivedline));

        if (isset($_SESSION['accrual'])) {
            $this->view->set('accrual', $_SESSION['accrual']);
        } else {
            $this->view->set('accrual', array());
        }
    }

    public function updateAccrual()
    {
        if ($this->_data['value'] == 'true') {
            $_SESSION['accrual']['data'][$this->_data['id']] = 'true';
            $_SESSION['accrual']['count'] ++;
        } else {
            $_SESSION['accrual']['data'][$this->_data['id']] = 'false';
            $_SESSION['accrual']['count'] --;
        }

        $this->view->set('value', $_SESSION['accrual']['count']);
        $this->setTemplateName('text_inner');
    }

    public function saveAccrual()
    {
        $flash = Flash::instance();

        $errors = array();

        foreach ($_SESSION['accrual']['data'] as $key => $value) {
            if ($value == 'false') {
                unset($_SESSION['accrual']['data'][$key]);
            }
        }

        if (POReceivedLine::accrueLine($_SESSION['accrual']['data'], $errors) === FALSE) {
            $flash->addErrors($errors);
        } else {
            $flash->addMessage('Selected GRN lines marked as accrued');

            unset($_SESSION['accrual']);
        }

        sendTo($this->name, 'index', $this->_modules);
    }

    public function authoriseOrder()
    {
        $data = array();
        $errors = array();
        $flash = Flash::Instance();

        $id = $this->_data['id'];
        $order = $this->_uses['POrder'];
        $order->load($id);

        if ($order->lines->count() > 0) {
            if ($this->authRequisition($order) == true) {
                // If all is well, set data
                $data['id'] = $this->_data['id'];
                $data['date_authorised'] = date(DATE_FORMAT);
                $data['authorised_by'] = EGS_USERNAME;
                $data['type'] = 'O';
                $data['status'] = 'N';

                $porder = DataObject::Factory($data, $errors, 'POrder');

                if ($porder) {
                    $result = $porder->save();

                    if ($result) {
                        $flash->addMessage('Order Authorised');
                        sendTo($this->name, 'view', $this->_modules, array(
                            'id' => $order->id
                        ));
                    }
                }
            } else {
                $flash->addError('You cannot authorise this requisition');
                sendTo($this->name, 'view', $this->_modules, array(
                    'id' => $order->id
                ));
            }
        } else {
            $flash->addError('No order lines to authorise');
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $order->id
            ));
        }

        $flash->addErrors($errors);
        $flash->addError('Failed to authorise order');

        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $order->id
        ));
    }

    public function viewByItems()
    {
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['stitem_id'];
        }

        if (isset($this->_data['prod_group_id'])) {
            $s_data['prod_group_id'] = $this->_data['prod_group_id'];
        }

        $this->setSearch('productlinesSearch', 'supplierItems', $s_data);

        // Get the list of items and required quantity
        $orders = new POrderCollection($this->_templateobject);

        $sh = $this->setSearchHandler($orders);

        $orders->getItems($sh);

        parent::index($orders, $sh);

        // Now get the balance for each item across all locations
        $on_order = 0;

        $items = array();

        foreach ($orders as $item) {
            $id = $item->id;

            $items[$id]['stitem'] = $item->stitem;
            $items[$id]['uom_name'] = $item->uom_name;
            $items[$id]['on_order'] = $item->on_order;

            $in_stock = 0;
            $required = 0;

            $wostructures = array();

            $stitem = DataObjectFactory::Factory('STItem');

            if ($stitem->load($id)) {
                $items[$id]['batch_size'] = $stitem->batch_size;
                $items[$id]['lead_time'] = $stitem->lead_time;

                $wostructures = $stitem->getWOStructures();

                $required = 0;

                foreach ($wostructures as $wostructure) {
                    $required += round($stitem->convertToUoM($wostructure->uom_id, $stitem->uom_id, $wostructure->outstandingQty()), $stitem->qty_decimals);
                }

                $sorders = new SOrderCollection();

                $cc = new ConstraintChain();

                $cc->add(new Constraint('stitem_id', '=', $id));

                $sh = new SearchHandler($sorders, false);

                $sorders->getItems($sh, $cc);

                $sorders->load($sh);

                foreach ($sorders as $order) {
                    $required += $order->required;
                }

                $in_stock = $stitem->currentBalance();
            }

            $shortfall = $required - ($item->on_order + $in_stock);

            if ($shortfall < 0) {
                $shortfall = 0;
            }

            $items[$id]['in_stock'] = $in_stock;
            $items[$id]['required'] = $required;
            $items[$id]['shortfall'] = $shortfall;
        }

        $this->view->set('orders', $items);
        $this->view->set('page_title', $this->getPageName('', 'View Items for Supply/Demand -'));

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'purchase_ledger',
                'controller' => 'PLSuppliers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );
        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'new order'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view requisitions/orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function viewByDates()
    {
        $id = $this->_data['id'];

        $cc = new ConstraintChain();

        $cc->add(new Constraint('stitem_id', '=', $id));

        $order = new POrderCollection($this->_templateobject);

        $orders = $order->getItemDates($cc);

        // Initialise variables
        $wostructures = array();

        $in_stock = 0;
        $shortfall = 0;
        $required = 0;

        $stitem = DataObjectFactory::Factory('STItem');

        // For the item, get a list of outstanding works orders and the current stock balance
        if ($stitem->load($id)) {
            $wostructures = $stitem->getWOStructures();
            $in_stock = $stitem->currentBalance();
            $this->view->set('stitem', $stitem);
        }

        // And finally update the orders with the projected stock balances
        $items = array();

        foreach ($orders as $row) {
            // Framework issue - renames first column to 'id'
            $key = $row->id;

            $items[$key]['stitem_id'] = $row->stitem_id;
            $items[$key]['uom_name'] = $row->uom_name;
            $items[$key]['on_order'] = $row->on_order;
            $items[$key]['in_stock'] = $in_stock;

            $in_stock += $row->on_order;

            // This needs to sum balances for the work orders that the selected stitem_id
            // is in a works order structure; i.e. need to get a list of orders not the stitem_id

            $items[$key]['required'] = $shortfall;
            $items[$key]['required'] -= $required;
            foreach ($wostructures as $wostructure) {
                if ($wostructure->required_by <= $key) {
                    $items[$key]['required'] += round($stitem->convertToUoM($wostructure->uom_id, $stitem->uom_id, $wostructure->outstandingQty()), $stitem->qty_decimals);
                }
            }

            // Get Sales Orders for this Item
            $sorder = new SOrderCollection();

            $cc = new ConstraintChain();

            $cc->add(new Constraint('stitem_id', '=', $id));
            $cc->add(new Constraint('due_date', '<', $key));

            $sorders = $sorder->getItemDates($cc);

            foreach ($sorders as $order) {
                $items[$key]['required'] += $order->required;
            }

            $required = $items[$key]['required'];
            $in_stock -= $required;

            $items[$key]['shortfall'] = 0;

            if ($required > $in_stock) {
                $items[$key]['shortfall'] = $required - $in_stock;
                $in_stock = 0;
                $shortfall = $items[$key]['shortfall'];
            }
        }

        $this->view->set('outstanding', $items);
        $this->view->set('page_title', $this->getPageName('', 'View Due Dates by Item for'));

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'purchase_ledger',
                'controller' => 'PLSuppliers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );
        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'new order'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view requisitions/orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function viewByWOrders()
    {
        $id = $this->_data['id'];

        // Now get the balance for each item across all saleable locations
        $wostructures = array();

        $stitem = DataObjectFactory::Factory('STItem');

        // For the item, get a list of outstanding works orders and the current stock balance
        if ($stitem->load($id)) {
            $wostructures = $stitem->getWOStructures();
        }

        $this->view->set('wostructures', $wostructures);
        $this->view->set('stitem', $stitem);
        $this->view->set('heading', $this->getPageName());

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'purchase_ledger',
                'controller' => 'PLSuppliers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );
        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'new order'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view requisitions/orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View Works Orders for Items with'));
    }

    public function getActions()
    {
        // Used by Ajax to return Product Lines list after selecting the Customer
        $receive_actions = WHAction::getReceiveActions();
        ;

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $receive_actions);
            $this->setTemplateName('select_options');
        } else {
            return $receive_actions;
        }
    }

    public function getDeliveryTerm($_plmaster_id = '')
    {
        if ($_plmaster_id == '') {
            $_plmaster_id = $this->_data['plmaster_id'];
        }

        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($_plmaster_id);

        if ($supplier->isLoaded()) {
            return $supplier->delivery_term_id;
        }

        return '';
    }

    public function getReceiveAction($_id = '')
    {
        // Used by Ajax to return Default Despatch Action after selecting the Supplier
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($_id);

        $receive_action = '';
        if ($supplier) {
            $receive_action = $supplier->receive_action;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $receive_action);
            $this->setTemplateName('text_inner');
        } else {
            return $receive_action;
        }
    }

    public function getNotes($_supplier_id = '')
    {
        // Used by Ajax to return Notes after selecting the Supplier
        if ($_supplier_id == '' && ! empty($this->_data['id'])) {
            $_supplier_id = $this->_data['id'];
        }

        $notes = new PartyNoteCollection();

        if ((! empty($_supplier_id))) {
            $supplier = DataObjectFactory::Factory('PLSupplier');

            $supplier->load($_supplier_id);

            $sh = new SearchHandler($notes, false);

            $sh->setFields(array(
                'id',
                'lastupdated',
                'note'
            ));

            $sh->setOrderby('lastupdated', 'DESC');

            $sh->addConstraint(new Constraint('note_type', '=', $this->module));
            $sh->addConstraint(new Constraint('party_id', '=', $supplier->companydetail->party_id));

            $notes->load($sh);
        }
        $this->view->set('no_ordering', true);
        $this->view->set('collection', $notes);

        return $this->view->fetch('datatable_inline');
    }

    public function printaction()
    {
        if (strtolower($this->_data['printaction']) == 'printorder') {
            if (! $this->loadData()) {
                $this->dataError();
                sendBack();
            }

            $order = $this->_uses[$this->modeltype];

            $supplier = DataObjectFactory::Factory('PLSupplier');

            $supplier->load($order->plmaster_id);

            $order_methods = $supplier->getEnumOptions('order_method');

            if (isset($order_methods[$supplier->order_method])) {
                $this->defaultprintaction = $order_methods[$supplier->order_method];
            }

            $this->view->set('email', $supplier->email_order());
        }

        parent::printAction();
    }

    public function orderAcknowledged()
    {
        $db = DB::Instance();
        $db->startTrans();

        $flash = Flash::Instance();

        $id = $this->_data['id'];

        $order = DataObjectFactory::Factory('POrder');

        $order->load($id);

        if ($order) {
            if (! $order->update($id, 'status', $order->acknowledgedStatus())) {
                $db->failTrans();
                $flash->addError('Failed to Acknowledge Order');
            } else {
                $flash->addMessage('Order Acknowledged');
            }
        }
        $db->CompleteTrans();

        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $id
        ));
    }

    public function updateStatus($data)
    {
        $db = DB::Instance();
        $db->startTrans();

        if ($data instanceof DataObjectCollection) {
            foreach ($data as $detailRow) {
                $order = DataObjectFactory::Factory('POrder');
                if (! $order->update($detailRow->id, 'status', $order->orderSentStatus())) {
                    $db->failTrans();
                    return false;
                }
                foreach ($detailRow->lines as $line) {
                    $orderline = DataObjectFactory::Factory('POrderLine');
                    if ($line->status == $orderline->newStatus() && ! $orderline->update($line->id, 'status', $orderline->awaitingDeliveryStatus())) {
                        $db->failTrans();
                        return false;
                    }
                }
            }
        } else {
            if (! $data->update($data->id, 'status', 'O')) {
                $db->failTrans();
                return false;
            }

            foreach ($data->lines as $line) {
                $orderline = DataObjectFactory::Factory('POrderLine');
                if ($line->status == $orderline->newStatus() && ! $orderline->update($line->id, 'status', $orderline->awaitingDeliveryStatus())) {
                    $db->failTrans();
                    return false;
                }
            }
        }

        return $db->CompleteTrans();
    }

    public function resetstatus()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $order = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        $errors = array();

        if (! $order) {
            $errors[] = 'Error getting order details';
        } else {
            $linestatuses = $order->getLineStatuses();

            if ($linestatuses['linecount'] > 0) {
                if (! $order->awaitingDelivery()) {
                    $errors[] = 'Cannot reset order - check order status';
                }

                if (! $order->allLinesAwaitingDelivery($linestatuses['count'])) {
                    $errors[] = 'Cannot reset order - check status on order lines';
                }
            }

            if (count($errors) > 0 || ! $order->update($this->_data['id'], 'status', $order->newStatus())) {
                $errors[] = 'Error reseting order status';
            } else {
                foreach ($order->lines as $line) {
                    if ($line->lineAwaitingDelivery()) {
                        if (! $line->update($line->id, 'status', $line->newStatus())) {
                            $errors[] = 'Error reseting order line status';
                            break;
                        }
                    }
                }
            }
        }
        if (count($errors) > 0) {
            $flash->addErrors($errors);
        } else {
            $flash->addMessage('Order status reset');
        }

        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $this->_data['id']
        ));
    }

    public function cancel_order()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $order = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        $errors = array();

        if ($order->type != 'R') {
            $flash->addError('Only Requisitions can be cancelled');
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->_data['id']
            ));
        }

        $db = DB::Instance();
        $db->startTrans();

        if (! $order->update($order->id, 'status', $order->cancelStatus())) {
            $errors[] = $db->ErrorMsg();
            $errors[] = 'Failed to cancel ' . $order->getFormatted('type');
        } else {
            foreach ($order->lines as $line) {
                if ($line->status != $line->cancelStatus()) {
                    if (! $line->update($line->id, array(
                        'status',
                        'glaccount_centre_id'
                    ), array(
                        $line->cancelStatus(),
                        'null'
                    ))) {
                        $errors[] = $db->ErrorMsg();
                        $errors[] = 'Failed to cancel ' . $order->getFormatted('type') . ' line';
                        break;
                    }
                }
            }
        }

        if (count($errors) > 0) {
            $db->FailTrans();
            $flash->addErrors($errors);
        } else {
            $flash->addMessage($order->getFormatted('type') . ' cancelled');
        }

        $db->CompleteTrans();
        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $this->_data['id']
        ));
    }

    public function createinvoice()
    {
        $s_data = array();

        // Set context from calling module

        if (isset($this->_data['plmaster_id'])) {
            $s_data['plmaster_id'] = $this->_data['plmaster_id'];
        } else {
            $s_data['plmaster_id'] = 0;
        }

        if (isset($this->_data['stitem_id'])) {
            $s_data['stitem'] = $this->_data['stitem'];
        } else {
            $s_data['stitem'] = '';
        }

        if (isset($this->_data['order_id'])) {
            $s_data['order_id'] = $this->_data['order_id'];
        } else {
            $s_data['order_id'] = '0';
        }

        $this->setSearch('pordersSearch', 'receivedOrders', $s_data);

        $search_plmaster_id = $this->search->getValue('plmaster_id');
        $search_order_id = $this->search->getValue('order_id');

        $this->view->set('search_plmaster_id', $search_plmaster_id);
        $this->view->set('search_stitem', $this->search->getValue('stitem'));
        $this->view->set('search_order_id', $search_order_id);

        $this->printaction = '';

        if (! empty($search_plmaster_id) || ! empty($search_order_id)) {
            // must select a supplier or an order
            $orderlines = new POReceivedLineCollection();

            $sh = $this->setSearchHandler($orderlines);

            parent::index($orderlines, $sh);
        }

        $this->view->set('page_title', $this->getPageName('Create Purchase Invoice from GRN', ''));
        $this->view->set('invoicedate', date(DATE_FORMAT));

        // get data from persistent selection session

        $key = 'purchase_order-porders-createinvoice';

        $selected_rows = array();
        $net_total = 0; // this should be a number... not a string

        if (isset($_SESSION['persistent_selection'][$key])) {
            if (isset($this->_data['Search']['clear']) || isset($this->_data['Search']['search'])) {
                unset($_SESSION['persistent_selection'][$key]);
            } else {
                $selected_rows = $_SESSION['persistent_selection'][$key];
                // if the array isn't empty, loop through the values and increment the total
                if (! empty($selected_rows)) {
                    foreach ($selected_rows as $_key => $value) {
                        if ($_key != '_total') {
                            $net_total += $value;
                        }
                    }
                }
            }
        }

        $this->view->set('selected_rows', $selected_rows);
        $this->view->set('total', number_format($net_total, 2, '.', ''));
    }

    public function saveinvoice()
    {
        $flash = Flash::Instance();

        $errors = array();

        $db = DB::Instance();
        $db->startTrans();

        if (! $this->checkParams('POReceivedLine')) {
            sendTo($this->name, 'index', $this->_modules);
        }

        // the key we'll use to get the data from the persistent selection array
        $key = 'purchase_order-porders-createinvoice';

        // get the data, but remove the _total item... it it exists
        $lines_data = $_SESSION['persistent_selection'][$key];
        unset($lines_data['_total']);

        $porder_descriptions = array();
        $pinvoicelines = array();
        $selected_lines = false;
        $line_number = 1;

        if (is_array($lines_data) && count($lines_data) > 0) {

            // Get the selected GRN lines
            $selected_lines = true;

            $poreceivedline = DataObjectFactory::Factory('POReceivedLine');
            $poreceivedlines = new POReceivedLineCollection($poreceivedline);

            $sh = new SearchHandler($poreceivedlines, FALSE);

            $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', array_keys($lines_data)) . ')'));

            $rows = $poreceivedlines->load($sh, null, RETURN_ROWS);

            if ($rows) {

                foreach ($rows as $received_line) {
                    // Create an invoice line for each selected GRN line
                    $supplier = $received_line['plmaster_id'];
                    $porderline = DataObjectFactory::Factory('POrderLine');
                    $porderline->load($received_line['orderline_id']);

                    if ($porderline->isLoaded()) {
                        foreach ($porderline->getFields() as $key => $value) {
                            $pinvoiceline[$key] = $porderline->$key;
                        }

                        foreach ($porderline->audit_fields as $field) {
                            unset($pinvoiceline[$field]);
                        }

                        $pinvoiceline['id'] = '';
                        $pinvoiceline['line_number'] = $line_number ++;
                        $pinvoiceline['purchase_order_id'] = $porderline->order_id;
                        $pinvoiceline['order_line_id'] = $porderline->id;
                        $pinvoiceline['purchase_qty'] = $received_line['received_qty'];
                        $pinvoiceline['purchase_price'] = $porderline->price;
                        $pinvoiceline['net_value'] = $lines_data[$received_line['id']];
                        $pinvoiceline['delivery_note'] = $received_line['delivery_note'];
                        $pinvoiceline['gr_number'] = $received_line['gr_number'];
                        $pinvoiceline['grn_id'] = $received_line['id'];
                        $pinvoiceline['glaccount_centre_id'] = $porderline->glaccount_centre_id;

                        // $pinvoiceline['description'] is pulled through from po_product_line
                        // If the order line is a free format (not based on a product line)
                        // then there will be no product line description so use the order line item description
                        if (empty($pinvoiceline['description'])) {
                            $pinvoiceline['description'] = $pinvoiceline['item_description'];
                        }

                        $pinvoicelines[] = $pinvoiceline;

                        // Now check order lines against received items
                        // Mark as invoiced if all received lines for the order line have been invoiced
                        // Mark order as invoiced if all order lines have been invoiced
                        $cc = new ConstraintChain();
                        $cc->add(new Constraint('orderline_id', '=', $porderline->id));
                        $cc->add(new Constraint('status', '!=', $poreceivedline->cancelStatus()));
                        $cc->add(new Constraint('invoice_id', 'is not', 'NULL'));

                        $invoiced_qty = $poreceivedline->getSum('received_qty', $cc) + $received_line['received_qty'];

                        if ($porderline->del_qty == $invoiced_qty && $porderline->os_qty == 0) {
                            $porderline->status = $porderline->invoiceStatus();
                            $porderline->glaccount_centre_id = null;
                            $porderline->save();
                        }

                        // Now check all order lines for the order
                        // Mark order as invoiced if all order lines have been invoiced
                        $order = DataObjectFactory::Factory('POrder');
                        $order->load($porderline->order_id);

                        $porder_id = $order->id;
                        $porder_order_number = $order->order_number;
                        $porder_descriptions[$order->id] = $order->description;

                        if (count($errors) == 0) {
                            if (! $order->save()) {
                                $errors[] = 'Failed to update order header ' . $db->ErrorMsg();
                            }
                        }
                    }
                }
            }
        }

        if ($selected_lines === false) {
            $errors[] = 'No order lines selected';
        }

        if (count($pinvoicelines) > 0) {
            // Create the invoice header for the invoice lines
            $pinvoiceheader = array();

            $pinvoiceheader['id'] = '';
            $pinvoiceheader['purchase_order_id'] = $porder_id;
            $pinvoiceheader['purchase_order_number'] = $porder_order_number;
            $pinvoiceheader['ext_reference'] = $this->_data['ext_reference'];
            $pinvoiceheader['plmaster_id'] = $supplier;
            $pinvoiceheader['transaction_type'] = 'I';
            $pinvoiceheader['invoice_date'] = $this->_data['invoice_date'];
            $pinvoiceheader['description'] = implode(', ', $porder_descriptions);
            // If there is a single order save the project & task from the order header
            if (count($porder_descriptions)==1){
                $pinvoiceheader['project_id'] = $order->project_id;
                $pinvoiceheader['task_id'] = $order->task_id;
            };
            $pinvoice = PInvoice::Factory($pinvoiceheader, $errors);

            // Save the header
            if ($pinvoice) {
                $result == false;
                $result = $pinvoice->save();
                if (! $result) {
                    $errors[] = 'Failed to create invoice';
                } else {
                    // Link each invoice line to the header and save
                    foreach ($pinvoicelines as $line_data) {

                        $pinvoiceline = PInvoiceLine::Factory($pinvoice, $line_data, $errors);

                        if ($pinvoiceline) {

                            $result = $pinvoiceline->save();

                            if (! $result) {
                                $errors[] = 'Failed to create invoice line';
                                break;
                            }
                        }
                    }
                    // Save header again to update status/values
                    $result = $pinvoice->save();

                    if (! $result) {
                        $errors[] = 'Failed to update invoice';
                    }
                }
            }
        }

        if ($result) {
            // Update the selected GRN lines to tag with the invoice details
            // Note: the GRN status is not changed until the invoice is posted to the GL
            // because the GL posting will depend on whether the GRN has been accrued or not
            if (! $poreceivedlines->update(array(
                'invoice_number',
                'invoice_id'
            ), array(
                $pinvoice->invoice_number,
                $pinvoice->id
            ), $sh)) {
                // update failed or no lines updated
                $errors[] = 'Error updating GRN lines with invoice number';
                $result = FALSE;
            }
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $flash->addError('Invoice creation failed');
            $db->failTrans();
            $db->CompleteTrans();
            $this->refresh();
        } else {
            $flash->addMessage('Invoice Created');

            $db->CompleteTrans();

            // clear persistent selected values
            unset($_SESSION['persistent_selection']['purchase_order-porders-createinvoice']);

            sendTo('pinvoices', 'view', 'purchase_invoicing', array(
                'id' => $pinvoice->id
            ));
        }
    }

    public function match_invoice()
    {
        $s_data = array();

        // Set context from calling module

        if (isset($this->_data['plmaster_id'])) {
            $s_data['plmaster_id'] = $this->_data['plmaster_id'];
        } else {
            $s_data['plmaster_id'] = 0;
        }

        if (isset($this->_data['stitem_id'])) {
            $s_data['stitem'] = $this->_data['stitem'];
        } else {
            $s_data['stitem'] = '';
        }

        if (isset($this->_data['order_id'])) {
            $s_data['order_id'] = $this->_data['order_id'];
        } else {
            $s_data['order_id'] = '0';
        }

        $this->setSearch('pordersSearch', 'receivedOrders', $s_data);

        $this->view->set('search_plmaster_id', $this->search->getValue('plmaster_id'));
        $this->view->set('search_stitem', $this->search->getValue('stitem'));
        $this->view->set('search_order_id', $this->search->getValue('order_id'));

        $orderlines = new POReceivedLineCollection();

        $sh = $this->setSearchHandler($orderlines);

        parent::index($orderlines, $sh);

        $this->view->set('page_title', $this->getPageName('Match Purchase Invoice to GRN', ''));
        $this->view->set('invoicedate', date(DATE_FORMAT));

        // get data from persistent selection session

        $key = 'purchase_order-porders-matchinvoice';

        $selected_rows = array();

        $net_total = 0; // this should be a number... not a string

        if (isset($_SESSION['persistent_selection'][$key])) {
            if (isset($this->_data['Search']['clear']) || isset($this->_data['Search']['search'])) {
                unset($_SESSION['persistent_selection'][$key]);
            } else {
                $selected_rows = $_SESSION['persistent_selection'][$key];
            }
        }

        $this->view->set('selected_rows', $selected_rows);
        $this->view->set('total', number_format($net_total, 2, '.', ''));
    }

    public function saveMatchInvoice()
    {
        $flash = Flash::Instance();

        $errors = array();

        $db = DB::Instance();
        $db->startTrans();

        if (! $this->checkParams('POReceivedLine')) {
            sendTo($this->name, 'index', $this->_modules);
        }

        // the key we'll use to get the data from the persistent selection array
        $key = 'purchase_order-porders-matchinvoice';

        // get the data, but remove the _total item... it it exists
        $lines_data = $_SESSION['persistent_selection'][$key];

        $selected_lines = false;

        if (count($lines_data) == 0) {
            $errors[] = 'No invoices selected';
        } else {
            foreach ($lines_data as $id => $invoice_line_id) {
                $selected_lines = true;
                $poreceivedline = DataObjectFactory::Factory('POReceivedLine');
                $poreceivedline->load($id);

                if ($poreceivedline->isLoaded()) {
                    // Load/Update PI Line
                    $pi_line = DataObjectFactory::Factory('PInvoiceLine');
                    $pi_line->load($invoice_line_id);

                    if (! $pi_line->isLoaded()) {
                        $errors[] = 'Failed to load invoice line';
                        break;
                    }

                    $pi_line->purchase_order_id = $poreceivedline->order_id;
                    $pi_line->delivery_note = $poreceivedline->delivery_note;

                    if (! $pi_line->save()) {
                        $errors[] = 'Failed to update invoice line';
                        break;
                    }
                    // Load PI Header
                    $pi_header = DataObjectFactory::Factory('PInvoice');
                    $pi_header->load($pi_line->invoice_id);

                    if (! $pi_header->isLoaded()) {
                        $errors[] = 'Failed to load invoice header';
                        break;
                    }
                    // Update PO Received Line status to Invoiced
                    $poreceivedline->invoice_id = $pi_header->id;
                    $poreceivedline->invoice_number = $pi_header->invoice_number;
                    $poreceivedline->status = $poreceivedline->invoiceStatus();

                    if (! $poreceivedline->save()) {
                        $errors[] = 'Failed to update grn line';
                        break;
                    }
                    // Now get the Order Line details for the Goods Received line
                    $porderline = DataObjectFactory::Factory('POrderLine');

                    if (! $porderline->update($poreceivedline->orderline_id, 'status', $porderline->invoiceStatus())) {
                        $errors[] = 'Failed to update order line';
                        break;
                    }
                    // Now save the order header
                    // This will set the order as invoiced if all order lines have been invoiced
                    $order = DataObjectFactory::Factory('POrder');
                    $order->load($poreceivedline->order_id);

                    if (! $order->isLoaded() || ! $order->save()) {
                        $errors[] = 'Failed to update order header';
                        break;
                    }
                }
            }
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $flash->addError('Invoice matching failed');
            $db->failTrans();
            $db->CompleteTrans();
            $this->refresh();
        } else {
            $flash->addMessage('Invoice(s) Matched');
            $db->CompleteTrans();

            // clear persistent selected values
            unset($_SESSION['persistent_selection']['purchase_order-porders-matchinvoice']);
            sendTo($this->name, 'match_invoice', $this->_modules);
        }
    }

    public function update_selected_invoices()
    {

        // the key to identify the page we're on
        $key = 'purchase_order-porders-createinvoice';

        // save selected row
        if ($this->_data['selected'] == 'true') {
            $_SESSION['persistent_selection'][$key][$this->_data['id']] = $this->_data['value'];
        } else {
            unset($_SESSION['persistent_selection'][$key][$this->_data['id']]);
        }

        exit();
    }

    public function update_matched_invoices()
    {

        // the key to identify the page we're on
        $key = 'purchase_order-porders-matchinvoice';

        // save selected row
        if (! empty($this->_data['value'])) {
            $_SESSION['persistent_selection'][$key][$this->_data['id']] = $this->_data['value'];
        } else {
            unset($_SESSION['persistent_selection'][$key][$this->_data['id']]);
        }

        exit();
    }

    public function authRequisition($order)
    {
        $porderlines = new POrderLineCollection();

        $porderlines->getAuthSummary($order->id);

        $user = getCurrentUser();

        if ($user->username == $order->checkAuthLimits($porderlines)) {
            return true;
        } else {
            return false;
        }
    }

    public function grn_write_off()
    {
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['plmaster_id'])) {
            $s_data['plmaster_id'] = $this->_data['plmaster_id'];
        }

        if (isset($this->_data['order_number'])) {
            $s_data['order_number'] = $this->_data['order_number'];
        }

        if (! empty($this->_data['invoice_number'])) {
            $s_data['invoice_number'] = $this->_data['invoice_number'];
        }

        $this->setSearch('pordersSearch', 'grn_write_off', $s_data);

        $this->view->set('clickaction', 'view');

        parent::index(new POReceivedLineCollection());

        if (isset($_SESSION['grn_write_off'])) {
            $this->view->set('grn_write_off', $_SESSION['grn_write_off']);
        } else {
            $this->view->set('grn_write_off', array());
        }
    }

    public function update_grn_write_off()
    {
        if ($this->_data['value'] == 'true') {
            $_SESSION['grn_write_off'][$this->_data['id']] = true;
        } else {
            unset($_SESSION['grn_write_off'][$this->_data['id']]);
        }
    }

    public function save_grn_write_off()
    {
        $flash = Flash::instance();

        $errors = array();

        if (POReceivedLine::accrueLine($_SESSION['grn_write_off'], $errors, TRUE) === FALSE) {
            $flash->addErrors($errors);
        } else {
            $flash->addMessage('Selected GRN lines written off');

            unset($_SESSION['grn_write_off']);
        }

        sendTo($this->name, 'index', $this->_modules);
    }

    /* output functions */

    /**
     * printOrder
     *
     * @param string $status
     * @param string $report
     *            Name of the Report Definition to Use
     */
    public function printOrder($status = 'generate', $report = 'PurchaseOrder')
    {

        // load the model
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        if (isset($this->_data['report'])) {
            $reportdef = new ReportDefinition();
            $reportdef->loadBy('name', $this->_data['report']);
            // If the report definition exists, then use it
            if ($reportdef->isLoaded()) {
                $report = $this->_data['report'];
            }
        }

        $order = $this->_uses[$this->modeltype];

        // build options array
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => ''
            ),
            'filename' => 'PO' . $order->order_number,
            'report' => $report
        );

        $order = $this->_uses[$this->modeltype];

        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($order->plmaster_id);

        $order_methods = $supplier->getEnumOptions('order_method');

        if (isset($order_methods[$supplier->order_method])) {
            $options['default_print_action'] = strtolower($order_methods[$supplier->order_method]);
        }

        $options['email_subject'] = '"Purchase Order ' . $order->order_number . '"';
        $options['email'] = $supplier->email_order();

        if (strtolower($status) === "dialog") {
            // show the main dialog
            // pick up the options from above, use these to shape the dialog
            return $options;
        }

        /* generate document */

        // construct extra array
        $extra = array();

        // set company name
        $extra['company_name'] = $this->getCompanyName();

        // set date
        $extra['date'] = date(DATE_FORMAT);

        // set company address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());
        $extra['company_address'] = $company_address;

        // set the company details
        $extra['company_details'] = $this->getCompanyDetails();

        // set document details
        $document_reference = array();
        $document_reference[]['line'] = array(
            'label' => 'Order Date',
            'value' => un_fix_date($order->order_date)
        );
        $document_reference[]['line'] = array(
            'label' => 'Order Number',
            'value' => $order->order_number
        );
        $document_reference[]['line'] = array(
            'label' => 'Our Reference',
            'value' => $order->our_reference
        );
        $extra['document_reference'] = $document_reference;

        // load the supplier
        $supplier = DataObjectFactory::Factory('PLSupplier');
        $supplier->load($order->plmaster_id);

        // set supplier address
        $supplier_address = array(
            'title' => 'To:',
            'name' => $supplier->name
        );
        $supplier_address += $this->formatAddress($supplier->getBillingAddress());
        $extra['supplier_address'] = $supplier_address;

        // load the associated sales order if set
        if ($order->sales_order_id > 0) {
            $sorder = DataObjectFactory::Factory('SOrder');
            $sorder->load($order->sales_order_id);
        }

        // set delivery address
        if ($order->use_sorder_delivery == 't' && $order->sales_order_id > 0 && $sorder->isLoaded()) {
            $delivery_address = array(
                'title' => 'Delivery Address:',
                'name' => $sorder->customer
            );
            $delivery_address += $this->formatAddress($sorder->getDeliveryAddress());
        } else {
            $delivery_address = array(
                'title' => 'Delivery Address:',
                'name' => $extra['company_name']
            );
            $delivery_address += $this->formatAddress($order->getDeliveryAddress());
        }

        $extra['delivery_address'] = $delivery_address;

        // set customer account and order number if a sales order is associated with the PO
        if (isset($sorder) && $sorder->isLoaded()) {
            $extra['customer_number'] = $sorder->customerdetails->accountnumber();
            $extra['sales_order_number'] = $sorder->order_number;
        }

        // set billing address
        $billing_address = array(
            'title' => 'Billing Address:',
            'name' => $extra['company_name']
        );
        $billing_address += $this->formatAddress($this->getCompanyAddress('', '', 'payment'));
        $extra['billing_address'] = $billing_address;

        $orderline = DataObjectFactory::Factory('POrderLine');
        $sh = new SearchHandler(new POrderLineCollection($orderline), false);
        $sh->addConstraint(new Constraint('status', '!=', $orderline->cancelStatus()));
        $order->addSearchHandler('lines', $sh);

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $order,
            'extra' => $extra,
            'relationship_whitelist' => array(
                'lines'
            )
        ));

        // execute the print output function, echo the returned json for jquery
        $this->_data['print']['refresh_page'] = true;

        $json_response = $this->generate_output($this->_data['print'], $options);

        // decode response, if it was successful update the print count
        $response = json_decode($json_response, TRUE);

        if ($response['status'] === TRUE && $report == 'PurchaseOrder' && ! self::updateStatus($order)) {

            // if the print was successful but the update wasn't...
            $response['message'] .= "<br /><span style='color: red; font-weight: bold;'>Failed to update Purchase Order status</span>";

            echo json_encode($response);
            exit();
        }

        echo $json_response;
        exit();
    }

    public function printOrderList($status = 'generate')
    {

        // build options array
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'filename' => 'POrderList' . fix_date(date(DATE_FORMAT)),
            'report' => 'PurchaseOrderList'
        );

        if (strtolower($status) == "dialog") {
            return $options;
        }

        // load the model
        if ($this->_data['type'] == 'received') {
            $polines = new POrderLineCollection();
            $sh = new SearchHandler($polines, false);
            $sh->addConstraint(new Constraint('type', '=', 'O'));
        } else {
            $porders = new POrderCollection($this->_templateobject);
            $sh = new SearchHandler($porders, false);
            $sh->addConstraint(new Constraint('type', '=', 'O'));
        }

        // Construct search handler
        switch ($this->_data['type']) {
            case ('new'):
                $sh->addConstraint(new Constraint('status', '=', 'N'));
                $sh->setOrderby('order_number');
                break;
            case ('overdue'):
                $sh->addConstraint(new Constraint('status', ' NOT IN', "('I', 'R', 'X')"));
                $sh->addConstraint(new Constraint('due_date', '<', Constraint::TODAY));
                $sh->setOrderby(array(
                    'supplier',
                    'due_date'
                ), array(
                    'ASC',
                    'ASC'
                ));
                break;
            case ('outstanding'):
                $sh->addConstraint(new Constraint('status', ' NOT IN', "('I', 'R', 'X')"));
                $sh->setOrderby('order_number');
                break;
                break;
        }

        $porders->load($sh);

        $extra = array(
            'title' => prettify($this->_data['type']) . ' Purchase Orders as at ' . un_fix_date(fix_date(date(DATE_FORMAT)))
        );

        if (strtolower($this->_data['lines']) == 'y') {
            $extra['showlines'] = true;
            $relationship_whitelist = array(
                'lines'
            );
        } else {
            $relationship_whitelist = array();
        }

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $porders,
            'extra' => $extra,
            'relationship_whitelist' => $relationship_whitelist
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    /* consolodation functions */
    public function getSupplierData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_id = $this->_data['id'];
        $_plmaster_id = $this->_data['plmaster_id'];
        $_product_search = $this->_data['product_search'];

        $receive_action = $this->getReceiveAction($_id);
        $output['receive_action'] = array(
            'data' => $receive_action,
            'is_array' => is_array($receive_action)
        );

        $notes = $this->getNotes($_id);
        $output['notes'] = array(
            'data' => $notes,
            'is_array' => is_array($notes)
        );

        // get default delivery term for supplier
        $supplier_term = $this->getDeliveryTerm($_plmaster_id);
        $output['delivery_term'] = array(
            'data' => $supplier_term,
            'is_array' => is_array($supplier_term)
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false

        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    /* protected functions */
    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName(($base) ? $base : 'purchase_orders', $action);
    }

    /* private functions */
    private function order_details()
    {
        $porder = $this->_uses[$this->modeltype];

        if (! isset($this->_data[$porder->idField]) && ! isset($this->_data['order_number'])) {
            $this->dataError();
            sendBack();
        }

        if (isset($this->_data[$porder->idField])) {
            $porder->load($this->_data[$porder->idField]);
        } else {
            $porder->loadBy('order_number', $this->_data['order_number']);
        }

        if (isset($this->_data['updatetype'])) {
            $this->view->set('updatetype', $this->_data['updatetype']);
        }

        $address = DataObjectFactory::Factory('Companyaddress');

        if ($porder->del_address_id) {
            $address = $address->load($porder->del_address_id);
        }

        $this->view->set('delivery_address', $address);

        return $porder;
    }

    public function getTaskList($_project_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['project_id'])) {
                $_project_id = $this->_data['project_id'];
            }
        }

        $tasks = $this->getOptions($this->_templateobject, 'task_id', '', '', '', array(
            'project_id' => $_project_id
        ));

        if (isset($this->_data['ajax'])) {
            echo $tasks;
            exit();
        }

        return $tasks;
    }
}

// End of PordersController
