<?php
/**
 *	Selector Controller
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
class SelectorController extends PrintController
{

    protected $version = '$Revision: 1.32 $';

    protected $itemFields = array();

    protected $targetModel = '';

    protected $targetFields = array();

    protected $itemTableName = '';

    protected $itemOverviewName = '';

    protected $linkTableName = '';

    protected $_templateobject;

    protected $title;

    protected $module;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->module = $module;
        $configdetails = SelectorCollection::getTypeDetails($module);

        // set parent controller variables
        $this->itemFields = $configdetails['itemFields'];

        $this->targetFields = $configdetails['targetFields'];

        $this->itemTableName = $configdetails['itemTableName'];

        $this->itemOverviewName = $configdetails['itemOverviewName'];

        $this->title = $configdetails['title'];
        $this->targetModel = $configdetails['targetModel'];

        $this->setTemplateObject();

        $this->linkTableName = $configdetails['linkTableName'];
    }

    public function setTemplateObject()
    {
        $this->_templateobject = new SelectorObject($this->itemTableName);
        $this->uses($this->_templateobject);
        $this->_templateobject->setDefaultDisplayFields($this->itemFields);
        $this->_templateobject->setEnum('description', $this->_templateobject->getDisplayFieldNames());
    }

    /*
     * extendable functions
     */
    public function index()
    {
        $this->view->set('clickaction', 'view');

        $s_data = array();

        // Clear any previous searches
        $s_data['clear'] = 'Clear';

        $this->setSearch($this->_templateobject, 'selectorSearch', 'itemSearch', $s_data);
        $collection = new SelectorCollection($this->_templateobject, $this->itemOverviewName);
        $sh = $this->setSearchHandler($collection);
        $sh->setFields(array(
            'id',
            'name'
        ));
        $sh->setOrderby('name');

        parent::index($collection, $sh);

        $this->view->set('collection', $collection);

        $top_level = current($this->itemFields);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['new'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'New ' . $top_level
        );

        $actions['output'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'select_item_component_output',
                'id' => $current->id
            ),
            'tag' => 'Output Item Component List'
        );

        $sidebar->addList('Actions', $actions);

        $this->sidebarRelatedItems($sidebar, $this->_templateobject);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->setTemplateName('selector_index');
    }

    public function view()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            // we are viewing data, but either no id has been provided
            // or the data for the supplied id does not exist
            $this->dataError();
            sendBack();
        }

        $this->view->set('clickaction', 'view');

        $current = $this->_uses[$this->modeltype];

        $parent = $this->getHierarchy($current->parent_id, $current->description);

        $this->view->set('clickaction', 'view');

        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['id'])) {
            $s_data['parent_id'] = $this->_data['id'];
        }

        $this->setSearch($this->_templateobject, 'selectorSearch', 'itemSearch', $s_data);

        $collection = new SelectorCollection($this->_templateobject, $this->itemOverviewName);
        $sh = $this->setSearchHandler($collection);
        $sh->setFields(array(
            'id',
            'name'
        ));
        $sh->setOrderby('name');
        parent::index($collection, $sh);

        $this->view->set('SelectorObject', $current);

        $this->view->set('collection', $collection);

        $this->view->set('no_ordering', TRUE);

        $top_level = current($this->itemFields);

        $link = new DataObject($this->linkTableName);
        $link->idField = $link->identifierField = 'target_id';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('item_id', '=', $current->id));
        $selected_ids = $link->getAll($cc);
        $selectorobjects = $this->getComponents($selected_ids);

        $this->view->set('component_count', count($selected_ids));
        $this->view->set('selectorobjects', $selectorobjects);
        $headings = $selectorobjects->getheadings();
        $targetHeadings = array();

        foreach ($this->targetFields as $key => $field) {
            if (isset($headings[$field])) {
                $targetHeadings[$field] = $headings[$field];
            }
        }
        $this->view->set('headings', $targetHeadings);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['view'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'View all ' . $top_level . 's'
        );

        $actions['new'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            ),
            'tag' => 'New ' . $top_level
        );

        $sidebar->addList('Actions', $actions);

        foreach (array_reverse($parent, true) as $id => $detail) {
            $actions = array();

            $actions['new_' . $detail['child_description']] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'parent_id' => $id
                ),
                'tag' => 'New ' . $detail['child_description']
            );

            // $actions['copy_'.$detail['child_description']] = array(
            // 'link'=>array('modules'=>$this->_modules
            // ,'controller'=>$this->name
            // ,'action'=>'copy'
            // ,'id'=>$id),
            // 'tag'=>'Copy '.$detail['child_description']
            // );

            $sidebar->addList($detail['description'] . ' : ' . $detail['name'], $actions);
        }

        foreach ($this->itemFields as $key => $fieldname) {
            if (strtolower($current->description) == $fieldname) {
                $child_description = $this->itemFields[$key + 1];
                break;
            }
        }

        $actions = array();

        $actions['edit_' . $current->description] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'edit',
                'id' => $current->id
            ),
            'tag' => 'Edit'
        );

        $actions['delete_' . $current->description] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'deleteSelector'
            ),
            'tag' => 'Delete',
            'class' => 'confirm',
            'data_attr' => [
                'data_uz-confirm-message' => "Delete {$current->name} and all connected selectors and component links?|This cannot be undone.",
                'data_uz-action-id' => $current->id
            ]
        );

        // $actions['copy_'.$current->description] = array(
        // 'link'=>array('modules'=>$this->_modules
        // ,'controller'=>$this->name
        // ,'action'=>'copy'
        // ,'id'=>$current->id),
        // 'tag'=>'Copy'
        // );

        if (! empty($child_description)) {
            $actions['new_' . $child_description] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'parent_id' => $current->id
                ),
                'tag' => 'New ' . $child_description
            );
        }

        if ($selectorobjects->count() > 0) {
            $actions['print_component_list' . $child_description] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'print_component_list',
                    'id' => $current->id
                ),
                'tag' => 'print_component_list'
            );
        }

        $actions['amend_components'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'select_targets',
                'item_id' => $current->id
            ),
            'tag' => 'Amend Component List'
        );

        $sidebar->addList($current->description . ' : ' . $current->name, $actions);

        $this->view->set('child_description', $child_description . 's');

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('view_title', 'view ' . $this->description . ' ' . $this->name);

        $this->setTemplateName('selector_view');
    }

    public function edit()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            // we are viewing data, but either no id has been provided
            // or the data for the supplied id does not exist
            $this->dataError();
            sendBack();
        }

        $this->_new();
    }

    public function _new()
    {
        parent::_new();

        $current = $this->_uses[$this->modeltype];

        $this->view->set('clickaction', 'view');

        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['id']) && $current->isLoaded()) {
            $s_data['parent_id'] = $current->parent_id;
        } elseif (isset($this->_data['parent_id']) && $this->_data['parent_id'] != '') {
            $s_data['parent_id'] = $this->_data['parent_id'];
        } else {
            $s_data['parent_id'] = '-1';
        }

        $this->setSearch($this->_templateobject, 'selectorSearch', 'itemSearch', $s_data);

        $collection = new SelectorCollection($this->_templateobject, $this->itemOverviewName);
        $sh = $this->setSearchHandler($collection);
        $sh->setFields(array(
            'id',
            'name'
        ));
        $sh->setOrderby('name');
        parent::index($collection, $sh);

        $this->view->set('SelectorObject', $current);

        $this->view->set('collection', $collection);
        $this->view->set('options', $collection->getAssoc());

        if ($current->isLoaded()) {
            $parent_id = $current->parent_id;
            $description = $current->description;
        } else {

            $parent_id = $this->search->getValue('parent_id');

            if ($parent_id != - 1) {
                $parent = new SelectorObject($this->itemTableName);
                $parent->load($parent_id);
                $description = $this->getNextDescription(strtolower($parent->description));
            } else {
                $parent_id = '';
                $description = strtolower(current($this->itemFields));
            }
        }

        $parent = $this->getHierarchy($parent_id, $description);

        $this->view->set('description', $description);

        $this->view->set('no_ordering', TRUE);

        $top_level = current($this->itemFields);

        $this->setTemplateName('selector_new');
    }

    public function save()
    {
        $flash = Flash::Instance();

        $errors = array();

        if (isset($this->_data['SelectorObject']['parent_id']) && $this->_data['SelectorObject']['parent_id'] == '-1') {
            $this->_data['SelectorObject']['parent_id'] = '';
        }

        $do = new DataObject($this->itemTableName);
        $do1 = DataObject::Factory($this->_data['SelectorObject'], $errors, $do);

        $db = DB::Instance();
        $db->StartTrans();

        if (count($errors) == 0 && $do1 && $do1->save()) {

            if ($do1 && ! empty($this->_data['SelectorObject']['copy_id'])) {

                $data = array(
                    'tablename' => $this->linkTableName,
                    'from_item_id' => $this->_data['SelectorObject']['copy_id'],
                    'to_item_id' => $do1->id
                );

                $result = SelectorCollection::copyItems($data, $errors);

                if (! $result) {
                    $errors[] = 'Failed to copy associations';
                }
            }

            $db->CompleteTrans();
            $flash->addMessage("$do1->description $do1->name saved successfully");
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $do1->id
            ));
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            if (count($errors) > 0) {
                $flash->addErrors($errors);
            }
            $flash->addError("Error saving $do1->description $do1->name " . $db->ErrorMsg());
            $this->_data['id'] = $this->_data['SelectorObject']['id'];
            $this->_data['parent_id'] = $this->_data['SelectorObject']['parent_id'];
            $this->refresh();
        }
    }

    /*
     * functions for the assigning of items to targets
     */
    public function select_items()
    {
        $flash = Flash::Instance();

        $s_data = array();

        $params['options']['parent_id'] = $this->_templateobject->getDisplayFieldNames();

        // so set context from calling module
        if (isset($this->_data['parent_id'])) {
            $s_data['parent_id'] = $this->_data['parent_id'];
        } elseif (! isset($this->_data['Search']['parent_id'])) {
            $s_data['parent_id'] = NULL;
        }

        $params['type'] = $this->module;

        parent::setSearch('SelectorItemSearch', 'useDefault', $s_data, $params);

        // load the default display fields into the session, we need these as we cannot rely on getting the headings from the data itself
        $_SESSION['selected_items']['headings'] = $this->_templateobject->getDisplayFieldNames();

        $this->_templateobject->idField = 'id';
        $this->_templateobject->orderby = $this->itemFields;
        $collection = new SelectorCollection($this->_templateobject);
        $collection->setTableName($collection->setOverview());

        $sh = $this->setSearchHandler($collection);

        if (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $sh->addConstraint(SelectorCollection::getItemHierarchy($this->module, $this->search->getValue('parent_id')));
        }
        // get list of items matching search criteria
        parent::index($collection, $sh);

        // construct and set link
        foreach ($this->_modules as $key => $value) {
            $modules[] = $key . '=' . $value;
        }
        $link = implode('&', $modules) . '&controller=' . $this->name . '&action=selected_items';
        $this->view->set('link', $link);

        // get the list of selected targets
        // - if target supplied on input, use that
        // - otherwise use the saved session
        if (isset($this->_data['target_id']) && ! isset($this->_data['page'])) {
            $this->_data['selected_targets'][] = $this->_data['target_id'];
            $_SESSION['selected_items']['data'] = $_SESSION['selected_targets']['data'] = $selected_targets = '';
        } else {
            $selected_targets = empty($_SESSION['selected_targets']['data']) ? array() : $_SESSION['selected_targets']['data'];
        }

        if (empty($selected_targets)) {
            if (! isset($this->_data['selected_targets']) || count($this->_data['selected_targets']) <= 0) {
                // no target set on input or in saved session - so start from scratch!
                $selected_targets = $_SESSION['selected_targets']['data'] = array();
            } else {

                // target must have been set on input so get the target details and save to session
                $_SESSION['selected_targets'] = array();
                $selected_target_name = $this->targetModel . 'Collection';
                $target = new $this->targetModel();
                $idField = $target->idField;
                $targets = new $selected_target_name($target);
                $sh = new SearchHandler($targets);
                $sh->addConstraint(new Constraint($idField, 'IN', '(' . implode(',', $this->_data['selected_targets']) . ')'));
                $rows = $targets->load($sh, null, RETURN_ROWS);
                foreach ($rows as $data) {
                    $selected_targets[$data[$idField]] = $data;
                }
                $_SESSION['selected_targets']['data'] = $selected_targets;

                $target_headings = $target->getDisplayFieldNames();
                foreach ($this->targetFields as $fieldname) {
                    $selected_target_headings[$fieldname] = $target_headings[$fieldname];
                }
                $_SESSION['selected_targets']['headings'] = $selected_target_headings;
            }
        }

        $this->view->set('selected_targets', $selected_targets);
        $this->view->set('current_targets_headings', $_SESSION['selected_targets']['headings']);

        // get list of selected items
        if (! empty($selected_targets) && empty($_SESSION['selected_items']['data'])) {
            // targets are defined but no items, so get the items associated with the targets
            $item_link = new DataObject($this->linkTableName);
            $item_link->idField = 'item_id';
            $cc = new ConstraintChain();
            $cc->add(new Constraint('target_id', 'IN', '(' . implode(',', array_keys($selected_targets)) . ')'));
            $item_ids = $item_link->getAll($cc);

            $item = new SelectorObject($this->itemTableName);
            $item->setDefaultDisplayFields($this->itemFields);
            $item->idField = 'id';
            $item->orderby = $this->itemFields;
            $items = new SelectorCollection($item);
            $items->setTableName($items->setOverview());
            $sh = new SearchHandler($items);
            if (count($item_ids) > 0) {
                $sh->addConstraint(new Constraint($item->idField, 'IN', '(' . implode(',', array_keys($item_ids)) . ')'));
            } else {
                $sh->addConstraint(new Constraint($item->idField, '=', - 1));
            }
            $rows = $items->load($sh, null, RETURN_ROWS);
            if (count($rows) > 0) {
                foreach ($rows as $data) {
                    $_SESSION['selected_items']['data'][$data['id']] = $data;
                }
            } else {
                $_SESSION['selected_items']['data'] = array();
            }
        }

        // get and set variables
        $selected_items = $_SESSION['selected_items']['data'];
        $selected_item_headings = empty($_SESSION['selected_items']['headings']) ? array() : $_SESSION['selected_items']['headings'];
        $this->view->set('selected_items', $selected_items);
        $this->view->set('selected_item_headings', $selected_item_headings);
        $this->view->set('selected_item_headings_count', count($selected_item_headings) + 1);
        $this->view->set('title', $this->title);
        $this->view->set('page_title', $this->getPageName('Select Items'));
        $this->printaction = '';
    }

    public function select_targets()
    {
        $flash = Flash::Instance();

        $s_data = array();

        parent::setSearch('SelectorTargetSearch', 'useDefault', $s_data);

        // load the default display fields into the session
        // we need these as we cannot rely on getting the headings from the data itself
        $target = new $this->targetModel();
        $target_headings = $target->getDisplayFieldNames();
        foreach ($this->targetFields as $fieldname) {
            $selected_target_headings[$fieldname] = $target_headings[$fieldname];
        }
        $_SESSION['selected_targets']['headings'] = $selected_target_headings;

        $collection_name = $this->targetModel . 'Collection';
        $collection = new $collection_name($target);

        // get list of targets matching search criteria
        parent::index($collection);

        $this->view->set('collection', $collection);

        // construct and set link
        foreach ($this->_modules as $key => $value) {
            $modules[] = $key . '=' . $value;
        }
        $link = implode('&', $modules) . '&controller=' . $this->name . '&action=selected_targets';
        $this->view->set('link', $link);

        // get the list of selected items
        // - if item supplied on input, use that
        // - otherwise use the saved session
        $selected_items = array();
        if (isset($this->_data['item_id']) && ! isset($this->_data['page'])) {
            // item_id supplied so get the object associated with this id
            $item = new SelectorObject($this->itemTableName);
            $item->load($this->_data['item_id']);
            // now get all items in the item hierarchy that match this id
            $items = new SelectorCollection(clone $this->_templateobject);
            $items->setTableName($items->setOverview());
            $this->_data['selected_items'][] = $this->_data['item_id'];
            $sh = new SearchHandler($items, false);
            $sh->addConstraint(new Constraint($item->description . '_id', '=', $item->id));
            $items->load($sh);
            
            // We may be here becuase the user cleared an item search.
            // Only create the session array if there are no targets,
            // otherwise the user's new selections will keep disapearing
            if (empty($_SESSION['selected_targets']['data']) || empty($this->_data['search_id'])) {
                $_SESSION['selected_targets']['data'] = [];
            }

            $idField = $items->getModel()->idField;
            $items_data = $items->getArray();
            if (! empty($items_data)) {
                foreach ($items->getArray() as $data) {
                    $selected_items[$data[$idField]] = $data;
                }
            }
            $_SESSION['selected_items']['data'] = $selected_items;
            $_SESSION['selected_items']['headings'] = $this->_templateobject->getDisplayFieldNames();
        } else {
            $selected_items = empty($_SESSION['selected_items']['data']) ? array() : $_SESSION['selected_items']['data'];
        }
        if (count($selected_items) == 0) {
            if (! isset($this->_data['selected_items']) || count($this->_data['selected_items']) <= 0) {
                // no item set on input or in saved session - so start from scratch!
                $selected_items = $_SESSION['selected_items']['data'] = array();
            } else {
                // item must have been set on input so get the item details and save to session
                $_SESSION['selected_items'] = array();
                $items = new SelectorCollection(clone $this->_templateobject);
                $items->setTableName($items->setOverview());
                $sh = new SearchHandler($items, false);
                $sh->addConstraint(new Constraint($this->_templateobject->idField, 'IN', '(' . implode(',', $this->_data['selected_items']) . ')'));
                $items->load($sh);

                $idField = $items->getModel()->idField;
                $items_data = $items->getArray();
                if (! empty($items_data)) {
                    foreach ($items->getArray() as $data) {
                        $selected_items[$data[$idField]] = $data;
                    }
                }
                $_SESSION['selected_items']['data'] = $selected_items;
                $_SESSION['selected_items']['headings'] = $this->_templateobject->getDisplayFieldNames();
            }
        }

        $this->view->set('selected_items', $selected_items);
        $this->view->set('current_items_headings', $_SESSION['selected_items']['headings']);

        // get list of selected targets
        if (! empty($selected_items) && empty($_SESSION['selected_targets']['data'])) {
            // items are defined but no targets, so get the targets associated with the items
            $target_link = new DataObject($this->linkTableName);
            $target_link->idField = 'target_id';
            $cc = new ConstraintChain();
            $cc->add(new Constraint('item_id', 'IN', '(' . implode(',', array_keys($selected_items)) . ')'));
            $target_ids = $target_link->getAll($cc);

            $selected_target_name = $this->targetModel . 'Collection';
            $target = new $this->targetModel();
            $targets = new $selected_target_name($target);
            $sh = new SearchHandler($targets, false);

            if (count($target_ids) > 0) {
                $values = '(' . implode('),(', array_keys($target_ids)) . ')';
                $sh->addConstraint(new Constraint($target->idField, '= ANY', "(VALUES {$values})"));

                $rows = $targets->load($sh, null, RETURN_ROWS);
                foreach ($rows as $data) {
                    $_SESSION['selected_targets']['data'][$data['id']] = $data;
                }
            } else {
                $_SESSION['selected_targets']['data'] = array();
            }
        }

        $selected_targets = $_SESSION['selected_targets']['data'];
        $selected_target_headings = empty($_SESSION['selected_targets']['headings']) ? array() : $_SESSION['selected_targets']['headings'];
        $this->view->set('selected_targets', $selected_targets);
        $this->view->set('selected_target_headings', $selected_target_headings);
        $this->view->set('selected_target_headings_count', count($selected_target_headings) + 1);
        $this->view->set('title', $this->title);
        $this->view->set('page_title', $this->getPageName('', 'Select'));
        $this->printaction = '';
    }

    public function confirm_relationships()
    {
        $flash = Flash::Instance();
        // echo 'Items<pre>'.print_r($_SESSION['selected_items'], true).'</pre><br>';
        // echo 'Targets<pre>'.print_r($_SESSION['selected_targets'], true).'</pre><br>';
        // double check the items data;
        // use the data from $_SESSION['selected_items'] if it exists
        // otherwise use the ids in $this->_data['selected_items']
        $selected_items = empty($_SESSION['selected_items']['data']) ? array() : $_SESSION['selected_items']['data'];
        if (empty($selected_items)) {
            if (! isset($this->_data['selected_items']) || count($this->_data['selected_items']) <= 0) {
                $flash->addError("You must select at least one item.");
                sendBack();
            } else {
                $items = new SelectorCollection($this->_templateobject);
                $items->setTableName($items->setOverview());
                $sh = new SearchHandler($items, false);
                $sh->addConstraint(new Constraint('id', 'IN', '(' . implode(',', $this->_data['selected_items']) . ')'));
                $items->load($sh);

                $idField = $items->getModel()->idField;
                foreach ($items->getArray() as $data) {
                    $selected_items[$data->$idField] = $data;
                }
            }
        }

        // double check the targets data
        $selected_targets = empty($_SESSION['selected_targets']['data']) ? array() : $_SESSION['selected_targets']['data'];
        if (empty($selected_targets)) {
            if (! isset($this->_data['selected_targets']) || count($this->_data['selected_targets']) <= 0) {
                $flash->addError("You must select at least one target.");
                sendBack();
            } else {
                $target = new $this->targetModel();
                $collection = $this->targetModel . 'Collection';
                $targets = new $collection($target);
                $sh = new SearchHandler($targets, false);
                $sh->addConstraint(new Constraint('id', 'IN', '(' . implode(',', $this->_data['selected_targets']) . ')'));
                $targets->load($sh);

                $idField = $targets->getModel()->idField;
                foreach ($targets->getArray() as $data) {
                    $selected_items[$data->$idField] = $data;
                }
            }
        }

        $selected_item_headings = empty($_SESSION['selected_items']['headings']) ? $this->_templateobject->getDisplayFieldNames() : $_SESSION['selected_items']['headings'];
        $selected_target_headings = empty($_SESSION['selected_targets']['headings']) ? $target->getDisplayFieldNames() : $_SESSION['selected_targets']['headings'];

        $this->view->set('selected_items', $selected_items);
        $this->view->set('selected_targets', $selected_targets);
        $this->view->set('selected_item_headings', $selected_item_headings);
        $this->view->set('selected_target_headings', $selected_target_headings);
        $this->view->set('target_title', $this->title);
        $this->view->set('page_title', 'Confirm Link of Items to ' . $this->title);

        $deleted_items = empty($_SESSION['selected_items']['delete']) ? array() : $_SESSION['selected_items']['delete'];
        $deleted_targets = empty($_SESSION['selected_targets']['delete']) ? array() : $_SESSION['selected_targets']['delete'];

        $this->view->set('deleted', 'none');

        if (count($deleted_items) > 0 || count($deleted_targets) > 0) {

            $this->_templateobject->idField = 'id';
            $this->_templateobject->orderby = $this->itemFields;
            $collection = new SelectorCollection(clone $this->_templateobject);
            $collection->setTableName($collection->selectorLinkOverview($this->itemFields, $this->linkTableName, $this->targetModel, $this->targetFields));

            $sh = $this->setSearchHandler($collection);
            $sh->setFields(array_merge(array(
                'id'
            ), $this->itemFields, $this->targetFields));

            $cc1 = new ConstraintChain();
            if (! empty($deleted_targets)) {
                $cc1->add(new Constraint('item_id', 'IN', '(' . implode(',', array_keys($selected_items)) . ')'));
                $cc1->add(new Constraint('target_id', 'IN', '(' . implode(',', array_keys($deleted_targets)) . ')'));
            }

            $cc2 = new ConstraintChain();
            if (! empty($deleted_items)) {
                $cc2->add(new Constraint('item_id', 'IN', '(' . implode(',', array_keys($deleted_items)) . ')'));
                $cc2->add(new Constraint('target_id', 'IN', '(' . implode(',', array_keys($selected_targets)) . ')'));
            }

            $sh->addConstraint($cc1);
            $sh->addConstraint($cc2, 'OR');

            $collection->load($sh);

            if ($collection->num_records > 0) {
                $this->view->set('deleted', $collection);
            }
        }
    }

    public function save_relationships()
    {
        $flash = Flash::Instance();

        $errors = array();

        $itemlink = array();
        $selectedlinks = array();

        $db = DB::Instance();
        $db->StartTrans();

        $selected_items = $_SESSION['selected_items']['data'];
        $deleted_items = $_SESSION['selected_items']['delete'];
        $selected_targets = $_SESSION['selected_targets']['data'];
        $deleted_targets = $_SESSION['selected_targets']['delete'];

        if (! empty($deleted_targets) || ! empty($deleted_items)) {
            // Need to delete any relationship that are just about to be saved
            $do = new DataObject($this->linkTableName);
            $doc = new DataObjectCollection($do);
            $sh = new SearchHandler($doc, false);
            $cc1 = new ConstraintChain();
            if (! empty($deleted_targets)) {
                $cc1->add(new Constraint('item_id', 'IN', '(' . implode(',', array_keys($selected_items)) . ')'));
                $cc1->add(new Constraint('target_id', 'IN', '(' . implode(',', array_keys($deleted_targets)) . ')'));
            }

            $cc2 = new ConstraintChain();
            if (! empty($deleted_items)) {
                $cc2->add(new Constraint('item_id', 'IN', '(' . implode(',', array_keys($deleted_items)) . ')'));
                $cc2->add(new Constraint('target_id', 'IN', '(' . implode(',', array_keys($selected_targets)) . ')'));
            }

            $sh->addConstraint($cc1);
            $sh->addConstraint($cc2, 'OR');

            $deleted = $doc->delete($sh);

            if ($deleted > 0) {
                $flash->addWarning($deleted . ' relationships deleted');
            }
        }

        $selectedlinks = array();

        foreach ($selected_items as $item_key => $item_data) {
            foreach ($selected_targets as $target_key => $target_data) {

                $do = new DataObject($this->linkTableName);

                $cc = new ConstraintChain();
                $cc->add(new Constraint('item_id', '=', $item_key));
                $cc->add(new Constraint('target_id', '=', $target_key));

                $do->loadBy($cc);

                if (! $do->isLoaded()) {
                    // Only save links that do not exist
                    $selectedlinks[] = array(
                        'item_id' => $item_key,
                        'target_id' => $target_key
                    );
                }
            }
        }

        if (count($selectedlinks) > 0) {
            $result = SelectorCollection::saveAssociations($selectedlinks, $this->linkTableName, $errors);
        } else {
            $flash->addWarning('These relationships already exist - no data saved');
            $result = true;
        }

        if ($result) {
            $db->CompleteTrans();
            unset($_SESSION['selected_items']);
            unset($_SESSION['selected_targets']);
            $flash->addMessage(count($selectedlinks) . " relationships saved successfully");
            sendTo($this->name, 'view', $this->module, ['id' => array_keys($selected_items)[0]]);
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            $flash->addErrors($errors);
            $flash->addError("Error saving relationships");
            sendTo($this->name, 'confirm_relationships', $this->_modules);
        }
    }

    public function used_by()
    {
        if (! isset($this->_data['target_id']) && ! isset($this->_data['Search']['target_id'])) {
            $this->dataError();
            sendBack();
        }

        $flash = Flash::Instance();

        if (is_null($this->targetModel)) {
            $flash->addWarning('No usages defined for ' . prettify($this->module));
            sendBack();
        }

        $s_data = array();

        $params['options']['parent_id'] = $this->_templateobject->getDisplayFieldNames();

        // so set context from calling module
        if (isset($this->_data['target_id'])) {
            $s_data['target_id'] = $this->_data['target_id'];
        }
        if (isset($this->_data['parent_id'])) {
            $s_data['parent_id'] = $this->_data['parent_id'];
        } elseif (! isset($this->_data['Search']['parent_id'])) {
            $s_data['parent_id'] = NULL;
        }

        $params['type'] = $this->module;

        parent::setSearch('SelectorItemSearch', 'usedby', $s_data, $params);

        $target = new $this->targetModel();
        $target->load($this->_data['target_id']);
        $this->view->set('target', $target);
        $this->view->set('target_headings', $this->targetFields);

        $item_ids = SelectorCollection::getItems($this->module, $this->_data['target_id']);

        // load the default display fields into the session, we need these as we cannot rely on getting the headings from the data itself
        $headings = $this->_templateobject->getDisplayFieldNames();

        $this->_templateobject->idField = 'id';
        $this->_templateobject->orderby = $this->itemFields;
        $collection = new SelectorCollection($this->_templateobject);
        $collection->setTableName($collection->setOverview());

        $sh = $this->setSearchHandler($collection);

        if (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $sh->addConstraint(SelectorCollection::getItemHierarchy($this->module, $this->search->getValue('parent_id')));
            if (count($item_ids) > 0) {
                $sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', array_column($item_ids, 'id')) . ')'));
            } else {
                $sh->addConstraint(new Constraint('id', '=', - 1));
            }
        }

        parent::index($collection, $sh);

        $this->view->set('headings', $headings);

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('Actions', array(
            'view' => array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                ),
                'tag' => 'View Selectors'
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('page_title', $this->title . ' - Used By');
    }

    public function select_item_component_output($status = 'generate')
    {

        // build options array
        $options = array(
            'type' => array(
                'csv' => '',
                'pdf' => '',
                'xml' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'filename' => 'Item_Component_list_' . fix_date(date(DATE_FORMAT))
        );
        // 'report' => 'CustomReport'

        // simply return the options if we're only at the dialog stage
        if (strtolower($status) === "dialog") {
            return $options;
        }
        ;

        $flash = Flash::Instance();

        $s_data = array();

        $params['options']['parent_id'] = $this->_templateobject->getDisplayFieldNames();

        // so set context from calling module
        if (isset($this->_data['parent_id'])) {
            $s_data['parent_id'] = $this->_data['parent_id'];
        } elseif (! isset($this->_data['Search']['parent_id'])) {
            $s_data['parent_id'] = NULL;
        }

        $params['type'] = $this->module;

        parent::setSearch('SelectorItemSearch', 'useDefault', $s_data, $params);

        // load the default display fields into the session, we need these as we cannot rely on getting the headings from the data itself
        $_SESSION['selected_items']['headings'] = $this->_templateobject->getDisplayFieldNames();

        $this->_templateobject->idField = 'id';
        $this->_templateobject->orderby = $this->itemFields;
        $collection = new SelectorCollection(clone $this->_templateobject);
        $collection->setTableName($collection->selectorLinkOverview($this->itemFields, $this->linkTableName, $this->targetModel, $this->targetFields));

        $sh = $this->setSearchHandler($collection);
        $sh->setFields(array_merge(array(
            'id',
            'item_id',
            'target_id'
        ), $this->itemFields, $this->targetFields));

        if (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $sh->addConstraint(SelectorCollection::getItemHierarchy($this->module, $this->search->getValue('parent_id')));
        }

        // set the query count sql - if no constraints, then just get count from link table
        $criteria = $sh->constraints->__toString();
        if (empty($criteria)) {
            $c_query = 'select count(*) as count from ' . $this->linkTableName . ' link';
        }

        // get list of items matching search criteria
        parent::index($collection, $sh, $c_query);

        $this->view->set('collection', $collection);

        $this->setTemplateName('selector_index');
    }

    /*
     * AJAX functions
     */
    public function selected_items()
    {
        $items = explode('^', $this->_data['id']);
        foreach ($items as $key => $value) {
            if (! empty($value)) {
                // dbug::e('processing value',true);
                unset($params);
                $params = explode(',', $value);
                // open up the first record
                $first_value = explode('=', $params[0]);
                $linkdata = SESSION::Instance();
                $selected_items = empty($_SESSION['selected_items']['data']) ? array() : $_SESSION['selected_items']['data'];
                $deleted_items = empty($_SESSION['selected_items']['delete']) ? array() : $_SESSION['selected_items']['delete'];
                if (isset($selected_items[$first_value[0]])) {
                    if (strtolower($first_value[1]) == 'false') {
                        $deleted_items[$first_value[0]] = $selected_items[$first_value[0]];
                        unset($selected_items[$first_value[0]]);
                    }
                } elseif (strtolower($first_value[1]) == 'true') {
                    $count = 0;
                    foreach ($params as $key => $value) {
                        $values = explode('=', $value);
                        if ($count > 0) {
                            unset($deleted_items[$first_value[0]]);
                            $selected_items[$first_value[0]][$values[0]] = $values[1];
                        }
                        $count ++;
                    }
                }
                $_SESSION['selected_items']['data'] = $selected_items;
                $_SESSION['selected_items']['delete'] = $deleted_items;
                $selected_item_headings = empty($_SESSION['selected_items']['headings']) ? array() : $_SESSION['selected_items']['headings'];
            }
        }

        $this->view->set('selected_items', $selected_items);
        $this->view->set('selected_item_headings', $selected_item_headings);
    }

    public function selected_targets()
    {
        $targets = explode('^', $this->_data['id']);
        foreach ($targets as $key => $value) {
            if (! empty($value)) {
                unset($params);
                $params = explode(',', $value);
                // open up the first record
                $first_value = explode('=', $params[0]);
                $linkdata = SESSION::Instance();
                $selected_targets = empty($_SESSION['selected_targets']['data']) ? array() : $_SESSION['selected_targets']['data'];
                $deleted_targets = empty($_SESSION['selected_targets']['delete']) ? array() : $_SESSION['selected_targets']['delete'];
                if (isset($selected_targets[$first_value[0]])) {
                    if (strtolower($first_value[1]) == 'false') {
                        $deleted_targets[$first_value[0]] = $selected_targets[$first_value[0]];
                        unset($selected_targets[$first_value[0]]);
                    }
                } elseif (strtolower($first_value[1]) == 'true') {
                    $count = 0;
                    foreach ($params as $key => $value) {
                        $values = explode('=', $value);
                        if ($count > 0) {
                            $selected_targets[$first_value[0]][$values[0]] = $values[1];
                        }
                        $count ++;
                    }
                    unset($deleted_targets[$first_value[0]]);
                }
                $_SESSION['selected_targets']['data'] = $selected_targets;
                $_SESSION['selected_targets']['delete'] = $deleted_targets;
                $selected_target_headings = empty($_SESSION['selected_targets']['headings']) ? array() : $_SESSION['selected_targets']['headings'];
            }
        }
        $this->view->set('selected_targets', $selected_targets);
        $this->view->set('selected_target_headings', $selected_target_headings);
    }

    public function getParentSelectorList()
    {
        echo json_encode($this->getParentSelectors($this->_data['id']));
        exit();
    }

    public function getParentSelectors($source = '')
    {
        $scclist = array();
        $scc = new DataObject($this->itemTableName);
        $cc = new ConstraintChain();
        if ($source == 'Please select an option')
            $source = '';
        switch ($source) {
            case '':
            case '-1':
                $cc->add(new Constraint('parent_id', 'IS', 'NULL'));
                $scclist[''] = 'Please select an option';
                break;
            default:
                $cc->add(new Constraint('parent_id', '=', $source));
                $scclist[$source] = 'Please select an option';
                break;
        }

        $scclist += $scc->getAll($cc);
        return $scclist;
    }

    public function getSelectTreeBreadcrumbs()
    {
        $breadcrumbs = array();
        /*
         * It would appear that if the JavaScript is given a null value for the select box rather than leave it empty
         * it sets the value as NaN... great but it causes a few problems. When building the breadcrumbs ignore the
         * value if it is set to NaN
         *
         * UPDATE:
         * We are now forcing an empty value to be -1, so lets make sure we catch this and don't process it
         */
        if (isset($this->_data['id']) && $this->_data['id'] != '' && $this->_data['id'] != '-1') {

            $do = $this->_templateobject->load($this->_data['id']);
            $parent_id = $do->parent_id;
            $breadcrumbs[] = array(
                'id' => $do->id,
                'name' => $do->name,
                'parent_id' => $do->parent_id,
                'descriptor' => $do->description
            );

            if ($do->parent_id != '') {
                while (! empty($parent_id)) {
                    $do = new DataObject($this->itemTableName);
                    $do->load($parent_id);
                    // what if parent_id = -1? <-- seems like it's already sorted!
                    $breadcrumbs[] = array(
                        'id' => $do->id,
                        'name' => $do->name,
                        'parent_id' => $do->parent_id,
                        'descriptor' => $do->description
                    );
                    $parent_id = $do->parent_id;
                }
            }
        }

        if (count($breadcrumbs) > 0) {
            foreach ($breadcrumbs as $key => $value) {
                $output[] = '<li>' . $value['name'] . ' <a href="#" onclick="setSelectTree(\'' . $this->_data['parent'] . '\',\'' . $value['parent_id'] . '\',\'' . $this->_data['module'] . '\',\'' . $this->_data['submodule'] . '\',\'' . $this->_data['controller'] . '\',\'getParentSelectorList\')" title="Click to see other items on this level">(Choose another ' . strtolower($value['descriptor']) . ')</a></li>';
            }
            // breadcrumbs are construct backwards, lets sort them out (pun intended)
            sort($output, SORT_NUMERIC);
            $html = '<li><strong>Structure:</strong></li>' . implode('', $output);
        } else {
            $html = '<li><strong>Structure:</strong></li>';
        }

        echo $html;
        exit();
    }
    
    /*
     * Protected Functions
     */
    protected function getComponents($_selected_ids = '')
    {
        // load the default display fields into the session
        // we need these as we cannot rely on getting the headings from the data itself
        $target = new $this->targetModel();

        $collection_name = $this->targetModel . 'Collection';
        $collection = new $collection_name($this->targetModel);

        if (empty($_selected_ids)) {
            return $collection;
        }

        if (! is_array($_selected_ids)) {
            $_selected_ids = array(
                $_selected_ids
            );
        }

        // $sh = new SearchHandler($collection, FALSE);
        $sh = $this->setSearchHandler($collection, 'selector_components');
        $sh->addConstraint(new constraint('id', 'in', '(' . implode(',', $_selected_ids) . ')'));

        // $sh->extractFields();

        // $collection->load($sh);

        $this->search = null;
        if (isset($this->_data['ajax_print'])) {
            $sh->setLimit(0);
        }
        parent::index($collection, $sh);

        return $collection;
    }

    protected function setSearch($do, $search, $method, $defaults = array(), $params = array())
    {
        $errors = array();
        $s_data = array();

        if (isset($this->_data['search_id'])) {
            $defaults['search_id'] = $this->_data['search_id'];
        } elseif (isset($this->_data['Search']['search_id'])) {
            $defaults['search_id'] = $this->_data['Search']['search_id'];
        } elseif (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $defaults['search_id'] = strtotime('now');
        }

        if (isset($this->_data['Search'])) {
            $s_data = $this->_data['Search'];
        } elseif (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $s_data = $defaults;
        }

        $this->search = $search::$method($do, $s_data, $errors, $defaults, $params);

        if (count($errors) > 0) {
            $flash = Flash::Instance();
            $flash->addErrors($errors);
            $this->search->clear();
        }
    }

    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((! empty($base)) ? $base : $this->title, $action);
    }

    /*
     * Output Functions
     */
    public function print_component_list($status = 'generate')
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
            'filename' => 'Component_list_' . fix_date(date(DATE_FORMAT)),
            'report' => 'selector_components'
        );

        // simply return the options if we're only at the dialog stage
        if (strtolower($status) === "dialog") {
            return $options;
        }
        ;

        $errors = array();
        $messages = array();

        if (! isset($this->_data) || ! $this->loadData()) {
            // we are viewing data, but either no id has been provided
            // or the data for the supplied id does not exist
            $this->dataError();
            sendBack();
        }

        $current = $this->_uses[$this->modeltype];

        $parent = $this->getHierarchy($current->parent_id, $current->description);

        $title_template = '<fo:table-row>' . "\r\n";
        $title_template .= '	<fo:table-cell padding="1mm" width="%s" text-align="%s">' . "\r\n";
        $title_template .= '    	<fo:block >%s</fo:block>' . "\r\n";
        $title_template .= '	</fo:table-cell>' . "\r\n";
        $title_template .= '	<fo:table-cell padding="1mm" text-align="%s" >' . "\r\n";
        $title_template .= '    	<fo:block font-weight="bold">%s</fo:block>' . "\r\n";
        $title_template .= '	</fo:table-cell>' . "\r\n";
        $title_template .= '</fo:table-row>' . "\r\n";

        // load the model
        $link = new DataObject($this->linkTableName);
        $link->idField = $link->identifierField = 'target_id';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('item_id', '=', $current->id));
        $selected_ids = $link->getAll($cc);
        $selectorobjects = $this->getComponents($selected_ids);

        $width = strlen($current->description);

        foreach ($parent as $item_detail) {
            $width = strlen($item_detail['description']) > $width ? strlen($item_detail['description']) : $width;
        }

        $item_list = '';

        foreach (array_reverse($parent) as $item_detail) {
            $item_list .= sprintf($title_template, ($width * 2) . 'mm', 'left', prettify($item_detail['description']), 'left', $item_detail['name']);
        }

        $item_list .= sprintf($title_template, ($width * 2) . 'mm', 'left', prettify($current->description), 'left', $current->name);

        $headings = $selectorobjects->getheadings();
        $targetHeadings = array();

        foreach ($this->targetFields as $key => $field) {
            if (isset($headings[$field])) {
                $targetHeadings[$field] = $headings[$field];
            }
        }

        $report_title = 'List of Component ' . (empty($title) ? prettify($this->getPageName('', '')) : 'items') . ' for Item, Printed on ' . date(DATE_TIME_FORMAT);

        // build the custom XSL
        $xsl = $this->build_custom_xsl($selectorobjects, 'selector_components', $report_title, $targetHeadings, '', array());

        if ($xsl === FALSE) {
            return FALSE;
        }

        $xsl = $this->process_xsl($xsl, array(
            'ITEM_LIST' => $item_list
        ));

        $options['xslSource'] = $xsl;

        $options['xmlSource'] = $this->generate_xml(array(
            'model' => $selectorobjects,
            'extra' => $extra,
            'load_relationships' => FALSE
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->generate_output($this->_data['print'], $options);
        exit();
    }

     //Private Functions

    /**
     * Get the descritpion of the next level in the selector hierarchy
     * 
     * @param string $description Current level in hierarchy
     * 
     * @return mixed string/false
     */
     private function getNextDescription($description = '')
    {
        // Return the description of the top level of the hierarchy
        $first = reset($this->itemFields);
        if (empty($description)) {
            return $first;
        }

        // Return the description of the next level in the hierarchy
        while ($this->itemFields) {
            if (current($this->itemFields) == $description) {
                return next($this->itemFields);
            }
            next($this->itemFields);
        }

        return false;
    }

    protected function getHierarchy($_parent_id = '', $_description = '')
    {
        $parent = array();

        if (! empty($_parent_id)) {
            $parent_id = $_parent_id;
            $child_description = $_description;
            if ($parent_id != '') {
                while (! empty($parent_id)) {
                    $do = new SelectorObject($this->itemTableName);
                    $do->load($parent_id);
                    $parent[$parent_id]['description'] = $do->description;
                    $parent[$parent_id]['child_description'] = $child_description;
                    $parent[$parent_id]['name'] = $do->name;
                    $parent_id = $do->parent_id;
                    $child_description = $do->description;
                }
            }
        }
        $this->view->set('parent', array_reverse($parent, true));

        return $parent;
    }
}

// End of SelectorController
