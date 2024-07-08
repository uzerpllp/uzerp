<?php

/**
 *	uzERP Planned Purchase Orders Controller
 *
 *	@author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2018 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class PoplannedController extends PrintController
{

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);
        $this->_templateobject = DataObjectFactory::Factory('POPlanned');
        $this->uses($this->_templateobject);
    }

    /**
     * Display a list view of planned orders
     */
    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $s_data = array();
        $this->setSearch('poplannedSearch', 'useDefault', $s_data);
        $this->view->set('clickaction', 'view');
        $plannedorders = new POPlannedCollection($this->_templateobject);
        parent::index($plannedorders);
        $plannedorders_objects = $plannedorders->getContents();

        $this->view->set('plannedorders', $plannedorders);
        $this->view->set('clickaction', null);
    }

    /**
     * Create a Puchase Order in uzERP
     * 
     * Uses selected planned orders as the basis for order lines
     */
    public function createOrder() {
        $session = new Session();
        $flash = Flash::Instance();
        $errors = [];

        try {
            // Retrieve and check the selected planned orders
            $selections = array_keys($this->_data['update']);
            if (count($selections) == 0) {
                throw new NewOrderException('No planned orders selected');
            }

            // Retrieve a planned order collection
            $plannedorders = new POPlannedCollection($this->_templateobject);
            $sh = new SearchHandler($plannedorders, false);
            $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', $selections) . ')'));
            $plannedorders->load($sh);

            // Check all selections are for the same supplier
            $supplier_id = $plannedorders->current()->plmaster_id;
            foreach ($plannedorders as $order) {
                if ($order->plmaster_id != $supplier_id) {
                    throw new NewOrderException('All selected orders must be for one supplier');
                }
                $supplier_id = $order->plmaster_id;
            }
        } catch (NewOrderException $e) {
            $flash->addWarning($e->getMessage());
            $session->form_data = $this->_data['update'];
            sendBack();
        }

        try {
            $db = DB::Instance();
            $db->startTrans();
            
            // Load header data
            $header = [];
            $header['type'] = 'O';

            $supplier = DataObjectFactory::Factory('PLSupplier');
            $supplier->load($supplier_id);
            $header['plmaster_id'] = $supplier_id;
            
            if ($supplier) {
                $header['currency_id'] = $supplier->currency_id;
                $header['payment_term_id'] = $supplier->payment_term_id;
                $header['tax_status_id'] = $supplier->tax_status_id;
                $header['receive_action'] = $supplier->receive_action;
            }
            
            $transferrules = new WHTransferruleCollection();
            $locations = $transferrules->getToLocations($supplier->receive_action, array());

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
            } else {
                throw new NewOrderException('No Receive Into action set for supplier');
            }

            if (empty($header['del_address_id'])) {
                $company = DataObjectFactory::Factory('Systemcompany');
                $company->load(EGS_COMPANY_ID);
                $companyAddress = $company->getCompanyAddress();
                $header['del_address_id'] = $companyAddress->id;
            }

            $porder = POrder::Factory($header, $errors);
            if ($porder && count($errors) == 0) {
                $result = $porder->save($errors);
            } else {
                $flash->addErrors($errors);
                $result = false;
            }

            if ($result == false) {
                throw new NewOrderException('Failed to add order number');
            }

            // Create lines
            $polines = new POrderLineCollection();
            $plannedorders->rewind();
            $line_number = 1;
            foreach ($plannedorders as $order) {
                if ($order->qty <= 0) {
                    throw new NewOrderException("Invalid order quantity for {$order->item_code}");
                }
                $net_value = 0;

                $productlines = new POProductlineCollection();
                $cc = new ConstraintChain();
                $cc->add(new Constraint('stitem_id', '=', $order->stitem_id));
                $cc->add(new Constraint('plmaster_id', '=', $supplier_id));
                $sh = new SearchHandler($productlines, false);
                $sh->addConstraint($cc);
                $productlines->load($sh);

                if (count($productlines)  > 1|| count($productlines)  == 0) {
                    throw new NewOrderException("Cannot locate prices for {$order->item_code} from {$order->supplier_name}, order not created");
                }
                $line_data['productline_id'] = $productlines->current()->id;
                $line_data['price'] = $productlines->current()->price;
                $net_value += $productlines->current()->price * $order->qty;
                
                
                $line_data['status'] = 'N';
                $line_data['revised_qty'] = $order->qty;
                $line_data['due_delivery_date'] = un_fix_date($order->delivery_date);
                $line_data['line_number'] = $line_number;
                $new_poline = POrderline::Factory($porder, $line_data, $errors);

                if ($new_poline && count($errors)==0) {
                    if ($porder->due_date < $new_poline->due_delivery_date)	{
                        $porder->due_date = $new_poline->due_delivery_date;
                    }
                    $polines->add($new_poline);
                    $line_number += 1;
                } else {
                    throw new NewOrderException('Failed to add an order line');
                }
            }

            if (!$polines->save()) {
                throw new NewOrderException('Failed to save order lines');
            }
            
            $porder->net_value = $net_value;
            $porder->save();

            $plannedorders->rewind();
            foreach ($plannedorders as $order) {
                if (!$order->delete($order->id)) {
                    throw new NewOrderException('Failed to delete planned order');
                }
            }
            $db->completeTrans();
            $flash->addMessage("Purchase Order {$porder->order_number} Added");
        } catch (NewOrderException $e) {
            $flash->addErrors([$e->getMessage(), 'Order not added']);
            $db->failTrans();
            $session->form_data = $this->_data['update'];
            sendBack();
        }
        $session->form_data = false;
        sendBack();
    }

}

/**
 * Custom Exception Class
 */
class NewOrderException extends Exception {};
