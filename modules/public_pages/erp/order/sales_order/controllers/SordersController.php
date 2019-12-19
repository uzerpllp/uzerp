<?php

/**
 *	Sales Order Controller
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
class SordersController extends printController
{

    use SOactionAllowedOnStop;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->uses(DataObjectFactory::Factory('SOrderLine'), FALSE);
        $this->_templateobject = DataObjectFactory::Factory('SOrder');
        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $this->view->set('clickaction', 'view');

        $s_data = array();

        // so set context from calling module
        if (isset($this->_data['slmaster_id'])) {
            $s_data['slmaster_id'] = $this->_data['slmaster_id'];
        }

        if (isset($this->_data['status'])) {
            $s_data['status'] = $this->_data['status'];
        }

        if (isset($this->_data['due_date'])) {
            $s_data['due_date']['from'] = un_fix_date($this->_data['due_date']);
            $s_data['due_date']['to'] = un_fix_date($this->_data['due_date']);
        }

        $this->setSearch('sordersSearch', 'useDefault', $s_data);

        parent::index(new SOrderCollection($this->_templateobject));

        $today = fix_date(date(DATE_FORMAT));

        $this->view->set('today', $today);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        foreach ($this->_templateobject->getEnumOptions('type') as $key => $description) {
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

        $actions['availabilityByItems'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'viewByItems'
            ),
            'tag' => 'view_availabilty_by_items'
        );
        $actions['availability'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'viewByOrders'
            ),
            'tag' => 'view_availabilty_by_orders'
        );

        if (isset($this->_data['lines']) && $this->_data['lines'] == 'show') {
            $this->_templateName = $this->getTemplateName('revieworders');
            $actions['vieworders'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                ),
                'tag' => 'view_orders (without lines)'
            );
        } else {
            $actions['vieworders'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index',
                    'lines' => 'show'
                ),
                'tag' => 'view_orders (with lines)'
            );
        }

        $actions['print'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'filename' => 'OrderList_' . fix_date(date(DATE_FORMAT)),
                'printaction' => 'printOrderList'
            ),
            'tag' => 'print_orders'
        );

        $actions['invoice'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'select_for_invoicing'
            ),
            'tag' => 'invoice_despatched_orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }


    /**
     * Cancel open lines and copy to a new order
     *
     * Ultimately returns status 400 failure,
     * or redirect url on success. see: lib::sendTo().
     *
     * Requires dialog='' in calling parameters (see: lib::sendTo()).
     */
    public function move_new_lines()
    {
        // Only POST requests with an XHR header are allowed
        $this->checkRequest(['post'], true);

        $this->make_clone($clone_status='new');
    }


    /**
     * Copy all lines to a new order
     */
    public function clone_order()
    {
        $this->make_clone();
    }


    /**
     * Copy current sales order lines to a new order
     *
     * The default is to copy all lines at any status, copied lines
     * will have status 'New' on the new order. If $clone_status is 'new',
     * then lines with status 'New' are cancelled on the current order and
     * copied to a new order.
     *
     * @param string $line_status all|new
     */
    private function make_clone($clone_status='all')
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

        $linestatuses = $order->getLineStatuses();
        $linestatus = $linestatuses['count'];

        // Prevent the order from being cloned if the customer is on stop
        if ($this->actionAllowedOnStop($order->customerdetails) === FALSE) {
            $flash->addError('Cannot save as new');
            sendBack();
        }

        if ($clone_status == 'new' && !$order->someLinesNew($linestatus)){
            $flash->addError('No open lines to copy');
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
                case 'despatch_date':
                case 'del_qty':
                    break;
                case 'ext_reference':
                    $data[$this->modeltype][$fieldname] = '';
                    break;
                case 'status':
                    $data[$this->modeltype][$fieldname] = $order->newStatus();
                    break;
                default:
                    $data[$this->modeltype][$fieldname] = $order->$fieldname;
            }
        }

        if (! empty($this->_data['type'])) {
            $data[$this->modeltype]['type'] = $this->_data['type'];
        }

        $line_count = 0;
        $skipped_lines = [];
        foreach ($order->lines as $orderline) {
            $line_count ++;
            //Only copy lines with 'New' status
            if ($orderline->status != $order->newStatus() && $clone_status == 'new' && $data[$this->modeltype]['type'] == 'O') {
                if ($line_count > 0){
                    $line_count = $line_count - 1;
                }
                $skipped_lines[] = $orderline->line_number;
                continue;
            }
            $modelname = get_class($orderline);
            foreach ($orderline->getFields() as $fieldname => $field) {
                switch ($fieldname) {
                    case $orderline->idField:
                    case 'created':
                    case 'createdby':
                    case 'lastupdated':
                    case 'alteredby':
                    case 'due_despatch_date':
                    case 'due_delivery_date':
                    case 'actual_despatch_date':
                    case 'delivery_note':
                        break;
                    case 'del_qty':
                        $data[$modelname][$fieldname][$line_count] = 0;
                    case 'order_id':
                        $data[$modelname][$fieldname][$line_count] = '';
                        break;
                    case 'status':
                        $data[$modelname][$fieldname][$line_count] = $orderline->newStatus();
                        break;
                    case 'line_number':
                        $data[$modelname][$fieldname][$line_count] = $line_count;
                        break;
                    case 'productline_id':
                        if (! is_null($orderline->productline_id)) {
                            $productline = DataObjectFactory::Factory('SOProductLine');
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
        }

        //Cancel lines on the current order with 'New' status
        if ($clone_status == 'new'){
            $db = DB::Instance();
            $db->startTrans();

            foreach ($order->lines as $line) {
                if ($line->status == $line->newStatus() && !in_array($line->line_number, $skipped_lines)) {
                    if (! $line->update($line->id, 'status', $line->cancelStatus())) {
                        $errors[] = $db->ErrorMsg();
                        $errors[] = 'Failed to cancel ' . $order->getFormatted('type') . ' line';
                        break;
                    }
                }
            }

            if (count($errors) > 0) {
                $db->FailTrans();
                $flash->addErrors($errors);
            } else {
                $order->save();
                $flash->addMessage('Open ' . strtolower($order->getFormatted('type')) . ' lines cancelled');
            }
            $db->CompleteTrans();
        }

        //Make the new order
        if (count($errors) == 0) {
            $result = $order->save_model($data);
        }

        if (isset($result) and $result !== FALSE) {
            switch ($this->_data['type']) {
                case 'Q':
                    $doctype = 'sales quote';
                    break;
                case 'T':
                    $doctype = 'sales template';
                    break;
                default:
                    $doctype = 'sales order';
            }
            $flash->addMessage("New ${doctype} saved");
            sendTo($this->name, 'view', $this->_modules, array(
                $order->idField => $result['internal_id']
            ));
        }

        sendBack();
    }

    public function delete()
    {
        $flash = Flash::Instance();

        parent::delete($this->modeltype);

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function _new()
    {
        parent::_new();

        // Get the OrderObject - if loaded, this is an edit
        $sorder = $this->_uses[$this->modeltype];

        // Prevent changes to the order if the customer is on stop
        if ($sorder->isLoaded() and ! $this->actionAllowedOnStop($sorder->customerdetails)) {
            $flash = Flash::Instance();
            $flash->addError($sorder->getFormatted('type') . ' cannot be changed');
            sendBack();
        }

        // get customer list
        if ($sorder->isLoaded() && $sorder->net_value != 0) {
            $customers = array(
                $sorder->slmaster_id => $sorder->customer
            );
        } else {
            $customers = $this->getOptions($this->_templateobject, 'slmaster_id', 'getOptions', 'getOptions', array(
                'use_collection' => true
            ));
        }

        if (isset($this->_data['type'])) {
            $sorder->type = $this->_data['type'];
        }

        // get the default/current selected customer
        if (isset($this->_data['slmaster_id'])) {
            // this is set if there has been error and we are redisplaying the screen
            $default_customer = $this->_data['slmaster_id'];
            $customer = $this->getCustomer($default_customer);
        } elseif (isset($this->_data[$this->modeltype]['slmaster_id'])) {
            // this is set if there has been error and we are redisplaying the screen
            $default_customer = $this->_data[$this->modeltype]['slmaster_id'];
        } else {
            if (! $sorder->isLoaded()) {
                $default_customer = $this->getDefaultValue($this->modeltype, 'slmaster_id', '');
            } else {
                $default_customer = $sorder->slmaster_id;
            }
        }

        if (empty($default_customer)) {
            $default_customer = key($customers);
        }

        $customer = $this->getCustomer($default_customer);

        // Prevent the new order action for customers on stop but allow the form
        // to be shown when the slmaster_id is not set. Otherwise, if the default
        // customer is on stop, then the form will never display.
        if (! $this->actionAllowedOnStop($customer) and isset($this->_data['slmaster_id'])) {
            $flash = Flash::Instance();

            // Assume it will be a new order if the order object has no type
            $message_type = "order";
            if (strtolower($sorder->getFormatted('type')) != '') {
                $message_type = strtolower($sorder->getFormatted('type'));
            }

            $flash->addError("Cannot add new ${message_type}, customer on stop");
            sendBack();
        }

        $this->view->set('company_id', $customer->company_id);

        if (! $sorder->isLoaded()) {
            $sorder->slmaster_id = $default_customer;
            // get delivery term for default customer
            $this->view->set('customer_term', $this->getDeliveryTerm($default_customer));
        }
        $this->view->set('selected_customer', $default_customer);

        // get people based on customer
        $people = $this->getPeople($default_customer);

        if (isset($this->_data[$this->modeltype]['person_id'])) {
            // this is set if there has been error and we are redisplaying the screen
            $default_person = $this->_data[$this->modeltype]['person_id'];
        } elseif (isset($this->_data['person_id'])) {
            // creating order from company/person
            $default_person = $this->_data['person_id'];
        } else {
            if (! $sorder->isLoaded()) {
                $default_person = $this->getDefaultValue($this->modeltype, 'person_id', '');
            } else {
                $default_person = $sorder->person_id;
            }
        }

        if (empty($default_person)) {
            $default_person = key($people);
        }
        $this->view->set('selected_people', $default_person);

        if ($sorder->isLoaded()) {
            $this->view->set('default_despatch_action', $sorder->despatch_action);
        } else {
            $this->view->set('default_despatch_action', $this->getDespatchAction($default_customer));
        }

        // get delivery address list for default person or first in person
        $delivery_address = $this->getPersonAddresses($default_person, 'shipping', $default_customer);
        $this->view->set('deliveryAddresses', $delivery_address);

        // get invoice address list for default person or first in person
        $invoice_address = $this->getPersonAddresses($default_person, 'billing', $default_customer);
        // Set default invoice address to delivery address if it exists
        // otherwise use customer default billing address
        if (isset($invoice_address[key($delivery_address)])) {
            $this->view->set('invoice_address', key($delivery_address));
            $this->view->set('invoiceAddresses', array(
                key($delivery_address) => current($delivery_address)
            ));
        } else {
            $this->view->set('invoice_address', $customer->billing_address_id);
            $this->view->set('invoiceAddresses', $invoice_address);
        }
        $this->view->set('default_inv_address', $customer->billing_address_id);

        // get payment terms list for default customer or first in customer
        $this->payment_terms($default_customer);

        // get Sales Order Notes for default customer or first in customer
        $this->getNotes($person, $default_customer);

        // get despatch actions
        $despatch_actions = WHAction::getDespatchActions();
        $this->view->set('despatch_actions', $despatch_actions);

        if (! is_null($sorder->type)) {
            $this->view->set('page_title', $this->_data['action'] . ' ' . $sorder->getFormatted('type'));
        }

        // This bit allows for projects and tasks
        $projects = $this->getProjects($default_customer);
        $this->view->set('projects', $projects);

        if (! $sorder->isLoaded() && ! empty($this->_data['project_id'])) {
            $sorder->project_id = $this->_data['project_id'];
        }

        $this->view->set('tasks', $this->getTaskList($sorder->project_id));
    }

    public function save()
    {
        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $flash = Flash::Instance();

        $errors = array();

        $data = $this->_data;
        $header = $data[$this->modeltype];

        if (! empty($header['ext_reference'])) {
            $check_sorder = $this->_uses[$this->modeltype];

            $check_sorder->loadBy(array(
                'slmaster_id',
                'ext_reference'
            ), array(
                $header['slmaster_id'],
                $header['ext_reference']
            ));

            if ($check_sorder->isLoaded()) {
                $flash->addWarning('Order already exists for this customer reference');
            }
        }

        if (isset($header['id']) && $header['id'] != '') {
            $action = 'updated';
        } else {
            $action = 'added';
        }

        $trans_type = $this->_uses[$this->modeltype]->getEnum('type', $header['type']);

        $order = SOrder::Factory($header, $errors);

        // Prevent the order header from being saved if the customer is on stop
        if (! $this->actionAllowedOnStop($order->customerdetails)) {
            $flash->addError('Customer account stopped, cannot save');
            sendBack();
        }

        // Check delivery address is valid for customer
        if (!array_key_exists($header['del_address_id'], $this->getPersonAddresses($header['person_id'], 'shipping', $header['slmaster_id']))) {
            $errors[] = 'Delivery address is not valid for the selected customer';
        }

        // Check invoice address is valid for customer
        if (!array_key_exists($header['inv_address_id'], $this->getPersonAddresses($header['person_id'], 'billing', $header['slmaster_id']))) {
            $errors[] = 'Invoice address is not valid for the selected customer';
        }

        $result = false;

        if ($order && empty($errors)) {
            $result = $order->save();
        } else {
            $flash->addErrors($errors);
        }

        if ($result) {
            $flash->addMessage($trans_type . ' ' . $action . ' successfully');
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $order->id
            ));
        } else {
            $flash->addError('Error saving ' . $trans_type);
        }

        if (isset($header['id']) && $header['id'] != '') {
            $this->_data['id'] = $header['id'];
        }

        if (isset($header['type']) && $header['type'] != '') {
            $this->_data['type'] = $header['type'];
        }

        if (isset($header['slmaster_id']) && $header['slmaster_id'] != '') {
            $this->_data['slmaster_id'] = $header['slmaster_id'];
        }

        $this->refresh();
    }

    public function view()
    {
        $order = $this->order_details();
        $id = $order->id;
        $type = $order->getFormatted('type');

        $this->view->set('sorderlines', $order->lines);
        $linestatuses = $order->getLineStatuses();
        $linestatus = $linestatuses['count'];

        if ($order->customerdetails->accountStopped()) {
            $flash = Flash::Instance();
            $flash->addWarning('Customer Account Stopped');
        }

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'sales_ledger',
                'controller' => 'SLCustomers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );

        foreach ($order->getEnumOptions('type') as $key => $description) {
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

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view quotes/orders'
        );

        $sidebar->addList('Actions', $actions);

        $actions = array();

        foreach ($order->getEnumOptions('type') as $key => $description) {
            $actions['new' . $description] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'slmaster_id' => $order->slmaster_id,
                    'type' => $key
                ),
                'tag' => 'new ' . $description
            );
        }

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index',
                'slmaster_id' => $order->slmaster_id
            ),
            'tag' => 'view quotes/orders'
        );

        $sidebar->addList($order->customerdetails->name, $actions);

        $actions = array();

        if ($order->type == 'O'
            && $order->someLinesNew($linestatus)
            && !$order->allLinesNew($linestatus)
            && $this->actionAllowedOnStop($order->customerdetails)
        ) {
            $actions["MoveNewLines"] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'move_new_lines',
                    'id' => $order->id
                ),
                'tag' => 'New Order with Outstanding',
                'class' => 'confirm-action',
                'id' => 'order-from-new-lines'
            );
        }

        foreach ($order->getEnumOptions('type') as $key => $description) {
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

        if ($order->type == 'Q' && $order->status == $order->newStatus()) {
            $actions['printQuote'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printacknowledgement',
                    'filename' => 'SQ' . $order->order_number,
                    'id' => $id
                ),
                'tag' => 'print Quote'
            );

            $actions['convert_to_order'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'edit',
                    'type' => 'O',
                    'id' => $id
                ),
                'tag' => 'Convert Quote to Order'
            );

            $actions['cancel_order'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'cancel_order',
                    'id' => $id
                ),
                'tag' => 'Cancel ' . $type
            );
        }

        if ($order->type == 'O' || $order->type == 'Q') {
            $actions['view'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'view',
                    'id' => $order->id
                ),
                'tag' => 'view current'
            );
        }

        $actions['review_line_notes'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'review_line_notes',
                'id' => $id
            ),
            'tag' => 'View Line Notes',
            'id' => 'view-notes',
            'class' => 'view-inplace'
        );

        if ($order->type == 'O') {
            if ($order->status == $order->newStatus()) {
                $actions['printAcknowledgement'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'printDialog',
                        'printaction' => 'printacknowledgement',
                        'filename' => 'SOack' . $order->order_number,
                        'id' => $id
                    ),
                    'tag' => 'print Acknowledgement'
                );
            }

            if ($order->status == $order->newStatus() || $order->status == 'P') {
                $actions['printProFormaInvoice'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'pro_forma',
                        'id' => $id
                    ),
                    'tag' => 'print Pro Forma Invoice',
                    'class' => 'related_link'
                );
            }

            $actions['printAddressLabel'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printaddresslabel',
                    'filename' => 'SODL' . $order->order_number,
                    'id' => $id
                ),
                'tag' => 'print Address Label'
            );

            $this->output_details_sidebar($sidebar, array(
                'filename' => 'SO_detail_' . $order->order_number,
                'id' => $id
            ));

            if ($order->someLinesNew($linestatus)) {
                $actions['printPickList'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'printDialog',
                        'printaction' => 'printpicklist',
                        'filename' => 'SOPickList' . $order->order_number,
                        'id' => $id
                    ),
                    'tag' => 'Print Pick List'
                );

                $actions['confirm_pick_list'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'confirm_pick_list',
                        'id' => $id
                    ),
                    'tag' => 'Confirm Pick List'
                );
            }

            $actions['select_print_item_labels'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'select_print_item_labels',
                    'id' => $id
                ),
                'tag' => 'Print Item Labels'
            );

            $actions['newdespatchnote'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'createSODespatchNote',
                    'id' => $id
                ),
                'tag' => 'Create Despatch Note'
            );

            if ($order->someLinesPicked($linestatus)) {
                $actions['unpick_lines'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'unconfirm_pick_list',
                        'id' => $id
                    ),
                    'tag' => 'Un Pick Lines'
                );

                $actions['confirm_sale'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'confirm_sale',
                        'id' => $id
                    ),
                    'tag' => 'Confirm Sale'
                );
            }

            if ($order->someLinesDespatched($linestatus) || $order->someLinesPicked($linestatus)) {
                // If there is at least one despatched line
                // and all lines are either despatched or cancelled
                // then the order can be invoiced
                $actions['createinvoice'] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'createinvoice',
                        'id' => $id
                    ),
                    'tag' => 'Create Invoice'
                );
            }
        }

        // edit
        if (count($order->lines) == 0 and ! $order->cancelled()) {
            $actions['cancel_order'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'cancel_order',
                    'id' => $id
                ),
                'tag' => 'Cancel ' . $type
            );
        }

        if (! $order->invoiced() && ! $order->cancelled()) {
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

            $actions['add_lines'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'sorderlines',
                    'action' => 'new',
                    'order_id' => $id
                ),
                'tag' => 'Add_Lines'
            );
        }

        // $lines_status = $order->getLineStatuses();
        if ($order->partDespatched() || $order->despatched() || $order->invoiced() || $linestatuses['count']['R'] > 0) {
            $actions['despatchnote'] = array(
                'link' => array(
                    'module' => 'despatch',
                    'controller' => 'sodespatchlines',
                    'action' => 'index',
                    'order_number' => $order->order_number,
                    'status' => ''
                ),
                'tag' => 'View Despatch Notes'
            );
        }

        if ($order->invoices->count() > 0) {
            $actions['viewinvoice'] = array(
                'link' => array(
                    'module' => 'sales_invoicing',
                    'controller' => 'Sinvoices',
                    'action' => 'view',
                    'order_id' => $order->id
                ),
                'tag' => 'View Invoice'
            );
        }

        if ($order->invoices->count() > 1) {
            $actions['viewinvoice'] = array(
                'link' => array(
                    'module' => 'sales_invoicing',
                    'controller' => 'Sinvoices',
                    'action' => 'index',
                    'sales_order_number' => $order->order_number
                ),
                'tag' => 'View Invoices'
            );
        }

        $sidebar->addList('This ' . $type, $actions);

        // 'Related Items' sidebar section
        if ($order->type == 'O') {
            $this->sidebarRelatedItems($sidebar, $order);
        }

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        if (! is_null($order->type)) {
            $this->view->set('page_title', strtolower($order->getFormatted('type') . ' Detail'));
        }
    }

    public function select_products()
    {
        $s_data = array();

        // so set context from calling module
        if (isset($this->_data['slmaster_id'])) {
            $s_data['slmaster_id'] = $this->_data['slmaster_id'];
        }

        if (isset($this->_data['parent_id'])) {
            $s_data['parent_id'] = $this->_data['parent_id'];
        }

        $this->setSearch('sordersSearch', 'selectProduct', $s_data);

        $slmaster_id = $this->search->getValue('slmaster_id');

        $this->view->set('slmaster_id', $slmaster_id);

        $slcustomer = DataObjectFactory::Factory('SLCustomer');

        if ($slmaster_id > 0) {
            $slcustomer->load($slmaster_id);
        }

        // Get the list of Product Headers for the selected item
        $product_ids = SelectorCollection::getTargets('sales_order', $this->search->getValue('parent_id'));

        $product = DataObjectFactory::Factory('SOProductLine');
        $product->identifierField = 'productline_header_id';

        $cc = new ConstraintChain();

        $customer_lines = array();

        if ($slcustomer->isLoaded()) {
            // Get any lines specific for this customer
            $this->view->set('slcustomer', $slcustomer->companydetail->name);

            $cc1 = new ConstraintChain();
            $cc1->add(new Constraint('slmaster_id', '=', $slcustomer->id));
            if (is_null($slcustomer->so_price_type_id)) {
                $cc1->add(new Constraint('so_price_type_id', 'is', 'NULL'));
                $cc->add(new Constraint('so_price_type_id', 'is', 'NULL'));
            } else {
                $cc1->add(new Constraint('so_price_type_id', '=', $slcustomer->so_price_type_id));
                $cc->add(new Constraint('so_price_type_id', '=', $slcustomer->so_price_type_id));
            }

            $cc1->add($product->currentConstraint());

            if (! empty($product_ids)) {
                $cc1->add(new Constraint('productline_header_id', 'in', '(' . implode(',', array_keys($product_ids)) . ')'));
            } else {
                $cc1->add(new Constraint('productline_header_id', '=', '-1'));
            }
            $customer_lines = $product->getAll($cc1, true, true);

            // Now exclude any lines for a header where the line for the customer exists
            if (count($customer_lines) > 0) {
                $cc->add(new Constraint('productline_header_id', 'not in', '(' . implode(',', $customer_lines) . ')'));
            }
        } else {
            $this->view->set('slcustomer', 'Not Set');
        }

        // Get the non-customer lines, excluding any lines for a header found above for a customer
        $cc->add(new Constraint('slmaster_id', 'is', 'NULL'));

        if (! empty($product_ids)) {
            $cc->add(new Constraint('productline_header_id', 'in', '(' . implode(',', array_keys($product_ids)) . ')'));
        } else {
            $cc->add(new Constraint('productline_header_id', '=', '-1'));
        }

        $cc->add($product->currentConstraint());

        $lines = $product->getAll($cc, true, true);

        $lines += $customer_lines;

        $products = new SOProductLineCollection($product);

        if (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $sh = new SearchHandler($products, false);

            $cc2 = new ConstraintChain();
            if (count($lines) > 0) {
                $cc2->add(new Constraint('id', 'in', '(' . implode(',', array_keys($lines)) . ')'));
            } else {
                // No lines found, so ensure no rows returned
                $cc2->add(new Constraint('id', '=', - 1));
            }
            $sh->addConstraint($cc2);
        } else {
            $sh = new SearchHandler($products);
        }

        $sh->extract();

        $fields = array(
            'id',
            'description',
            'price',
            'currency',
            'uom_name',
            'so_price_type',
            'stitem_id',
            'slmaster_id',
            'prod_group_id'
        );

        $sh->setFields($fields);
        $sh->setGroupby($fields);

        $sh->setOrderby(array(
            'description',
            'so_price_type'
        ));

        parent::index($products, $sh);

        $this->view->set('stitem', DataObjectFactory::Factory('STItem'));

        if (empty($this->_data['sorders'])) {
            $this->_data['sorders'] = array();
        }

        $this->view->set('selected', $this->_data['sorders']);

        foreach ($this->_modules as $key => $value) {
            $modules[] = $key . '=' . $value;
        }

        $link = implode('&', $modules) . '&controller=' . $this->name . '&action=showProducts';

        $this->view->set('link', $link);

        $selectedproduct = empty($_SESSION['selectedproducts']) ? array() : $_SESSION['selectedproducts'];

        $selectedproducts = new SOProductLineCollection(DataObjectFactory::Factory('SOProductLine'));

        if (! empty($selectedproduct)) {
            $sh = new SearchHandler($selectedproducts, false);

            $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', array_keys($selectedproduct)) . ')'));

            $selectedproducts->load($sh);
        }

        $this->view->set('selected', $selectedproduct);
        $this->view->set('productlines', $selectedproducts);

        $this->view->set('page_title', $this->getPageName('', 'Select Products for'));
    }

    public function showProducts()
    {
        $params = explode('=', $this->_data['id']);

        $linkdata = SESSION::Instance();

        $selectedproduct = empty($_SESSION['selectedproducts']) ? array() : $_SESSION['selectedproducts'];

        if (isset($selectedproduct[$params[0]])) {
            if (strtolower($params[1]) == 'false') {
                unset($selectedproduct[$params[0]]);
            }
        } elseif (strtolower($params[1]) == 'true') {
            $selectedproduct[$params[0]] = true;
        }

        $_SESSION['selectedproducts'] = $selectedproduct;

        $selectedproducts = new SOProductLineCollection(DataObjectFactory::Factory('SOProductLine'));

        if (! empty($selectedproduct)) {
            $sh = new SearchHandler($selectedproducts, false);

            $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', array_keys($selectedproduct)) . ')'));

            $selectedproducts->load($sh);
        }

        $this->view->set('productlines', $selectedproducts);
    }

    public function saveselectedproducts()
    {
        if (! isset($this->_data['cancelform']) && ! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $errors = array();

        $flash = Flash::Instance();

        if (isset($this->_data['cancelform'])) {
            unset($_SESSION['selectedproducts']);
            sendBack();
        }

        $selectedproducts = empty($_SESSION['selectedproducts']) ? array() : $_SESSION['selectedproducts'];

        if (empty($selectedproducts)) {
            $flash->addError('No products selected - order not created');
            sendBack();
        }

        $productlines = new SOProductLineCollection(DataObjectFactory::Factory('SOProductLine'));

        $sh = new SearchHandler($productlines, false);

        $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', array_keys($selectedproducts)) . ')'));

        $productlines->load($sh);

        $db = DB::Instance();
        $db->StartTrans();

        $header = $this->_data[$this->modeltype];

        $header['type'] = 'O';

        $trans_type = 'Sales Order';

        // Check the Customer
        $customer = DataObjectFactory::Factory('SLCustomer');
        $customer->load($header['slmaster_id']);

        if ($customer) {
            $header['currency_id'] = $customer->currency_id;
            $header['payment_term_id'] = $customer->payment_term_id;
            $header['tax_status_id'] = $customer->tax_status_id;
            $deliveryAddresses = $customer->getDeliveryAddresses();
            $header['del_address_id'] = key($deliveryAddresses);
            $invoiceAddresses = $customer->getInvoiceAddresses();
            $header['inv_address_id'] = key($invoiceAddresses);
            $header['despatch_action'] = $customer->despatch_action;
        } else {
            $errors[] = 'Cannot find Customer';
        }

        if ($customer->accountStopped()) {
            $flash->addWarning('Customer Account on Stop');
        }

        $header['despatch_date'] = $header['due_date'] = $header['order_date'] = date(DATE_FORMAT);

        $header['net_value'] = 0;

        $lines_data = array();

        foreach ($productlines as $productline) {
            $line = array();
            $line['productline_id'] = $productline->id;
            $line['revised_qty'] = $line['os_qty'] = $line['order_qty'] = $this->_data['qty'][$productline->id];
            $line['price'] = $productline->price;
            $line['currency_id'] = $productline->currency_id;
            $line['glaccount_id'] = $productline->glaccount_id;
            $line['glcentre_id'] = $productline->glcentre_id;
            $line['stitem_id'] = $productline->product_detail->stitem_id;
            $line['stuom_id'] = $productline->product_detail->stuom_id;
            $line['item_description'] = $productline->product_detail->stitem;
            $line['description'] = $productline->product_detail->description;
            $line['tax_rate_id'] = $productline->product_detail->tax_rate_id;
            $line['net_value'] = bcmul($line['order_qty'], $line['price']);

            $lines_data[] = $line;

            $header['net_value'] = bcadd($header['net_value'], $line['net_value']);
        }

        $order = SOrder::Factory($header, $errors);

        if ($order && count($errors) === 0) {
            $result = $order->save();
        } else {
            $result = false;
        }

        if ($result) {
            foreach ($lines_data as $line_data) {
                $orderline = SOrderLine::Factory($order, $line_data, $errors);

                if ($orderline) {
                    $result = $orderline->save();
                } else {
                    $result = false;
                }
                if (count($errors) > 0 || $result === false) {
                    $errors[] = 'Error saving Sales Order Line ';
                    break;
                }
            }
        }

        if ($result !== false && $db->CompleteTrans()) {
            unset($_SESSION['selectedproducts']);

            $flash->addMessage($trans_type . ' created successfully');

            sendTo($this->name, 'edit', $this->_modules, array(
                'id' => $order->id
            ));
        }

        $flash->addError('Error saving ' . $trans_type);

        $db->FailTrans();
        $db->completeTrans();

        $flash->addErrors($errors);
        $this->view->set('page_title', $this->getPageName('', 'Select Products for'));
        $this->refresh();
    }

    public function viewByItems()
    {
        // Get the list of saleable items
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['stitem_id'];
        }

        if (isset($this->_data['prod_group_id'])) {
            $s_data['prod_group_id'] = $this->_data['prod_group_id'];
        }

        $this->setSearch('productlinesSearch', 'customerItems', $s_data);

        // Get the list of items and required quantity
        $orders = new SOrderCollection($this->_templateobject);

        $sh = $this->setSearchHandler($orders);
        $sh->addConstraintChain(new Constraint('type', '=', 'O'));

        $orders->getItems($sh);

        if (isset($this->search)) {
            if ($this->isPrintDialog()) {
                return $this->PrintCollection();
            } elseif ($this->isPrinting()) {
                $orders->setParams();
                $cc = $this->search->toConstraintChain();
                $sh->addConstraintChain($cc);
                $sh->setLimit(0);
                $orders->load($sh);
                $this->printCollection($orders);
                exit();
            }
        }

        parent::index($orders, $sh);

        // Now get the balance for each item across all saleable locations
        $on_order = 0;

        $items = array();

        foreach ($orders as $item) {
            $id = $item->id;

            $items[$id]['min_qty'] = 0;
            $items[$id]['uom_name'] = $item->uom_name;
            $items[$id]['stitem'] = $item->stitem;
            $items[$id]['required'] = $item->required;

            $cc = new ConstraintChain();

            $cc->add(new Constraint('stitem_id', '=', $id));

            $worders = MFWorkorder::getBalances($cc);

            $cc->add(new Constraint('supply_demand', 'is', true));

            $items[$id]['in_stock'] = STBalance::getBalances($cc);
            $items[$id]['on_order']['po_value'] = 0;
            $items[$id]['on_order']['wo_value'] = 0;

            if ($worders) {
                $items[$id]['on_order']['wo_value'] = $worders[0]['sumbalance'];
            }

            $stitem = DataObjectFactory::Factory('STItem');

            if ($stitem->load($item->id) && $stitem->isLoaded()) {
                $items[$id]['min_qty'] = $stitem->min_qty;
                $porders = $stitem->getPOrderLines();

                foreach ($porders as $porder) {
                    $items[$id]['on_order']['po_value'] += round($stitem->convertToUoM($porder->stuom_id, $stitem->uom_id, $porder->os_qty), $stitem->qty_decimals);
                }
            }

            $salelocations = WHLocation::getSaleLocations();

            if (empty($salelocations)) {
                $items[$id]['for_sale'] = 0;
            } else {
                $cc->add(new Constraint('whlocation_id', 'in', '(' . implode(',', $salelocations) . ')'));
                $items[$id]['for_sale'] = STBalance::getBalances($cc);
            }

            $items[$id]['in_stock'] -= $items[$id]['for_sale'];
            $items[$id]['actual_shortfall'] = $items[$id]['required'] - ($items[$id]['for_sale'] + $items[$id]['in_stock']) + $items[$id]['min_qty'];
            $items[$id]['shortfall'] = $items[$id]['actual_shortfall'] - $items[$id]['on_order']['po_value'] - $items[$id]['on_order']['wo_value'];
            $items[$id]['indicator'] = 'green';

            if ($items[$id]['actual_shortfall'] <= 0) {
                $items[$id]['actual_shortfall'] = 0;
            } else {
                $items[$id]['indicator'] = 'amber';
            }

            if ($items[$id]['shortfall'] <= 0) {
                $items[$id]['shortfall'] = 0;
            } else {
                $items[$id]['indicator'] = 'red';
            }
        }

        $this->view->set('orders', $items);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'sales_ledger',
                'controller' => 'SLCustomers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );

        $actions['newquote'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'type' => 'Q'
            ),
            'tag' => 'new quote'
        );
        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'type' => 'O'
            ),
            'tag' => 'new order'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view quotes/orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('page_title', $this->getPageName('', 'View availability by items for'));
    }

    public function viewByDates()
    {
        $id = $this->_data['id'];
        $cc = new ConstraintChain();
        $cc->add(new Constraint('stitem_id', '=', $id));
        $order = new SOrderCollection($this->_templateobject);
        $orders = $order->getItemDates($cc);
        $in_stock = 0;
        $shortfall = 0;
        $required = 0;
        $for_sale = 0;

        // Now get the balance for each item across all saleable locations
        $cc = new ConstraintChain();
        $cc->add(new Constraint('stitem_id', '=', $id));
        $in_stock = STBalance::getBalances($cc);

        $salelocations = WHLocation::getSaleLocations();
        if (count($salelocations) > 0) {
            $cc->add(new Constraint('whlocation_id', 'in', '(' . implode(',', $salelocations) . ')'));
            $for_sale = STBalance::getBalances($cc);
        }
        $in_stock -= $for_sale;
        // And finally update the orders with the projected stock balances
        $on_order = 0;
        $total_orders = 0;
        $items = array();
        foreach ($orders as $row) {
            // Framework assumes first column is the unique 'id'
            $due_date = $row->id;
            $items[$due_date]['due_date'] = un_fix_date($row->id);
            $items[$due_date]['required'] = $row->required;
            $items[$due_date]['stitem'] = $row->stitem;
            $items[$due_date]['stitem_id'] = $row->stitem_id;
            $items[$due_date]['for_sale'] = $for_sale;
            $cc = new ConstraintChain();
            $cc->add(new Constraint('stitem_id', '=', $row->stitem_id));
            $cc->add(new Constraint('required_by', '<=', $due_date));
            $worders = MFWorkorder::getBalances($cc);
            if ($worders) {
                $on_order = $worders[0]['sumbalance'] - $total_orders;
                $total_orders = $worders[0]['sumbalance'];
            } else {
                $on_order = 0;
            }
            $for_sale -= $row->required;

            if ($for_sale < 0) {
                $in_stock += $for_sale;
                $for_sale = 0;
            }
            $in_stock = $in_stock - $shortfall + $on_order;
            if ($in_stock < 0) {
                $shortfall -= $in_stock;
                $in_stock = 0;
            } else {
                $shortfall = 0;
            }
            $items[$due_date]['in_stock'] = $in_stock;
            $items[$due_date]['on_order'] = $on_order;
            $items[$due_date]['shortfall'] = $shortfall;
        }

        $this->view->set('outstanding', $items);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'sales_ledger',
                'controller' => 'SLCustomers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );

        $actions['newquote'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'type' => 'Q'
            ),
            'tag' => 'new quote'
        );
        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'type' => 'O'
            ),
            'tag' => 'new order'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view quotes/orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('page_title', $this->getPageName('', 'View item availability by date for'));
    }

    /**
     * Show Order Lines with Notes
     *
     * To be called using update_page (XHR) from sales_order.js to display
     * a list of sales order lines in place
     */
    public function review_line_notes(){
        // Only POST requests with an XHR header are allowed
        $this->checkRequest(['post'], true);

        $order = $this->order_details();
        foreach ($order->lines as $line){
            if ($line->note != ''){
                $notes[] = $line;
            }
        }
        $this->view->set('page_title', 'Sale Order Line Notes');
        $this->view->set('sorderlines', $notes);
    }

    /**
     * createSODespatchNote - Create a Despatch Note From the Sales Order
     *
     * Marks any order lines with status 'New' as released and creates a despatch note.
     * Excludes order lines that are not linked to a productline or where the product
     * is marked as not 'despatcheable'.
     */
    public function createSODespatchNote()
    {
        $flash = Flash::Instance();
        $db = DB::Instance();
        $db->StartTrans();

        // Release the order lines
        $orderline = DataObjectFactory::Factory('SOrderLine');
        $orderlines = new SOrderLineCollection($orderline);
        $sh = new SearchHandler($orderlines, false);
        $sh->addConstraint(new Constraint('status', '=', $orderline->newStatus()));
        $sh->addConstraint(new Constraint('order_id', '=', $this->_data['id']));
        $sh->addConstraint(new Constraint('productline_id', 'IS NOT', 'NULL'));
        $sh->addConstraint(new Constraint('not_despatchable', 'IS NOT', true));

        $orderlines->load($sh);

        if (count($orderlines) > 0) {
            $errors = array();
            $despatch = array();
            $despatchline = array();

            $order = DataObjectFactory::Factory('SOrder');
            $order->load($this->_data['id']);

            foreach ($orderlines as $line) {
                if ($line->delivery_note == '' && $line->stitem == '') {
                    $line->update($line->id, 'status', $line->awaitingDespatchStatus());

                    $despatch[$this->_data['id']][$line->id] = SODespatchLine::makeLine($order, $line, $errors);
                }
            }

            $despatch_num = SODespatchLine::createDespatchNote($despatch, $errors);

            if ($despatch_num && count($errors) === 0 && $db->CompleteTrans()) {
                // Redirect to the desptch note view
                sendTo('sodespatchlines', 'view', 'despatch', [
                    'id' => $despatch_num
                ]);
            } else {
                $errors[] = 'Error creating Despatch Note';
                $flash->addErrors($errors);
                $db->FailTrans();
                $db->CompleteTrans();
                $this->refresh();
            }
        } else {
            $flash->addMessage("No lines available to create a despatch note");
            sendBack();
        }
    }

    public function viewByOrders()
    {
        $cc = new ConstraintChain();
        $cc->add(new Constraint('type', '=', 'O'));
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $cc->add(new Constraint('stitem_id', '=', $id));
        }
        $order = new SOrderCollection($this->_templateobject);
        $order->orderby = array(
            'status',
            'due_despatch_date',
            'order_number'
        );
        $order->direction = array(
            'DESC',
            'ASC'
        );
        $orders = $order->getItemOrders($cc);

        // Create an array of items ordered
        $stitems = array();
        foreach ($orders as $row) {
            // ignore PLs without stitem
            if ($row->stitem_id) {
                $stitems[$row->stitem_id]['shortfall'] = 0;
            }
        }

        // Now get the balance for each item across all saleable locations
        foreach ($stitems as $key => $item) {
            $cc = new ConstraintChain();
            $cc->add(new Constraint('stitem_id', '=', $key));
            $stitems[$key]['in_stock'] = STBalance::getBalances($cc);
            $salelocations = WHLocation::getSaleLocations();
            if (empty($salelocations)) {
                $stitems[$key]['for_sale'] = 0;
            } else {
                $cc->add(new Constraint('whlocation_id', 'in', '(' . implode(',', $salelocations) . ')'));
                $stitems[$key]['for_sale'] = STBalance::getBalances($cc);
            }
            $stitems[$key]['in_stock'] -= $stitems[$key]['for_sale'];
            $stitems[$key]['total_orders'] = 0;
        }

        // And finally update the orders with the projected stock balances
        $items = array();
        foreach ($orders as $key => $row) {
            // echo 'count '.$row->id.' - '.SOrder::lineExistsInDespatchLines($row->id).'<br>';
            $items[$key]['id'] = $row->id;
            $items[$key]['stitem_id'] = $row->stitem_id;
            $items[$key]['stitem'] = $row->stitem;
            $items[$key]['item_description'] = $row->item_description;
            $items[$key]['productline_id'] = $row->productline_id;
            $items[$key]['required'] = $row->required;
            $items[$key]['due_despatch_date'] = $row->due_despatch_date;
            $items[$key]['order_number'] = $row->order_number;
            $items[$key]['order_id'] = $row->order_id;
            $items[$key]['customer'] = $row->customer;
            $items[$key]['slmaster_id'] = $row->slmaster_id;
            $items[$key]['stuom'] = $row->stuom;
            $items[$key]['for_sale'] = $stitems[$row->stitem_id]['for_sale'];
            $items[$key]['shortfall'] = 0;
            // ignore PLs without stitem
            if ($row->stitem_id) {
                $cc = new ConstraintChain();
                $cc->add(new Constraint('stitem_id', '=', $row->stitem_id));
                $cc->add(new Constraint('required_by', '<=', $row->due_despatch_date));
                $worders = MFWorkorder::getBalances($cc);
                if ($worders) {
                    $stitems[$row->stitem_id]['on_order'] = $worders[0]['sumbalance'] - $stitems[$row->stitem_id]['total_orders'];
                    $stitems[$row->stitem_id]['total_orders'] = $worders[0]['sumbalance'];
                } else {
                    $stitems[$row->stitem_id]['on_order'] = 0;
                }
            }
            $on_order = $stitems[$row->stitem_id]['on_order'];
            $items[$key]['on_order'] = $stitems[$row->stitem_id]['on_order'];
            $stitems[$row->stitem_id]['for_sale'] -= $items[$key]['required'];
            if ($stitems[$row->stitem_id]['for_sale'] < 0) {
                $stitems[$row->stitem_id]['in_stock'] += $stitems[$row->stitem_id]['for_sale'];
                $stitems[$row->stitem_id]['for_sale'] = 0;
            }
            $stitems[$row->stitem_id]['in_stock'] = $stitems[$row->stitem_id]['in_stock'] - $stitems[$row->stitem_id]['shortfall'] + $stitems[$row->stitem_id]['on_order'];
            // $stitems[$row->stitem_id]['in_stock']=$stitems[$row->stitem_id]['in_stock']-$stitems[$row->stitem_id]['shortfall'];

            if ($stitems[$row->stitem_id]['in_stock'] < 0) {
                $stitems[$row->stitem_id]['shortfall'] -= $stitems[$row->stitem_id]['in_stock'];
                $stitems[$row->stitem_id]['in_stock'] = 0;
                $items[$key]['shortfall'] = ($stitems[$row->stitem_id]['shortfall'] < $on_order) ? 0 : $stitems[$row->stitem_id]['shortfall'] - $on_order;
                // $items[$key]['shortfall']=$stitems[$row->stitem_id]['shortfall'];
            } else {
                $stitems[$row->stitem_id]['shortfall'] = 0;
            }
            $items[$key]['in_stock'] = $stitems[$row->stitem_id]['in_stock'];
            if (! empty($row->delivery_note)) {
                $items[$key]['status'] = 'Awaiting Despatch';
            } elseif ($row->status == 'R') {
                $items[$key]['status'] = 'Ready for Despatch';
            } else {
                $items[$key]['status'] = '';
            }
            $items[$key]['account_status'] = $row->account_status;
            $items[$key]['despatch_number'] = SOrder::lineExistsInDespatchLines($row->id);

            if ($row->status == 'R') {
                $items[$key]['despatch'] = true;
            } else {
                $items[$key]['despatch'] = false;
            }
        }

        $this->view->set('orders', $items);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'sales_ledger',
                'controller' => 'SLCustomers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );

        $actions['newquote'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'type' => 'Q'
            ),
            'tag' => 'new quote'
        );
        $actions['neworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new',
                'type' => 'O'
            ),
            'tag' => 'new order'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view quotes/orders'
        );
        $actions['viewdespatches'] = array(
            'link' => array(
                'module' => 'despatch',
                'controller' => 'sodespatchlines',
                'action' => 'viewByOrders'
            ),
            'tag' => 'view despatches'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('page_title', $this->getPageName('', 'View availability by'));
    }

    public function releaseorders()
    {
        $ids = array();
        if (isset($this->_data['sorders'])) {
            $ids = array_keys($this->_data['sorders']);
            $search_ids = '(' . implode(',', $ids) . ')';
        }

        $orderline = DataObjectFactory::Factory('SOrderLine');
        $orderlines = new SOrderLineCollection($orderline);
        $sh = new SearchHandler($orderlines, false);
        $sh->addConstraint(new Constraint('status', '=', $orderline->awaitingDespatchStatus()));
        if (count($ids) > 0) {
            $sh->addConstraint(new Constraint('id', 'not in', $search_ids));
        }
        $orderlines->load($sh);
        foreach ($orderlines as $orderline) {
            if ($orderline->delivery_note == '') {
                $orderline->update($orderline->id, 'status', $orderline->newStatus());
            }
        }

        if (count($ids) > 0) {
            $orderlines = new SOrderLineCollection($orderline);
            $sh = new SearchHandler($orderlines, false);
            $sh->addConstraint(new Constraint('status', '=', $orderline->newStatus()));
            $sh->addConstraint(new Constraint('id', 'in', $search_ids));
            $orderlines->load($sh);
            foreach ($orderlines as $orderline) {
                $orderline->update($orderline->id, 'status', $orderline->awaitingDespatchStatus());
            }
        }

        sendTo($this->name, 'viewbyorders', $this->_modules);
    }

    public function getDespatchAction($_slmaster_id = '')
    {
        // Used by Ajax to return Default Despatch Action after selecting the Supplier
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
        }

        $customer = $this->getCustomer($_slmaster_id);

        $despatch_action = '';
        if ($customer) {
            $despatch_action = $customer->despatch_action;
        }

        if (empty($despatch_action)) {
            $despatch_actions = WHAction::getDespatchActions();
            $despatch_action = current(array_keys($despatch_actions));
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $despatch_action);
            $this->setTemplateName('text_inner');
        } else {
            return $despatch_action;
        }
    }

    public function getPersonAddresses($_person_id = '', $_type = '', $_slmaster_id = '')
    {
        /*
         * We only want to override the function parameters if the call has come from
         * an ajax request, simply overwriting them as we were leads to a mix up in
         * values
         */
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['person_id'])) {
                $_person_id = $this->_data['person_id'];
            }
            if (! empty($this->_data['type'])) {
                $_type = $this->_data['type'];
            }
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
        }

        /* set the type and slmaster_id variables to the data array */
        $data['type'] = $_type;
        $data['slmaster_id'] = $_slmaster_id;

        $people = $this->_templateobject->getPersonAddresses($_person_id, $data);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $people);
            $this->setTemplateName('select_options');
        } else {
            return $people;
        }
    }

    public function getNotes($_person_id = '', $_slmaster_id = '')
    {
        // Used by Ajax to return Notes after selecting the Customer
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['person_id'])) {
                $_person_id = $this->_data['person_id'];
            }
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
        }

        $notes = new PartyNoteCollection(DataObjectFactory::Factory('PartyNote'));
        $party_id = '';
        if (! empty($_person_id)) {
            $person = DataObjectFactory::Factory('Person');
            $person->load($_person_id);
            $party_id = $person->party_id;
        }
        if (empty($party_id) && ! empty($_slmaster_id)) {
            $customer = $this->getCustomer($_slmaster_id);
            $party_id = $customer->companydetail->party_id;
        }
        if (! empty($party_id)) {
            $sh = new SearchHandler($notes, false);
            $sh->setFields(array(
                'id',
                'lastupdated',
                'note'
            ));
            $sh->setOrderby('lastupdated', 'DESC');
            $sh->addConstraint(new Constraint('note_type', '=', $this->module));
            $sh->addConstraint(new Constraint('party_id', '=', $party_id));
            $notes->load($sh);
        }
        $this->view->set('no_ordering', true);
        $this->view->set('collection', $notes);

        if (isset($this->_data['ajax'])) {
            $this->setTemplateName('datatable_inline');
        } else {
            return $this->view->fetch($this->getTemplateName('datatable_inline'));
        }
    }

    public function orderitemsummary()
    {
        // Use by Ajax from within eglet
        $orderlines = new SOrderLineCollection(DataObjectFactory::Factory('SOrderLine'));

        $period = (isset($this->_data['period'])) ? $this->_data['period'] : '';
        $type = (isset($this->_data['type'])) ? $this->_data['type'] : '';
        $page = (isset($this->_data['page'])) ? $this->_data['page'] : $page = '';

        // prepare URL, we do this because this isn't an ordinary eglet, paging needs
        // to be handled manually, therefore we must pass the base URL through.
        $url_parts = array(
            "module",
            "controller",
            "action",
            "period",
            "type",
            "_target"
        );
        $url = array();
        foreach ($url_parts as $part) {
            $url[$part] = $part . "=" . $this->_data[$part];
        }

        $ordersummary = $orderlines->getOrderItemSummary($period, $type, $page);
        $ordersummary['url'] = '/?' . implode('&', $url);
        $this->view->set('page_title', $this->getPageName('', 'items for despatch'));
        $this->view->set('content', $ordersummary);
    }

    public function sorders_summary()
    {
        $orders = new SOrderLineCollection(DataObjectFactory::Factory('SOrderLine'));
        $customersales = $orders->getTopOrders(10, $this->_data['type']);
        $this->view->set('content', $customersales);
    }

    public function pro_forma()
    {
        $order = $this->order_details();
        $id = $order->id;
        $linestatus = $order->getLineStatuses();
        $this->view->set('linevalue', $linestatus['value']);
        $this->view->set('filename', 'SOpfi' . $order->order_number);
        $this->view->set('page_title', $this->getPageName('', 'Print Pro Forma Invoice for'));
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

        if ($order->type != 'Q' and count($order->lines) > 0) {
            $flash->addError('Only Quotes or empty orders can be cancelled');
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
                    if (! $line->update($line->id, 'status', $line->cancelStatus())) {
                        $errors[] = $db->ErrorMsg();
                        $errors[] = 'Failed to cancel ' . $order->getFormatted('type') . ' line';
                        break;
                    }
                }
            }
            $order->update($order->id, 'description', $order->description . "\r\n[Cancelled by " . EGS_USERNAME . ", " . date("Y-m-d H:i:s") . "]");
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

    public function createInvoice()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sorder = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();
        $errors = array();
        $db = DB::Instance();
        $db->startTrans();
        $this->createPostInvoice($sorder, $errors);
        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $flash->addError('Invoice creation failed');
            $db->failTrans();
        } else {
            $flash->addMessage('Invoice Created and Posted OK');
        }
        $db->CompleteTrans();
        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $sorder->id
        ));
    }

    /**
     * Select from a list of SO lines to print item labels for
     *
     * The user can select SO lines to print and a quantity of labels
     * for each one. This would be used where barcode labels must
     * be attached to ordered items, for exmaple.
     */
    public function select_print_item_labels()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $sorder = $this->_uses[$this->modeltype];
        $this->view->set('page_title', 'Print Item Labels - Sales Order');
        $this->view->set('no_ordering', true);
        $this->view->set('printers', $this->selectPrinters());
        $this->view->set('default_printer', $this->getDefaultPrinter());
    }

    /**
     * Print labels for selected SO lines
     *
     * Handles POST action from select_print_item_labels(),
     * processes the submitted data and prints the specified
     * number of labels for selected SO lines.
     */
    public function printItemLabels()
    {
        $flash = Flash::Instance();
        $errors = array();

        $this->loadData();
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $sorder = $this->_uses[$this->modeltype];

        // convert the order lines to an array for easy lookup
        $lines = [];
        foreach ($sorder->lines as $orderline) {
            $lines[$orderline->id] = $orderline->_data;
        }

        // build the label data
        $extra = [];
        foreach ($this->_data['SOrderLine'] as $key => $line) {
            if (! isset($line['id'])) {
                continue;
            } elseif ($line['print_qty'] <= 0) {
                $errors[] = 'line ' . $line['line_number'] . ' : Print quantity must be greater than zero';
                continue;
            }
            $productline = new SOProductline();
            $productline->load($lines[$key]['productline_id']);
            $product = new SOProductlineHeader();
            $product->load($productline->productline_header_id);
            
            $label_data = [];
            $description_parts = explode('-', $lines[$key]['description'], 2);
            $label_data[$key]['item_code'] = trim($lines[$key]['item_code']);
            $label_data[$key]['item_number'] = trim($description_parts[0]);
            $label_data[$key]['item_description'] = trim($description_parts[1]);
            $label_data[$key]['customer_product_code'] = trim($productline->customer_product_code);
            $label_data[$key]['ean'] = trim($product->ean);

            for ($count = 1; $count <= $line['print_qty']; $count ++) {
                $extra[]['label'] = $label_data;
            }
        }

        // abort if there were errors
        if (count($errors) > 0) {
            $flash->addErrors($errors);
            sendBack();
        }

        // generate the XML
        $xml = $this->generateXML([
            'model' => $sorder,
            'extra' => $extra
        ]);

        // set the print options
        $data['printtype'] = 'pdf';
        $data['printaction'] = 'Print';
        $data['printer'] = $this->_data['printer'];

        // build a basic list of options
        $options = array(
            'report' => 'SOItemLabel',
            'xmlSource' => $xml
        );

        // print the labels
        $response = json_decode($this->generate_output($data, $options));

        if ($response->status !== true) {
            $flash->addError("Failed to print labels" . ": " . $response->message);
        } else {
            $flash->addMessage("Item labels printed successfully");
        }

        // back to the sales order view
        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $sorder->id
        ));
    }

    public function confirm_pick_list()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sorder = $this->_uses[$this->modeltype];
        $this->view->set('no_ordering', true);

        $pick_from = array();
        foreach ($sorder->lines as $orderline) {
            if (! is_null($orderline->stitem_id)) {
                $stbalance = DataObjectFactory::Factory('STBalance');
                $balances = new STBalanceCollection($stbalance);
                $cc = new ConstraintChain();
                $cc->add(new Constraint('balance', '>', '0'));
                $cc->add(new Constraint('pickable', 'is', true));
                $pick_from[$orderline->id]['locations'] = $balances->getLocationList($orderline->stitem_id, $cc);
                if (empty($pick_from[$orderline->id]['locations'])) {
                    $pick_from[$orderline->id]['balance'] = 0;
                } else {
                    $pick_from[$orderline->id]['balance'] = $stbalance->getStockBalance($orderline->stitem_id, key($pick_from[$orderline->id]['locations']));
                }
            } else {
                $pick_from[$orderline->id] = array();
            }
        }
        $this->view->set('action_list', $pick_from);
        $this->view->set('from_locations', $sorder->despatch_from->rules_list('from_location'));
    }

    public function unconfirm_pick_list()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sorder = $this->_uses[$this->modeltype];
        $this->view->set('no_ordering', true);

        $pick_from = array();
        foreach ($sorder->lines as $orderline) {
            if (! is_null($orderline->stitem_id)) {
                $balances = new STBalanceCollection(DataObjectFactory::Factory('STBalance'));
                $cc = new ConstraintChain();
                $cc->add(new Constraint('pickable', 'is', true));
                $pick_from[$orderline->id] = $balances->getLocationList($orderline->stitem_id, $cc);
            } else {
                $pick_from[$orderline->id] = array();
            }
        }
        $this->view->set('action_list', $pick_from);
        $this->view->set('from_locations', $sorder->despatch_from->rules_list('from_location'));
    }

    public function save_pick_list()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sorder = $this->_uses[$this->modeltype];

        $sorder_data = $this->_data[$this->modeltype];
        $sorderline_data = $this->_data['SOrderLine'];
        $flash = Flash::Instance();
        $errors = array();
        $db = DB::Instance();
        $db->startTrans();
        // The value of the $sorder_data[to_location_id] is the whtransfer_rule_id
        // so get the location_ids for the rule_list - note it is the from_location
        // because we are transferring into the location from which the goods
        // will be invoiced/despatched
        $location_ids = $sorder->despatch_from->rules_list('from_whlocation_id');
        $next_line_number = $sorder->getNextLineNumber();
        if (isset($sorderline_data)) {
            foreach ($sorderline_data as $key => $value) {
                if (! isset($value['id'])) {
                    continue;
                } elseif ($value['del_qty'] <= 0) {
                    $errors[] = 'line ' . $value['line_number'] . ' : Picked quantity must be greater than zero';
                    continue;
                }
                $sorderline = DataObjectFactory::Factory('SOrderLine');
                $sorderline->load($key);
                if (! $sorderline) {
                    $errors[] = 'line ' . $value['line_number'] . ' : Failed to get order line ' . $db->ErrorMsg();
                    break;
                }
                if (isset($value['backorder']) && $value['del_qty'] < $sorderline->os_qty) {
                    $data = array();
                    foreach ($sorderline->getFields() as $field) {
                        if ($field->type == 'date' && $field->value != '') {
                            $data[$field->name] = un_fix_date($field->value);
                        } else {
                            $data[$field->name] = $field->value;
                        }
                    }
                    $data['line_number'] = $next_line_number;
                    $next_line_number ++;
                    unset($data['id']);
                    $data['os_qty'] = $data['revised_qty'] = $data['order_qty'] = $sorderline->os_qty - $value['del_qty'];
                    $data['net_value'] = round(bcmul($data['revised_qty'], $data['price'], 4), 2);
                    $data['twin_currency_id'] = $sorderline->twin_currency_id;
                    $data['twin_rate'] = $sorderline->twin_rate;
                    $data['base_net_value'] = round(bcdiv($data['net_value'], $data['rate'], 4), 2);
                    $data['twin_net_value'] = round(bcmul($data['base_net_value'], $data['twin_rate'], 4), 2);
                    $data['del_qty'] = 0;
                    $neworderline = SOrderLine::Factory($sorder, $data, $errors);
                    if (count($errors) > 0 || ! $neworderline || ! $neworderline->save()) {
                        $errors[] = 'line ' . $value['line_number'] . ' : Failed to back order item';
                    } else {
                        $sorder->net_value = bcadd($sorder->net_value, $neworderline->net_value);
                    }
                }
                if ($sorderline->revised_qty != $value['del_qty']) {
                    $sorderline->revised_qty = $sorderline->os_qty = $value['del_qty'];
                    if (isset($value['backorder'])) {
                        $sorderline->order_qty = $value['del_qty'];
                    }
                    $sorder->net_value = bcsub($sorder->net_value, $sorderline->net_value);
                    $sorder->base_net_value = bcsub($sorder->base_net_value, $sorderline->base_net_value);
                    $sorder->twin_net_value = bcsub($sorder->twin_net_value, $sorderline->twin_net_value);
                    $sorderline->net_value = round(bcmul($sorderline->revised_qty, $sorderline->price, 4), 2);
                    $sorderline->base_net_value = round(bcdiv($sorderline->net_value, $sorderline->rate, 4), 2);
                    $sorderline->twin_net_value = round(bcmul($sorderline->base_net_value, $sorderline->twin_rate, 4), 2);
                    $sorder->net_value = bcadd($sorder->net_value, $sorderline->net_value);
                    $sorder->base_net_value = bcadd($sorder->base_net_value, $sorderline->base_net_value);
                    $sorder->twin_net_value = bcadd($sorder->twin_net_value, $sorderline->twin_net_value);
                }
                $sorderline->status = $sorderline->pickedStatus();
                if (! $sorderline->save()) {
                    $errors[] = 'line ' . $value['line_number'] . ' : Failed to pick item ';
                    $errors[] = $db->ErrorMsg();
                    break;
                }
                if (! is_null($sorderline->stitem_id)) {
                    if (! empty($value['whlocation_id']) && ! empty($sorder_data['to_location_id'])) {
                        $data = array();
                        $data['stitem_id'] = $sorderline->stitem_id;
                        $data['qty'] = $value['del_qty'];
                        $data['process_name'] = 'SO';
                        $data['process_id'] = $sorderline->order_id;
                        $data['whaction_id'] = '';
                        if ($value['whlocation_id'] == $location_ids[$sorder_data['to_location_id']]) {
                            $errors['whlocation_id'] = 'line ' . $value['line_number'] . ' : Cannot transfer from/to the same location';
                            break;
                        }
                        $data['from_whlocation_id'] = $value['whlocation_id'];
                        $data['to_whlocation_id'] = $location_ids[$sorder_data['to_location_id']];

                        $models = STTransaction::prepareMove($data, $errors);
                        if (count($errors) == 0) {
                            foreach ($models as $model) {
                                if (! $model || ! $model->save($errors)) {
                                    $errors[] = 'line ' . $value['line_number'] . ' : Error updating stock';
                                    break;
                                }
                            }
                        }
                    } else {
                        $errors[] = 'line ' . $value['line_number'] . ' : Cannot move stock - missing location';
                    }
                }
                if (count($errors) == 0 && ! $sorder->save()) {
                    $errors[] = 'line ' . $value['line_number'] . ' : Failed to pick item ';
                    $errors[] = $db->ErrorMsg();
                    break;
                }
            }
        } else {
            $errors[] = 'No lines confirmed';
        }
        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $db->FailTrans();
            $db->CompleteTrans();
            $this->refresh();
        } else {
            $flash->addMessage('Picking action successfully completed');
            $db->CompleteTrans();
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $sorder->id
            ));
        }
    }

    public function save_unpick_list()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sorder = $this->_uses[$this->modeltype];

        $sorder_data = $this->_data[$this->modeltype];
        $sorderline_data = $this->_data['SOrderLine'];
        $flash = Flash::Instance();
        $errors = array();
        $db = DB::Instance();
        $db->startTrans();
        // The value of the $sorder_data[to_location_id] is the whtransfer_rule_id
        // so get the location_ids for the rule_list - note it is the from_location
        // because we are transferring into the location from which the goods
        // will be invoiced/despatched
        $location_ids = $sorder->despatch_from->rules_list('from_whlocation_id');
        $next_line_number = $sorder->getNextLineNumber();
        if (isset($sorderline_data)) {
            foreach ($sorderline_data as $key => $value) {
                if (! isset($value['id'])) {
                    continue;
                }
                $sorderline = DataObjectFactory::Factory('SOrderLine');
                $sorderline->load($key);
                if (! $sorderline) {
                    $errors[] = 'Failed to get order line';
                    break;
                }
                if ($sorderline->revised_qty < $value['del_qty']) {
                    $errors[] = 'Trying to unpick more than was picked!';
                    break;
                } elseif ($sorderline->revised_qty > $value['del_qty']) {
                    $data = array();
                    foreach ($sorderline->getFields() as $field) {
                        if ($field->type == 'date' && $field->value != '') {
                            $data[$field->name] = un_fix_date($field->value);
                        } else {
                            $data[$field->name] = $field->value;
                        }
                    }
                    $data['line_number'] = $next_line_number;
                    $next_line_number ++;
                    unset($data['id']);
                    $data['status'] = $sorderline->newStatus();
                    $data['os_qty'] = $data['revised_qty'] = $data['order_qty'] = $value['del_qty'];
                    $data['net_value'] = round(bcmul($data['revised_qty'], $data['price'], 4), 2);
                    $data['twin_currency_id'] = $sorderline->twin_currency_id;
                    $data['twin_rate'] = $sorderline->twin_rate;
                    $data['base_net_value'] = round(bcdiv($data['net_value'], $data['rate'], 4), 2);
                    $data['twin_net_value'] = round(bcmul($data['base_net_value'], $data['twin_rate'], 4), 2);
                    $neworderline = SOrderLine::Factory($sorder, $data, $errors);
                    if (count($errors) > 0 || ! $neworderline || ! $neworderline->save()) {
                        $errors[] = 'Failed to unpick required amount';
                    } else {
                        $sorderline->os_qty = $sorderline->order_qty = $sorderline->revised_qty = $sorderline->revised_qty - $value['del_qty'];
                        $sorder->net_value = bcsub($sorder->net_value, $sorderline->net_value);
                        $sorder->base_net_value = bcsub($sorder->base_net_value, $sorderline->base_net_value);
                        $sorder->twin_net_value = bcsub($sorder->twin_net_value, $sorderline->twin_net_value);
                        $sorderline->net_value = round(bcmul($sorderline->revised_qty, $sorderline->price, 4), 2);
                        $sorderline->base_net_value = round(bcdiv($sorderline->net_value, $sorderline->rate, 4), 2);
                        $sorderline->twin_net_value = round(bcmul($sorderline->base_net_value, $sorderline->twin_rate, 4), 2);
                        $sorder->net_value = bcadd($sorder->net_value, $sorderline->net_value);
                        $sorder->base_net_value = bcadd($sorder->base_net_value, $sorderline->base_net_value);
                        $sorder->twin_net_value = bcadd($sorder->twin_net_value, $sorderline->twin_net_value);
                    }
                } else {
                    // $sorderline->os_qty+=$value['del_qty'];
                    $sorderline->status = $sorderline->newStatus();
                }
                if (! $sorderline->save()) {
                    $errors[] = 'Failed to unpick item ';
                    break;
                }
                if (! is_null($sorderline->stitem_id)) {
                    if (! empty($value['whlocation_id']) && ! empty($sorder_data['from_location_id'])) {
                        $data = array();
                        $data['stitem_id'] = $sorderline->stitem_id;
                        $data['qty'] = $value['del_qty'];
                        $data['process_name'] = 'SO';
                        $data['process_id'] = $sorderline->order_id;
                        $data['whaction_id'] = '';
                        if ($value['whlocation_id'] == $location_ids[$sorder_data['from_location_id']]) {
                            $errors['whlocation_id'] = 'Cannot transfer from/to the same location';
                            break;
                        }
                        $data['to_whlocation_id'] = $value['whlocation_id'];
                        $data['from_whlocation_id'] = $location_ids[$sorder_data['from_location_id']];

                        $models = STTransaction::prepareMove($data, $errors);
                        if (count($errors) == 0) {
                            foreach ($models as $model) {
                                if (! $model || ! $model->save($errors)) {
                                    $errors[] = 'Error updating stock';
                                    break;
                                }
                            }
                        }
                    } else {
                        $errors[] = 'Cannot move stock - missing location';
                    }
                }
                // Save header to update status/values
                if (count($errors) == 0 && ! $sorder->save()) {
                    $errors[] = 'Failed to unpick item ';
                    break;
                }
            }
        } else {
            $errors[] = 'No lines confirmed';
        }
        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $db->FailTrans();
            $db->CompleteTrans();
            $this->refresh();
        } else {
            $flash->addMessage('Unpick action successfully completed');
            $db->CompleteTrans();
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $sorder->id
            ));
        }
    }

    public function confirm_sale()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $sorder = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        $errors = array();

        $pt = DataObjectFactory::Factory('PaymentType');

        $payment_term = DataObjectFactory::Factory('PaymentTerm');
        $payment_term->load($sorder->customerdetails->payment_term_id);

        $this->view->set('payment_types', $pt->getAll());
        $this->view->set('payment_type_default', $sorder->customerdetails->payment_type_id);

        $tax_value = 0;
        $net_value = 0;

        $orderline = DataObjectFactory::Factory('SOrderLine');

        $sh = new SearchHandler(new SOrderLineCollection($orderline), false);

        $sh->addConstraint(new Constraint('status', '=', $orderline->pickedStatus()));

        $sorder->addSearchHandler('lines', $sh);

        foreach ($sorder->lines as $orderlines) {
            $linetax = $orderlines->calcTax($sorder->customerdetails->tax_status_id, $payment_term);
            $tax_value = bcadd($tax_value, $linetax);
            $net_value = bcadd($net_value, $orderlines->net_value);
        }

        $this->view->set('net_value', $net_value);
        $this->view->set('tax_value', $tax_value);

        $gross_value = bcadd($net_value, $tax_value);

        $settlement_discount = $payment_term->calcSettlementDiscount($net_value);
        $gross_value = bcsub($gross_value, $settlement_discount);

        $this->view->set('settlement_discount', $settlement_discount);

        $this->view->set('gross_value', $gross_value);

        if (! is_null($sorder->person_id)) {
            $this->view->set('inv_addresses', $sorder->getPersonAddresses($sorder->person_id, array(
                'type' => 'billing'
            )));
            $this->_data['context'] = 'confirm_sale';
            $this->view->set('del_addresses', $sorder->getPersonAddresses($sorder->person_id, array(
                'type' => 'shipping'
            )));
            $this->view->set('people', array(
                $sorder->person_id => $sorder->person
            ));
        } else {
            $this->view->set('inv_addresses', $sorder->getInvoiceAddresses());
            $this->view->set('del_addresses', array(
                '' => 'Same as Invoice Address'
            ) + $sorder->getDeliveryAddresses());
            $this->view->set('people', $this->getPeople($sorder->slmaster_id));
        }

        $country = DataObjectFactory::Factory('Country');

        $this->view->set('countries', $country->getAll());

        $country = new DataField('countrycode');

        $this->view->set('country', $country->default_value);

        // get Sales Order Notes for person/customer
        $this->getNotes($sorder->person_id, $sorder->slmaster_id);
    }

    public function save_confirmsale()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $sorder = $this->_uses[$this->modeltype];

        $sorder_data = $this->_data[$this->modeltype];

        $flash = Flash::Instance();

        $errors = array();

        $db = DB::Instance();

        $db->startTrans();

        $slcustomer = DataObjectFactory::Factory('SLCustomer');
        $slcustomer->load($sorder->slmaster_id);

        // save the customer details

        // Existing or new person?
        $person_data = array();

        if (! empty($sorder_data['person_id']) && $sorder_data['person_id'] > 0) {
            // Existing person
            $sorder->person_id = $sorder_data['person_id'];

            $person = DataObjectFactory::Factory('Person');
            $person->load($sorder_data['person_id']);

            if ($person->isLoaded()) {
                $sorder_data['party_id'] = $person->party_id;
            }
        } elseif (! empty($sorder_data['surname'])) {
            // TODO: Saving of person and party data needs to move to appropriate model
            // See also EmployeesController, CompanysController and PersonsController
            // new person
            $sorder_data['party_id'] = '';
            $person_data['Party']['id'] = '';
            $person_data['Party']['type'] = 'Person';
            $person_data['Person']['title'] = $sorder_data['title'];
            $person_data['Person']['firstname'] = $sorder_data['firstname'];
            $person_data['Person']['surname'] = $sorder_data['surname'];
            $person_data['Person']['id'] = '';
            $person_data['Person']['party_id'] = '';
            $person_data['Person']['company_id'] = $slcustomer->company_id;

            $index = 0;

            foreach (array(
                'phone',
                'email'
            ) as $contact_type) {
                if (! empty($sorder_data[$contact_type])) {
                    $cm = DataObjectFactory::Factory('PartyContactMethod');
                    $person_data[$index]['Contactmethod']['id'] = '';
                    $person_data[$index]['Contactmethod']['contact'] = $sorder_data[$contact_type];
                    $person_data[$index]['PartyContactMethod']['id'] = '';
                    $person_data[$index]['PartyContactMethod']['contactmethod_id'] = '';
                    $person_data[$index]['PartyContactMethod']['party_id'] = '';
                    $person_data[$index]['PartyContactMethod']['name'] = 'MAIN';
                    $person_data[$index]['PartyContactMethod']['type'] = $cm->getType($contact_type);
                    $person_data[$index]['PartyContactMethod']['main'] = 't';
                    $person_data[$index]['PartyContactMethod']['billing'] = 't';
                    $person_data[$index]['PartyContactMethod']['shipping'] = 't';
                    $person_data[$index]['PartyContactMethod']['payment'] = 't';
                    $person_data[$index]['PartyContactMethod']['technical'] = 't';

                    $index ++;
                }
            }

            if (parent::save('Person', $person_data, $errors)) {
                foreach ($this->saved_models as $model) {
                    if (isset($model['Person'])) {
                        $person = $model['Person'];
                        $sorder->person_id = $person->id;
                        $sorder_data['party_id'] = $person->party_id;
                    }
                }
            } else {
                $errors[] = 'Error saving Customer Details : ' . $db->ErrorMsg();
            }
        }

        foreach (array(
            'invoice' => $sorder_data['inv_address_id'],
            'delivery' => $sorder_data['del_address_id']
        ) as $address_type => $address_id) {
            $address_data = array();

            if (! empty($sorder_data[$address_type]['street1'])) {
                // New address
                $address_data['Address']['id'] = '';
                $address_data['Address']['street1'] = $sorder_data[$address_type]['street1'];
                $address_data['Address']['street2'] = $sorder_data[$address_type]['street2'];
                $address_data['Address']['street3'] = $sorder_data[$address_type]['street3'];
                $address_data['Address']['town'] = $sorder_data[$address_type]['town'];
                $address_data['Address']['county'] = $sorder_data[$address_type]['county'];
                $address_data['Address']['postcode'] = $sorder_data[$address_type]['postcode'];
                $address_data['Address']['countrycode'] = $sorder_data[$address_type]['countrycode'];
                $address_data['PartyAddress']['address_id'] = '';
            } elseif (! empty($person_data['Person'])) {
                // Existing address needs to be linked to new person
                $address_data['PartyAddress']['address_id'] = $address_id;
            }

            if (! empty($sorder_data[$address_type]['street1']) || ! empty($person_data['Person'])) {
                // If new address or new person, link address to party
                $address_data['PartyAddress']['id'] = '';
                $address_data['PartyAddress']['party_id'] = $sorder_data['party_id'];
                $address_data['PartyAddress']['name'] = 'MAIN';
                $address_data['PartyAddress']['main'] = (($address_type == 'invoice') ? 't' : 'f');
                ;
                $address_data['PartyAddress']['billing'] = (($address_type == 'invoice') ? 't' : 'f');
                $address_data['PartyAddress']['shipping'] = (($address_type == 'delivery') ? 't' : 'f');
                $address_data['PartyAddress']['payment'] = (($address_type == 'invoice') ? 't' : 'f');
                $address_data['PartyAddress']['technical'] = 'f';

                if (parent::save('Address', $address_data, $errors)) {
                    foreach ($this->saved_models as $model) {
                        if (isset($model['Address'])) {
                            $address = $model['Address'];
                            if ($address_type == 'invoice') {
                                $sorder_data['inv_address_id'] = $address->id;
                            } else {
                                $sorder_data['del_address_id'] = $address->id;
                            }
                        }
                    }
                } else {
                    $errors[] = 'Error saving Customer ' . $address_type . ' Address Details : ' . $db->ErrorMsg();
                }
            }
        }

        if (! empty($sorder_data['inv_address_id']) && $sorder_data['inv_address_id'] > 0) {
            $sorder->inv_address_id = $sorder_data['inv_address_id'];
        }

        if (! empty($sorder_data['del_address_id']) && $sorder_data['del_address_id'] > 0) {
            $sorder->del_address_id = $sorder_data['del_address_id'];
        } else {
            $sorder->del_address_id = $sorder_data['inv_address_id'];
        }

        if (count($errors) > 0 || ! $sorder->save()) {
            $errors[] = 'Error updating customer order details';
        }

        // create and post the invoice
        if (count($errors) == 0) {
            $invoice = $this->createPostInvoice($sorder, $errors);
        }

        // Create the Payment
        if (count($errors) == 0) {
            $cb_data = array();

            $cb_data['cb_account_id'] = $slcustomer->cb_account_id;
            $cb_data['source'] = 'S';
            $cb_data['slmaster_id'] = $sorder->slmaster_id;
            $cb_data['company_id'] = $slcustomer->company_id;

            if ($sorder->person_id > 0) {
                $cb_data['person_id'] = $sorder->person_id;
            }

            $cb_data['ext_reference'] = $sorder_data['ext_reference'];
            $cb_data['payment_term_id'] = $slcustomer->payment_term_id;
            $cb_data['payment_type_id'] = $sorder_data['payment_type_id'];
            $cb_data['transaction_type'] = 'R';
            $cb_data['currency_id'] = $sorder->currency_id;
            $cb_data['transaction_date'] = date(DATE_FORMAT);
            // $cb_data['net_value'] = $sorder_data['gross_value'];
            $cb_data['net_value'] = bcsub($invoice->gross_value, $invoice->settlement_discount);

            if (! SLTransaction::saveTransaction($cb_data, $errors)) {
                $errors[] = 'Error saving Payment Details';
            }
        }

        // Match Payment to Invoice
        if (count($errors) == 0) {
            if ($invoice->isLoaded()) {
                $invoice_trans = DataObjectFactory::Factory('SLTransaction');
                $invoice_trans->identifierField = gross_value;

                $cc = new ConstraintChain();
                $cc->add(new Constraint('transaction_type', '=', 'I'));
                $cc->add(new Constraint('status', '=', 'O'));
                $cc->add(new Constraint('our_reference', '=', $invoice->invoice_number));

                $transactions = $invoice_trans->getAll($cc);

                $transactions[$cb_data['ledger_transaction_id']] = $cb_data['payment_value'];

                // Save settlement discount if present?
                if ($invoice->settlement_discount > 0) {
                    $payment_term = DataObjectFactory::Factory('PaymentTerm');
                    $payment_term->load($slcustomer->payment_term_id);

                    // Create GL Journal for settlement discount

                    $discount = $cb_data;

                    $discount['gross_value'] = $discount['net_value'] = $invoice->settlement_discount;

                    $discount['glaccount_id'] = $payment_term->sl_discount_glaccount_id;
                    $discount['glcentre_id'] = $payment_term->sl_discount_glcentre_id;

                    $discount['tax_value'] = '0.00';
                    $discount['source'] = 'S';
                    $discount['transaction_type'] = 'SD';
                    $discount['description'] = (! empty($payment_term->sl_discount_description) ? $payment_term->sl_discount_description . ' ' : '');
                    $discount['description'] .= $cb_data->description;
                    $discount['status'] = 'P';

                    $sldiscount = SLTransaction::Factory($discount, $errors, 'SLTransaction');

                    if ($sldiscount && $sldiscount->save('', $errors) && $sldiscount->saveGLTransaction($discount, $errors)) {
                        $transactions[$sldiscount->{$sldiscount->idField}] = bcadd($discount['net_value'], 0);
                    } else {
                        $errors[] = 'Errror saving Settlement Discount : ' . $db->ErrorMsg();
                        $flash->addErrors($errors);
                    }
                }

                if (! SLTransaction::allocatePayment($transactions, $invoice->slmaster_id, $errors) || ! SLAllocation::saveAllocation($transactions, $errors)) {
                    $errors[] = 'Error allocating Payment';
                }
            } else {
                $errors[] = 'Error matching to Invoice';
            }
        }

        // Check for errors
        if (count($errors) > 0) {
            $flash->clear();
            $flash->addErrors($errors);
            $flash->addError('Sale confirmation failed');
            $db->failTrans();
        } else {
            $flash->addMessage('Sale Confirmed');
        }

        $db->CompleteTrans();

        if (count($errors) == 0) {
            // now print the invoice to the users default printer
            $userPreferences = UserPreferences::instance(EGS_USERNAME);
            $defaultPrinter = $userPreferences->getPreferenceValue('default_printer', 'shared');
            if (empty($defaultPrinter)) {
                // Use normal print action
                // ATTN: what happens here? If we don't have a default printer set it's going to ignore our request?
                $this->_data['invoice_id'] = $invoice->id;
                $this->_data['printaction'] = 'printinvoice';
                parent::printaction();
                $this->printaction = array(
                    'Print' => 'Print'
                );
                $this->printtype = array(
                    'pdf' => 'PDF'
                );
                $this->_templateName = $this->getTemplateName('printaction');
                return;
            } else {
                // Overide print action
                $data = array();
                $data['invoice_id'] = $invoice->id;
                $data['printtype'] = 'pdf';
                $data['printaction'] = 'Print';
                $data['printer'] = $defaultPrinter;

                // call printInvoice and decode the response, output errors / messages
                $response = json_decode($this->printInvoice($invoice, $data));
                if ($response->status === true) {
                    $flash->addMessage('Print Sales Invoice Completed');
                    $invoice->update($invoice->id, array(
                        'date_printed',
                        'print_count'
                    ), array(
                        fix_date(date(DATE_FORMAT)),
                        $invoice->print_count + 1
                    ));
                } else {
                    $flash->addError('Print Sales Invoice Failed');
                    $flash->addError($response->message);
                }
            }

            // we're not using JavaScript to get here
            // so just go back to the order view
        }

        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $sorder->id
        ));
    }

    public function getCustomer($_slmaster_id = '')
    {
        if (isset($this->_uses['SLCustomer'])) {
            $customer = $this->_uses['SLCustomer'];
        } else {
            $customer = DataObjectFactory::Factory('SLCustomer');
        }

        if (! $customer->isLoaded() && ! empty($_slmaster_id)) {
            $customer->load($_slmaster_id);
        }

        $this->uses($customer, false);

        return $customer;
    }

    public function getPeople($_slmaster_id = '')
    {
        if ($_slmaster_id == '') {
            $_slmaster_id = $this->_data['slmaster_id'];
        }

        $customer = $this->getCustomer($_slmaster_id);

        if ($customer->isLoaded()) {
            $cc = new ConstraintChain();

            $cc->add(new Constraint('company_id', '=', $customer->company_id));

            $this->_templateobject->belongsTo[$this->_templateobject->belongsToField['person_id']]['cc'] = $cc;
        }

        $smarty_params = array(
            'nonone' => 'true',
            'depends' => 'slmaster_id'
        );
        unset($this->_data['depends']);

        return $this->getOptions($this->_templateobject, 'person_id', 'getPeople', 'getOptions', $smarty_params, $depends);
    }

    public function select_for_invoicing()
    {
        $despatched_orders = new SOrderLineCollection(DataObjectFactory::Factory('SOrderLine'));

        $despatched_orders->ordersForInvoicing();

        $order_ids = array();

        foreach ($despatched_orders as $order) {
            $order_ids[] = $order->id;
        }

        if (count($order_ids) > 0) {
            $orders = new SOrderCollection($this->_templateobject);
            $sh = new SearchHandler($orders, false);
            $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', $order_ids) . ')'));
            $orders->load($sh);

            $this->view->set('orders', $orders);
        } else {
            $flash = Flash::Instance();
            $flash->addWarning('No outstanding dispatched orders');
        }
    }

    public function saveForInvoicing()
    {
        $flash = Flash::Instance();
        $errors = array();
        $db = DB::Instance();
        $db->startTrans();

        if (isset($this->_data['sorders'])) {

            foreach ($this->_data['sorders'] as $id => $on) {

                $sorder = DataObjectFactory::Factory('SOrder');
                $sorder->load($id);
                $this->createPostInvoice($sorder, $errors);
                if (count($errors) > 0) {
                    break;
                }
            }

            if (count($errors) > 0) {
                $flash->addErrors($errors);
                $flash->addError('Invoice creation failed');
                $db->failTrans();
            } else {
                $flash->addMessage('Invoice Created and Posted OK');
                $db->CompleteTrans();
            }

            sendTo($this->name, 'index', $this->_modules);
        } else {
            $flash->addWarning('No orders selected');
            $this->refresh();
            $this->select_for_invoicing();
        }
    }

    public function getDeliveryTerm($_slmaster_id = '')
    {
        if ($_slmaster_id == '' && ! empty($this->_data['slmaster_id'])) {
            $_slmaster_id = $this->_data['slmaster_id'];
        }

        $customer = $this->getCustomer($_slmaster_id);

        if ($customer->isLoaded()) {
            return $customer->delivery_term_id;
        }

        return '';
    }

    public function payment_terms($_slmaster_id = '')
    {
        if ($_slmaster_id == '' && ! empty($this->_data['slmaster_id'])) {
            $_slmaster_id = $this->_data['slmaster_id'];
        }

        $customer = $this->getCustomer($_slmaster_id);

        $this->view->set('customer', $customer);

        return $this->view->fetch($this->getTemplateName(__FUNCTION__));
    }

    public function viewPacking_slips()
    {
        $order = $this->order_details();
        $linestatus = $order->getLineStatuses();
        $this->view->set('linevalue', $linestatus['value']);
        $this->view->set('clickaction', 'edit');
        $this->view->set('clickcontroller', 'sopackingslips');

        $sopacking = new SOPackingSlipCollection(DataObjectFactory::Factory('SOPackingSlip'));
        $sh = new SearchHandler($sopacking, false);
        $sh->addConstraint(new Constraint('order_id', '=', $order->id));
        parent::index($sopacking, $sh);
    }

    public function view_Transactions()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $works_order = $this->_uses[$this->modeltype];

        $transaction = DataObjectFactory::Factory('STTransaction');
        $transaction->setDefaultDisplayFields(array(
            'stitem' => 'stock_item',
            'created',
            'flocation' => 'from_location',
            'fbin' => 'from_bin',
            'whlocation' => 'to_location',
            'whbin' => 'to_bin',
            'qty',
            'error_qty',
            'balance',
            'status',
            'remarks'
        ));

        $related_collection = new STTransactionCollection($transaction);

        $sh = $this->setSearchHandler($related_collection);

        $sh->addConstraint(new Constraint('process_id', '=', $works_order->id));
        $sh->addConstraint(new Constraint('process_name', '=', 'SO'));
        $sh->addConstraint(new Constraint('qty', '>=', 0));
        $sh->addConstraint(new Constraint('error_qty', '>=', 0));

        parent::index($related_collection, $sh);

        $this->_templateName = $this->getTemplateName('view_related');
        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'stitems');
        $this->view->set('linkvaluefield', 'stitem_id');
        $this->view->set('related_collection', $related_collection);
        $this->view->set('collection', $related_collection);
        $this->view->set('no_ordering', true);
    }

    /*
     * Protected Functions
     */
    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((! empty($base)) ? $base : 'sales_order', $action);
    }

    /*
     * Private Functions
     */
    private function createPostInvoice($sorder, &$errors)
    {
        if ($sorder->customerdetails->accountStopped()) {
            $errors[] = 'Cannot create invoice, customer account stopped';
            return FALSE;
        }

        set_time_limit(0);

        $linestatuses = $sorder->getLineStatuses();
        $linestatus = $linestatuses['count'];

        if (! $sorder->someLinesDespatched($linestatus) && ! $sorder->someLinesPicked($linestatus)) {
            $errors[] = 'No lines picked or despatched';
            return FALSE;
        }

        $sinvoicelines = array();

        $db = DB::Instance();
        $db->startTrans();

        $sodespatchlines = array();
        $create_sodespatchlines = array();
        $latest_despatch_date = 0;
        $sorderline = DataObjectFactory::Factory('SOrderLine');
        $sorderlines = new SOrderLineCollection($sorderline);

        $sh = new SearchHandler($sorderlines, FALSE);
        $sh->addConstraint(new Constraint('order_id', '=', $sorder->id));

        $statuses = array(
            $db->qstr($sorderline->despatchStatus()),
            $db->qstr($sorderline->pickedStatus())
        );

        $sh->addConstraint(new Constraint('status', 'in', '(' . implode(',', $statuses) . ')'));

        $sorderlines->load($sh);

        $sodespatchline = DataObjectFactory::Factory('SODespatchLine');

        foreach ($sorderlines as $sorderline) {

            $sinvoiceline = array();

            $sodespatchlines[$sorderline->id]['id'] = $sorderline->id;
            $sodespatchlines[$sorderline->id]['line_number'] = $sorderline->line_number;
            if ($sorderline->status == $sorderline->pickedStatus()) {
                $sinvoiceline['move_stock'] = 't';
                $sorderline->del_qty = $sorderline->revised_qty;
                $sorderline->actual_despatch_date = fix_date(date(DATE_FORMAT));

                if (! is_null($sorderline->stitem_id)) {
                    $create_sodespatchlines[$sorder->id][$sorderline->id] = SODespatchLine::makeline($sorder, $sorderline, $errors);
                    $create_sodespatchlines[$sorder->id][$sorderline->id]['status'] = $sodespatchline->despatchStatus();
                }

                $sodespatchlines[$sorderline->id]['fields'] = array(
                    'status',
                    'del_qty',
                    'actual_despatch_date'
                );
                $sodespatchlines[$sorderline->id]['values'] = array(
                    $sorderline->invoicedStatus(),
                    $sorderline->del_qty,
                    $sorderline->actual_despatch_date
                );
            } else {
                $sodespatchlines[$sorderline->id]['fields'] = array(
                    'status'
                );
                $sodespatchlines[$sorderline->id]['values'] = array(
                    $sorderline->invoicedStatus()
                );
            }

            foreach ($sorderline->getFields() as $key => $value) {
                $sinvoiceline[$key] = $sorderline->$key;
            }

            foreach ($sorderline->audit_fields as $field) {
                unset($sinvoiceline[$field]);
            }

            if ($sorderline->actual_despatch_date > $latest_despatch_date) {
                $latest_despatch_date = $sorderline->actual_despatch_date;
            }

            $sinvoiceline['sales_order_id'] = $sorderline->order_id;
            $sinvoiceline['order_line_id'] = $sorderline->id;
            $sinvoiceline['sales_qty'] = $sorderline->del_qty;
            $sinvoiceline['sales_price'] = $sorderline->price;
            $sinvoiceline['net_value'] = bcmul($sorderline->price, $sorderline->del_qty);
            $sinvoiceline['glaccount_centre_id'] = null;
            $sinvoiceline['id'] = '';

            $sinvoicelines[] = $sinvoiceline;
        }

        if (count($errors) == 0 && count($create_sodespatchlines) > 0 && ! SODespatchLine::createDespatchNote($create_sodespatchlines, $errors)) {
            $errors[] = 'Error creating Despatch Note';
        }

        foreach ($sodespatchlines as $id => $data) {

            $data['fields'][] = 'glaccount_centre_id';
            $data['values'][] = 'null';

            $result = $this->_uses['SOrderLine']->update($id, $data['fields'], $data['values']);

            if (! $result) {
                $errors['id' . $id] = 'Failed to update order line ' . $data['line_number'] . ' : ' . $db->errorMsg();
            }
        }

        if (count($errors) == 0) {

            // Check line statuses and update header status accordingly
            if (! $sorder->save()) {
                $errors[] = 'Failed to update order header ' . $db->ErrorMsg();
            }
        }

        if (count($sinvoicelines) == 0) {
            $errors[] = 'Failed to create invoice lines';
        }

        // Save the Invoice Header if no errors
        if (count($errors) == 0) {

            $sinvoiceheader = array();

            foreach ($sorder->getFields() as $key => $value) {
                $sinvoiceheader[$key] = $sorder->$key;
            }

            foreach ($sorder->audit_fields as $field) {
                unset($sinvoiceheader[$field]);
            }

            $sinvoiceheader['id'] = '';
            $sinvoiceheader['sales_order_id'] = $sorder->id;
            $sinvoiceheader['sales_order_number'] = $sorder->order_number;
            $sinvoiceheader['despatch_date'] = un_fix_date($latest_despatch_date);
            $sinvoiceheader['transaction_type'] = 'I';
            $sinvoiceheader['invoice_date'] = un_fix_date($latest_despatch_date);

            $sinvoice = SInvoice::Factory($sinvoiceheader, $errors);

            if ($sinvoice) {

                $result = $sinvoice->save();

                if (! $result) {
                    $errors[] = 'Failed to create invoice';
                }
            }
        }

        // Save the Invoice Lines if no errors
        if (count($errors) == 0) {

            $result = FALSE;

            foreach ($sinvoicelines as $line_data) {

                $sinvoiceline = SInvoiceLine::Factory($sinvoice, $line_data, $errors);

                if ($sinvoiceline) {
                    $result = $sinvoiceline->save();
                }

                if (! $result) {
                    $errors[] = 'Failed to create invoice line ' . $db->ErrorMsg();
                    break;
                }
            }

            // Now save the header again to update the totals
            $result = $sinvoice->save();

            if (! $result) {
                $errors[] = 'Failed to update invoice totals';
                $errors[] = $db->ErrorMsg();
            }
        }

        // Post the Invoice if no errors
        if (count($errors) == 0) {

            $result = $sinvoice->post($errors);

            if (! $result) {
                $errors[] = 'Failed to post invoice';
            }

            foreach ($sodespatchlines as $id => $data) {
                $sodespatchlines = new SODespatchLineCollection($sodespatchline);

                $sh = new SearchHandler($sodespatchlines, FALSE);
                $sh->addConstraint(new Constraint('orderline_id', '=', $id));
                $sh->addConstraint(new Constraint('status', '=', $sodespatchline->despatchStatus()));
                $sh->addConstraint(new Constraint('invoice_id', 'is', 'NULL'));

                if ($sodespatchlines->update(array(
                    'invoice_id',
                    'invoice_number'
                ), array(
                    $sinvoice->id,
                    $sinvoice->invoice_number
                ), $sh) === false) {
                    $errors[] = 'Error updating despatch lines : ' . $db->ErrorMsg();
                }
            }
        }

        if (count($errors) > 0) {
            $db->FailTrans();
            $result = FALSE;
        } else {
            // Everything is OK so return the newly created Invoice object
            $result = $sinvoice;
        }

        $db->CompleteTrans();
        return $result;
    }

    private function order_details()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sorder = $this->_uses[$this->modeltype];
        $id = $sorder->id;
        if (isset($this->_data['updatetype'])) {
            $this->view->set('updatetype', $this->_data['updatetype']);
        }
        $this->view->set('sorder', $sorder);

        $address = DataObjectFactory::Factory('Address');
        if ($sorder->del_address_id) {
            $address->load($sorder->del_address_id);
        }
        $this->view->set('delivery_address', $address);

        return $sorder;
    }

    /* output functions */

    /**
     * printInvoice: similar in consturction to other FOP print functions, however
     * this is more restrictive, at the time only used as part of save_confirmsale
     *
     * @param DataObject $invoice
     * @param array $data
     */
    protected function printInvoice($invoice, $data)
    {
        $options = array(
            'filename' => 'SalesInvoice' . $invoice->id,
            'report' => 'SalesInvoice'
        );

        // prepare the extra array
        $extra = array();

        $tax_status = DataObjectFactory::Factory('TaxStatus');
        $tax_status->load($invoice->tax_status_id);

        // get the company address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());
        $extra['company_address'] = $company_address;

        // get the company details
        $extra['company_details'] = $this->getCompanyDetails();

        // get the invoice address
        $invoice_address = array();
        $invoice_address['name'] = $invoice->customer;
        if (! is_null($invoice->person_id)) {
            $invoice_address['person'] = $invoice->person;
        }
        $invoice_address += (array) $this->formatAddress($invoice->getInvoiceAddress());
        $extra['invoice_address'] = $invoice_address;

        $extra['tax_description'] = $invoice->customerdetail->companydetail->tax_description;
        $extra['vatnumber'] = $invoice->customerdetail->companydetail->vatnumber;

        // get Sales Invoice Notes for default customer or first in customer
        $note = DataObjectFactory::Factory('PartyNote');
        $party_id = $invoice->customerdetail->companydetail->party_id;

        $cc = new ConstraintChain();
        $note->orderby = 'lastupdated';
        $note->orderdir = 'DESC';
        $cc->add(new Constraint('note_type', '=', 'sales_invoicing'));
        $cc->add(new Constraint('party_id', '=', $party_id));

        $latest_note = $note->loadBy($cc);
        $extra['notes'] = $latest_note->note;

        // get the delivery address
        $delivery_address = $invoice->customer . ", " . $invoice->getDeliveryAddress()->fulladdress;
        $extra['delivery_address'] = $delivery_address;

        // get the settlement terms
        if ($invoice->transaction_type == 'I') {
            $settlement_terms = $invoice->getSettlementTerms();
            $extra['settlement_terms'] = $settlement_terms;

            $bank_account = $invoice->customerdetail->bank_account_detail;

            if (! is_null($bank_account->bank_account_number)) {
                $extra['bank_account']['bank_name'] = $bank_account->bank_name;
                $extra['bank_account']['bank_sort_code'] = $bank_account->bank_sort_code;
                $extra['bank_account']['bank_account_number'] = $bank_account->bank_account_number;
                $extra['bank_account']['bank_address'] = $bank_account->bank_address;
                $extra['bank_account']['bank_iban_number'] = $bank_account->bank_iban_number;
                $extra['bank_account']['bank_bic_code'] = $bank_account->bank_bic_code;
            }
        }

        // get invoice totals
        $invoice_totals = array();
        $invoice_totals[]['line'][] = array(
            'field1' => 'NET VALUE',
            'field2' => $invoice->net_value . ' ' . $invoice->currency
        );
        $invoice_totals[]['line'][] = array(
            'field1' => 'VAT',
            'field2' => $invoice->tax_value . ' ' . $invoice->currency
        );
        $invoice_totals[]['line'][] = array(
            'field1' => strtoupper($invoice->getFormatted('transaction_type')) . ' TOTAL',
            'field2' => $invoice->gross_value . ' ' . $invoice->currency
        );
        $extra['invoice_totals'] = $invoice_totals;

        // get invoice vat analysis
        if ($tax_status->eu_tax == 't' && $tax_status->apply_tax == 'f') {
            $extra['vat_analysis_exempt'][]['line'] = 'Zero rated intra-EU supply';
            $extra['vat_analysis_exempt'][]['line'] = 'Not subject to UK VAT under Article 56 Directive 2006/112/EC';
        } else {
            $vat_analysis = $invoice->vatAnalysis();
            foreach ($vat_analysis as $key => $value) {
                $extra['vat_analysis'][]['line'][] = $value;
            }
        }

        // generate the XML, include the extras array too
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $invoice,
            'relationship_whitelist' => array(
                'lines'
            ),
            'extra' => $extra
        ));

        // construct the document, capture the response
        return $this->constructOutput($data, $options);
    }

    public function printOrderList($status = 'generate')
    {

        // this function is very extensive, and thus we'll remove the max_execution_time
        set_time_limit(0);

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
            'filename' => 'SOrderList' . fix_date(date(DATE_FORMAT)),
            'report' => 'SalesOrderList'
        );

        if (strtolower($status) == "dialog") {
            return $options;
        }

        // load the model
        $orders = new SOrderCollection($this->_templateobject);
        $sh = new SearchHandler($orders, false);
        $sh->addConstraint(new Constraint('status', '=', $this->_templateobject->newStatus()));
        $orders->load($sh);

        // build extra array
        $extra = array(
            'title' => prettify($title) . ' Sales Orders as at ' . un_fix_date(fix_date(date(DATE_FORMAT))),
            'showlines' => true
        );

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $orders,
            'extra' => $extra,
            'relationship_whitelist' => array(
                'lines'
            )
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    public function printAcknowledgement($status = 'generate')
    {
        // set the models
        $order = $this->_uses[$this->modeltype];
        $order->load($this->_data['id']);

        // set type / name depending on the order type
        if ($order->type == 'Q') {
            $document_type = 'Quote';
            $document_name = 'SQ';
            $report_definition = 'SOQuote';
        } else {
            $document_type = 'Order Acknowledgement';
            $document_name = 'SOack';
            $report_definition = 'SOacknowledgement';
        }

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
            'filename' => $document_name . $order->order_number,
            'report' => $report_definition
        );

        if (strtolower($status) == 'dialog') {
            // show the main dialog
            // pick up the options from above, use these to shape the dialog
            return $options;
        }

        /* generate document */

        // set the extra array
        $extra = array();

        // get the title
        $extra['title'] = 'SALES ' . strtoupper($document_type);

        // get company_address address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());
        $extra['company_address'] = $company_address;

        // get the company details
        $extra['company_details'] = $this->getCompanyDetails();

        // get customer address
        $customer_address = array(
            'title' => 'Customer Address:',
            'customer' => $order->customer
        );

        $customer = $this->getCustomer($order->slmaster_id);
        $tax_status = DataObjectFactory::Factory('TaxStatus');
        $tax_status->load($customer->tax_status_id);

        $extra['show_vat'] = $tax_status->apply_tax;

        if (! is_null($order->person_id)) {
            $names = explode(',', $order->person);
            $customer_address += array(
                'person' => $names[1] . ' ' . $names[0]
            );
        }

        $customer_address += $this->formatAddress($order->getInvoiceAddress());
        $extra['customer_address'] = $customer_address;
        $extra['customer_number'] = $customer->accountnumber();
        $extra['price_type'] = $order->customer->so_price_type;

        // get delivery address
        $delivery_address = array(
            'title' => 'Delivery Address:',
            'customer' => $order->customer
        );
        $delivery_address += $this->formatAddress($order->getDeliveryAddress());
        $extra['delivery_address'] = $delivery_address;

        // get order details
        $order_details = array();

        $order_details[]['line'] = array(
            'label' => 'Your Reference:',
            'value' => $order->ext_reference
        );
        $order_details[]['line'] = array(
            'label' => 'Order Date:',
            'value' => un_fix_date($order->order_date)
        );
        $order_details[]['line'] = array(
            'label' => 'Our Order Number: ',
            'value' => $order->order_number
        );
        $order_details[]['line'] = array(
            'label' => 'Due Date: ',
            'value' => un_fix_date($order->due_date)
        );

        $extra['order_details'] = $order_details;

        // Set variables for invoice values
        $vat_total = 0;
        $gross_total = 0;

        // Calculate net + vat for each item on order
        $payment_term = DataObjectFactory::Factory('PaymentTerm');
        $payment_term->load($order->customer->payment_term_id);

        foreach ($order->lines as $orderlines) {
            if ($orderlines->status == 'X') {
                continue;
            }
            // tax (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
            // this function is a wrapper to a call to a config-dependent method
            // $tax_percentage=calc_tax_percentage($orderlines->tax_rate_id,$customer->tax_status_id,$orderlines->net_value);

            // tax_value is the tax percentage of the net value
            // $tax_total=trunc(bcmul($orderlines->net_value,$tax_percentage,4),2);
            $tax_total = $orderlines->calcTax($order->customer->tax_status_id, $payment_term);

            // Construct totals for generic info
            $vat_total = bcadd($vat_total, $tax_total);

            $line_gross = bcadd($orderlines->net_value, $tax_total);

            $gross_total = bcadd($line_gross, $gross_total);

            $orderlines->setAdditional('vat_value', 'numeric');
            $orderlines->vat_value = $tax_total;

            $orderlines->setAdditional('gross_value', 'numeric');
            $orderlines->gross_value = $line_gross;

            // $extra['lines'][] = $orderlines;
        }

        $currency = new CurrencyFormatter($order->currency_id);

        $extra['order_totals']['VAT'] = $currency->format($vat_total);
        $extra['order_totals']['gross'] = $currency->format($gross_total);

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $order,
            'relationship_whitelist' => array(
                'lines'
            ),
            'extra' => $extra
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    public function printProFormaInvoice($status = 'generate')
    {

        // load the model
        $order = $this->_uses[$this->modeltype];
        $order->load($this->_data['id']);

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
            'filename' => 'SoProForma' . $order->order_number,
            'report' => 'SOProFormaInvoice'
        );

        if (strtolower($status) == "dialog") {

            // show the main dialog
            // pick up the options from above, use these to shape the dialog

            return $options;
        }

        /* generate document */

        // get the original data
        $saved_data = $this->decode_original_form_data($this->_data['encoded_query_data']);

        // we should never get to this point and have no lines
        if (count($saved_data['SOrderLine']) == 0) {
            echo $this->returnResponse(FALSE, array(
                'message' => 'No lines selected for the Pro Forma'
            ));
            exit();
        }

        // construct an array of the selected lines
        $selected_lines = array();

        foreach ($saved_data['SOrderLine'] as $id => $select) {

            if (isset($select['select_line'])) {
                $selected_lines[] = $id;
            }
        }

        $sorderline = DataObjectFactory::Factory('SOrderLine');

        $sh = new SearchHandler(new SOrderLineCollection($sorderline), false);
        $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', $selected_lines) . ')'));

        $order->addSearchHandler('lines', $sh);

        // construct extra array
        $extra = array();

        // load a few models for later
        $customer = $this->getCustomer($order->slmaster_id);
        $bank_account = $order->customerdetails->bank_account_detail;

        $payment_term = DataObjectFactory::Factory('PaymentTerm');
        $payment_term->load($customer->payment_term_id);

        // get invoice address
        $inv_address = array(
            'customer' => $order->customer
        );

        if (! is_null($order->person_id)) {
            $inv_address = array(
                'person' => $order->person
            );
        }

        $inv_address += $this->formatAddress($order->getInvoiceAddress());

        $extra['invoice_address'] = $inv_address;

        // get company_address address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());

        $extra['company_address'] = $company_address;

        // get the company details
        $extra['company_details'] = $this->getCompanyDetails();

        // get document details
        $document_reference = array();

        $document_reference[]['line'] = array(
            'label' => 'Date',
            'value' => un_fix_date($order->order_date)
        );
        $document_reference[]['line'] = array(
            'label' => 'Sales Order',
            'value' => $order->order_number
        );
        $document_reference[]['line'] = array(
            'label' => 'Your Ref',
            'value' => $order->ext_reference
        );
        $document_reference[]['line'] = array(
            'label' => 'Del Date',
            'value' => un_fix_date($order->despatch_date)
        );
        $document_reference[]['line'] = array(
            'label' => 'Del Note',
            'value' => $order->delivery_note
        );

        $extra['document_reference'] = $document_reference;

        // wish us luck... we're about to calculate VAT!

        // Return tax percentage and vat anlysis message

        // Set variables for invoice values
        $net_total = 0;
        $vat_total = 0;
        $inv_total = 0;

        $vat_analysis = '';
        $vat_rate = '';
        $vat_amount = '';
        $net_amount = '';

        $taxrate = array();

        // Calculate net + vat for each item on order
        foreach ($order->lines as $orderlines) {

            // tax (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
            // this function is a wrapper to a call to a config-dependent method
            // $tax_percentage=calc_tax_percentage($orderlines->tax_rate_id,$customer->tax_status_id,$orderlines->net_value);

            // tax_value is the tax percentage of the net value
            // $tax_total=trunc(bcmul($orderlines->net_value,$tax_percentage,4),2);

            $tax_total = $orderlines->calcTax($customer->tax_status_id, $payment_term);

            // Construct totals for generic info
            $net_total = bcadd($orderlines->net_value, $net_total);
            $vat_total = bcadd($vat_total, $tax_total);

            // Construct array for summary info$orderlines->_data['tax_rate_id']
            if (isset($taxrate[$orderlines->tax_rate_id]['vat'])) {
                $taxrate[$orderlines->tax_rate_id]['vat'] += $tax_total;
            } else {
                $taxrate[$orderlines->tax_rate_id]['vat'] = $tax_total;
            }

            if (isset($taxrate[$orderlines->tax_rate_id]['net'])) {
                $taxrate[$orderlines->tax_rate_id]['net'] += $orderlines->net_value; // $order->net_value;
            } else {
                $taxrate[$orderlines->tax_rate_id]['net'] = $orderlines->net_value; // $order->net_value;
            }
        }

        // Set invoice total
        $inv_total = bcadd($net_total, $vat_total);

        $vat_analysis = array();

        // Construct generic order information
        foreach ($taxrate as $key => $value) {

            $rate = DataObjectFactory::Factory('TaxRate');
            $rate->load($key);

            $vat_analysis[]['line'] = array(
                'description' => $rate->description,
                'currency' => $order->currency,
                'tax_rate' => $rate->percentage,
                'net_amount' => number_format($taxrate[$key]['net'], 2, '.', ''),
                'tax_amount' => number_format($taxrate[$key]['vat'], 2, '.', '')
            );
        }

        $extra['vat_analysis'] = $vat_analysis;

        // get invoice totals
        $invoice_totals = array();

        $invoice_totals[]['line'] = array(
            'label' => 'NET VALUE',
            'value' => number_format($net_total, 2, '.', '') . ' ' . $order->currency
        );
        $invoice_totals[]['line'] = array(
            'label' => 'VAT',
            'value' => number_format($vat_total, 2, '.', '') . ' ' . $order->currency
        );
        $invoice_totals[]['line'] = array(
            'label' => strtoupper($order->getFormatted('type')) . ' TOTAL',
            'value' => number_format($inv_total, 2, '.', '') . ' ' . $order->currency
        );

        $extra['invoice_totals'] = $invoice_totals;

        // get delivery address
        $extra['delivery_address'] = $order->customer . ", " . $order->getDeliveryAddress()->fulladdress;

        // get bank details
        if (! is_null($bank_account->bank_account_number)) {
            $extra['bank_account']['bank_name'] = $bank_account->bank_name;
            $extra['bank_account']['bank_sort_code'] = $bank_account->bank_sort_code;
            $extra['bank_account']['bank_account_number'] = $bank_account->bank_account_number;
            $extra['bank_account']['bank_address'] = $bank_account->bank_address;
            $extra['bank_account']['bank_iban_number'] = $bank_account->bank_iban_number;
            $extra['bank_account']['bank_bic_code'] = $bank_account->bank_bic_code;
        }

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $order,
            'extra' => $extra,
            'relationship_whitelist' => array(
                'lines'
            )
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    public function printPickList($status = 'generate')
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
            'filename' => 'SOPickList' . $id,
            'report' => 'SOPickList'
        );

        if (strtolower($status) == "dialog") {
            return $options;
        }

        // load the model
        $order = $this->_uses[$this->modeltype];
        $order->load($this->_data['id']);

        // build line status condition
        $line = DataObjectFactory::Factory('SOrderLine');

        $line_status = array(
            '\'' . $line->newStatus() . '\'',
            '\'' . $line->partDespatchStatus() . '\''
        );

        // constrain the transactions relationship
        $sh = new SearchHandler(new SOrderLineCollection($line), false);
        $sh->addConstraint(new Constraint('status', 'IN', '(' . implode(",", $line_status) . ')'));
        $order->addSearchHandler('lines', $sh);

        // construct extra array
        $extra = array();

        // get company address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());
        $extra['company_address'] = $company_address;

        // get the current user
        $user = getCurrentUser();

        // get order details
        $order_details = array();

        $order_details[]['line'] = array(
            'label' => 'Picked By:',
            'value' => $user->getPersonName()
        );
        $order_details[]['line'] = array(
            'label' => 'Order Date:',
            'value' => un_fix_date($order->order_date)
        );
        $order_details[]['line'] = array(
            'label' => 'Our Order Number:',
            'value' => $order->order_number
        );
        $order_details[]['line'] = array(
            'label' => 'Customer Ref:',
            'value' => $order->ext_reference
        );
        $order_details[]['line'] = array(
            'label' => 'Due Date:',
            'value' => un_fix_date($order->due_date)
        );

        $extra['order_details'] = $order_details;

        // get delivery address
        if (! is_null($order->person_id)) {
            $extra['delivery_details']['person'] = $order->person;
        }

        if (! is_null($order->slmaster_id)) {
            $extra['delivery_details']['customer'] = $order->customer;
        }

        $extra['delivery_details']['full_address'] = implode($this->formatAddress($order->del_address), ", ");

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => array(
                $order
            ),
            'extra' => $extra,
            'relationship_whitelist' => array(
                'lines'
            )
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    /**
     * Print a Delivery Address Label for a Sales Order
     *
     * @param string $status
     *            Either, 'dialog' to display the print dialog
     *            or 'generate' to generate the document
     *
     * @return array Options to be passed back
     *
     * @see printController::printDialog()
     */
    public function printAddressLabel($status = 'generate')
    {
        // set the models
        $order = $this->_uses[$this->modeltype];
        $order->load($this->_data['id']);

        /* generate dialog */
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
            'filename' => 'SOADL' . $order->order_number,
            'report' => 'SOAddressLabel'
        );

        if (strtolower($status) == 'dialog') {
            return $options;
        }

        /* generate document */
        $extra = array(
            'delivery_address' => array(
                $this->formatAddress($order->getDeliveryAddress())
            ),
            'account_number' => $order->customerdetails->companydetail->accountnumber
        );

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $order,
            'extra' => $extra
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }


    /* Ajax functions */
    public function getBalance($_stitem_id = '', $_location_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['stitem_id'])) {
                $_stitem_id = $this->_data['stitem_id'];
            }
            if (! empty($this->_data['location_id'])) {
                $_location_id = $this->_data['location_id'];
            }
        }

        $stbalance = DataObjectFactory::Factory('STBalance');
        $balance = $stbalance->getStockBalance($_stitem_id, $_location_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $balance);
            $this->setTemplateName('text_inner');
        } else {
            return $balance;
        }
    }

    /* consolodation functions */
    public function getCustomerData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        $this->_data['ajax_call'] = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_slmaster_id = $this->_data['slmaster_id'];

        $customer = $this->getCustomer($_slmaster_id);

        $person_id = $this->getPeople($_slmaster_id);
        $output['person_id'] = array(
            'data' => $person_id,
            'is_array' => is_array($person_id)
        );

        $payment_terms = $this->payment_terms($_slmaster_id);
        $output['payment_terms'] = array(
            'data' => $payment_terms,
            'is_array' => is_array($payment_terms)
        );

        // get default delivery term for customer
        $customer_term = $this->getDeliveryTerm($_slmaster_id);
        $output['delivery_term'] = array(
            'data' => $customer_term,
            'is_array' => is_array($customer_term)
        );

        $project_id = $this->getProjects($_slmaster_id);
        $output['project_id'] = array(
            'data' => $project_id,
            'is_array' => is_array($project_id)
        );

        $output['company_id'] = array(
            'data' => $customer->company_id,
            'is_array' => false
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    public function getPersonData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_person_id = $this->_data['person_id'];
        $_slmaster_id = $this->_data['slmaster_id'];
        $_del_type = $this->_data['del_type'];
        $_inv_type = $this->_data['inv_type'];

        $del_address_id = $this->getPersonAddresses($_person_id, $_del_type, $_slmaster_id);

        if (! empty($this->_data['context']) && $this->_data['context'] == 'confirm_sale') {
            $del_address_id = array(
                '' => 'Same as Invoice Address'
            ) + $del_address_id;
        }
        $output['del_address_id'] = array(
            'data' => $del_address_id,
            'is_array' => is_array($del_address_id)
        );

        $inv_address_id = $this->getPersonAddresses($_person_id, $_inv_type, $_slmaster_id);
        $output['inv_address_id'] = array(
            'data' => $inv_address_id,
            'is_array' => is_array($inv_address_id)
        );

        $notes = $this->getNotes($_person_id, $_slmaster_id);
        $output['notes'] = array(
            'data' => $notes,
            'is_array' => is_array($notes)
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        if ($ajax) {
            $this->view->set('data', $output);
            $this->setTemplateName('ajax_multiple');
        } else {
            return $output;
        }
    }

    public function getProjects($_slmaster_id = '')
    {
        // Used by Ajax to return projects for a customer after selecting the Customer
        if ($_slmaster_id == '') {
            $_slmaster_id = $this->_data['slmaster_id'];
        }

        $customer = $this->getCustomer($_slmaster_id);
        $cc = new ConstraintChain();
        if ($customer->isLoaded()) {
            $cc = new ConstraintChain();
            $cc->add(new Constraint('company_id', '=', $customer->company_id));
            $this->_templateobject->belongsTo[$this->_templateobject->belongsToField['project_id']]['cc'] = $cc;
        }

        $smarty_params = array(
            'nonone' => 'true',
            'depends' => 'slmaster_id'
        );
        unset($this->_data['depends']);

        return $this->getOptions($this->_templateobject, 'project_id', 'getProjects', 'getOptions', $smarty_params, $depends);
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

// End of SordersController
