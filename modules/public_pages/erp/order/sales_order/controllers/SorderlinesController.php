<?php

/**
 *	Sales Order Lines Controller
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
class SorderlinesController extends printController
{

    use SOactionAllowedOnStop;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('SOrderLine');

        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $this->view->set('clickaction', 'edit');

        parent::index(new SOrderLineCollection($this->_templateobject));

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', array(
            'new' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => $this->name,
                    'action' => 'new'
                )),
                'tag' => 'new_SOrderLine'
            )
        ));

        $this->view->register('sidebar', $sidebar);

        $this->view->set('sidebar', $sidebar);
    }

    public function delete()
    {
        if (empty($this->_data['SOrderLine']['id'])) {
            $this->dataError();
            sendBack();
        }

        $flash = Flash::Instance();

        $sorderline = $this->_uses[$this->modeltype];

        $sorderline->load($this->_data['SOrderLine']['id']);

        if ($sorderline->isLoaded() && $sorderline->delete()) {
            $flash->addMessage($sorderline->header->getFormatted('type') . ' Line Deleted');

            if (isset($this->_data['dialog'])) {
                $link = array(
                    'modules' => $this->_modules,
                    'controller' => 'sorders',
                    'action' => 'view',
                    'other' => array(
                        'id' => $sorderline->order_id
                    )
                );

                $flash->save();

                echo parent::returnJSONResponse(TRUE, array(
                    'redirect' => '/?' . setParamsString($link)
                ));
                exit();
            } else {
                sendTo('sorders', 'view', $this->_modules, array(
                    'id' => $sorderline->order_id
                ));
            }
        }

        $flash->addError('Error deleting ' . $sorderline->header->getFormatted('type') . ' Line');

        $this->_data['id'] = $this->_data['SOrderLine']['id'];
        $this->_data['order_id'] = $this->_data['SOrderLine']['order_id'];

        $this->refresh();
    }

    public function _new()
    {
        $flash = Flash::Instance();

        parent::_new();

        // Get the Order Line Object - if loaded, this is an edit
        $sorderline = $this->_uses[$this->modeltype];

        if (! $sorderline->isLoaded()) {
            if (empty($this->_data['order_id'])) {
                $flash->addError('No Sales Order supplied');
                sendBack();
            }
            $sorderline->order_id = $this->_data['order_id'];
        }

        $sorder = DataObjectFactory::Factory('SOrder');
        $sorder->load($sorderline->order_id);

        // Prevent any changes to the order line if the customer is on stop
        if ($sorder->isLoaded() and !$this->actionAllowedOnStop($sorder->customerdetails))
        {
            $flash->addError($sorder->getFormatted('type') . ' cannot be changed');
            sendBack();
        }

        $_slmaster_id = $sorder->slmaster_id;

        if (isset($this->_data[$this->modeltype])) {
            // We've had an error so refresh the page
            $_slmaster_id = $this->_data['SOrder']['slmaster_id'];

            $sorderline->line_number = $this->_data['SOrderLine']['line_number'];

            $_product_search = $this->_data['SOrderLine']['product_search'];

            if (! empty($this->_data['SOrderLine']['productline_id'])) {
                $_productline_id = $this->_data['SOrderLine']['productline_id'];
            } else {
                $_productline_id = '';
            }
            $_glaccount_id = $this->_data['SOrderLine']['glaccount_id'];
        } elseif ($sorderline->isLoaded()) {
            // This needs changing - get the product line if productline_id not null
            // then, check if this is in productline_options, if not, add it
            $_product_search = $sorderline->description;
            $_productline_id = $sorderline->productline_id;
            $_glaccount_id = $sorderline->glaccount_id;
        } else {
            $sorderline->due_despatch_date = $sorder->despatch_date;
            $sorderline->due_delivery_date = $sorder->due_date;
        }

        $display_fields = $sorderline->getDisplayFields();

        if (isset($display_fields['product_search'])) {
            if (empty($_product_search)) {
                $_product_search = 'None';
                $productline_options = array(
                    '' => 'None'
                );
            } else {
                $productline_options = $this->getProductLines($_slmaster_id, $_product_search);
            }
        } else {
            $productline_options = $this->getProductLines($_slmaster_id);
        }

        if (empty($_productline_id)) {
            $_productline_id = key($productline_options);
        }

        $this->view->set('display_fields', $display_fields);
        $this->view->set('product_search', $_product_search);
        $this->view->set('productline_options', $productline_options);

        $data = $this->getProductLineData($_productline_id, $_slmaster_id);

        $this->view->set('stuom_options', $data['stuom_id']);
        $this->view->set('glaccount_options', $data['glaccount_id']);

        if (empty($_glaccount_id)) {
            $_glaccount_id = key($data['glaccount_id']);
        }

        $this->view->set('glcentre_options', $this->getCentre($_glaccount_id, $_productline_id));
        $this->view->set('taxrate_options', $data['tax_rate_id']);
        $this->view->set('sorder', $sorder);
    }

    public function save()
    {
        $flash = Flash::Instance();

        $errors = array();

        $data = $this->_data['SOrderLine'];
        $action = $this->_data['original_action'];

        if ( $action === 'edit' && isset($data['productline_id'])) {
            $flash->addError('Product change not allowed, please cancel the line and add a new one.');
            sendBack();
        }

        if (empty($data['order_id'])) {
            $errors[] = 'Order header not defined';
        } else {
            $sorder = DataObjectFactory::Factory('SOrder');

            $sorder->load($data['order_id']);

            if (! $sorder->isLoaded()) {
                $errors[] = 'Cannot find order header';
            } elseif ($sorder->isLatest($this->_data['SOrder'], $errors)) {
                if (isset($data['cancel_line'])) {
                    $data['status'] = $this->_templateobject->cancelStatus();
                    $data['glaccount_centre_id'] = NULL;
                }

                $sorderline = SOrderLine::Factory($sorder, $data, $errors);

                if ($sorder->due_date < $sorderline->due_delivery_date) {
                    $sorder->due_date = $sorderline->due_delivery_date;
                }

                if ($sorder->due_date < $sorderline->due_delivery_date) {
                    $sorder->despatch_date = $sorderline->due_despatch_date;
                }

                if ($sorderline && count($errors) == 0) {
                    if (! $sorderline->save($sorder)) {
                        $errors[] = 'Failed to save order line';
                    }
                }
            }
        }

        if (count($errors) == 0) {
            $flash->addMessage($sorder->getFormatted('type') . ' Line Saved');

            if (isset($this->_data['saveAnother'])) {
                $other = array(
                    'order_id' => $sorderline->order_id
                );
                sendTo($this->name, 'new', $this->_modules, $other);
            } else {
                $action = 'view';
                $controller = 'sorders';
                $other = array(
                    'id' => $sorderline->order_id
                );
            }

            sendTo($controller, $action, $this->_modules, $other);
        } else {
            $flash->addErrors($errors);
            $this->_data['id'] = $this->_data['SOrderLine']['id'];
            $this->_data['order_id'] = $this->_data['SOrderLine']['order_id'];
            $this->refresh();
        }
    }

    // Private Functions
    private function buildProductLines($customer = '', $productsearch = '')
    {
        // return the Product Lines list for a Customer
        $orderlines = array(
            '' => 'None'
        );

        $productlines = DataObjectFactory::Factory('SOProductline');

        if (! empty($customer)) {
            $orderlines += $productlines->getCustomerLines($customer, $productsearch);
        } else {
            $orderlines += $productlines->getNonSPecific($productsearch);
        }

        return $orderlines;
    }

    private function getAccount()
    {
        $account_list = array();

        $accounts = DataObjectFactory::Factory('GLAccount');

        $cc = new ConstraintChain();

        $cc->add(new Constraint('control', '=', 'FALSE'));

        return $accounts->getAll($cc);
    }

    private function getProductLineData($_productline_id = '', $_slmaster_id = '')
    {
        $data = array();

        if (! empty($_productline_id)) {
            $productline = DataObjectFactory::Factory('SOProductline');

            $productline->load($_productline_id);
            
            $header = DataObjectFactory::Factory('SOProductlineHeader');
            $header->load($productline->productline_header_id);
            $stitem = DataObjectFactory::Factory('STItem');
            $stitem->load($header->stitem_id);

            if ($productline->isLoaded()) {
                $data['description'] = $productline->description;
                $data['price'] = $productline->getPrice('', '', $_slmaster_id);
                $data['stuom_id'] = array(
                    $productline->product_detail->stuom_id => $productline->product_detail->uom_name
                );
                if ($stitem->isLoaded()) {
                    $data['sales_stock'] = $stitem->pickableBalance();
                }

                $account = DataObjectFactory::Factory('GLAccount');
                $account->load($productline->glaccount_id);

                $data['glaccount_id'] = array(
                    $account->id => $account->account . ' - ' . $account->description
                );

                $tax_rate = DataObjectFactory::Factory('TaxRate');
                $tax_rate->load($productline->product_detail->tax_rate_id);

                $data['tax_rate_id'] = array(
                    $tax_rate->id => $tax_rate->description
                );
            }
        } else {
            $data['description'] = $this->getDefaultValue('SOrderLine', 'item_description', '');
            $data['price'] = $this->getDefaultValue('SOrderLine', 'price', '0');
            $data['stuom_id'] = $this->getUomList();
            $data['glaccount_id'] = $this->getAccount();
            $data['tax_rate_id'] = $this->getTaxRate();
        }

        return $data;
    }

    private function getTaxRate()
    {
        $tax_rate_list = array();

        $tax_rates = DataObjectFactory::Factory('TaxRate');

        return $tax_rates->getAll();
    }

    private function getUomList()
    {
        $uom_list = array();

        $uom = DataObjectFactory::Factory('STuom');

        return $uom->getAll();
    }

    // Ajax stuff!
    public function getProductLines($_slmaster_id = '', $_product_search = '', $_limit = '')
    {
        // Used by Ajax to return Product Lines list after selecting the Customer
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
            if (! empty($this->_data['product_search'])) {
                $_product_search = $this->_data['product_search'];
            }
            if (! empty($this->_data['limit'])) {
                $_limit = $this->_data['limit'];
            }
        }

        $productlist = $this->buildProductLines($_slmaster_id, $_product_search);

        if (! empty($_limit) && count($productlist) > $_limit) {
            $productlist = array(
                '' => 'Refine Search - List > ' . $_limit
            );
        } elseif (! empty($_product_search) && count($productlist) > 1) {
            unset($productlist['']);
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $productlist);
            $this->setTemplateName('select_options');
        } else {
            return $productlist;
        }
    }

    public function getCentres($_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        // Used by Ajax to return Centre list after selecting the Account
        $account = DataObjectFactory::Factory('GLAccount');
        $account->load($_id);
        $centres = $account->getCentres();

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $centres);
            $this->view->set('model', $this->_templateobject);
            $this->view->set('attribute', 'glcentre_id');
            $this->setTemplateName('select');
        } else {
            return $centres;
        }
    }

    public function getCentre($_glaccount_id = '', $_productline_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['glaccount_id'])) {
                $_glaccount_id = $this->_data['glaccount_id'];
            }
            if (! empty($this->_data['productline_id'])) {
                $_productline_id = $this->_data['productline_id'];
            }
        }

        // Used by Ajax to return Centre list after selecting the Product
        $account_list = array();

        if ($_productline_id > 0) {
            $product = DataObjectFactory::Factory('SOProductline');
            $product->load($_productline_id);
            $centre = DataObjectFactory::Factory('GLCentre');
            $centre->load($product->glcentre_id);
            $centre_list[$centre->id] = $centre->cost_centre . ' - ' . $centre->description;
        } else {
            $account = DataObjectFactory::Factory('GLAccount');
            $account->load($_glaccount_id);
            $centre_list = $account->getCentres();
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $centre_list);
            $this->view->set('model', $this->_templateobject);
            $this->view->set('attribute', 'glcentre_id');
            $this->setTemplateName('select');
        } else {
            return $centre_list;
        }
    }

    /* consolodation functions */
    public function getLineData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_productline_id = $this->_data['productline_id'];
        $_slmaster_id = $this->_data['slmaster_id'];

        $data = $this->getProductLineData($_productline_id, $_slmaster_id);

        $data['stuom_id'] = $this->buildSelect('', 'stuom_id', $data['stuom_id']);
        $data['glaccount_id'] = $this->buildSelect('', 'glaccount_id', $data['glaccount_id']);
        $data['tax_rate_id'] = $this->buildSelect('', 'tax_rate_id', $data['tax_rate_id']);

        foreach ($data as $field => $values) {
            $output[$field] = array(
                'data' => $values,
                'is_array' => is_array($values)
            );
        }

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    /**
     * Override the view page_title, set in viewRelated, using the order type
     *
     * @see Controller::viewRelated()
     */
    protected function viewRelated($name) {
        parent::viewRelated($name);
        $order = DataObjectFactory::Factory('SOrder');
        $order->load($this->_data['order_id']);
        $order_type = $order->getFormatted('type');
        $this->view->set('page_title', "View ${order_type} Lines");
    }

    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((! empty($base)) ? $base : 'sales_order_line', $action);
    }
}

// End of SorderlinesController
