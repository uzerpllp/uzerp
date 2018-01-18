<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class StitemsController extends printController
{

    protected $version = '$Revision: 1.68 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('STItem');

        $this->uses($this->_templateobject);

        // Get module preferences
        $system_prefs = SystemPreferences::instance();
        $this->module_prefs = $system_prefs->getModulePreferences($this->module);

        // Fix empty prefs
        if (! isset($this->module_prefs['default-cost-basis'])) {
            $this->module_prefs['default-cost-basis'] = 'VOLUME';
        }
        if (! isset($this->module_prefs['use-only-default-cost-basis'])) {
            $this->module_prefs['use-only-default-cost-basis'] = 'on';
        }

        // Make module preferences available to smarty
        $this->view->set('module_prefs', $this->module_prefs);
    }

    public function index()
    {
        $errors = array();
        $s_data = array();

        // Set context from calling module
        $this->setSearch('stitemsSearch', 'useDefault', $s_data);

        $this->view->set('clickaction', 'view');
        // $this->view->set('page_title', 'Stock Items List');
        parent::index(new STItemCollection($this->_templateobject));

        $sidebar = new SidebarController($this->view);

        $sidebarlist = array();

        $sidebarlist['new'] = array(
            'tag' => 'New Stock Item',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            )
        );

        $sidebarlist['documents'] = array(
            'tag' => $this->_templateobject->getTitle() . ' Documents',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'attachments',
                'action' => 'index',
                'entity_id' => ModuleComponent::getComponentId($this->_modules['module'], strtolower(get_class($this))),
                'data_model' => 'modulecomponent'
            )
        );

        $sidebar->addList('Actions', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function clone_item()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        parent::_new();
    }

    public function delete()
    {
        $flash = Flash::Instance();

        parent::delete($this->modeltype);

        sendTo($this->name, 'index', $this->_modules);
    }

    public function save()
    {
        if (! $this->CheckParams($this->modeltype)) {
            sendBack();
        }

        $flash = Flash::Instance();

        // Check cost basis preferences
        if (! $this->costBasisValid($this->_data[$this->modeltype]['cost_basis'])) {
            $flash->addError('Cost basis not allowed by module settings');
            sendBack();
        }

        $db = DB::Instance();

        $db->StartTrans();

        $errors = array();

        $data = $this->_data[$this->modeltype];

        $data['item_code'] = strtoupper($data['item_code']);

        $update_cost = FALSE;

        $stitem = $this->_uses[$this->modeltype];

        if (! empty($data['id'])) {
            $stitem->load($data['id']);
        }

        // Set the cost basis for items with a non-manufactured comp_class
        if ($data['comp_class'] !== 'M') {
            $data['cost_basis'] = $this->module_prefs['default-cost-basis'];
        }

        if ($data['comp_class'] == 'B') {
            $data['latest_cost'] = $data['latest_mat'];
            $data['latest_lab'] = 0;
            $data['latest_osc'] = 0;
            $data['latest_ohd'] = 0;

            if ($stitem->isLoaded()) {
                $old_costs = array(
                    $stitem->latest_cost,
                    $stitem->latest_mat,
                    $stitem->latest_lab,
                    $stitem->latest_osc,
                    $stitem->latest_ohd
                );

                $new_costs = array(
                    $data['latest_cost'],
                    $data['latest_mat'],
                    $data['latest_lab'],
                    $data['latest_osc'],
                    $data['latest_ohd']
                );

                $total_costs = count($old_costs);

                for ($i = 0; $i < $total_costs; $i ++) {
                    if (bccomp($old_costs[$i], $new_costs[$i], $stitem->cost_decimals) != 0) {
                        $update_cost = true;
                        break;
                    }
                }
            } elseif ($data['latest_cost'] > 0) {
                $update_cost = true;
            }
        } else {
            unset($data['latest_mat']);
        }

        $product_data = array();

        if ($stitem->isLoaded()) {
            if (is_null($stitem->obsolete_date) && ! empty($data['obsolete_date'])) {
                $product_data['end_date'] = 'obsolete_date';
            }

            if ($data['prod_group_id'] != $stitem->prod_group_id) {
                $product_data['prod_group_id'] = 'prod_group_id';
            }

            // Indicate that the user wants the description change cascaded to all
            // linked products and product lines
            if ((isset($this->_data[$this->modeltype]['cascade_description_change_so']) && $this->_data[$this->modeltype]['cascade_description_change_so'] === 'on') || (isset($this->_data[$this->modeltype]['cascade_description_change_po']) && $this->_data[$this->modeltype]['cascade_description_change_po'] === 'on')) {
                $product_data['description'] = 'description';
            }
        }

        if ((! $update_cost) && ($stitem->isLoaded())) {
            $update_cost = (($data['uom_id'] != $stitem->uom_id) || ($data['cost_decimals'] != $stitem->cost_decimals) || ($data['comp_class'] != $stitem->comp_class) || ((strlen($data['obsolete_date']) > 0) && (fix_date($data['obsolete_date']) != $stitem->obsolete_date)));
        }

        if (parent::save($this->modeltype, $data, $errors)) {
            if ($update_cost) {
                $this->saved_model->calcLatestCost();
                if (($this->saved_model->saveCosts()) && (STCost::saveItemCost($this->saved_model))) {
                    if (! $this->saved_model->rollUp(STItem::ROLL_UP_MAX_LEVEL)) {
                        $errors[] = 'Could not roll-up latest costs';
                        $db->FailTrans();
                    }
                } else {
                    $errors[] = 'Could not save latest costs';
                    $db->FailTrans();
                }
            }
            if (! empty($product_data)) {
                // Need to cascade data changes to linked current products
                // if they exist
                $products['PO'] = $this->saved_model->getPOProductlineHeader();
                $products['SO'] = $this->saved_model->getSOProductlineHeader();

                foreach ($products as $type => $product) {
                    if ($product->isLoaded()) {
                        foreach ($product_data as $field => $value) {
                            if ($value == 'description') {
                                $product->description = $this->saved_model->item_code . ' - ' . $this->saved_model->description;
                            } else {
                                $product->$field = $this->saved_model->$value;
                            }
                        }
                        if (! $product->save()) {
                            $errors[] = 'Error updating ' . $type . ' Product : ' . $db->ErrorMsg();
                            $db->FailTrans();
                        }

                        // Update productline descriptions if requested
                        if (isset($this->_data[$this->modeltype]['cascade_description_change_so']) && $this->_data[$this->modeltype]['cascade_description_change_so'] === 'on' && $type == 'SO' && count($errors) == 0) {
                            $product->updateProductlineDescriptions($errors);
                        }

                        if (isset($this->_data[$this->modeltype]['cascade_description_change_po']) && $this->_data[$this->modeltype]['cascade_description_change_po'] === 'on' && $type == 'PO' && count($errors) == 0) {
                            $product->updateProductlineDescriptions($errors);
                        }
                    }
                }
            }
        } else {
            $errors[] = 'Could not save stock item';
            $db->FailTrans();
        }

        $db->CompleteTrans();

        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $this->refresh();
        } elseif (isset($this->_data['saveform'])) {
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->saved_model->id
            ));
        } else {
            sendTo($this->name, 'new', $this->_modules);
        }
    }

    public function save_clone()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $flash = Flash::Instance();

        $db = DB::Instance();

        $db->StartTrans();

        $errors = array();

        $stitem = $this->_uses[$this->modeltype];

        $original_id = $stitem->id;

        $this->_data[$this->modeltype]['item_code'] = strtoupper($this->_data[$this->modeltype]['item_code']);

        if ($this->_data[$this->modeltype]['copy_so_product_prices'] == 'on' && ! isset($this->_data[$this->modeltype]['copy_so_products'])) {
            $errors[] = 'Cannot copy prices without product';
        }

        if (empty($this->_data[$this->modeltype]['pstart_date']) && (isset($this->_data[$this->modeltype]['copy_so_products']) || isset($this->_data[$this->modeltype]['copy_so_product_prices']))) {
            $errors[] = 'A start date must be specified';
        }

        if ($stitem->item_code == $this->_data[$this->modeltype]['item_code']) {
            $errors[] = 'The Item Code must be Unique';
        } else {
            $stitem->item_code = $this->_data[$this->modeltype]['item_code'];
            $stitem->description = $this->_data[$this->modeltype]['description'];
            $stitem->balance = 0;
            $stitem->std_cost = 0;
            $stitem->std_mat = 0;
            $stitem->std_lab = 0;
            $stitem->std_ohd = 0;
            $stitem->std_osc = 0;
            $stitem->created = $stitem->autoHandle('created');
            $stitem->createdby = $stitem->autoHandle('createdby');

            $test = $stitem->autoHandle($stitem->idField);

            if ($test !== false) {
                $stitem->{$stitem->idField} = $test;
                $stitem_id = $test;
            } else {
                $errors[] = 'Error getting identifier for new item';
            }
        }

        if (count($errors) == 0 && ! $stitem->save()) {
            $errors[] = 'Error saving cloned item ' . $db->ErrorMsg();
        } elseif (count($errors) == 0) {

            $hasmany = $stitem->getHasMany();

            foreach ($this->_data[$this->modeltype] as $key => $value) {
                if (substr($key, 0, 5) == 'copy_' && isset($hasmany[substr($key, 5)])) {
                    $do_name = $hasmany[substr($key, 5)]['do'];
                    $do = DataObjectFactory::Factory($do_name);

                    $cc = new ConstraintChain();

                    $cc->add(new Constraint('stitem_id', '=', $original_id));

                    if ($do->isField('start_date') && $do->isField('end_date')) {
                        $cc->add(currentDateConstraint());
                    }

                    if (isset($so_product_id) && ! is_null($so_product_id) && $do_name == 'SOProductLine') {
                        $do = DataObjectFactory::Factory($do_name);
                        $cc = new ConstraintChain();
                        $cc->add(new Constraint('productline_header_id', '=', $so_product_id));
                        $cc->add(new Constraint('slmaster_id', '=', 'NULL'));
                        if ($do->isField('start_date') && $do->isField('end_date')) {
                            $cc->add(currentDateConstraint());
                        }
                        $children = $do->getAll($cc);
                    } else {
                        $children = $do->getAll($cc);
                    }

                    if (! empty($children)) {
                        foreach ($children as $child_id => $value) {
                            $child = DataObjectFactory::Factory($do_name);

                            $child->load($child_id);

                            if ($child->isLoaded()) {
                                $test = $child->autoHandle($child->idField);

                                if ($test !== false) {
                                    $child->{$stitem->idField} = $test;
                                } else {
                                    $errors[] = 'Error getting identifier for new item';
                                }

                                if ($do_name == 'SOProductLineHeader') {
                                    $child->start_date = $this->_data[$this->modeltype]['pstart_date'];
                                    $child->description = $this->_data[$this->modeltype]['item_code'] . ' - ' . $this->_data[$this->modeltype]['description'];
                                    $child->ean = '';
                                }

                                if (isset($so_product_id) && ! is_null($so_product_id) && $do_name == 'SOProductLine') {
                                    $child->productline_header_id = $new_so_product_id;
                                    $child->description = $this->_data[$this->modeltype]['item_code'] . ' - ' . $this->_data[$this->modeltype]['description'];
                                    $child->start_date = $this->_data[$this->modeltype]['pstart_date'];
                                } else {
                                    $child->stitem_id = $stitem_id;
                                }

                                $db->StartTrans();

                                if (! $child->save()) {
                                    $errors[] = 'Failed to copy ' . $do_name . ' ' . $db->ErrorMsg();
                                    $db->FailTrans();
                                    $db->CompleteTrans();
                                    break;
                                }

                                $db->CompleteTrans();

                                if ($do_name == 'SOProductLineHeader') {
                                    $so_product_id = $child_id;
                                    $new_so_product_id = $test;
                                }
                            } else {
                                $errors[] = 'Failed to load ' . $do_name;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);

            $db->FailTrans();

            $db->CompleteTrans();

            sendBack();
        }

        // All OK, so now check latest cost
        $stitem->calcLatestCost();

        if (! $stitem->save()) {
            $flash->addError('Error saving cloned item ' . $db->ErrorMsg());

            $db->FailTrans();

            $db->CompleteTrans();

            sendBack();
        }

        $db->CompleteTrans();

        $flash->addMessage('New item saved');
        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $stitem_id
        ));
    }

    public function view()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $transaction = $this->_uses[$this->modeltype];
        $id = $transaction->id;

        $this->view->set('transaction', $transaction);
        $obsolete = $transaction->isObsolete();

        $chain = new ConstraintChain();

        $chain->add(new Constraint('stitem_id', '=', $transaction->id));

        $transaction->balance = STBalance::getBalances($chain);

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Show', array(
            'stores' => array(
                'tag' => 'All Items',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            )
        ));

        $sidebarlist = array();

        $sidebarlist['view'] = array(
            'tag' => $transaction->getIdentifierValue(),
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        $sidebarlist['edit'] = array(
            'tag' => 'Edit',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'edit',
                'id' => $id
            )
        );
        // Only allow delete if no foreign key rows exist
        $delete = true;

        foreach ($transaction->getHasMany() as $name => $hasmany) {
            if ($transaction->$name->count() > 0) {
                $delete = false;
                break;
            }
        }

        if ($delete) {
            $sidebarlist['delete'] = array(
                'tag' => 'Delete',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'delete',
                    'id' => $id
                )
            );
        }

        $sidebarlist['cost_sheet'] = array(
            'tag' => 'Show Cost Sheet',
            'link' => array(
                'module' => 'costing',
                'controller' => 'STCosts',
                'action' => 'costSheet',
                'stitem_id' => $id
            )
        );

        $sidebarlist['cost_history'] = array(
            'tag' => 'Show Cost History',
            'link' => array(
                'module' => 'costing',
                'controller' => 'STCosts',
                'action' => 'index',
                'stitem_id' => $id
            )
        );

        if ($transaction->po_products->count() > 0 || $transaction->workorders->count() > 0 || $transaction->wo_structures->count() > 0) {
            $sidebarlist['po_supply'] = array(
                'tag' => 'Purchasing Supply/Demand',
                'link' => array(
                    'module' => 'purchase_order',
                    'controller' => 'poproductlineheaders',
                    'action' => 'viewbydates',
                    'id' => $id
                )
            );
        }

        if ($transaction->so_products->count() > 0) {
            $sidebarlist['so_supply'] = array(
                'tag' => 'Sales Supply/Demand',
                'link' => array(
                    'module' => 'sales_order',
                    'controller' => 'soproductlineheaders',
                    'action' => 'viewbydates',
                    'id' => $id
                )
            );
        }

        if ($transaction->comp_class != 'B') {
            $sidebarlist['preorder'] = array(
                'tag' => 'Pre-Order Requirements',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'MFStructures',
                    'action' => 'preorder',
                    'stitem_id' => $id
                )
            );
        }

        $sidebarlist[] = 'spacer';

        $sidebarlist['cloneitem'] = array(
            'tag' => 'Clone Item',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'clone_item',
                'id' => $id
            )
        );

        $sidebarlist[] = 'spacer';

        if (! $obsolete) {
            $sidebarlist['mark_obsolete'] = array(
                'tag' => 'Mark As Obsolete',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'markObsolete',
                    'id' => $id
                )
            );
        }

        $sidebar->addList('This Item', $sidebarlist);

        $this->sidebarRelatedItems($sidebar, $transaction);

        $sidebar->addList('related_items', array(
            'documents' => array(
                'tag' => 'Show Documents',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'attachments',
                    'action' => 'index',
                    'entity_id' => $id,
                    'data_model' => strtolower($this->modeltype)
                ),
                'new' => array(
                    'modules' => $this->_modules,
                    'controller' => 'attachments',
                    'action' => 'new',
                    'entity_id' => $id,
                    'data_model' => strtolower($this->modeltype)
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    function viewbalances()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $transaction = $this->_uses[$this->modeltype];
        $id = $transaction->id;

        $this->view->set('transaction', $transaction);

        $balances = new STBalanceCollection();

        $balances->orderby = 'whlocation';

        $sh = $this->setSearchHandler($balances);

        $sh->addConstraint(new Constraint('stitem_id', '=', $this->_data['id']));

        parent::index($balances, $sh);

        $this->view->set('clickaction', 'viewTransactions');

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Show', array(
            'allItems' => array(
                'tag' => 'All Items',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            ),
            'thisItem' => array(
                'tag' => 'Item Detail',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'view',
                    'id' => $this->_data['id']
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    public function viewOutside_Operations()
    {
        $id = $this->_data['id'];

        $transaction = DataObjectFactory::Factory($this->modeltype);

        $transaction->load($id);

        $this->view->set('transaction', $transaction);

        $outside_ops = new MFOutsideOperationCollection();

        $outside_ops->orderby = 'op_no';

        $sh = $this->setSearchHandler($outside_ops);

        $cc = new ConstraintChain();
        $cc->add(new Constraint('stitem_id', '=', $id));

        $db = DB::Instance();

        $date = Constraint::TODAY;
        $between = $date . ' BETWEEN ' . $db->IfNull('start_date', $date) . ' AND ' . $db->IfNull('end_date', $date);

        $cc->add(new Constraint('', '', '(' . $between . ')'));

        $sh->addConstraintChain($cc);

        parent::index($outside_ops, $sh);

        $this->view->set('linkfield', 'id');
        $this->view->set('linkvaluefield', 'id');
        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'MFOutsideOperations');

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Show', array(
            'allItems' => array(
                'tag' => 'All Items',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            ),
            'thisItem' => array(
                'tag' => 'Item Detail',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'view',
                    'id' => $id
                )
            ),
            'addoperation' => array(
                'tag' => 'Add Outside Operation',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'MFOutsideOperations',
                    'action' => 'new',
                    'stitem_id' => $id
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    public function viewTransactions()
    {
        $errors = array();
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['id'])) {
            $s_data['stitem_id'] = $this->_data['id'];
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $s_data['qty'] = 0;

        $this->setSearch('stitemsSearch', 'itemTransactions', $s_data);

        if (! empty($s_data['stitem_id'])) {
            $stitem = DataObjectFactory::Factory($this->modeltype);

            $stitem->load($s_data['stitem_id']);

            $this->view->set('stitem', $stitem);
        }

        parent::index(new STTransactionCollection());

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Show', array(
            'allItems' => array(
                'tag' => 'All Items',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            ),
            'thisItem' => array(
                'tag' => 'Item Detail',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'view',
                    'id' => $stitem->id
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);

        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    function viewPO_Product_prices()
    {

        // Search
        $errors = array();
        $s_data = array();

        // This is called from Related Items on STitems
        // but is also be called from Supply/Demand
        // Set context from calling module
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $s_data['stitem_id'] = $id;
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $this->setSearch('stitemsSearch', 'viewPOProducts', $s_data);

        $id = $this->search->getValue('stitem_id');

        // Load stitem
        $transaction = DataObjectFactory::Factory($this->modeltype);

        $transaction->load($id);

        $this->view->set('transaction', $transaction);

        $this->view->set('id', $id);

        // Load PO Product Lines
        $productlines = new POProductlineCollection();

        $productlines->orderby = 'supplier';

        $sh = $this->setSearchHandler($productlines);

        $sh->setFields(array(
            'id',
            'plmaster_id',
            'supplier',
            'description',
            'supplier_product_code',
            'uom_name',
            'start_date',
            'end_date',
            'price',
            'currency'
        ));

        parent::index($productlines, $sh);

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebarlist = array();

        $sidebarlist['allItems'] = array(
            'tag' => 'All Items',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        $sidebarlist['thisItem'] = array(
            'tag' => 'Item Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));

        $this->_templateName = $this->getTemplateName('viewpo_products');
    }

    function viewPurchase_orders()
    {

        // Search
        $errors = array();
        $s_data = array();

        // This is called from Related Items on STitems
        // but is also be called from Supply/Demand
        // Set context from calling module
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $s_data['stitem_id'] = $id;
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $this->setSearch('stitemsSearch', 'viewPurchaseorders', $s_data);

        $id = $this->search->getValue('stitem_id');

        // Load stitem
        $transaction = DataObjectFactory::Factory($this->modeltype);

        $transaction->load($id);

        $this->view->set('transaction', $transaction);

        $this->view->set('id', $id);

        // Load orders
        $purchaseorderlines = new POrderLineCollection();

        $purchaseorderlines->orderby = 'due_date';

        $sh = $this->setSearchHandler($purchaseorderlines);

        $sh->setFields(array(
            'id',
            'order_id',
            'order_number',
            'supplier',
            'order_qty',
            'del_qty',
            'due_delivery_date',
            'status'
        ));

        parent::index($purchaseorderlines, $sh);

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'POrders');
        $this->view->set('clickmodule', 'purchase_order');

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebarlist = array();

        $sidebarlist['allItems'] = array(
            'tag' => 'All Items',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        $sidebarlist['thisItem'] = array(
            'tag' => 'Item Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    function viewPurchase_invoices()
    {

        // Search
        $errors = array();
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $s_data['stitem_id'] = $id;
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $this->setSearch('stitemsSearch', 'viewPurchaseinvoices', $s_data);

        $id = $this->search->getValue('stitem_id');
        // Load invoice lines
        $purchaseinvoicelines = new PInvoiceLineCollection();

        $tablename = $purchaseinvoicelines->_tablename;

        $purchaseinvoice = DataObjectFactory::Factory('PInvoiceLine');

        $cc = $this->search->toConstraintChain();

        $cc->add(new Constraint('transaction_type', '=', 'I'));

        $purchaseinvoicetotals = $purchaseinvoice->getSumFields(array(
            'purchase_qty',
            'net_value'
        ), $cc, $tablename);

        $cc = $this->search->toConstraintChain();

        $cc->add(new Constraint('transaction_type', '=', 'C'));

        $purchasecredittotals = $purchaseinvoice->getSumFields(array(
            'purchase_qty',
            'net_value'
        ), $cc, $tablename);

        $total_qty = 0;
        $total_value = 0;

        if (! empty($purchaseinvoicetotals)) {
            $total_qty += $purchaseinvoicetotals['purchase_qty'];
            $total_value += $purchaseinvoicetotals['net_value'];
        }

        if (! empty($purchasecredittotals)) {
            $total_qty -= $purchasecredittotals['purchase_qty'];
            $total_value -= $purchasecredittotals['net_value'];
        }

        $this->view->set('total_qty', $total_qty);
        $this->view->set('total_value', $total_value);

        $purchaseinvoicelines->orderby = 'invoice_number';

        $sh = $this->setSearchHandler($purchaseinvoicelines);

        $sh->setFields(array(
            'id',
            'invoice_id',
            'transaction_type',
            'invoice_number',
            'invoice_date',
            'supplier',
            'plmaster_id',
            'purchase_order_id',
            'order_number',
            'purchase_qty',
            'net_value'
        ));

        parent::index($purchaseinvoicelines, $sh);

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebarlist = array();

        $sidebarlist['allItems'] = array(
            'tag' => 'All Items',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        $sidebarlist['thisItem'] = array(
            'tag' => 'Item Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    function viewSO_Product_prices()
    {

        // Search
        $errors = array();
        $s_data = array();

        // This is called from Related Items on STitems
        // but is also be called from Supply/Demand
        // Set context from calling module
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $s_data['stitem_id'] = $id;
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $this->setSearch('stitemsSearch', 'viewSOProducts', $s_data);

        $id = $this->search->getValue('stitem_id');

        // Load stitem
        $transaction = DataObjectFactory::Factory($this->modeltype);

        $transaction->load($id);

        $this->view->set('transaction', $transaction);

        $this->view->set('id', $id);

        // Load SO Product Lines
        $productlines = new SOProductlineCollection();

        $productlines->orderby = array(
            'customer',
            'so_price_type',
            'start_date'
        );
        $productlines->direction = array(
            'ASC',
            'ASC',
            'DESC'
        );

        $sh = $this->setSearchHandler($productlines);

        $sh->setFields(array(
            'id',
            'slmaster_id',
            'customer',
            'description',
            'customer_product_code',
            'uom_name',
            'start_date',
            'end_date',
            'so_price_type',
            'price',
            'currency'
        ));

        parent::index($productlines, $sh);

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebarlist = array();

        $sidebarlist['allItems'] = array(
            'tag' => 'All Items',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        $sidebarlist['thisItem'] = array(
            'tag' => 'Item Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));

        $this->_templateName = $this->getTemplateName('viewso_products');
    }

    function viewSales_orders()
    {

        // Search
        $errors = array();
        $s_data = array();

        // This is called from Related Items on STitems
        // but is also be called from Supply/Demand
        // Set context from calling module
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $s_data['stitem_id'] = $id;
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $this->setSearch('stitemsSearch', 'viewSalesorders', $s_data);

        $id = $this->search->getValue('stitem_id');

        // Load stitem
        $transaction = DataObjectFactory::Factory($this->modeltype);

        $transaction->load($id);

        $this->view->set('transaction', $transaction);

        $this->view->set('id', $id);

        // Load orders
        $salesorderlines = new SOrderLineCollection();

        $salesorderlines->orderby = 'due_date';

        $sh = $this->setSearchHandler($salesorderlines);

        $sh->setFields(array(
            'id',
            'order_id',
            'order_number',
            'customer',
            'order_qty',
            'revised_qty',
            'del_qty',
            'due_despatch_date',
            'status'
        ));

        parent::index($salesorderlines, $sh);

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'SOrders');
        $this->view->set('clickmodule', 'sales_order');

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebarlist = array();

        $sidebarlist['allItems'] = array(
            'tag' => 'All Items',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        $sidebarlist['thisItem'] = array(
            'tag' => 'Item Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        if ($transaction->comp_class == 'M') {
            $sidebarlist['newWorkorder'] = array(
                'tag' => 'New Works Order',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'MFWorkorders',
                    'action' => 'new',
                    'stitem_id' => $id
                )
            );

            $sidebarlist['viewWorkorders'] = array(
                'tag' => 'Show Work Orders',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'viewWorkorders',
                    'id' => $id
                )
            );
        }

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    function viewSales_invoices()
    {

        // Search
        $errors = array();
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $s_data['stitem_id'] = $id;
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $this->setSearch('stitemsSearch', 'viewSalesinvoices', $s_data);

        $id = $this->search->getValue('stitem_id');
        // Load invoice lines
        $salesinvoicelines = new SInvoiceLineCollection();

        $tablename = $salesinvoicelines->_tablename;

        $salesinvoice = DataObjectFactory::Factory('SInvoiceLine');

        $cc = $this->search->toConstraintChain();

        $cc->add(new Constraint('transaction_type', '=', 'I'));

        $salesinvoicetotals = $salesinvoice->getSumFields(array(
            'sales_qty',
            'net_value'
        ), $cc, $tablename);

        $cc = $this->search->toConstraintChain();

        $cc->add(new Constraint('transaction_type', '=', 'C'));

        $salescredittotals = $salesinvoice->getSumFields(array(
            'sales_qty',
            'net_value'
        ), $cc, $tablename);

        $total_qty = 0;
        $total_value = 0;

        if (! empty($salesinvoicetotals)) {
            $total_qty += $salesinvoicetotals['sales_qty'];
            $total_value += $salesinvoicetotals['net_value'];
        }

        if (! empty($salescredittotals)) {
            $total_qty -= $salescredittotals['sales_qty'];
            $total_value -= $salescredittotals['net_value'];
        }

        $this->view->set('total_qty', $total_qty);
        $this->view->set('total_value', $total_value);

        $salesinvoicelines->orderby = 'invoice_number';

        $sh = $this->setSearchHandler($salesinvoicelines);

        $sh->setFields(array(
            'id',
            'invoice_id',
            'transaction_type',
            'invoice_number',
            'invoice_date',
            'customer',
            'slmaster_id',
            'sales_order_id',
            'order_number',
            'sales_qty',
            'uom_name',
            'net_value'
        ));

        parent::index($salesinvoicelines, $sh);

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebarlist = array();

        $sidebarlist['allItems'] = array(
            'tag' => 'All Items',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        $sidebarlist['thisItem'] = array(
            'tag' => 'Item Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        if (isset($s_data['stitem_id'])) {
            $stitem = DataObjectFactory::Factory($this->modeltype);

            $stitem->load($s_data['stitem_id']);

            if ($stitem && $stitem->comp_class == 'M') {
                $sidebarlist['newWorkorder'] = array(
                    'tag' => 'New Works Order',
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => 'MFWorkorders',
                        'action' => 'new',
                        'stitem_id' => $id
                    )
                );

                $sidebarlist['viewWorkorders'] = array(
                    'tag' => 'Show Work Orders',
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'viewWorkorders',
                        'id' => $id
                    )
                );
            }
        }

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    function viewWorkorders()
    {
        $errors = array();
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['id'])) {
            $s_data['stitem_id'] = $this->_data['id'];
        } elseif (isset($this->_data['Search']['stitem_id'])) {
            $s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
        }

        $_GET['id'] = $id = $s_data['stitem_id'];

        $this->setSearch('stitemsSearch', 'itemSearch', $s_data);

        $stitem = &$this->_uses[$this->modeltype];

        $stitem->load($id);

        $this->view->set('transaction', $stitem);
        $this->view->set('id', $id);

        $worksorders = new MFWorkorderCollection();

        $worksorders->orderby = 'required_by';
        $worksorders->direction = 'desc';

        $sh = $this->setSearchHandler($worksorders);

        $sh->setFields(array(
            'id',
            'wo_number',
            'order_qty',
            'made_qty',
            'required_by',
            'status'
        ));

        parent::index($worksorders, $sh);

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'MFWorkorders');

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Show', array(
            'allItems' => array(
                'tag' => 'All Items',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            ),
            'thisItem' => array(
                'tag' => 'Item Detail',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'view',
                    'id' => $id
                )
            ),
            'newWorkorder' => array(
                'tag' => 'New Works Order',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'MFWorkorders',
                    'action' => 'new',
                    'stitem_id' => $id
                )
            ),
            'SalesOrders' => array(
                'tag' => 'Show Sales Orders',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'viewSalesorders',
                    'id' => $id
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    public function where_Used()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $transaction = $this->_uses[$this->modeltype];

        $id = $transaction->id;

        $this->view->set('transaction', $transaction);

        $elements = new MFStructureCollection();

        $elements->orderby = array(
            'stitem, line_no'
        );

        $sh = $this->setSearchHandler($elements);

        $sh->addConstraint(new Constraint('ststructure_id', '=', $id));

        $sh->setFields(array(
            'id',
            'line_no',
            'stitem',
            'stitem_id',
            'start_date',
            'end_date',
            'qty',
            'uom',
            'waste_pc'
        ));

        parent::index($elements, $sh);

        // Sidebar only visible when called as stand alone not as Related Items
        $sidebar = new SidebarController($this->view);

        $sidebarlist = array();

        $sidebarlist['viewItem'] = array(
            'tag' => 'View Detail',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            )
        );

        $sidebarlist['show_parts'] = array(
            'tag' => 'Show Parts',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'MFStructures',
                'action' => 'index',
                'stitem_id' => $id
            )
        );

        $sidebar->addList('This Item', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'MFStructures');

        $this->view->set('page_title', $this->getPageName('', 'View'));
    }

    public function markObsolete()
    {
        $flash = Flash::Instance();

        $db = DB::Instance();

        $db->StartTrans();

        $errors = array();

        $stitem = DataObjectFactory::Factory($this->modeltype);

        if ($stitem->load($this->_data['id'])) {
            $stitem->obsolete_date = date('Y-m-d');

            if (! $stitem->save()) {
                $errors[] = 'Could not mark as obsolete';
                $db->FailTrans();
            }
        } else {
            $errors[] = 'Could not mark as obsolete';
            $db->FailTrans();
        }

        if (count($errors) == 0) {
            $cc = new ConstraintChain();

            $cc->add(new Constraint('ststructure_id', '=', $stitem->id));

            $cc->add(new Constraint('end_date', 'IS', 'NULL'));

            $mfstructure = DataObjectFactory::Factory('MFStructure');

            $mfstructure_ids = array_keys($mfstructure->getAll($cc));

            $data = array(
                'end_date' => date(DATE_FORMAT),
                'remarks' => 'Marked as obsolete'
            );

            foreach ($mfstructure_ids as $mfstructure_id) {
                $data['id'] = $mfstructure_id;
                $mfstructure = MFStructure::Factory($data, $errors, 'MFStructure');

                if ((count($errors) == 0) && ($mfstructure->save())) {
                    continue;
                }

                $errors[] = 'Could not update structure';

                $db->FailTrans();
                break;
            }

            if (count($errors) == 0) {
                if (! $stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL)) {
                    $errors[] = 'Could not roll-up latest costs';
                    $db->FailTrans();
                }
            }
        }

        $db->CompleteTrans();

        if (count($errors) == 0) {
            $flash->addMessage('Stock item marked as obsolete');
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->_data['id']
            ));
        } else {
            $flash->addErrors($errors);
            sendBack();
        }
    }

    public function getStockAtLocation()
    {
        // Used by Ajax to return Stock list after selecting the location
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $stitems = array();

        if (! empty($_id)) {
            $stitems = STBalance::getStockList($_id);
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $stitems);
            $this->setTemplateName('select_options');
        } else {
            return $stitems;
        }
    }

    public function getStockBalanceAtLocation($_id = '', $_whlocation_id = '')
    {
        // Used by Ajax to return Stock list after selecting the location
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
            if (! empty($this->_data['whlocation_id'])) {
                $_whlocation_id = $this->_data['whlocation_id'];
            }
        }

        $balance = 0;

        if (! empty($_id)) {
            $cc = new ConstraintChain();

            $cc->add(new Constraint('stitem_id', '=', $_id));
            $cc->add(new Constraint('whlocation_id', '=', $_whlocation_id));

            $stbalance = DataObjectFactory::Factory('STBalance');

            $stbalance->loadBy($cc);

            if ($stbalance) {
                $balance = $stbalance->balance;
            }
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $balance);
            $this->setTemplateName('text_inner');
        } else {
            return $balance;
        }
    }

    public function getUomId($_id = '')
    {
        // Used by Ajax to return UoM list after selecting the item
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        if (! empty($_id)) {
            $stitem = DataObjectFactory::Factory($this->modeltype);

            $stitem->load($_id);

            if ($stitem) {
                $uom_id = $stitem->uom_id;
            }
        } else {
            $uom_id = '';
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $uom_id);
            $this->setTemplateName('text_inner');
        } else {
            return $uom_id;
        }
    }

    public function getUomList($_id = '')
    {
        // Used by Ajax to return UoM list after selecting the item
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $uom_list = array();

        if (! empty($_id)) {
            $stitem = DataObjectFactory::Factory($this->modeltype);

            $stitem->load($_id);

            $uom_list = $stitem->getUomList();
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $uom_list);
            $this->setTemplateName('select_options');
        } else {
            return $uom_list;
        }
    }

    public function getUomName($_id = '')
    {
        // Used by Ajax to return UoM list after selecting the item
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        if (! empty($_id)) {
            $stitem = DataObjectFactory::Factory($this->modeltype);

            $stitem->load($_id);

            if ($stitem) {
                $uom_name = $stitem->uom_name;
            }
        } else {
            $uom_name = '';
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $uom_name);
            $this->setTemplateName('text_inner');
        } else {
            return $uom_name;
        }
    }

    /* consolidation function */

    /*
     * this is a good example of something that belongs in whtranfers but
     * accesses stuff from stitems, if the functions were in a model or even in
     * the parent controller method this could be in its own controller.
     */
    public function getWhtransfersLineData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_id = $this->_data['id'];
        $_whlocation_id = $this->_data['whlocation_id'];

        $stuom_id = $this->getUomId($_id);
        $output['stuom_id'] = array(
            'data' => $stuom_id,
            'is_array' => is_array($stuom_id)
        );

        $uom_name = $this->getUomName($_id);
        $output['uom_name'] = array(
            'data' => $uom_name,
            'is_array' => is_array($uom_name)
        );

        $available_qty = $this->getStockBalanceAtLocation($_id, $_whlocation_id);
        $output['available_qty'] = array(
            'data' => $available_qty,
            'is_array' => is_array($available_qty)
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((empty($base) ? 'Stock Items' : $base), $action);
    }

    /**
     * Check that a cost basis is valid with the current module settings
     *
     * @return boolean
     */
    protected function costBasisValid($cost_basis)
    {
        if (($this->module_prefs['use-only-default-cost-basis'] == 'on' &&
                $this->module_prefs['default-cost-basis'] !== $cost_basis) &&
                $this->_data[$this->modeltype]['comp_class'] == 'M') {
            return false;
        }
        return true;
    }

    /**
     * Item search
     *
     * Search for an item code in the STItems table and
     * return the results as a JSON response.
     *
     * Designed as a source for the jQuery UI Autcomplete widget.
     *
     * @return void
     */
    public function searchItems($max_results = 200)
    {
        // GET request with XHR header required
        $this->checkRequest([
            'get'
        ], true);

        $search_params = [
            $this->_data['term'] . '%',
            $max_results + 1
        ];

        // Build a query to search the item codes
        $stitems = DataObjectFactory::Factory('STItem');
        $st_filter = new ConstraintChain();
        $st_filter->add(new Constraint('obsolete_date', 'is', 'NULL'));

        // Set place-holder that will become a query parameter placeholder.
        // The DB table has a suitable index that uses varchar_pattern_ops
        // to speeds up left anchored pattern searching.
        $st_filter->add(new Constraint('item_code', 'ILIKE', '####'));

        $query = $stitems->getQuery([
            'id',
            'item_code',
            'description'
        ], $st_filter);
        $query .= ' ORDER BY item_code ASC LIMIT ?';
        $query = str_replace("'####'", '?', $query);

        // Run query
        $db = DB::Instance();
        $items = $db->GetAll($query, $search_params);

        // If more than max_results, return a message
        if (count($items) > $max_results) {
            echo json_encode([
                [
                    'label' => "Over {$max_results} items found, please enter more of the item code",
                    'value' => 'error'
                ]
            ]);
            exit();
        }

        // Return the results as JSON
        $json_array = [];
        foreach ($items as $item) {
            $json_array[] = [
                'item_code' => $item['item_code'],
                'description' => $item['description'],
                'value' => $item['id'],
                'label' => "{$item['item_code']} - {$item['description']}"
            ];
        }
        echo json_encode($json_array);
        exit();
    }
}
// End of StitemsController
