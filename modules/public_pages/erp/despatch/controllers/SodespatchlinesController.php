<?php

/**
 *  Sodespatchline Controller
 *
 *  @package despatch
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class SodespatchlinesController extends printController
{

    protected $version = '$Revision: 1.40 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('SODespatchLine');

        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['slmaster_id'])) {
            $s_data['slmaster_id'] = $this->_data['slmaster_id'];
        }

        if (isset($this->_data['status'])) {
            $s_data['status'] = $this->_data['status'];
        } else {
            $s_data['status'] = 'N';
        }

        if (isset($this->_data['order_number'])) {
            $s_data['order_number'] = $this->_data['order_number'];
        }

        if (isset($this->_data['invoice_number'])) {
            $s_data['invoice_number'] = $this->_data['invoice_number'];
        }

        $this->setSearch('sodespatchSearch', 'useDefault', $s_data);

        $this->view->set('clickaction', 'view');

        $coll = new SODespatchLineCollection($this->_templateobject);

        $coll->orderby = array(
            'despatch_date',
            'despatch_number'
        );
        $coll->direction = array(
            'DESC',
            'DESC'
        );

        parent::index($coll);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['viewOrders'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewByOrders'
            )),
            'tag' => 'View Orders for Despatch'
        );
        $actions['print'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewAwaitingDespatch',
                'type' => 'print'
            )),
            'tag' => 'Print Despatch Notes'
        );
        $actions['confirm'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewAwaitingDespatch',
                'type' => 'confirm'
            )),
            'tag' => 'Confirm Despatches'
        );
        $actions['cancel'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewAwaitingDespatch',
                'type' => 'cancel'
            )),
            'tag' => 'Cancel Despatches'
        );
        $actions['invoice'] = array(
            'link' => array(
                'module' => 'sales_order',
                'controller' => 'sorders',
                'action' => 'select_for_invoicing'
            ),
            'tag' => 'invoice_despatched_orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function delete()
    {
        $flash = Flash::Instance();

        parent::delete('SODespatchLine');

        sendTo($this->name, 'index', $this->_modules);
    }

    public function save()
    {
        $flash = Flash::Instance();

        if (parent::save('SODespatchLine')) {
            sendTo($this->name, 'index', $this->_modules);
        } else {
            $this->refresh();
        }
    }

    public function view()
    {
        $errors = array();

        $despatchheader = $this->getDespatchHeader($this->_data, $errors);

        if (! $despatchheader->isLoaded()) {
            $flash = Flash::Instance();
            $flash->addErrors($errors);
            sendTo($this->name, 'index', $this->_modules);
        }

        $despatch_number = $despatchheader->despatch_number;
        $despatch_date = $despatchheader->despatch_date;

        $this->view->set('despatch_number', $despatch_number);

        $order = DataObjectFactory::Factory('SOrder');
        $order->load($despatchheader->order_id);
        $this->view->set('order', $order);

        $address = DataObjectFactory::Factory('Address');

        if ($order->del_address_id) {
            $address = $address->load($order->del_address_id);
        }
        $this->view->set('delivery_address', $address);

        $despatchnote = new SODespatchLineCollection($this->_templateobject);

        $sh = new SearchHandler($despatchnote, false);
        $sh->addConstraint(new Constraint('despatch_number', '=', $despatch_number));

        $despatchnote->load($sh);
        $this->view->set('despatchlines', $despatchnote);

        $sidebar = new SidebarController($this->view);
        $actions = array();

        $actions['viewnotes'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'index'
            )),
            'tag' => 'view despatch notes'
        );

        $actions['viewOrders'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewByOrders'
            )),
            'tag' => 'View Orders for Despatch'
        );
        $actions['confirm'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewAwaitingDespatch',
                'type' => 'confirm'
            )),
            'tag' => 'Confirm Despatches'
        );
        $actions['cancel'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewAwaitingDespatch',
                'type' => 'cancel'
            )),
            'tag' => 'Cancel Despatches'
        );
        if ($despatchheader->status == 'N') {
            $actions['print'] = array(
                'link' => array_merge($this->_modules, array(
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printDespatchNote',
                    'filename' => 'DN' . $despatch_number,
                    'despatch_number' => $despatch_number,
                    'despatch_date' => $despatch_date,
                    'order_id' => $order->id
                )),
                'tag' => 'Print Despatch Note'
            );
        }

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function cancel_despatchnote()
    {
        if (! $this->checkParams('SODespatchLine')) {
            sendBack();
        }

        $flash = Flash::Instance();
        $db = DB::Instance();
        $db->StartTrans();
        $errors = array();

        foreach ($this->_data['SODespatchLine'] as $key => $value) {

            // Check Customer
            if (! isset($value['slmaster_id'])) {
                $errors['DN' . $key] = 'Cannot find Customer reference for DN' . $key;
                $db->FailTrans();
                continue;
            }

            $customer = DataObjectFactory::Factory('SLCustomer');
            $customer->load($value['slmaster_id']);

            if (! $customer->isLoaded()) {
                $errors['DN' . $key] = 'Cannot find Customer details for DN' . $key;
                $db->FailTrans();
                continue;
            }

            if (isset($value['cancel_despatch'])) {
                // Get all the despatch lines for this Despatch Note
                $despatches = new SODespatchLineCollection($this->_templateobject);
                $sh = new SearchHandler($despatches, false);
                $sh->addConstraint(new Constraint('despatch_number', '=', $key));
                $despatches->load($sh);

                foreach ($despatches as $despatch) {
                    $despatchline = DataObjectFactory::Factory('SODespatchLine');

                    $result = $despatchline->update($despatch->id, 'status', 'X');

                    if ($result === false) {
                        $flash->addError('Error updating Despatch Note ' . $despatch_note);
                        $db->FailTrans();
                        sendBack();
                    }

                    $orderline = DataObjectFactory::Factory('SOrderLine');
                    $result = $orderline->update($despatch->orderline_id, 'delivery_note', 'null');

                    if ($result === false) {
                        $flash->addError('Error updating order line');
                        $db->FailTrans();
                        sendBack();
                    }
                }
            }
        }

        if (count($errors) === 0 && $db->CompleteTrans()) {
            $flash->addMessage('Despatch Notes cancelled');
            sendTo($this->name, 'index', $this->_modules);
        } else {
            $flash->addErrors($errors);
            $db->FailTrans();
            sendBack();
        }
    }

    public function confirm_despatch()
    {
        if (! $this->checkParams('SODespatchLine')) {
            sendBack();
        }

        $flash = Flash::Instance();
        $db = DB::Instance();
        $db->StartTrans();
        $errors = array();

        foreach ($this->_data['SODespatchLine'] as $key => $value) {

            if (isset($value['confirm_despatch'])) {
                // Check Customer
                if (! isset($value['slmaster_id'])) {
                    $errors['DN' . $key] = 'Cannot find Customer reference for DN' . $key;
                    $db->FailTrans();
                    continue;
                }

                $customer = DataObjectFactory::Factory('SLCustomer');
                $customer->load($value['slmaster_id']);

                if (! $customer->isLoaded()) {
                    $errors['DN' . $key] = 'Cannot find Customer details for DN' . $key;
                    $db->FailTrans();
                    continue;
                } elseif ($customer->accountStopped()) {
                    $errors['DN' . $key] = 'Cannot Confirm Despatch for DN' . $key . ' (' . $customer->name . ') Account Stopped';
                    $db->FailTrans();
                    continue;
                }

                // Get all the despatch lines for this Despatch Note
                $despatches = new SODespatchLineCollection($this->_templateobject);

                $sh = new SearchHandler($despatches, false);
                $sh->addConstraint(new Constraint('despatch_number', '=', $key));

                $despatches->load($sh);

                foreach ($despatches as $despatch) {
                    if ($data['stitem_id'] != '') {
                        // Create transaction pair for Dispatch
                        $data = array();
                        $data['qty'] = $despatch->despatch_qty;
                        $data['process_name'] = 'D';
                        $data['process_id'] = $despatch->despatch_number;
                        $data['whaction_id'] = $despatch->despatch_action;
                        $data['stitem_id'] = $despatch->stitem_id;

                        $result = false;

                        if (STTransaction::getTransferLocations($data, $errors)) {
                            $models = STTransaction::prepareMove($data, $errors);
                            if (count($errors) === 0) {
                                foreach ($models as $model) {
                                    $result = $model->save($errors);
                                    if ($result === false) {
                                        break;
                                    }
                                }
                            }
                        }

                        if ($result === false) {
                            $flash->addErrors($errors);
                            $flash->addError('Error updating stock');
                            $db->FailTrans();
                            sendBack();
                        }
                    }

                    $despatchline = DataObjectFactory::Factory('SODespatchLine');

                    $result = $despatchline->update($despatch->id, 'status', 'D');

                    if ($result === false) {
                        $flash->addError('Error updating Despatch Note ' . $despatch_note);
                        $db->FailTrans();
                        sendBack();
                    }

                    $orderline = DataObjectFactory::Factory('SOrderLine');

                    $orderline->load($despatch->orderline_id);

                    $data = array();

                    $data['id'] = $despatch->orderline_id;
                    $data['os_qty'] = $orderline->os_qty - $despatch->despatch_qty;
                    $data['del_qty'] = $despatch->despatch_qty;
                    $data['actual_despatch_date'] = date(DATE_FORMAT);
                    $data['status'] = 'D';

                    $orderline = DataObject::Factory($data, $errors, 'SOrderLine');

                    if ($orderline) {
                        $result = $orderline->save();
                    } else {
                        $result = false;
                    }

                    if ($result === false) {
                        $flash->addError('Error updating order line ' . $db->ErrorMsg());
                        $db->FailTrans();
                        sendBack();
                    }

                    $order = DataObjectFactory::Factory('SOrder');

                    if ($order->load($orderline->order_id)) {
                        if (! $order->save()) {
                            $flash->addError('Error updating order ' . $db->ErrorMsg());
                            $db->FailTrans();
                            sendBack();
                        }
                    }
                }
            }
        }

        if (count($errors) === 0 && $db->CompleteTrans()) {
            $flash->addMessage('Despatches confirmed');
            sendTo($this->name, 'index', $this->_modules);
        } else {
            $flash->addErrors($errors);
            $db->FailTrans();
            sendBack();
        }
    }

    public function print_despatch_notes()
    {
        if (! $this->checkParams('SODespatchLine')) {
            sendBack();
        }

        $flash = Flash::Instance();
        $errors = array();

        $print_count = 0;

        foreach ($this->_data['SODespatchLine'] as $key => $value) {

            // Check Customer
            if (! isset($value['slmaster_id'])) {
                $errors['DN' . $key] = 'Cannot find Customer reference for DN' . $key;
                continue;
            }

            $customer = DataObjectFactory::Factory('SLCustomer');
            $customer->load($value['slmaster_id']);

            if (! $customer->isLoaded()) {
                $errors['DN' . $key] = 'Cannot find Customer details for DN' . $key;
                continue;
            } elseif ($customer->accountStopped()) {
                $errors['DN' . $key] = 'Cannot Print Despatch for DN' . $key . ' (' . $customer->name . ') Account Stopped';
                continue;
            }

            if (count($errors) === 0 && isset($value['print_despatch'])) {
                // Get the Despatch Header
                $header = $this->getDespatchHeader(array(
                    'despatch_number' => $key
                ), $errors);

                $this->_data['despatch_number'] = $key;
                $this->_data['despatch_date'] = $header->due_despatch_date;
                $this->_data['order_id'] = $header->order_id;
                $this->_data['print']['print_copies'] = $value['print_copies'];

                // $response=json_decode($this->printDespatchNote(),true);
                // return $response['status'];

                $this->printDespatchNote();

                $print_count ++;
            }
        }

        if (count($errors) === 0) {
            $flash->addMessage($print_count . ' Despatch Notes printed');
            sendTo($this->name, 'index', $this->_modules);
        } else {
            $flash->addErrors($errors);
            sendBack();
        }
    }

    public function save_despatchnote()
    {
        if (! $this->checkParams('sodespatchlines')) {
            sendBack();
        }

        $flash = Flash::Instance();
        $db = DB::Instance();
        $db->StartTrans();

        $errors = array();
        $despatch = array();
        $despatchline = array();
        // Group Despatch Lines By Order
        foreach ($this->_data['sodespatchlines'] as $key => $value) {
            $orderline = DataObjectFactory::Factory('SOrderLine');

            $orderline->load($key);

            if ($orderline) {
                $order = DataObjectFactory::Factory('SOrder');
                $order->load($orderline->order_id);
                $despatch[$order->id][$orderline->id] = SODespatchLine::makeLine($order, $orderline, $errors);
            }
        }

        if (SODespatchLine::createDespatchNote($despatch, $errors) && count($errors) === 0 && $db->CompleteTrans()) {
            $flash->addMessage('Despatch Notes added successfully');
            sendTo($this->name, 'index', $this->_modules);
        } else {
            $errors[] = 'Error creating Despatch Note';
            $flash->addErrors($errors);
            $db->FailTrans();
            $db->CompleteTrans();
            $this->refresh();
        }
    }

    public function viewByOrders()
    {
        $cc = new ConstraintChain();

        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $cc->add(new Constraint('stitem_id', '=', $id));
        } elseif (isset($this->_data['order_id'])) {
            $order_id = $this->_data['order_id'];
            $cc->add(new Constraint('order_id', '=', $order_id));
        } else {
            $cc->add(new Constraint('type', '=', 'O'));
        }

        $cc->add(new Constraint('status', '=', 'R'));
        $order = new SOrderCollection();
        $order->orderby = array(
            'delivery_note',
            'order_number',
            'line_number'
        );
        $order->direction = array(
            'ASC',
            'ASC',
            'ASC'
        );
        $orders = $order->getItemOrders($cc);
        // Create an array of items ordered
        $stitems = array();
        foreach ($orders as $row) {
            $stitems[$row->stitem_id]['in_stock'] = 0;

            if (! isset($stitems[$row->stitem_id]['despatch_action'][$row->despatch_action])) {
                $transferrules = new WHTransferruleCollection(DataObjectFactory::Factory('WHTransferrule'));

                $locations = $transferrules->getFromLocations($row->despatch_action);

                if (count($locations) > 0 and $row->stitem_id) // ignore PLs without stitem
{
                    // Should never be zero or somethingis very wrong!
                    $cc = new ConstraintChain();
                    $cc->add(new Constraint('stitem_id', '=', $row->stitem_id));
                    $cc->add(new Constraint('whlocation_id', 'in', '(' . implode(',', array_keys($locations)) . ')'));
                    $stitems[$row->stitem_id]['despatch_action'][$row->despatch_action] = STBalance::getBalances($cc);
                } else {
                    $stitems[$row->stitem_id]['despatch_action'][$row->despatch_action] = 0;
                    // Flag it as a non-stock item
                    $stitems[$row->stitem_id]['non-stock'] = true;
                }
            }
        }

        // And check orders for stock availability
        // Items not available cannot be despatched!
        $items = array();

        foreach ($orders as $key => $row) {

            // Exclude any order lines that cannot be fulfilled
            // or have already been despatched
            $stitems[$row->stitem_id]['despatch_action'][$row->despatch_action] -= $row->required;

            $sorder = DataObjectFactory::Factory('SOrder');

            $sorder->load($row->order_id);

            $items[$row->order_number]['order_number'] = $row->order_number;
            $items[$row->order_number]['order_id'] = $row->order_id;
            $items[$row->order_number]['customer'] = $row->customer;
            $items[$row->order_number]['del_address'] = $sorder->del_address->address;

            if ($stitems[$row->stitem_id]['non-stock']) // We can always despatch non-stock items
{
                $items[$row->order_number]['line_number'][$row->line_number]['despatch'] = true;
            } elseif ($stitems[$row->stitem_id]['despatch_action'][$row->despatch_action] >= 0) {
                $items[$row->order_number]['line_number'][$row->line_number]['despatch'] = true;
            } else {
                $items[$row->order_number]['line_number'][$row->line_number]['despatch'] = false;
            }

            $items[$row->order_number]['line_number'][$row->line_number]['despatch_action'] = $row->despatch_action;
            $items[$row->order_number]['due_despatch_date'] = $row->due_despatch_date;
            $items[$row->order_number]['line_number'][$row->line_number]['stitem'] = $row->stitem;
            $items[$row->order_number]['line_number'][$row->line_number]['item_description'] = $row->item_description;
            $items[$row->order_number]['line_number'][$row->line_number]['delivery_note'] = $row->delivery_note;
            $items[$row->order_number]['line_number'][$row->line_number]['required'] = $row->required;
            $items[$row->order_number]['line_number'][$row->line_number]['stuom'] = $row->stuom;
            $items[$row->order_number]['line_number'][$row->line_number]['id'] = $row->id;
        }

        $this->view->set('orders', $items);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['viewnotes'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'index'
            )),
            'tag' => 'view despatch notes'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('page_title', $this->getPageName('Despatch', 'View Order Lines for'));
    }

    public function viewAwaitingDespatch()
    {
        $despatches = new SODespatchLineCollection($this->_templateobject);
        $sh = new SearchHandler($despatches, false);
        // $sh->extract();
        $sh->addConstraint(new Constraint('status', '=', 'N'));
        $despatches->load($sh);

        $orders = array();
        foreach ($despatches as $despatch) {
            $orders[$despatch->despatch_number] = $despatch;
        }

        if ($this->_data['type'] == 'cancel') {
            $this->_templateName = $this->getTemplateName('canceldespatch');
            $this->view->set('page_title', $this->getPageName('- Cancel Despatch'));
        } elseif ($this->_data['type'] == 'confirm') {
            $this->_templateName = $this->getTemplateName('confirmdespatch');
            $this->view->set('page_title', $this->getPageName('- Confirm Despatch'));
            // Could enable selection of despatch method here!
            // Need to change template to display drop down list - see sorders/new.tpl
            // $whaction=new WHAction();
            // $despatch_actions=$whaction->getActions('D');
            // $this->view->set('despatch_actions',$despatch_actions);
        } elseif ($this->_data['type'] == 'print') {
            $this->_templateName = $this->getTemplateName('print_despatch_notes');
            $this->view->set('page_title', $this->getPageName('- Print Despatch Notes'));
            $this->view->set('printers', $this->selectPrinters());
            $this->view->set('default_printer', $this->getDefaultPrinter());
        } else {
            $this->dataError();
            sendBack();
        }

        $this->view->set('clickaction', 'view');
        $this->view->set('orders', $orders);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['viewnotes'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'index'
            )),
            'tag' => 'view despatch notes'
        );
        $actions['viewfordispatch'] = array(
            'link' => array_merge($this->_modules, array(
                'controller' => $this->name,
                'action' => 'viewbyorders'
            )),
            'tag' => 'view Orders for Despatch'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    /* protected functions */
    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((empty($base) ? 'despatches' : $base), $action);
    }

    /* output functions */
    public function printDespatchNote($status = 'generate')
    {
        $despatch_number = $this->_data['despatch_number'];
        $despatch_date = $this->_data['despatch_date'];
        $order_id = $this->_data['order_id'];

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
            'filename' => 'DN' . $despatch_number,
            'report' => 'DespatchNote'
        );

        if (strtolower($status) == "dialog") {
            return $options;
        }

        // load the model
        $despatchnote = new SODespatchLineCollection($this->_templateobject);

        $sh = new SearchHandler($despatchnote, false);
        $sh->addConstraint(new Constraint('despatch_number', '=', $despatch_number));

        $despatchnote->load($sh);

        $order = DataObjectFactory::Factory('SOrder');
        $order->load($order_id);

        // get the company address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());
        $extra['company_address'] = $company_address;

        // get the company details
        $extra['company_details']['tel'] = 'Tel  : ' . $this->getContactDetails('T');
        $extra['company_details']['fax'] = 'Fax  : ' . $this->getContactDetails('F');
        $extra['company_details']['email'] = 'email: ' . $this->getContactDetails('E');

        // get the despatch location
        $extra['despatch_location'] = implode(',', $order->despatch_from->rules_list('from_location'));

        // get customer account number
        $customer = DataObjectFactory::Factory('SLCustomer');
        $customer->load($order->slmaster_id);

        $customername = $customer->name;

        $extra['account_number'] = $customer->accountnumber();

        // get customer address
        $customer_address = array(
            'title' => 'Customer Address:',
            'name' => $customername
        );
        $customer_address += $this->formatAddress($customer->getBillingAddress());
        $extra['customer_address'] = $customer_address;

        // get delivery address
        $delivery_address = array(
            'title' => 'Delivery Address:',
            'name' => $customername
        );
        $delivery_address += $this->formatAddress($customer->getDeliveryAddress($order->del_address_id));
        $extra['delivery_address'] = $delivery_address;

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generate_xml(array(
            'model' => array(
                $order,
                $despatchnote
            ),
            'extra' => $extra
        ));

        // ATTN: We're handling multiple models in this example, but we also
        // want to handle an individual relationship_whitelist for both models

        // execute the print output function, echo the returned json for jquery
        $json_response = $this->generate_output($this->_data['print'], $options);

        // if($response->status===true) {
        //
        // }

        // now we've done our checks, output the original JSON for jQuery to use
        // echo the response if we're using ajax, return the response otherwise
        if (isset($this->_data['ajax'])) {
            echo $json_response;
        } else {
            return $json_response;
        }

        exit();
    }

    /* private functions */
    private function getDespatchHeader($data = array(), &$errors = array())
    {

        // Currently, there is no Despatch Header table there are only Despatch Lines
        // This function constructs a 'Header' containing the common data from the
        // despatch lines
        $despatchline = DataObjectFactory::Factory('SOdespatchline');

        if (isset($data['id'])) {
            $despatchline->load($data['id']);
        } elseif (isset($data['despatch_number'])) {
            $despatchline->loadBy('despatch_number', $data['despatch_number']);
        }

        if (! $despatchline->isLoaded()) {
            $errors[] = 'Error getting Despatch Note details';
            return $despatchline;
        }

        return $despatchline;
    }
}

// End of SodespatchlinesController
