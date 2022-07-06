<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class soproductlineheadersController extends printController
{

    protected $version = '$Revision: 1.17 $';

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('SOProductlineHeader');

        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $this->view->set('clickaction', 'view');

        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['slmaster_id'])) {
            $s_data['slmaster_id'] = $this->_data['slmaster_id'];
        }
        if (isset($this->_data['status'])) {
            $s_data['status'] = $this->_data['status'];
        }

        if (! isset($this->_data['Search']) || isset($this->_data['Search']['clear'])) {
            $s_data['start_date/end_date'] = date(DATE_FORMAT);
        }

        $this->setSearch('productlinesSearch', 'headerDefault', $s_data);

        parent::index(new SOProductlineHeaderCollection($this->_templateobject));
        $sidebar = new SidebarController($this->view);
        $actions = array();

        $actions['all_lines'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'soproductlines',
                'action' => 'index'
            ),
            'tag' => 'view all product lines'
        );

        $actions['new'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'new_product'
        );

        $actions['plan'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'viewByItems'
            ),
            'tag' => 'view_supply/demand'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function view()
    {
        $this->setSidebarView($this->getHeader());
        $this->view->set('printaction', '');
    }

    public function _new()
    {

        // need to store the ajax flag in a different variable and the unset the original
        // this is to prevent any functions that are further called from returning the wrong datatype
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        parent::_new();

        $product = $this->_uses[$this->modeltype];

        $prod_groups = $this->getProductGroups();
        $this->view->set('prod_groups', $prod_groups);

        $glaccount = DataObjectFactory::Factory('GLAccount');
        $gl_accounts = $glaccount->nonControlAccounts();
        $this->view->set('gl_accounts', $gl_accounts);

        if (isset($_POST[$this->modeltype]['prod_group_id'])) {
            $product->prod_group_id = $_POST[$this->modeltype]['prod_group_id'];
        } elseif (! $product->isLoaded()) {
            $product->prod_group_id = key($prod_groups);
        }

        if (isset($_POST[$this->modeltype]['glaccount_id'])) {
            $product->glaccount_id = $_POST[$this->modeltype]['glaccount_id'];
        } elseif (! $product->isLoaded()) {
            $product->glaccount_id = key($gl_accounts);
        }
        $this->view->set('gl_centres', $this->getCentres($product->glaccount_id));

        $stitem_list = array(
            '' => 'None'
        );
        if ($product->isLoaded()) {
            $this->_data['stitem_id'] = $product->stitem_id;
            $product_lines = $product->getLineIds();
            $order_lines = $product->checkOrderlines($product_lines);
            $invoice_lines = $product->checkInvoicelines($product_lines);
        } else {
            $product_lines = array();
            $order_lines = array();
            $invoice_lines = array();
        }

        if (! empty($this->_data['stitem_id'])) {
            $stitem = $this->getItem($this->_data['stitem_id']);
            $this->view->set('stitem', $stitem);
            $this->view->set('description', $stitem);

            $product_group = $this->getProductGroups($this->_data['stitem_id']);
            $product->prod_group_id = $this->_data['prod_group_id'] = key($product_group);
            $this->view->set('product_group', current($product_group));

            $this->view->set('uoms', $this->getUomList($this->_data['stitem_id']));
        } else {
            $stitem_list = $this->getItems($product->prod_group_id);
            $this->view->set('stitems', $stitem_list);
            $this->view->set('uoms', $uom_list);
        }

        $tax_rates = array();

        if ($product && $product->tax_rate_id && ! empty($this->_data['stitem_id'])) {
            $tax_rates[$product->tax_rate_id] = $product->tax_rate;
        } else {
            $taxrate = DataObjectFactory::Factory('TaxRate');

            $tax_rates = $taxrate->getAll();
        }

        $this->view->set('tax_rates', $tax_rates);
    }

    public function save()
    {
        $flash = Flash::Instance();
        $errors = array();
        $db = DB::Instance();

        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $this->loadData();
        $header = $this->_uses[$this->modeltype];

        $stitem = DataObjectFactory::Factory('STItem');

        if (! empty($this->_data[$this->modeltype]['stitem_id'])) {
            $stitem->load($this->_data[$this->modeltype]['stitem_id']);
            $end_date = un_fix_date($stitem->obsolete_date);

            if (! empty($end_date) && $end_date != $this->_data[$this->modeltype]['end_date']) {
                $this->_data[$this->modeltype]['end_date'] = $end_date;
                $flash->addWarning('Item has obsolete date - setting end date on product');
            }
        }

        // If there is no description, use the description item code description
        if (empty($this->_data[$this->modeltype]['description']) && $stitem->isLoaded()) {
            $this->_data[$this->modeltype]['description'] = $stitem->getIdentifierValue();
        }
        // If there is no description, then no supplier or item has been selected
        if (empty($this->_data[$this->modeltype]['description'])) {
            $errors[] = 'You must select an item &/or enter a description';
        }

        $db->StartTrans();
        if (count($errors) == 0) {
            if (parent::save($this->modeltype, null, $errors)) {
                // Replace the default 'success' message
                // inserted by Controller::save
                $flash->clearMessages();
                $flash->addMessage('Product updated');
            } else {
                $errors[] = 'Failed to save Product';
                $db->FailTrans();
            }
        }

        // Update descriptions on productlines
        if (isset($this->_data[$this->modeltype]['cascade_description_change']) && $this->_data[$this->modeltype]['cascade_description_change'] === 'on' && count($errors) == 0) {
            $this->saved_model->updateProductlineDescriptions($errors);
        }
        $db->CompleteTrans();

        $flash->addErrors($errors);

        if (isset($this->_data['saveform'])) {
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->saved_model->id
            ));
        } else {
            sendTo($this->name, 'new', $this->_modules);
        }

        if (isset($this->_data[$this->modeltype]['id'])) {
            $this->_data['id'] = $this->_data[$this->modeltype]['id'];
        }

        $this->refresh();
    }

    public function viewByDates()
    {

        // Need to build an array for the supplied item
        // - get all sales orders by date
        // - get all works orders by date
        // - get stock balances for these dates
        $flash = Flash::Instance();
        // Id must be set
        if (! isset($this->_data['id'])) {
            $flash->addError('Stock Item not supplied');
            sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
        }

        $stitem = DataObjectFactory::Factory('STItem');
        if ($stitem->load($this->_data['id'])) {
            // Get the current stock level for the item
            $in_stock = $stitem->currentBalance();

            $orders = array();

            // Get any Purchase Orders by date for the item
            $porders = $stitem->getPOrderLines();
            foreach ($porders as $porder) {
                if (isset($orders[$porder->due_delivery_date . 'PO' . $porder->order_id])) {
                    $orders[$porder->due_delivery_date . 'PO' . $porder->order_id]['on_order'] += round($stitem->convertToUoM($porder->stuom_id, $stitem->uom_id, $porder->os_qty), $stitem->qty_decimals);
                } else {
                    $orders[$porder->due_delivery_date . 'PO' . $porder->order_id] = array(
                        'due_date' => un_fix_date($porder->due_delivery_date),
                        'order_type' => 'PO',
                        'reference' => $porder->order_number,
                        'reference_id' => $porder->order_id,
                        'stitem_id' => $porder->stitem_id,
                        'stitem' => $porder->stitem,
                        'uom_name' => $porder->uom_name,
                        'on_order' => round($stitem->convertToUoM($porder->stuom_id, $stitem->uom_id, $porder->os_qty), $stitem->qty_decimals),
                        'required' => 0
                    );
                }
            }
            // Get any Sales Orders by date for the item
            $sorders = $stitem->getSOrderLines();
            foreach ($sorders as $sorder) {
                if (isset($orders[$sorder->due_despatch_date . 'SO' . $sorder->order_id])) {
                    $orders[$sorder->due_despatch_date . 'SO' . $sorder->order_id]['required'] += round($stitem->convertToUoM($sorder->stuom_id, $stitem->uom_id, $sorder->os_qty), $stitem->qty_decimals);
                } else {
                    $orders[$sorder->due_despatch_date . 'SO' . $sorder->order_id] = array(
                        'due_date' => un_fix_date($sorder->due_despatch_date),
                        'order_type' => 'SO',
                        'reference' => $sorder->order_number,
                        'reference_id' => $sorder->order_id,
                        'stitem_id' => $sorder->stitem_id,
                        'stitem' => $sorder->stitem,
                        'uom_name' => $sorder->uom_name,
                        'on_order' => 0,
                        'required' => round($stitem->convertToUoM($sorder->stuom_id, $stitem->uom_id, $sorder->os_qty), $stitem->qty_decimals)
                    );
                }
            }
            // Get any Works Orders by date for the item
            $worders = $stitem->getWorkOrders();
            foreach ($worders as $worder) {
                $orders[$worder->required_by . 'WO' . $worder->id] = array(
                    'due_date' => un_fix_date($worder->required_by),
                    'order_type' => 'WO',
                    'reference' => $worder->wo_number,
                    'reference_id' => $worder->id,
                    'stitem_id' => $worder->stitem_id,
                    'stitem' => $worder->stitem,
                    'uom_name' => $worder->uom,
                    'required' => 0,
                    'on_order' => round(((($worder->order_qty - $worder->made_qty) < 0) ? 0 : $worder->order_qty - $worder->made_qty), $stitem->qty_decimals)
                );
            }
            ksort($orders);

            $salelocations = WHLocation::getSaleLocations();

            if (empty($salelocations)) {
                $available = 0;
            } else {
                $cc = new ConstraintChain();
                $cc->add(new Constraint('stitem_id', '=', $stitem->id));
                $cc->add(new Constraint('whlocation_id', 'in', '(' . implode(',', $salelocations) . ')'));
                $available = STBalance::getBalances($cc);
            }

            $balance = $available;
            // $in_stock-=$available;

            // Now build the manufacturing plan for the order dates
            foreach ($orders as $key => $row) {
                $orders[$key]['available'] = $available;
                $orders[$key]['shortfall'] = 0;
                $available -= $row['required'];

                $balance = $balance - $row['required'] + $row['on_order'];
                if ($balance < 0) {
                    $orders[$key]['shortfall'] -= $balance;
                    $orders[$key]['in_stock'] = 0;
                } else {
                    $orders[$key]['in_stock'] = $balance;
                }
                if ($available < 0) {
                    $available = 0;
                }

                // Sales kits are 'made' during picking.
                // A shortfall makes no sense here.
                if ($stitem->comp_class == 'K') {
                    $orders[$key]['on_order'] = $orders[$key]['in_stock'] = $orders[$key]['required'];
                    $orders[$key]['shortfall'] = 0;
                }
                // $in_stock=$balance-$available;
                // if ($in_stock<0) {
                // $in_stock=0;
                // }
            }
        }
        $this->view->set('itemplan', $orders);
        $this->view->set('stitem', $stitem);
        $this->view->set('page_title', $this->getPageName('for Stock Item', 'View Supply/Demand by Date'));

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

        $actions['allines'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view all product lines'
        );
        $actions['plan'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'viewByItems'
            ),
            'tag' => 'view_supply/demand'
        );
        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'SOrders',
                'action' => 'index'
            ),
            'tag' => 'view quotes/orders'
        );

        $sidebar->addList('Actions', $actions);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function viewByItems()
    {
        $itemplan = $this->getItemPlan();

        $this->view->set('itemplan', $itemplan);
        $this->view->set('page_title', $this->getPageName('Stock Items', 'View Supply/Demand by'));

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

        $actions['allines'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view all products'
        );

        $actions['allines'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'soproductlines',
                'action' => 'index'
            ),
            'tag' => 'view all product lines'
        );

        $actions['vieworder'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'SOrders',
                'action' => 'index'
            ),
            'tag' => 'view quotes/orders'
        );

        $sidebar->addList('Actions', $actions);

        /*
         * $sidebarlist=array();
         * $sidebarlist['supplydemand']=array(
         * 'link'=>array('modules'=>$this->_modules
         * ,'controller'=>$this->name
         * ,'action'=>'printaction'
         * ,'filename'=>'SupplyDemand_'.date('Y-m-d')
         * ,'printaction'=>'printSupplyDemand'
         * ),
         * 'tag'=>'Supply/demand'
         * );
         * $sidebar->addList('Reports', $sidebarlist);
         */

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function view_orders()
    {
        if (isset($this->_data['Search']['productline_header_id'])) {
            $this->_data['id'] = $this->_data['Search']['productline_header_id'];
        }

        if (! isset($this->_data['id'])) {
            $this->DataError();
            sendBack();
        }

        $s_data = array();

        // Set context from calling module

        $s_data['productline_header_id'] = $this->_data['id'];

        $this->setSearch('productlinesSearch', 'customerOrders', $s_data);

        $this->view();

        // Now load the required data
        $orders = new SOrderCollection();
        $orders->setViewName('so_product_orders');
        $sh = $this->setSearchHandler($orders);

        parent::index($orders, $sh);

        $this->view->set('clickcontroller', 'sorders');
        $this->view->set('clickaction', 'view');
        $this->view->set('related_collection', $orders);
        $this->setTemplateName('view_related');
    }

    public function view_invoices()
    {
        if (isset($this->_data['Search']['productline_header_id'])) {
            $this->_data['id'] = $this->_data['Search']['productline_header_id'];
        }

        if (! isset($this->_data['id'])) {
            $this->DataError();
            sendBack();
        }

        $s_data = array();

        // Set context from calling module

        $s_data['productline_header_id'] = $this->_data['id'];

        $this->setSearch('productlinesSearch', 'customerInvoices', $s_data);

        $this->view();

        // Now load the required data
        $invoices = new SInvoiceCollection();
        $invoices->setViewName('so_product_invoices');
        $sh = $this->setSearchHandler($invoices);

        parent::index($invoices, $sh);

        $this->view->set('clickmodule', array(
            'sales_invoicing'
        ));
        $this->view->set('clickcontroller', 'sinvoices');
        $this->view->set('clickaction', 'view');
        $this->view->set('related_collection', $invoices);
        $this->setTemplateName('view_related');
    }

    /*
     * Protected Functions
     *
     */
    protected function getHeader()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $so_pl_header = $this->_uses[$this->modeltype];
        $this->view->set('SOProductlineHeader', $so_pl_header);

        return $so_pl_header;
    }

    protected function setSidebarView($so_pl_header)
    {
        $sidebar = new SidebarController($this->view);

        $this->sidebarActions($sidebar, $so_pl_header, array(
            'delete' => false
        ));

        $sidebarlist = array();

        $sidebarlist['all_lines'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'soproductlines',
                'action' => 'index'
            ),
            'tag' => 'view all SO product lines'
        );

        $sidebar->addList('All Actions', $sidebarlist);

        $sidebarlist = array();

        $sidebarlist['orders'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view_orders',
                'id' => $so_pl_header->id
            ),
            'tag' => 'view orders'
        );

        $sidebarlist['invoices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view_invoices',
                'id' => $so_pl_header->id
            ),
            'tag' => 'view invoices'
        );

        $sidebarlist['prices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'soproductlines',
                'action' => 'price_uplift',
                'productline_header_id' => $so_pl_header->id
            ),
            'tag' => 'amend prices'
        );

        $sidebar->addList('this ' . $so_pl_header->getTitle(), $sidebarlist);

        $sidebarlist = array();

        if (SelectorCollection::TypeDetailsExist($this->modeltype)) {
            $sidebarlist['items'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'soproductselectors',
                    'action' => 'used_by',
                    'target_id' => $so_pl_header->id
                ),
                'new' => array(
                    'modules' => $this->_modules,
                    'controller' => 'soproductselectors',
                    'action' => 'select_items',
                    'target_id' => $so_pl_header->id
                ),
                'tag' => 'used by'
            );
        }

        $sidebar->addList('related_items', $sidebarlist);

        $this->sidebarRelatedItems($sidebar, $so_pl_header);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName(($base) ? $base : 'SO_products', $action);
    }

    /*
     * Private Functions
     *
     */
    private function getItemPlan()
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

        $items = new SOProductlineHeaderCollection($this->_templateobject);

        $sh = $this->setSearchHandler($items);

        $items->getItems($sh);

        parent::index($items, $sh);

        // Now get totals for Sales Orders, In Stock, Work Orders
        $itemplan = array();
        foreach ($items as $item) {

            $stitem = DataObjectFactory::Factory('STItem');

            if ($stitem->load($item->stitem_id)) {
                $itemplan[$item->stitem_id]['stitem_id'] = $item->stitem_id;
                $itemplan[$item->stitem_id]['stitem'] = $item->stitem;
                $itemplan[$item->stitem_id]['uom_name'] = $item->uom_name;
                $itemplan[$item->stitem_id]['in_stock'] = $stitem->currentBalance();

                $itemplan[$item->stitem_id]['required'] = 0;
                $itemplan[$item->stitem_id]['on_order']['po_value'] = 0;
                $itemplan[$item->stitem_id]['on_order']['wo_value'] = 0;

                $porders = $stitem->getPOrderLines();

                foreach ($porders as $porder) {
                    $itemplan[$item->stitem_id]['on_order']['po_value'] += round($stitem->convertToUoM($porder->stuom_id, $stitem->uom_id, $porder->os_qty), $stitem->qty_decimals);
                }

                $sorders = $stitem->getSOrderLines();

                foreach ($sorders as $sorder) {
                    $itemplan[$item->stitem_id]['required'] += round($stitem->convertToUoM($sorder->stuom_id, $stitem->uom_id, $sorder->os_qty), $stitem->qty_decimals);
                }

                $worders = $stitem->getWorkOrders();

                foreach ($worders as $worder) {
                    $itemplan[$item->stitem_id]['on_order']['wo_value'] += round(((($worder->order_qty - $worder->made_qty) < 0) ? 0 : $worder->order_qty - $worder->made_qty), $stitem->qty_decimals);
                }

                $salelocations = WHLocation::getSaleLocations();

                if (empty($salelocations)) {
                    $itemplan[$item->stitem_id]['for_sale'] = 0;
                } else {
                    $cc = new ConstraintChain();

                    $cc->add(new Constraint('stitem_id', '=', $item->stitem_id));
                    $cc->add(new Constraint('whlocation_id', 'in', '(' . implode(',', $salelocations) . ')'));

                    $itemplan[$item->stitem_id]['for_sale'] = STBalance::getBalances($cc);
                }

                $itemplan[$item->stitem_id]['in_stock'] -= $itemplan[$item->stitem_id]['for_sale'];
                $itemplan[$item->stitem_id]['actual_shortfall'] = $itemplan[$item->stitem_id]['required'] - ($itemplan[$item->stitem_id]['for_sale'] + $itemplan[$item->stitem_id]['in_stock']);
                $itemplan[$item->stitem_id]['shortfall'] = $itemplan[$item->stitem_id]['actual_shortfall'] - $itemplan[$item->stitem_id]['on_order']['po_value'] - $itemplan[$item->stitem_id]['on_order']['wo_value'];
                $itemplan[$item->stitem_id]['indicator'] = 'green';

                if ($itemplan[$item->stitem_id]['actual_shortfall'] <= 0) {
                    $itemplan[$item->stitem_id]['actual_shortfall'] = 0;
                } else {
                    $itemplan[$item->stitem_id]['indicator'] = 'amber';
                }

                if ($itemplan[$item->stitem_id]['shortfall'] <= 0) {
                    $itemplan[$item->stitem_id]['shortfall'] = 0;
                } else {
                    $itemplan[$item->stitem_id]['indicator'] = 'red';
                }

                // Sales kits are 'made' during picking.
                // A shortfall makes no sense here.
                if ($stitem->comp_class == 'K') {
                    $itemplan[$item->stitem_id]['on_order']['so_value'] =  $itemplan[$item->stitem_id]['required'];
                    $itemplan[$item->stitem_id]['shortfall'] = 0;
                    $itemplan[$item->stitem_id]['indicator'] = 'green';
                }
            }
        }

        return $itemplan;
    }

    /*
     * Ajax Functions
     *
     */
    public function getCentres($_glaccount_id = '')
    {
        // Used by Ajax to return Centre list after selecting the Account
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['glaccount_id'])) {
                $_glaccount_id = $this->_data['glaccount_id'];
            }
        }

        $account = DataObjectFactory::Factory('GLAccount');

        $account->load($_glaccount_id);

        $centre_list = $account->getCentres();

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $centre_list);
            $this->view->set('model', $this->_templateobject);
            $this->view->set('attribute', 'glcentre_id');
            $this->setTemplateName('select');
        } else {
            return $centre_list;
        }
    }

    public function getItem($_stitem_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['stitem_id'])) {
                $_stitem_id = $this->_data['stitem_id'];
            }
        }

        $item = $this->_templateobject->getItem($_stitem_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $item);
            $this->setTemplateName('text_inner');
        } else {
            return $item;
        }
    }

    public function getItems($_prod_group_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['prod_group_id'])) {
                $_prod_group_id = $this->_data['prod_group_id'];
            }
        }

        // Get the list of current items for the product group
        // for which product lines do not exist
        $cc = new ConstraintChain();
        if (! empty($_prod_group_id)) {
            $cc->add(new Constraint('prod_group_id', '=', $_prod_group_id));
        } else {
            $cc->add(new Constraint('prod_group_id', '=', - 1));
        }
        // TODO: Need an object.method for constructing subqueries, particularly correlated subqueries!
        $sql = 'select stitem_id from so_product_lines_header where stitem_id = st_items.id';
        $cc->add(new Constraint('', 'not exists', '(' . $sql . ')'));

        if (! $date) {
            $date = Constraint::TODAY;
        } elseif (is_int($date)) {
            $db = DB::Instance();
            $date = $db->DBDate($date);
        }

        $cc1 = new ConstraintChain();
        $cc1->add(new Constraint('obsolete_date', '=', 'NULL'));
        $cc1->add(new Constraint('obsolete_date', '>', $date), 'OR');
        $cc2 = new ConstraintChain();
        $cc2->add(new Constraint('comp_class', '!=', 'P'));
        $cc2->add($cc1);
        $cc->add($cc2);

        $stitem = DataObjectFactory::Factory('STitem');

        $items = array(
            '' => 'None'
        );

        $items += $stitem->getAll($cc);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $items);
            $this->setTemplateName('select_options');
        } else {
            return $items;
        }
    }

    public function getTaxRate($_stitem_id = '')
    {
        // Used by Ajax to return Tax Rate list after selecting the item
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['stitem_id'])) {
                $_stitem_id = $this->_data['stitem_id'];
            }
        }

        $tax_rate_list = array();
        // ATTENTION: JQI: this is one of "those" functions, check the refactored condition
        if (empty($this->_data['stitem_id'])) {
            $tax_rates = DataObjectFactory::Factory('TaxRate');
            $tax_rate_list = $tax_rates->getAll();
            ksort($tax_rate_list, SORT_NUMERIC);
        } else {
            $item = DataObjectFactory::Factory('STItem');
            $item->load($_stitem_id);

            $tax_rate = DataObjectFactory::Factory('TaxRate');
            $tax_rate->load($item->tax_rate_id);
            $tax_rate_list[$tax_rate->id] = $tax_rate->description;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $tax_rate_list);
            $this->setTemplateName('select_options');
        } else {
            return $tax_rate_list;
        }
    }

    public function getEndDate($_stitem_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['stitem_id'])) {
                $_stitem_id = $this->_data['stitem_id'];
            }
        }

        $item = $this->_templateobject->getEndDate($_stitem_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', un_fix_date($item));
            $this->setTemplateName('text_inner');
        } else {
            return un_fix_date($item);
        }
    }

    public function getUomList($_stuom_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['stuom_id'])) {
                $_stuom_id = $this->_data['stuom_id'];
            }
        }

        $list = $this->_templateobject->getUomList($_stuom_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $list);
            $this->setTemplateName('select_options');
        } else {
            return $list;
        }
    }

    function getProductGroups($_stitem_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['stitem_id'])) {
                $_stitem_id = $this->_data['stitem_id'];
            }
        }

        $groups = $this->_templateobject->getProductGroups($_stitem_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $groups);
            $this->setTemplateName('select_options');
        } else {
            return $groups;
        }
    }

    /* consolodation functions */
    public function getItemData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_stitem_id = $this->_data['stitem_id'];
        $_prod_group_id = $this->_data['prod_group_id'];

        $stuom_id = $this->getUomList($_stitem_id);
        $_selected = (isset($this->_data['stuom_id'])) ? $this->_data['stuom_id'] : '';
        $stuom_id = $this->buildSelect('', 'stuom_id', $stuom_id, $_selected);
        $output['stuom_id'] = array(
            'data' => $stuom_id,
            'is_array' => is_array($stuom_id)
        );

        $tax_rate_id = $this->getTaxRate($_stitem_id);
        $_selected = (isset($this->_data['tax_rate_id'])) ? $this->_data['tax_rate_id'] : '';
        $tax_rate_id = $this->buildSelect('', 'tax_rate_id', $tax_rate_id, $_selected);
        $output['tax_rate_id'] = array(
            'data' => $tax_rate_id,
            'is_array' => is_array($tax_rate_id)
        );

        $prod_group_id = $this->getProductGroups($_stitem_id);
        $prod_group_id = $this->buildSelect('', 'prod_group_id', $prod_group_id);
        $output['prod_group_id'] = array(
            'data' => $prod_group_id,
            'is_array' => is_array($prod_group_id)
        );

        if (! empty($_stitem_id)) {
            $description = $this->getItem($_stitem_id);
        } else {
            $description = '';
        }
        if (! empty($description)) {
            $output['description'] = array(
                'data' => $description,
                'is_array' => is_array($description)
            );
        }

        $end_date = $this->getEndDate($_stitem_id);
        $output['end_date'] = array(
            'data' => $end_date,
            'is_array' => is_array($end_date)
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }
}

// End of soproductlineheadersController
