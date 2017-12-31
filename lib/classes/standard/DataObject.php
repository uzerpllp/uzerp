<?php

/**
 *  DataObject Class
 *
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *  uzERP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 */
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class DataObject implements Iterator
{

    protected $version = '$Revision: 1.133 $';

    // string: name of the table represented by the Object
    protected $_title;
    protected $_tablename;
    protected $_tablenames = array();
    protected $_classname;
    protected $_classnames = array();
    protected $_select;

    // string: false if any errors found during construct allows graceful trap of errors during construct
    protected $_valid;

    // array: array of DataField Objects, representing the fields of the table
    protected $_fields;

    // array: an array used to insert data from a collection to prevent multiple queries
    public $_data;

    private $ser_fields = '';

    // array: an array of DataField Objects representing the ones to be displayed on an overview
    private $_displayFields = null;

    // boolean: has the object has been updated since it was loaded
    protected $_modified;

    // boolean: has the object was loaded from the database
    protected $_loaded;

    // array: field-names that shouldn't be settable from anywhere else (e.g. 'created', 'modified' etc.)
    protected $_protected = array();

    // array: name->object pairs representing foreign-key relationships
    public $_lookups = array();

    protected $_validators = array();

    // string: the name of the table's primary key
    public $idField = 'id';

    public $fkField = '';

    // the pointer for the iterator
    protected $_pointer = 0;

    // string: The name of the view to be used when displaying an overview. Accessed by getViewName
    private $_viewname;

    // string: the name of the field used as the identifierField and therefore used to sort fields
    // this will probably disappear depending on usage of views
    public $identifierField = 'name';

    // Where the identifierField is an array of fields this is the string used when concatenating the fields
    public $identifierFieldJoin = ' - ';

    // These fields will be removed on insert. The DB will fill them using a predefined sequence.
    protected $sequenceFields = array();

    // which field should be used to order overviews
    public $orderby;

    public $orderdir;

    protected $_autohandlers = array();

    public $subClass = FALSE;

    protected $parent_field;

    protected $parent_relname;

    protected $acts_as_tree = FALSE;

    // enumeration fields and their options
    protected $enums = array();

    protected $enumCheck = array();

    public $audit_fields = array(
        'created',
        'createdby',
        'lastupdated',
        'alteredby',
        'usercompanyid'
    );

    protected $force_change = array(
        'lastupdated',
        'alteredby'
    );

    protected $notEditable = array();
 // non-editable fields
    protected $isUnique = array();
    public $belongsTo = array();
    public $belongsToField = array();
    protected $hasOne = array();
    protected $hasMany = array();
    protected $hasManyThrough = array();
    public $composites = array();
    protected $compositesField = array();
    public $aliases = array();
    protected $isCached = array();
    public $concatenations = array();
    protected $hashes = array();
    protected $habtm = array();

    // fields (if true) that cannot be deleted
    protected $indestructable = array();

    // fields to be hidden
    protected $hidden = array();
    protected $defaultDisplayFields = array();
    public $defaultInputFields = array();

    // fields whose defaults cannot be overridden
    protected $defaultsNotAllowed = array();

    // search-handlers array, to be used when a hasMany relationship is loaded
    protected $searchHandlers = array();

    // model limited by usernameaccess fields?
    protected $_accessControlled = FALSE;

    protected $_accessContraint;
    protected $_accessFields = array();
    public $_policyConstraint;

    /*
     * Link Rules are used to override default related view links which are generated from hasMany dependencies
     * and where the controller has called sidebarRelatedItems()
     *
     * The format is:-
     * array('<hasmany name>' = array('newtab'=>array('new'=>true)
     * ,'modules'=>array('new'=>array('module'=>'<module_name>'))
     * ,'actions'=>array('link','new')
     * ,'rules'=>array(array('field'=>'<field_name>', 'criteria'=>"<condition>"))
     * ,'label'=>'sidebar description (automatically prefixed with Show)'
     * )
     * 'newtab' used by rules.js to ensure link behaves as a normal link (not ajax content)
     * 'modules' overrides the default, and the default module is the module of the currently viewed data
     * 'action', allowed options are 'link' and 'new' and are the defaults; e.g. to suppress 'new' link secifiy 'actions'=>array('link')
     * 'rules', if specified, define the condition(s) when this related view link will appear and relates to the currently viewed data
     * 'label', overrides the default sidebar text which is 'Show <hasmany name>'
     *
     * If no link rules are allowed for a hasMany dependency, then an entry of the following form is required:-
     * array('<hasmany name>' = array('actions'=>array()
     * ,'rules'=>array()
     * )
     *
     * If the controller calls sidebarRelatedItems() to enable related view links, and no link rules are defined,
     * all hasMany dependencies will appear with default options (i.e. view and new) pointing to the controller
     * associated with the hasMany model under the current module.
     *
     */
    protected $linkRules = array();

    public $clickInfoData = false;

    /**
     * Constructor
     *
     * Takes a table name and puts together an Object representing a row in the table
     *
     * @param $tablename string
     *            The name of a table in the database
     */
    public function __construct($tablename)
    {
        $db = &DB::Instance();

        if (empty($this->_tablenames)) {
            $this->_tablenames = array(
                $tablename
            );
        }

        $this->_tablename = $tablename;

        foreach ($this->_tablenames as $tablename) {
            $this->_valid = $this->setFields($tablename);
        }

        $this->validateIdentifierField();

        if ($this->isField($this->idField)) {
            $this->_protected[] = $this->idField;
        }

        if ($this->isField('owner')) {
            $this->belongsTo('User', 'owner', 'owned_by');
        }

        // $this->setTags();
        $this->setDefaultHidden();
        $this->setDefaultValidators();
        // $this->setAutoHandlers();
        // $this->setDefaultRelationships();
        $this->setDefaultFieldValues();
        $this->_classname = DataObject::className(get_class($this));

        if (empty($this->_classnames)) {
            $this->_classnames = array(
                $this->_classname
            );
        }

        $this->getDefaultOrderby();
        $this->loadModelConfig(FILE_ROOT . 'conf/model-config.yml');
        $this->setClickInfo();

        $this->setPolicyConstraint(get_class($this));

        return $this->_valid;
    }

    /**
     * Load custom model configuration from a yaml file
     *
     * @param string $yaml_file
     *            File name to load
     */
    private function loadModelConfig($yaml_file = null)
    {
        if (is_null($yaml_file)) {
            return;
        }

        $cache = Cache::Instance();
        $cache_id = 'model-config';
        $model_config = $cache->get($cache_id);
        $flash = Flash::Instance();

        try {
            // if the cache key is empty, load it from the file
            if ($model_config === false && file_exists($yaml_file)) {
                $model_config = Yaml::parse(file_get_contents($yaml_file));
                $cache->add($cache_id, $model_config);
            }
        } catch (ParseException $e) {
            $flash->addError('Unable to use model settings from ' . $yaml_file . ': ' . $e->getMessage());
        }
    }

    /**
     * Get part of the custom model configuration by keyword
     *
     * @param string $key
     *            Keyword index of configuration part
     */
    private function getModelConfig($key)
    {
        $cache = Cache::Instance();
        $cache_id = 'model-config';
        $model = get_class($this);
        $model_config = $cache->get($cache_id);

        if (isset($model_config[$model][$key])) {
            return $model_config[$model][$key];
        }
    }

    /**
     * Set configuration for ClickInfo
     *
     * Define fields and method results to be displayed
     *
     * @see Controller::clickInfo(
     */
    private function setClickInfo()
    {
        $click_info = $this->getModelConfig('ClickInfo');

        if (isset($click_info)) {
            $model_fields = array_keys($this->getFields());
            $click_info_data = [];
            foreach ($click_info['fields'] as $field => $label) {
                if (in_array($field, $model_fields)) {
                    $click_info_data['fields'][$field] = $label;
                }
            }
            foreach ($click_info['methods'] as $method => $label) {
                if (method_exists($this, $method)) {
                    $click_info_data['methods'][$method] = $label;
                }
            }
            $this->clickInfoData = $click_info_data;
        }
    }

    /**
     * Set custom model ordering
     *
     * @throws Exception
     */
    private function setCustomModelOrder()
    {
        $model = get_class($this);
        $custom_order = $this->getModelConfig('ModelOrder');

        if (isset($custom_order)) {
            $sort_fields = [];
            $sort_directions = [];
            foreach ($custom_order as $field => $order_direction) {
                $sort_fields[] = $field;
                $sort_directions[] = $order_direction;
            }

            $this->getDisplayFields();
            if (count(array_diff($sort_fields, array_keys($this->_fields))) == 0) {
                $this->orderby = $sort_fields;
                $this->orderdir = $sort_directions;
                $this->orderoverride = TRUE;
            } else {
                $fields = implode(', ', array_diff($sort_fields, array_keys($this->_fields)));
                throw new Exception("field(s) '${fields}' not found in ${model} model display fields");
            }
        }
    }

    public function clear($var)
    {
        if (isset($this->isCached[$var])) {
            unset($this->isCached[$var]);
        }
    }

    private function debug($msg)
    {
        if (get_class($this) != 'Debug' && get_class($this) != 'Debuglines') {
            debug($msg);
        }
    }

    public function isValid()
    {
        return $this->_valid;
    }

    private function setDefaultRelationships()
    {
        if ($this->isField('parent_id')) {
            $this->setParent();
        }

        foreach ($this->_fields as $field) {

            if ($field->name !== 'parent_id' && substr($field->name, - 3) == '_id') {

                // we have a possible 'belongsTo', so see if there's a model available
                $classname = ucfirst(str_replace('_id', '', $field->name));

                if (class_exists($classname)) {
                    $this->belongsTo($classname, $field->name, strtolower($classname));
                }
            }

            if ($field->name == 'owner') {
                $this->belongsTo('User', 'owner', 'owner');
            }
        }
    }

    public function actsAsTree($fieldname = 'parent_id')
    {
        $this->acts_as_tree = TRUE;
        $this->parent_field = $fieldname;
    }

    public function hasParentRelationship($fieldname)
    {
        return (! empty($this->parent_field) && $this->parent_field == $fieldname);
    }

    public function getParent()
    {
        if (! empty($this->parent_field) && $this->{$this->parent_field} !== null) {
            return $this->{$this->parent_field};
        } else {
            return FALSE;
        }
    }

    protected function setParent($fieldname = 'parent_id', $relname = 'parent')
    {
        $this->parent_field = $fieldname;
        $this->parent_relname = $relname;
    }

    /**
     * Certain fields have ways of being rescued from being null
     */
    private function setAutoHandlers()
    {
        $this->_autohandlers['id'] = new IDGenHandler();
        $this->_autohandlers['usercompanyid'] = new UserCompanyHandler();
        $this->_autohandlers['created'] = new CurrentTimeHandler();
        $this->_autohandlers['createdby'] = new CurrentUserHandler(TRUE, 'EGS_USERNAME');
        $this->_autohandlers['lastupdated'] = new CurrentTimeHandler(TRUE);
        $this->_autohandlers['alteredby'] = new CurrentUserHandler(TRUE, 'EGS_USERNAME');
        $this->_autohandlers['glperiods'] = new CurrentPeriodHandler();
        $this->_autohandlers['owner'] = new CurrentUserHandler(FALSE, 'EGS_USERNAME');
        // $this->_autohandlers['assigned'] = new CurrentUserHandler(FALSE, EGS_USERNAME);
        $this->_autohandlers['lang'] = new DefaultLanguageHandler();
        $this->_autohandlers['password'] = new PasswordGenerationHandler();
        $this->_autohandlers['job_no'] = new JobNumberHandler();
        $this->_autohandlers['accountnumber'] = new AccountNumberHandler();
        $this->_autohandlers['revision'] = new RevisionHandler();
        $this->_autohandlers['position'] = new PositionHandler();
        $this->_autohandlers['index'] = new PositionHandler('index');
    }

    function assignAutoHandler($fieldname, AutoHandler $handler)
    {
        $this->_autohandlers[$fieldname] = $handler;
    }

    function setFields($tablename)
    {
        $fields = system::getFields($tablename);

        if ($fields === FALSE || ! is_array($fields) || empty($tablename)) {
            $this->debug('DataObject(' . get_class($this) . ')::setFields Failed to load fields, perhaps invalid table name specified in DataObject: ' . get_class($this) . ' Table Name: ' . $this->_tablename);
            return FALSE;
        }

        foreach ($fields as $key => $field) {

            if (! isset($this->_fields[$key])) {

                $this->_fields[$key] = new DataField($field);

                if ($this->_fields[$key]->has_default) {
                    if ($field->default_value == 'now()') {
                        $this->_fields[$key]->system_default_value = 'Current Date/Time';
                    } else {
                        $this->_fields[$key]->system_default_value = $this->_fields[$key]->default_value;
                    }
                }

                if (in_array($key, $this->defaultsNotAllowed)) {
                    $this->_fields[$key]->user_defaults_allowed = FALSE;
                } else {
                    $this->_fields[$key]->user_defaults_allowed = TRUE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Set Display Fields
     *
     * Sets the fields this data object should display
     */
    function setDisplayFields()
    {
        if (count($this->defaultDisplayFields) > 0) {

            foreach ($this->defaultDisplayFields as $fieldname => $tag) {

                if (is_string($fieldname)) {

                    $field = $this->getField($fieldname);

                    if ($field === FALSE) {
                        $field = new DataField($fieldname);
                    }

                    $this->_displayFields[$fieldname] = $field;
                    $this->_displayFields[$fieldname]->tag = $tag;
                    $this->_displayFields[$fieldname]->name = $fieldname;
                    $this->_fields[$fieldname] = $this->_displayFields[$fieldname];
                } else {

                    $field = $this->getField($tag);

                    if ($field === FALSE) {
                        $field = new DataField($tag);
                    }

                    $this->_displayFields[$tag] = $field;
                    $this->_displayFields[$tag]->tag = prettify($tag);
                    $this->_displayFields[$tag]->name = $tag;
                    $this->_fields[$tag] = $this->_displayFields[$tag];
                }
            }

            return TRUE;
        } else {

            if (empty($this->_fields)) {
                debug('DataObject(' . get_class($this) . ')::setDisplayFields No Fields Defined');
            }

            foreach ($this->_fields as $field) {

                if ($field instanceof DataField && ! $this->isHidden($field->name)) {
                    $this->_displayFields[$field->name] = $field;
                }
            }

            if (count($this->_displayFields) > 6) {
                $this->_displayFields = array_slice($this->_displayFields, 0, 6);
            }
        }
    }

    function setAdditional($fieldname, $type = null, $tag = null)
    {
        $t = new ADOFieldObject();
        $t->type = (isset($type) ? $type : 'varchar');
        $t->tag = (isset($tag) ? $tag : prettify($fieldname));
        $t->name = $fieldname;
        $t->ignoreField = TRUE;

        if (in_array($fieldname, $this->defaultsNotAllowed)) {
            $t->user_defaults_allowed = FALSE;
        } else {
            $t->user_defaults_allowed = TRUE;
        }

        $this->_fields[$fieldname] = new Datafield($t);
    }

    /**
     * Might this be needed?
     *
     * @return DataField[] An array of DataField Objects
     */
    public function setDefaultDisplayFields($fields)
    {
        $this->defaultDisplayFields = $fields;
    }

    public function getDisplayFields()
    {
        if (! isset($this->_displayFields)) {
            $this->setDisplayFields();
        }

        return $this->_displayFields;
    }

    public function getInputFields()
    {
        $fields = array();

        foreach ($this->defaultInputFields as $field) {
            $fields[$field] = $field;
        }

        return $fields;
    }

    public function getDisplayFieldNames()
    {
        $return = array();

        foreach ($this->getDisplayFields() as $field) {
            $return[$field->name] = $field->tag;
        }

        return $return;
    }

    /**
     * Might this be needed?
     *
     * @param string $field
     *            The name of the field to be checked
     * @return boolean true if is displayed, false if not displayed
     */
    public function isDisplayedField($fieldname)
    {
        return (isset($this->_displayFields[$fieldname]));
    }

    /**
     * Set any validators that should be based on the Object as a whole
     *
     * @todo Add some, I'm sure there must be things?
     */
    function setDefaultValidators()
    {}

    public function update($id, $fields, $values)
    {
        if (! $this->_valid) {
            return FALSE;
        }

        $db = &DB::Instance();

        if (! is_array($fields)) {
            $fields = array(
                $fields
            );
        }

        if (! is_array($values)) {
            $values = array(
                $values
            );
        }

        if ($this->isField('lastupdated') && ! in_array('lastupdated', $fields)) {
            $fields[] = 'lastupdated';
            $values[] = 'now()';
        }

        if ($this->isField('alteredby') && ! in_array('alteredby', $fields)) {
            $fields[] = 'alteredby';
            $values[] = EGS_USERNAME;
        }

        if (count($fields) != count($values)) {
            return FALSE;
        }

        if ($this->isAccessAllowed($id, 'write') || isModuleAdmin()) {

            $field_value = array();

            foreach ($values as $index => $value) {

                if (strtolower($value) !== 'null' && substr($value, 0, 1) != '(') {
                    $field_value[$index] = $fields[$index] . ' = ' . $db->qstr($value);
                } else {
                    $field_value[$index] = $fields[$index] . ' = ' . $value;
                }
            }

            $query = "update {$this->_tablename} set " . implode(',', $field_value) . " where {$this->idField} = {$db->qstr($id)}";
            // echo 'DataObject('.get_class($this).')::update query:'.$query.'<br>';
        }

        return ($db->Execute($query) !== FALSE);
    }

    public function addPolicyConstraint($cc = '')
    {
        if (! SYSTEM_POLICIES_ENABLED) {
            return;
        }

        if ($cc instanceof ConstraintChain && $cc->count() > 0) {

            if (! ($this->_policyConstraint['constraint'] instanceof ConstraintChain)) {
                $this->_policyConstraint['constraint'] = new ConstraintChain();
            }

            $this->_policyConstraint['constraint']->add($cc, 'OR');
        }
    }

    /**
     * Load a record from the database and assign appropriate values to the Object
     * -Doesn't load foreign-table properties until they are requested
     */
    public function load($clause, $override = FALSE, $return = FALSE)
    {
        if (! $this->_valid) {
            $this->debug('DataObject(' . get_class($this) . ')::load model ' . get_class($this) . ' is not valid');
            return FALSE;
        }

        if (isset($this->_data)) {
            $row = $this->_data;
            // $this->setView();
        } else {

            $db = &DB::Instance();
            $select = $this->_select;

            if (empty($select)) {
                $select = '*';
            }

            if (empty($clause)) {
                return FALSE;
            }

            $query = 'SELECT ' . $select . ' FROM ' . $this->_tablename . ' WHERE ' . $this->idField . '=' . $db->qstr($clause);

            if (! $override && $this->isField('usercompanyid') && defined('EGS_COMPANY_ID') && EGS_COMPANY_ID != 'null') {
                $query .= ' AND usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
            }

            if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {
                $query .= ' AND ' . $this->getAccessConstraint('read')->__toString();
            }

            if ($this->_policyConstraint['constraint'] instanceof ConstraintChain && $this->_policyConstraint['constraint']->count() > 0) {
                $query .= ' AND ' . $this->_policyConstraint['constraint']->__toString();
            }

            if ($return) {
                return $query;
            }

            $this->debug('DataObject(' . get_class($this) . ')::load : ' . $query);
            // echo 'DataObject(' . get_class($this) . ')::load : ' . $query.'<br>';

            $row = $db->GetRow($query);

            if ($row === FALSE) {
                // echo 'DataObject(' . get_class($this) . ')::load Query failed: ' . $db->ErrorMsg().'<br>';
                $this->debug('DataObject(' . get_class($this) . ')::load Query failed: ' . $db->ErrorMsg());
                return FALSE;
            }
        }

        if (! is_array($row)) {
            return FALSE;
        }

        foreach ($row as $key => $val) {

            $this->$key = stripslashes($val);

            if (! isset($this->_fields[$key])) {
                $this->_fields[$key] = stripslashes($val);
            }
        }

        foreach ($this->hashes as $fieldname => $array) {
            $this->hashes[$fieldname] = unserialize(base64_decode($this->$fieldname));
        }

        if (count($row) > 0) {

            $this->_loaded = TRUE;
            $this->_data = $row;

            // $this->setDisplayFields();

            $this->cb_loaded(TRUE);

            return $this;
        }

        return FALSE;
    }

    /**
     * returns the identifier value, loading the model if needed
     *
     * @param
     *            $id
     */
    public function load_identifier_value($id = NULL)
    {
        if (! $this->loaded) {

            if (! $this->load($id)) {
                return FALSE;
            }
        }

        return $this->_data[$this->identifierField];
    }

    /**
     * Archive (copy) current record to another table
     * after DO has been loaded
     */
    public function archive($id = null, &$errors = array(), $archive_table = null, $archive_schema = null)
    {
        if (! $this->_valid) {
            return FALSE;
        }

        if ($id == null && $this->_loaded) {
            $id = $this->{$this->idField};
        }

        if ($id !== null && ($this->isAccessAllowed($id, 'write') || isModuleAdmin())) {

            $db = &DB::Instance();
            $db->StartTrans();

            if (empty($archive_schema)) {
                $archive_schema = 'archive.';
            } elseif ($archive_schema == 'current') {
                $archive_schema = '';
            }

            if (empty($archive_table)) {
                $archive_table = 'archive_' . $this->_tablename;
            }

            $a_query = 'INSERT INTO ' . $archive_schema . $archive_table . ' SELECT * FROM ' . $this->_tablename . ' WHERE ' . $this->idField . '=' . $id;

            $this->debug("DataObject(" . get_class($this) . ")::delete " . $a_query);
            // echo "DataObject(" . get_class($this) . ")::delete " . $a_query.'<br>';

            $result = $db->Execute($a_query);

            if ($result === FALSE) {
                $errors[] = 'Failed to archive record';
                $db->FailTrans();
            }

            $db->CompleteTrans();

            return ($result !== FALSE);
        } else {
            return FALSE;
        }
    }

    /**
     * Delete a record from the database
     * after DO has been loaded
     */
    public function delete($id = null, &$errors = array(), $archive = FALSE, $archive_table = null, $archive_schema = null)
    {
        if (! $this->_valid) {
            return FALSE;
        }

        if ($id == null && $this->_loaded) {
            $id = $this->{$this->idField};
        }

        if ($id !== null && ($this->isAccessAllowed($id, 'write') || isModuleAdmin())) {

            $delete = TRUE;

            foreach ($this->indestructable as $field => $value) {

                if ($this->{$field} == $value) {
                    $delete = FALSE;
                }
            }

            $db = &DB::Instance();
            $db->StartTrans();

            if (count($this->hasMany) > 0) {

                if (! $this->_loaded) {
                    $this->load($id);
                }

                if (! $this->cascadeDelete($errors)) {
                    $delete = FALSE;
                }
            }

            if (! $delete) {
                $db->FailTrans();
                $db->CompleteTrans();
                return $delete;
            }

            if ($archive) {
                $result = $this->archive($id, $errors, $archive_table, $archive_schema);
            } else {
                $result = TRUE;
            }

            if ($result) {

                $query = 'DELETE FROM ' . $this->_tablename . ' WHERE ' . $this->idField . '=' . $id;

                $this->debug("DataObject(" . get_class($this) . ")::delete " . $query);
                // echo "DataObject(" . get_class($this) . ")::delete " . $query.'<br>';

                $result = $db->Execute($query);
            }

            if ($result === FALSE) {
                $errors[] = $db->ErrorMsg();
                $db->FailTrans();
            }

            $db->CompleteTrans();

            return ($result !== FALSE);
        } else {
            return FALSE;
        }
    }

    function cascadeDelete(&$errors = array())
    {
        foreach ($this->hasMany as $name => $fk) {

            if (is_null($fk['cascade'])) {
                // No action - let the database FK constraint actions apply
                continue;
            }

            $do = DataObjectFactory::Factory($fk['do']);
            $db = &DB::Instance();

            $join = ' WHERE ';

            if (is_array($fk['fkfield'])) {
                $criteria = '';

                foreach ($fk['fkfield'] as $index => $fkfield) {
                    $criteria .= $join . $fkfield . '=' . $this->{$fk['field'][$index]};
                    $join = ' AND ';
                }
            } else {
                $criteria = $join . $fk['fkfield'] . '=' . $this->{$fk['field']};
            }

            $expected_count = $do->getCount($criteria);

            if ($expected_count === FALSE) {
                $errors[] = $db->errorMsg();
                return FALSE;
            }

            if ($fk['cascade'] === FALSE) {
                // Cascade delete not allowed
                if ($expected_count > 0) {
                    // FK rows exist so exit with error
                    $errors[] = 'Delete failed - linked to ' . $count . ' ' . $name;
                    return FALSE;
                }
                // No FK rows so check next constraint
                continue;
            } else {
                // Cascade delete allowed
                $query = 'DELETE FROM ' . $do->_tablename . $criteria;

                $this->debug("DataObject(" . get_class($this) . ")::cascadeDelete " . $query);
                // echo "DataObject(".get_class($this).")::cascadeDelete ".$query.'<br>';

                $db->StartTrans();

                $result = $db->Execute($query);

                $deleted_count = $db->Affected_Rows();

                if ($deleted_count === FALSE) {
                    // $db->Affected_Rows() not supported by this database
                    $deleted_count = $expected_count;
                }

                if ($result === FALSE || $deleted_count != $expected_count) {
                    $errors[] = 'Error deleting linked ' . $name . ': ' . $db->ErrorMsg();
                    $db->FailTrans();
                    $db->CompleteTrans();
                    return FALSE;
                }

                $db->CompleteTrans();
            }
        }

        return TRUE;
    }

    /**
     * Load a DO based on something other than it's idField
     *
     * Need to decide whether multiple DOs meeting the criteria will cause error, or pick one, or just ignore?
     */
    function loadBy($field, $value = null, $tablename = FALSE)
    {
        if (! $this->_valid) {
            return FALSE;
        }

        $db = &DB::Instance();

        if ($field instanceof SearchHandler) {

            $sh = $field;
            $sh->setLimit(1);

            $qb = new QueryBuilder($db);

            if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {
                $sh->addConstraintChain($this->getAccessConstraint('read'));
            }

            $query = $qb->select($sh->fields)
                ->from($this->_tablename)
                ->where($sh->constraints)
                ->orderby($sh->orderby, $sh->orderdir)
                ->limit($sh->perpage, $sh->offset)
                ->__toString();
        } else {

            if ($field instanceof ConstraintChain) {
                $where = $field->__toString();
            } elseif (! is_array($field) && ! is_array($value) && ! empty($value)) {
                $where = $field . '=' . $db->qstr($value);
            } elseif (! (is_array($field) && is_array($value))) {
                $this->debug('DataObject(' . get_class($this) . ')::loadBy Error: $fieldname and $value must be of same type, array or string');
                return FALSE;
            } else {

                $where = '1=1';

                for ($i = 0; $i < count($field); $i ++) {

                    if ((! $tablename) && (($this->getField($field[$i])->type == 'date') || ($this->getField($field[$i])->type == 'numeric') || (substr($this->getField($field[$i])->type, 0, 3) == 'int')) && ($value[$i] == '')) {
                        $where .= ' AND ' . $field[$i] . ' is null';
                    } else {
                        $where .= ' AND ' . $field[$i] . '=' . $db->qstr($value[$i]);
                    }
                }
            }

            if (defined('EGS_COMPANY_ID') && $this->isField('usercompanyid') && EGS_COMPANY_ID != 'null') {
                $where .= ' AND usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
            }

            if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {
                $where .= ' AND ' . $this->getAccessConstraint('read')->__toString();
            }

            if ($tablename) {
                $query = 'SELECT id FROM ' . $tablename . ' WHERE ' . $where;
            } else {
                $query = 'SELECT * FROM ' . $this->_tablename . ' WHERE ' . $where;
            }
        }

        $this->debug("DataObject(" . get_class($this) . ")::loadBy " . $query);
        // echo "DataObject(".get_class($this).")::loadBy ".$query.'<br>';

        $row = $db->GetRow($query);

        if ($row === FALSE) {
            $this->debug("DataObject(" . get_class($this) . ")::loadBy Error in loadby: " . $db->ErrorMsg() . $query);
            return FALSE;
        }

        if (count($row) > 0) {
            $this->_data = $row;
            return $this->load($row[$this->idField]);
        }

        return FALSE;
    }

    /**
     * Saves the current state of the Object to the database.
     * Assumes data has been validated, so will result in exception if update/insert fails
     * Will call save() on any loaded hasMany() relationships. (Actual DB-updates on such Objects will depend on their check for modification)
     *
     * @throws Exception
     * @return boolean true on success, false otherwise
     * @todo Use a 'modified' variable to avoid un-necessary saves
     * @todo If caching is implemented elsewhere, will probably need to be able to flush appropriate bits from here
     */
    function save($debug = FALSE)
    {
        $this->debug('DataObject(' . get_class($this) . ')::save model ' . get_class($this));

        if (! $this->_valid) {
            return FALSE;
        }

        $db = &DB::Instance();

        if ($debug) {
            $db->debug = TRUE;
        }

        $data = array();
        $myIdField = $this->{$this->idField};

        foreach ($this->getFields() as $key => $field) {

            if ($field->ignoreField) {
                continue;
            }

            $value = $field->finalvalue;

            if (in_array($key, $this->force_change)) {
                $value = $this->autoHandle($key);
            }

            // TODO: Something looks wrong here - if the field is type file
            // and value empty, then saveFile?!?

            if ($field->type == 'file' && empty($value)) {
                $this->saveFile();
                continue;
            }

            if (($field->type == 'timestamp' && trim($value) === '') || (substr($field->type, 0, 3) == 'int' && trim($value) === '') || ($field->type == 'numeric' && trim($value) === '') || ($field->type == 'varchar' && trim($value) === '' && $field->not_null !== TRUE) || ($field->type == 'date' && trim($value) === '')) {
                $data[$key] = 'NULL';
            } elseif ($field->type == 'date' && is_int($value)) {
                $data[$key] = fix_date(date(DATE_FORMAT, $value));
            } else {
                $data[$key] = $value;
            }
        }

        foreach ($this->hashes as $fieldname => $array) {
            $data[$fieldname] = base64_encode(serialize($array));
        }

        if (isset($data[$this->idField]) && $data[$this->idField] == 'NULL') {
            unset($data[$this->idField]);
        }

        // Remove fields with sequences. Values filled by the DB on insert
        foreach ($this->sequenceFields as $seq) {
            unset($data[$seq]);
        }

        // Need a method of checking whether insert is allowed
        // - assume it is since we have got to this point

        if (! isset($data[$this->idField]) || (isset($data[$this->idField]) && ($this->isAccessAllowed($data[$this->idField], 'write') || isModuleAdmin()))) {
            $ret = $db->Replace($this->_tablename, $data, $this->idField, TRUE);
        } else {
            return FALSE;
        }

        if ($debug) {
            $db->debug = FALSE;
        }

        if ($ret === 0) {
            $this->debug('DataObject(' . get_class($this) . ')::save Save of ' . get_class($this) . ' failed: ' . $db->ErrorMsg());
            return FALSE;
        } else {
            $this->_loaded = TRUE;
            return TRUE;
        }
    }

    private function saveFile()
    {
        // $db = DB::Instance();
        // $db->UpdateBlobFile('file','file','','id='.$this->{$this->idField});
    }

    public static function className($var = null)
    {
        static $name;

        if (empty($name) && ! empty($var)) {
            $name = $var;
        }

        return $name;
    }

    function getBooleanFields()
    {
        $return = array();

        foreach ($this->getFields() as $field) {

            if ($field->type == 'bool') {
                $return[] = $field;
            }
        }

        return $return;
    }

    function autoHandle($fieldname)
    {
        $this->setAutoHandlers();

        if (empty($this->_autohandlers[$fieldname])) {
            return FALSE;
        }

        return $this->_autohandlers[$fieldname]->handle($this);
    }

    /**
     * Static function that attempts to construct a dataobject based on the passed $data array
     *
     * @param $data array
     *            An array of key=>value pairs to use when constructing the DataObject
     * @param
     *            &$errors array An array passed by reference to which errors will be added
     *
     * @return mixed On success, a valid DataObject will be returned. Otherwise, false.
     *
     */
    public static function Factory($data, &$errors = array(), $do_name = null)
    {

        // first we get an instance of the desired class
        if (! ($do_name instanceof DataObject)) {
            $do = DataObjectFactory::Factory($do_name);
        } else {
            $do = $do_name;
        }

        if (! $do->isValid()) {
            return FALSE;
        }

        $do->debug('DataObject(' . get_class($do) . ')::Factory model ' . get_class($do));

        // then get the fields and then loop their validators
        $do_fields = $do->getFields();
        $mode = "NEW";

        foreach ($data as $key => $value) {

            if (! is_array($value)) {
                $data[$key] = trim($value);
            }
        }

        // if editing, assign current values to $data where fields are empty
        // if($do->idField!=$do->getIdentifier()&&!empty($data[$do->idField])) {

        if (! empty($data[$do->idField])) {

            $mode = "EDIT";
            $current = $do->load($data[$do->idField]);
            $maintain = array(
                'created',
                'createdby',
                'owner',
                'last_login'
            );

            foreach ($maintain as $fieldname) {

                if ($do->isField($fieldname) && empty($data[$fieldname])) {
                    $field = $do->getField($fieldname);
                    $field->ignoreField = TRUE;
                }
            }

            if ($current === FALSE) {

                if ($do->idField == $do->getIdentifier()) {
                    $mode = "NEW";
                } else {
                    $do->debug("DataObject(" . get_class($do) . ")::Factory Load failed while trying to edit " . get_class($do));
                    return FALSE;
                }
            } else {
                if (! $current->isLatest($data, $errors)) {
                    return FALSE;
                }
            }
        } else {
            $maintain = array(
                'created',
                'createdby'
            );

            foreach ($maintain as $fieldname) {
                // This is an insert; these fields are set automatically
                if ($do->isField($fieldname) && isset($data[$fieldname])) {
                    unset($data[$fieldname]);
                }
            }
        }

        $do->debug('DataObject(' . get_class($do) . ')::Factory mode=' . $mode);

        $db = &DB::Instance();

        foreach ($do_fields as $name => $field) {

            if ($field->ignoreField) {
                continue;
            }

            if ($field->type == 'oid') {
                $data[$name] = 0;
            }

            if ($mode == "EDIT" && ! isset($data[$name])) {

                if ($field->type != 'bool' || ! isset($data['_checkbox_exists_' . $name])) {
                    continue;
                } else {
                    $data[$name] = 'false';
                }
            }

            if ($field->type == 'numeric' && isset($data[$name]) && $data[$name] === '0') {
                $data[$name] = 0;
            }

            if (empty($data[$name]) && ! (isset($data[$name]) && $data[$name] === 0)) {

                if ($field->type == 'bool' && (! isset($data[$name]) || $data[$name] !== TRUE)) {
                    $data[$name] = 'false';
                }

                $do->debug('DataObject(' . get_class($do) . ')::Factory autohandle for ' . $name);

                $test = $do->autoHandle($name);

                if ($test !== FALSE) {
                    $data[$name] = $test;
                }
            }

            if ($mode == "EDIT" && isset($data[$name]) && $do->isNotEditable($name)) {
                unset($data[$name]);
                continue;
            }

            if (! empty($data[$name])) {

                if (isset($do->belongsToField[$name])) {
                    $fk = DataObjectFactory::Factory($do->belongsTo[$do->belongsToField[$name]]['model']);

                    // Add any Policy Constraints defined against the FK
                    $fk->addPolicyConstraint($do->belongsTo[$do->belongsToField[$name]]['cc']);

                    $fk->load($data[$name]);

                    if (! $fk->isLoaded()) {
                        $errors[$name] = 'Input value ' . $data[$name] . ' for ' . $do->belongsToField[$name] . ' does not exist in ' . $do->belongsTo[$do->belongsToField[$name]]['model'];
                    }
                } else {

                    foreach ($do->hasOne as $hasname => $hasone) {

                        if ($hasone['field'] == $name && empty($do->$hasname)) {

                            $fk = DataObjectFactory::Factory($hasone['model']);

                            // This is a fudge to ensure that any FK references are resolved
                            // i.e. remove constraints so only fails if fk truly does not exit
                            $fk->_policyConstraint = null;

                            if ($fk->idField == (empty($hasone['fkfield']) ? $fk->idField : $hasone['fkfield'])) {
                                $fk->load($data[$name]);
                            } else {
                                $fk->loadBy($hasone['fkfield'], $data[$name]);
                            }

                            if (! $fk->isLoaded()) {
                                $errors[$name] = get_class($do) . ' Input value ' . $data[$name] . ' for ' . $hasname . ' does not exist in ' . $hasone['model'];
                            }
                        }
                    }
                }
            }

            if (isset($data[$name])) {
                $do->debug('DataObject(' . get_class($do) . ')::Factory test field ' . $name);
                $do->$name = $field->test($data[$name], $errors);
            }

            if (! isset($data[$name]) && $field->has_default == 1) {
                $do->debug('DataObject(' . get_class($do) . ')::Factory Get Default for ' . $name);
                $do->$name = $field->default_value;
                $do->debug('DataObject(' . get_class($do) . ')::Factory default value ' . $do->$name);
            }
        }

        $do->debug('DataObject(' . get_class($do) . ')::Factory Call do->test');
        $do->test($errors);

        // then test the model as a whole
        if (count($errors) == 0) {
            return $do;
        }

        return FALSE;
    }

    protected function addValidator(ModelValidation $validator)
    {
        $this->_validators[] = $validator;
    }

    public function getValidators()
    {
        return $this->_validators;
    }

    /**
     * Add a validation rule that the specified fieldname must be unique
     *
     * @param $fieldname string
     *            the name of a DataObject field
     */
    protected function validateUniquenessOf($fields, $message = NULL, $ignore_nulls = FALSE)
    {
        $this->isUnique[] = $fields;

        if (! is_array($fields)) {
            $fields = array(
                $fields
            );
        }

        foreach ($fields as $fieldname) {

            if (! $this->isField($fieldname)) {
                $this->debug('DataObject(' . get_class($this) . ')::validateUniquenessOf Invalid fieldname (' . $fieldname . ') provided to validateUniquenessOf() in DataObject.php');
                return FALSE;
            }
        }

        $this->addValidator(new UniquenessValidator($fields, $message, $ignore_nulls));

        return TRUE;
    }

    public function checkUniqueness($fields)
    {
        if (! is_array($fields)) {
            return in_array($fields, $this->isUnique);
        }
    }

    protected function validateEqualityOf($fieldname, $fieldname2)
    {
        $fieldnames = array();
        $args = func_get_args();

        foreach ($args as $fieldname) {
            $fieldnames[] = $fieldname;
        }

        if (count($fieldnames) < 2) {
            $this->debug('DataObject(' . get_class($this) . ')::validateEqualityOf Need at least 2 fieldnames to test for equality!');
            return FALSE;
        }

        $this->addValidator(new EqualityValidator($fieldnames));
    }

    protected function test(Array &$errors)
    {
        foreach ($this->getValidators() as $validator) {
            $validator->test($this, $errors);
        }
    }

    public function isLoaded()
    {
        return $this->_loaded;
    }

    public function fieldTest(&$errors)
    {
        $myIdField = $this->{$this->idField};
        $fields = $this->getFields();

        foreach ($fields as $field) {

            if ($field->_name == $myIdField) {
                continue;
            }

            $field = $field->test($errors);
        }

        if ($errors > 0) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Returns the name of the table used by the DataObject
     *
     * @return string the name of the table
     */
    public function getTableName()
    {
        return $this->_tablename;
    }

    /**
     * Allows for the setting of db-field values
     *
     * @param $key string
     *            the name of the fields
     * @param $val mixed
     *            the value to be assigned
     *
     * @todo should this check Validators? Booleans will need to be coerced for example...
     */
    public function __set($key, $val)
    {
        if ($this->isField($key)) {

            if (is_object($this->_fields[$key])) {
                $this->_fields[$key]->value = $val;
            } else {
                $this->_fields[$key] = $val;
            }
        } else {

            if (isset($this->belongsTo[$key])) {
                $this->belongsTo[$key] = $val;
            }
        }
    }

    protected function isProtected($var)
    {
        return (in_array($var, $this->_protected));
    }

    /**
     * Might this be needed?
     *
     * @return DataField[] An array of DataField Objects
     */
    public function getFields()
    {
        $return = array();

        if (! empty($this->_fields)) {

            $idfield = array();

            foreach ($this->_fields as $fieldname => $field) {

                if ($fieldname != $this->idField) {
                    $return[$fieldname] = $this->getField($fieldname);
                } else {
                    $idfield[$fieldname] = $this->getField($fieldname);
                }
            }

            ksort($return);

            $return = $idfield + $return;
        }

        return $return;
    }

    /**
     * Return the DataField Object representing the named field
     *
     * @param
     *            string the name of a db field
     * @return ADOFieldObject
     */
    public function getField($field)
    {
        if (! $this->_valid) {
            return new DataField('default');
        }

        $field = strtolower($field);

        if (isset($this->_fields[$field])) {
            return $this->_fields[$field];
        }

        if (($field == $this->getIdentifier()) && (strpos($field, '||'))) {
            $ob = new DataField($field, $this->getIdentifierValue());
            return $ob;
        }

        if (isset($this->concatenations[$field])) {

            $concat_field = new DataField($field);
            $concat_field->type = 'varchar';

            foreach ($this->concatenations[$field]['fields'] as $fieldname) {
                $concat_field->value .= $this->$fieldname . $this->concatenations[$field]['separator'];
            }

            $concat_field->ignoreField = TRUE;
            $this->_fields[$field] = new DataField($concat_field);
        }

        if (isset($this->belongsTo[$field])) {
            $this->_fields[$field]->ignoreField = TRUE;
        }

        if (isset($this->aliases[$field])) {

            $alias = $this->aliases[$field];
            $model = DataObjectFactory::Factory($alias['modelName']);

            // $constraints=$alias['constraints'];
            // $constraints->add(new Constraint(get_class($this).'_id','=',$this->{$this->idField}));
            // $model->loadBy($constraints);

            $alias_field = clone $model->getField($alias['requiredField']);

            // $alias_field->value = $this->$field;

            return $alias_field;
        }

        return FALSE;
    }

    /**
     * Checks if the given value is the name of one of the DB fields represented by the objects
     *
     * @param $var string
     *            the name to be tested
     * @return boolean
     */
    public function isField($var, $depth = 1)
    {
        return (! is_object($var) && isset($this->_fields[strtolower($var)]));
    }

    public function getOptions($_field, $depth = 5)
    {
        if ($this->isField($_field, 0)) {

            if (! is_null($this->_fields[$_field]->options)) {
                $options = $this->_fields[$_field]->options;
            } else {
                $options = new FieldOptions();
            }

            // echo 'DataObject::getOptions '.get_class($this).'->'.$_field.'<pre>'.print_r($options, TRUE).'</pre><br>';

            $cc = new ConstraintChain();

            if (! is_null($options->_depends)) {

                foreach ($options->_depends as $depends_field => $value) {
                    $cc->add(new Constraint($depends_field, '=', $value));
                }
            }

            if (isset($this->belongsToField[$_field])) {

                $bt = $this->belongsTo[$this->belongsToField[$_field]];

                if ($bt["cc"] instanceof ConstraintChain) {
                    $cc->add($bt["cc"]);
                }

                $this->belongsTo[$this->belongsToField[$_field]]['cc'] = $cc;
                $model = DataObjectFactory::Factory($bt['model']);
            } else {
                $model = $this;
            }

            $autocomplete_limit = get_config('AUTOCOMPLETE_SELECT_LIMIT');
            $count = $this->getOptionsCount($_field);
            $autocomplete = ($count > $autocomplete_limit);
            $options->_autocomplete = $autocomplete;
            $options->_count = $count;

            $limit = (($autocomplete) ? $autocomplete_limit : '');

            if (! is_null($options->_identifierfield)) {
                $model->identifierField = $options->_identifierfield;
            } elseif (! is_null($bt["identifierField"])) {
                $model->identifierField = $bt["identifierField"];
            }

            if (is_array($model->identifierField)) {
                $model->identifierField = implode("||'" . $model->identifierFieldJoin . "'||", $model->identifierField);
            }

            if ($options->_autocomplete && ! empty($options->_autocomplete_value)) {
                $cc->add(new Constraint('lower(' . $model->identifierField . ')', 'like', strtolower($options->_autocomplete_value) . '%'));
            }

            // echo 'DataObject::getOptions '.$_field.' cc='.$cc->__toString().'<br>';
            // echo 'DataObject::getOptions '.$_field.'<pre>'.print_r($options, TRUE).'</pre><br>';
            // echo 'DataObject::getOptions '.$_field.'<pre>'.print_r($this->_fields[$field], TRUE).'</pre><br>';

            if ($this->_fields[$_field]->not_null == '') {
                $options->_data = array(
                    '' => 'None'
                );
                $options->_nonone = TRUE;
            }

            $options->_data += $model->getAll($cc, TRUE, $options->_use_collection, $limit);

            // echo 'DataObject::getOptions '.$_field.'<pre>'.print_r($options, TRUE).'</pre><br>';

            $this->_fields[$_field]->options = $options;

            return $this->_fields[$_field]->options;
        }

        if ($depth > 0) {

            foreach ($this->composites as $name => $array) {

                $model = DataObjectFactory::Factory($array['modelName']);
                $options = $model->getOptions($_field, $depth - 1);

                if ($options !== FALSE) {
                    return $options;
                }
            }

            foreach ($this->aliases as $alias => $array) {

                $model = DataObjectFactory::Factory($array['modelName']);
                $options = $model->getOptions($_field, $depth - 1);

                if ($options !== FALSE) {
                    return $options;
                }
            }
        }

        return FALSE;
    }

    public function getOptionsCount($field)
    {
        if ($this->isField($field, 0)) {

            if (isset($this->belongsToField[$field])) {

                $bt = $this->belongsTo[$this->belongsToField[$field]];
                $model = DataObjectFactory::Factory($bt['model']);

                return $model->getCount($bt['cc']);
            } else {
                return $this->getCount();
            }
        }
    }

    function setOptions($field, $options)
    {
        if ($this->isField($field) && $options instanceof fieldOptions) {
            $this->_fields[$field]->options = $options;
        }
    }

    /**
     * A function to cycle the fields and assign human-friendly tags
     *
     * @see getTag();
     */
    public function setTags()
    {
        foreach ($this->getFields() as $fieldname => $field) {
            $field->tag = $this->getTag($fieldname);
        }
    }

    /**
     * Returns a human-friendly title for the field
     * Expected to be over-ridden by sub-classes, and probably user-customisable as well?
     *
     * @param
     *            string the name of the field
     * @return string the name passed through ucwords()
     */
    public function getTag($field)
    {
        global $system;

        // return ucwords(str_replace('_id','',strtolower($field)));

        $translator = $system->injector->instantiate('Translation');

        return $translator->translate($field);
    }

    public function getEnum($name, $val)
    {
        return $this->enums[$name][$val];
    }

    function getFormatted($name, $html = TRUE)
    {
        if (isset($this->_fields[$name]->html)) {
            $this->_fields[$name]->html = $html;
        }

        if (isset($this->belongsToField[$name])) {
            return $this->{$this->belongsToField[$name]};
        } elseif (! is_null($this->_fields[$name]->formatted) && $this->_fields[$name]->formatted != '') {
            return $this->_fields[$name]->formatted;
        } elseif (! is_object($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * Allows for the getting of the values of DB-fields
     * (potentially over-ridden by child-classes as a way to modify public variables?)
     *
     * @return mixed The value of the corresponding field
     *
     * @todo need to use this method for lazy-loading of relationships
     */
    public function __get($var)
    {
        $this->debug('DataObject(' . get_class($this) . ')::__get field ' . $var);
        // echo 'DataObject('.get_class($this).')::__get field '.$var.'<br>';

        $var = strtolower($var);

        if ($this->isField($var, 1)) {
            if (is_string($this->_fields[$var])) {
                return $this->_fields[$var];
            }

            $attempt = $this->_fields[$var]->value;

            if (! empty($attempt) || $attempt === 0 || $attempt === '0') {
                return $attempt;
            }
        }

        if ($var == $this->parent_relname) {

            $p_name = get_class($this);
            $parent_model = DataObjectFactory::Factory($p_name);
            $parent_id = $this->getParent();

            $parent_model->load($parent_id);

            $parent = $parent_model->{$this->getIdentifier()};
            return $parent;
        }

        if (isset($this->isCached[$var])) {
            return $this->isCached[$var];
        }

        if (isset($this->aliases[$var])) {

            $model = DataObjectFactory::Factory($this->aliases[$var]['modelName']);
            $fkvalue = ($this->subClass ? $this->{$this->fkField} : $this->{$this->idField});

            // $fkvalue=$this->{$this->idField};

            if (! is_null($fkvalue)) {

                $cc = $this->aliases[$var]['constraints'];

                $fkfield = (empty($this->aliases[$var]['fkfield']) ? get_class($this) . '_id' : $this->aliases[$var]['fkfield']);

                if ($cc instanceof SearchHandler) {
                    $cc->addConstraint(new Constraint($fkfield, '=', $fkvalue));
                } else {
                    $cc->add(new Constraint($fkfield, '=', $fkvalue));
                }

                $model->loadby($cc);
                $this->isCached[$var] = $model;
            }

            return $model;
        }

        // if(isset($this->compositesField[$var])) {
        // $model=new $this->composites[$this->compositesField[$var]]['modelName'];
        // $model->load($this->{$this->composites[$this->compositesField[$var]]['field']});
        // return $model->$var;
        // }

        if (isset($this->composites[$var])) {

            $model = DataObjectFactory::Factory($this->composites[$var]['modelName']);

            $model->load($this->{$this->composites[$var]['field']});

            $this->isCached[$var] = $model;

            return $model;
        }

        if (isset($this->concatenations[$var])) {

            $string = '';

            foreach ($this->concatenations[$var]['fields'] as $fieldname) {
                // $string.=$this->$fieldname.' ';

                $s = $this->$fieldname;

                if (! empty($s)) {
                    $string .= $this->$fieldname . $this->concatenations[$var]['separator'];
                }
            }

            return $string;
        }

        if (isset($this->hasManyThrough[$var])) {

            $jo = $this->hasManyThrough[$var]['jo'];
            $collectionname = $jo . 'Collection';
            $collection = new $collectionname(DataObjectFactory::Factory($jo));

            if (! isset($handlers[$var])) {
                $sh = new SearchHandler($collection, FALSE);
                $sh->extract();
            } else {
                $sh = $handlers[$var];
            }

            unset($sh->fields[strtolower(get_class($this))]);
            unset($sh->fields[strtolower(get_class($this)) . '_id']);

            $sh->addConstraint(new Constraint(get_class($this) . '_id', '=', $this->{$this->idField}));
            $collection->load($sh);

            return $collection;
        }

        if (isset($this->habtm[$var])) {

            $db = DB::Instance();
            $r_model = DataObjectFactory::Factory($this->habtm[$var]['model']);

            $a = strtolower(get_class($this)) . '_id';
            $b = strtolower($this->habtm[$var]['model']) . '_id';
            $query = 'SELECT remote.* FROM ' . $this->_tablename . ' AS local JOIN ' . $this->habtm[$var]['table'] . ' AS middle ON (local.' . $this->idField . '=middle.' . $a . ') JOIN ' . $r_model->_tablename . ' AS remote ON (middle.' . $b . '=remote.' . $r_model->idField . ') WHERE local.' . $this->idField . '=' . $db->qstr($this->{$this->idField});
            $c_query = str_replace('remote.*', 'count(*)', $query);
            $c_name = $this->habtm[$var]['model'] . 'Collection';
            $collection = new $c_name($r_model);

            $collection->load($query, $c_query);
            $this->isCached[$var] = $collection;

            return $collection;
        }

        if (isset($this->hasMany[$var])) {

            $do = $this->hasMany[$var]['do'];
            $collectionname = $do . 'Collection';
            $collection = new $collectionname(DataObjectFactory::Factory($do));

            if (! $this->isLoaded()) {
                return $collection;
            }

            $handlers = $this->searchHandlers;

            if (! isset($handlers[$var])) {
                $sh = new SearchHandler($collection, FALSE);
            } else {
                $sh = $handlers[$var];
            }

            unset($sh->fields[strtolower(get_class($this))]);
            unset($sh->fields[strtolower(get_class($this)) . '_id']);

            if (is_array($this->hasMany[$var]['fkfield'])) {
                foreach ($this->hasMany[$var]['fkfield'] as $index => $fkfield) {
                    $sh->addConstraint(new Constraint($fkfield, '=', $this->{$this->hasMany[$var]['field'][$index]}));
                }
            } elseif (! is_array($this->hasMany[$var]['field'])) {
                $sh->addConstraint(new Constraint($this->hasMany[$var]['fkfield'], '=', $this->{$this->hasMany[$var]['field']}));
            }

            $collection->load($sh);

            $this->isCached[$var] = $collection;

            return $collection;
        }

        if (isset($this->hasOne[$var])) {

            $field = $this->hasOne[$var]['field'];
            $fkfield = $this->hasOne[$var]['fkfield'];

            if (isset($this->hasOne[$var]['cached'])) {

                if (is_null($fkfield)) {
                    $id = $this->hasOne[$var]['cached']->idField;
                } else {
                    $id = $fkfield;
                }

                if ($this->hasOne[$var]['cached']->$id == $this->$field) {
                    // return the cached object as it hasn't changed
                    return $this->hasOne[$var]['cached'];
                }
            }

            // no cached object or cached object has changed
            $model = DataObjectFactory::Factory($this->hasOne[$var]['model']);

            if (is_null($fkfield)) {
                $model->load($this->$field);
            } else {
                $model->loadBy($fkfield, $this->$field);
            }

            $this->hasOne[$var]['cached'] = $model;

            return $model;
        }

        if (isset($this->belongsTo[$var])) {

            $fields = $this->_fields;

            if (isset($fields[$var])) {
                $value = $this->_fields[$var]->value;
            }

            if (empty($value)) {

                $model = DataObjectFactory::Factory($this->belongsTo[$var]['model']);

                // Remove any policies to ensure the FK can be resolved to get the FK value
                $model->_policyConstraint = null;

                $model->load($this->{$this->belongsTo[$var]['field']});

                if (! empty($this->belongsTo[$var]['identifierField'])) {
                    $model->identifierField = $this->belongsTo[$var]['identifierField'];
                }

                $field = $model->getField($model->getIdentifier());

                // $this->_fields[$var] = clone $field;

                $field->tag = prettify($var);
                $value = $field->value;

                if (empty($value)) {
                    $value = $model->{$model->getIdentifier()};
                }
            }

            return $value;
        }

        if ($var == 'identifierField') {
            return $this->getIdentifier();
        }

        if ($var == $this->getIdentifier()) {
            return $this->getIdentifierValue();
        }

        if ($var == 'loaded' || $var == 'modified' || $var == 'tablename') {
            return $this->{'_' . $var};
        }

        foreach ($this->hashes as $fieldname => $objects) {

            if (isset($objects[$var])) {
                return unserialize($objects[$var]);
            }
        }
    }

    /**
     * Returns array of fields defined in $identifierField
     *
     * @return array
     *
     */
    public function getIdentifierFields()
    {
        if (! is_array($this->identifierField)) {

            if (strpos($this->identifierField, '||')) {
                $identifier_fields = explode('||', $this->identifierField);
            } elseif (strpos($this->identifierField, $this->identifierFieldJoin)) {
                $identifier_fields = explode($this->identifierFieldJoin, $this->identifierField);
            } else {
                $identifier_fields = array(
                    $this->identifierField
                );
            }

            foreach ($identifier_fields as $key => $field) {
                $identifier_fields[$key] = trim(str_replace(array(
                    ' ',
                    ',',
                    "'",
                    '"',
                    '/'
                ), '', $field));
            }
        } else {
            $identifier_fields = $this->identifierField;
        }

        foreach ($identifier_fields as $key => $field) {

            if (! $this->isField($field) && ! isset($this->concatenations[$field])) {
                unset($identifier_fields[$key]);
            }
        }

        return array_values($identifier_fields);
    }

    function getIdentifierValue()
    {
        $exploded = explode('||', $this->getIdentifier());
        $return = '';

        if ($this->isLoaded()) {
            foreach ($exploded as $var) {

                if ($this->isField(trim($var))) {
                    $return .= $this->{$this->_fields[trim($var)]->name};
                } elseif (isset($this->concatenations[$var])) {
                    $return .= $this->$var;
                } else {
                    $return .= str_replace('\'', '', $var);
                }
            }
        }

        return $return;
    }

    /**
     * Register a particular field as being a foreign key to another table
     * also used to populate drop down lists on forms
     *
     * @param string $do
     *            the name of the dataobject that should be represented
     * @param string $field
     *            the name of the foreign-key field in the do (defaults to the do-class-name suffixed with 'id')
     * @param string $name
     *            the name by which the object can later be referenced
     * @param string $field
     *            the name of the parent field in the current object (defaults to the current object id field)
     * @return boolean
     */
    function hasOne($do, $field = null, $name = null, $fkfield = null)
    {
        if (! isset($field)) {

            if ($this->isField(strtolower($do) . 'id')) {
                $field = strtolower($do) . 'id';
            } elseif ($this->isField(strtolower($do) . '_id')) {
                $field = strtolower($do) . '_id';
            } else {
                $this->ErrorMsg = 'If neither <fk>id nor <fk>_id are fields in the table, the fieldname must be specified';
                return FALSE;
            }
        }

        if (! $this->isField($field)) {
            return FALSE;
        }

        if (! isset($name)) {
            $name = strtolower($do);
        }

        if (class_exists($do)) {

            $this->hasOne[$name] = array(
                'model' => $do,
                'field' => $field,
                'name' => $name,
                'fkfield' => $fkfield

            );

            return TRUE;
        }

        return FALSE;
    }

    function getHasOne()
    {
        return $this->hasOne;
    }

    public function addSearchHandler($cname, SearchHandler $sh)
    {
        $this->searchHandlers[$cname] = $sh;
    }

    /**
     * Register a particular field as being a foreign key to another table
     * also used to populate drop down lists on forms
     *
     * @param string $do
     *            the name of the dataobject that should be represented
     * @param string $field
     *            the field-name
     * @param string $name
     *            the name by which the object can later be referenced
     * @param string $cc
     *            a constraint chain declaration to restrict drop down list
     * @param string $identifierField
     *            to be used to populate the drop down list
     * @return boolean
     */
    function belongsTo($do, $field = null, $name = null, $cc = null, $identifierField = null)
    {
        if (! isset($field)) {

            if ($this->isField(strtolower($do) . 'id')) {
                $field = strtolower($do) . 'id';
            } elseif ($this->isField(strtolower($do) . '_id')) {
                $field = strtolower($do) . '_id';
            } else {
                $this->ErrorMsg = 'If neither <fk>id nor <fk>_id are fields in the table, the fieldname must be specified';
                return FALSE;
            }
        }

        if (! $this->isField($field)) {
            return FALSE;
        }

        if (! isset($name)) {
            $name = strtolower($do);
        }

        if (class_exists($do)) {

            $this->belongsTo[$name] = array(
                'model' => $do,
                'field' => $field,
                'cc' => $cc,
                'identifierField' => $identifierField
            );

            $this->belongsToField[$field] = $name;
            $this->_fields[$name] = new DataField(new ADOFieldObject());
            $this->_fields[$name]->name = $name;
            $this->_fields[$name]->tag = prettify($name);
            $this->_fields[$name]->field = $field;
            $this->_fields[$name]->ignoreField = TRUE;

            // $this->getField($field)->addValidator(new ForeignKeyValidator($do));

            $this->setPolicyConstraint($do, $field);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Tell the class of another dataobject that is dependent on it.
     * Any registered dependents will be loaded
     * when the object is
     *
     * @param
     *            string the name of the DO that is to be registered
     * @param
     *            string the name that should be given to the collection (defaults to $name.'s')
     * @param
     *            string the name of the foreign-key field in the dependent (defaults to the current-class-name suffixed with 'id')
     * @param
     *            string the name of the parent field in the foreign-key relationship (defaults to the parent id field)
     * @param
     *            boolean specifies whether to allow cascade delete (defaults to true)
     * @return boolean
     */
    function hasMany($do, $name = null, $fkfield = null, $field = null, $cascade = NULL)
    {
        if (! isset($name)) {
            // default to adding an s
            $name = strtolower($do) . 's';
        }

        if (! isset($fkfield)) {
            // default to the name of the current class + id
            $fkfield = strtolower(get_class($this)) . '_id';
        }

        if (! isset($field) || empty($field)) {
            if (is_array($fkfield)) {
                $field = $fkfield;
            } else {
                // default to the id field of the current class
                $field = $this->idField;
            }
        }

        if ((is_array($fkfield) && is_array($field) && count($fkfield) == count($field)) || (! is_array($field) && ! is_array($fkfield))) {
            // add the DOC to the class's list (will allow for multiple dependents)
            $this->hasMany[$name] = array(
                'do' => $do,
                'fkfield' => $fkfield,
                'field' => $field,
                'cascade' => $cascade
            );

            return TRUE;
        } else {
            return FALSE;
        }
    }

    function hasManyThrough($jo, $field, $name)
    {
        $this->hasManyThrough[$name] = array(
            'jo' => $jo,
            'field' => $field
        );
    }

    function hasAndBelongsToMany($do, $j_table, $name = null)
    {
        if ($name == null) {
            $inflector = new Inflector();
            $name = $inflector->pluralize(strtolower($do));
        }

        $this->habtm[$name] = array(
            'model' => $do,
            'table' => $j_table
        );
    }

    function getHasMany($name = '')
    {
        if (empty($name)) {
            return $this->hasMany;
        }

        if (isset($this->hasMany[$name])) {
            return $this->hasMany[$name];
        } else {
            return array();
        }
    }

    function getBelongsTo()
    {
        return $this->belongsTo;
    }

    /**
     * Sets an alias to a model with extra constraints
     *
     * @param string $modelName
     *            the name of the model to inherit
     * @param string $field_name
     *            field on $this that contains FK value to reference $modelName
     * @param string $name
     *            the name for the composite
     * @param string $fields
     *            list of fields to inherit from $modelName
     *
     *            For allowing models to act as if they inherit something else
     *            e.g. a store_customer is essentially a person, with a few extra fields
     *            so:
     *
     *            $this->setComposite('Person', 'person_id', 'person_name, array('firstname', 'surname'));
     *
     *            will add firstname and surname from Person, referenced via the value in person_id,
     *            as additional fields to this model
     */
    protected function setComposite($model_name, $field_name = null, $name = null, $fields = array())
    {
        if ($field_name == null) {
            $field_name = strtolower($model_name) . '_id';
        }

        if ($name == null) {
            $name = strtolower($model_name);
        }

        $this->composites[$name]['modelName'] = $model_name;
        $this->composites[$name]['field'] = $field_name;

        if (is_array($fields) && ! empty($fields)) {
            $model = DataObjectFactory::Factory($model_name);

            foreach ($fields as $fieldname) {
                $this->_fields[$fieldname] = clone $model->getField($fieldname);

                $this->_fields[$fieldname]->ignoreField = TRUE;

                $this->compositesField[$fieldname] = $name;
            }
        }
    }

    /**
     * Sets an alias to a model with extra constraints
     *
     * @param string $alias
     *            the alias for a model
     * @param string $modelName
     *            the name of the model to alias
     * @param constraintchain $constraints
     *            a chain of constraints to be met by the model
     * @param string $requiredfield
     *            required to be present in order for aliased model to be saved
     * @param string $fkfield
     *            the field on $modelName (default is <$modelName>_id)
     */
    function setAlias($alias, $modelName, $constraints = null, $requiredField = null, $otherFields = array(), $fkfield = null)
    {
        if (is_null($constraints)) {
            $constraints = new ConstraintChain();
        }

        $this->aliases[$alias] = array(
            'modelName' => $modelName,
            'constraints' => $constraints,
            'requiredField' => $requiredField,
            'otherFields' => $otherFields,
            'fkfield' => $fkfield
        );

        $model = DataObjectFactory::Factory($modelName);

        $this->debug('DataObject(' . get_class($this) . ')::setAlias ' . $alias . ' for ' . $modelName);

        if (count($otherFields) > 0) {

            foreach ($otherFields as $fieldname) {
                $this->_fields[$fieldname] = clone $model->getField($fieldname);
                $this->_fields[$fieldname]->ignoreField = TRUE;
            }
        }

        if (! empty($requiredField)) {
            $this->_fields[$alias] = clone $model->getField($requiredField);
            $this->_fields[$alias]->ignoreField = TRUE;
        }
    }

    /**
     * Returns a modelname, constraints and required field for a given alias
     *
     * @param string $alias
     *            fieldname to check alias for
     * @return array an array of modelname, contraints and required field
     */
    function getAlias($alias)
    {
        if (isset($this->aliases[$alias])) {
            return $this->aliases[$alias];
        }

        return FALSE;
    }

    function getComposite($alias)
    {
        if (isset($this->composites[$alias])) {
            return $this->composites[$alias];
        }

        return FALSE;
    }

    function setConcatenation($name, Array $fields, $separator = ' ')
    {
        $this->concatenations[$name] = array(
            'fields' => $fields,
            'separator' => $separator
        );
    }

    function getCount($constraint = '')
    {
        $db = &DB::Instance();
        $tablename = $this->_tablename;

        if ($constraint instanceof ConstraintChain) {

            $constraint = $constraint->__toString();

            if (! empty($constraint)) {
                $constraint = ' WHERE ' . $constraint;
            }
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {

            if ($constraint == '') {
                $constraint = ' WHERE ';
            } else {
                $constraint .= ' AND ';
            }

            $constraint .= $this->getAccessConstraint('read')->__toString();
        }

        if ($this->isField('usercompanyid')) {

            if ($constraint == '') {
                $constraint = ' WHERE ';
            } else {
                $constraint .= ' AND ';
            }

            $constraint .= 'usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
        }

        $query = 'SELECT count(*) FROM ' . $tablename;

        if ($constraint != '') {
            $query .= $constraint;
        }

        // echo get_class($this).'::getCount : '.$query.'<br>';

        return $db->GetOne($query);
    }

    function getDistinct($field, $constraint = '', $tablename = '')
    {
        $db = &DB::Instance();

        // if no tablename provided, use current
        if ($tablename == '') {
            $tablename = $this->_tablename;
        }

        // handle both object and string contraints
        if ($constraint instanceof ConstraintChain) {
            $sql_constraint = ' WHERE ' . $constraint->__toString();
        } else {
            $sql_constraint = $constraint;
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= $this->getAccessConstraint('read')->__toString();
        }

        if ($this->isField('usercompanyid')) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= 'usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
        }

        $query = 'SELECT distinct ' . $field . ' FROM ' . $tablename;

        if ($sql_constraint != '') {
            $query .= $sql_constraint;
        }

        $this->debug('DataObject(' . get_class($this) . ')::getDistinct : ' . $query);
        // echo get_class($this).'::getDistinct : '.$query.'<br>';

        $rows = $db->GetAll($query);
        $array = array();

        foreach ($rows as $row) {
            $array[$row[$field]] = $row[$field];
        }

        // echo get_class($this).'::getDistinct : rows=<pre>'.print_r($array,TRUE).'</pre><br>';

        return $array;
    }

    function getSum($field, $constraint = '', $tablename = '')
    {
        $db = &DB::Instance();

        // if no tablename provided, use current
        if ($tablename == '') {
            $tablename = $this->_tablename;
        }

        // handle both object and string contraints
        if ($constraint instanceof ConstraintChain) {
            $sql_constraint = ' WHERE ' . $constraint->__toString();
        } else {
            $sql_constraint = $constraint;
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= $this->getAccessConstraint('read')->__toString();
        }

        if ($this->isField('usercompanyid')) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= 'usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
        }

        $query = 'SELECT sum(' . $field . ') FROM ' . $tablename;

        if ($sql_constraint != '') {
            $query .= $sql_constraint;
        }

        $this->debug('DataObject(' . get_class($this) . ')::getSum : ' . $query);

        // echo get_class($this).'::getSum : '.$query.'<br>';

        $sum = $db->GetOne($query);

        if ($sum == '') {
            $sum = 0;
        }

        return $sum;
    }

    function getSumFields($fields, $constraint = '', $tablename = '')
    {
        $db = &DB::Instance();

        if (! is_array($fields)) {
            $fields = array(
                $fields
            );
        }

        $sumfields = array();

        foreach ($fields as $field) {
            $sumfields[] = 'sum(' . $field . ') as ' . $field;
        }

        // if no tablename provided, use current
        if ($tablename == '') {
            $tablename = $this->_tablename;
        }

        // handle both object and string contraints
        if ($constraint instanceof ConstraintChain) {
            $sql_constraint = ' WHERE ' . $constraint->__toString();
        } else {
            $sql_constraint = $constraint;
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= $this->getAccessConstraint('read')->__toString();
        }

        if ($this->isField('usercompanyid')) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= 'usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
        }

        $query = 'SELECT count(id) as numrows, ' . implode(',', $sumfields) . ' FROM ' . $tablename;

        if ($sql_constraint != '') {
            $query .= $sql_constraint;
        }

        $this->debug('DataObject(' . get_class($this) . ')::getSum : ' . $query);

        // echo get_class($this).'::getSum : '.$query.'<br>';

        return $db->GetRow($query);
    }

    function getMax($field, $constraint = '', $tablename = '')
    {
        $db = &DB::Instance();

        // if no tablename provided, use current
        if ($tablename == '') {
            $tablename = $this->_tablename;
        }

        // handle both object and string contraints
        if ($constraint instanceof ConstraintChain) {
            $sql_constraint = ' WHERE ' . $constraint->__toString();
        } else {
            $sql_constraint = $constraint;
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= $this->getAccessConstraint('read')->__toString();
        }

        if ($this->isField('usercompanyid')) {

            if ($sql_constraint == '') {
                $sql_constraint = ' WHERE ';
            } else {
                $sql_constraint .= ' AND ';
            }

            $sql_constraint .= 'usercompanyid=' . $db->qstr(EGS_COMPANY_ID);
        }

        $query = 'SELECT max(' . $field . ') FROM ' . $tablename;

        if ($sql_constraint != '') {
            $query .= $sql_constraint;
        }

        $this->debug('DataObject(' . get_class($this) . ')::getMax : ' . $query);
        // echo get_class($this).'::getMax : '.$query.'<br>';

        return $db->GetOne($query);
    }

    function clearPolicyConstraint()
    {
        $this->_policyConstraint = null;
    }

    function setPolicyConstraint($module_component = '', $field = '')
    {
        // echo 'DataObject('.get_class($this).')::setPolicyConstraint module component '.$module_component.'<br>';
        if (! SYSTEM_POLICIES_ENABLED || empty($module_component)) {
            return;
        }

        if (isLoggedIn() && defined('EGS_USERNAME')) {

            if (! isset($this->_policyConstraint['constraint']) || ! ($this->_policyConstraint['constraint'] instanceof ConstraintChain)) {
                $this->_policyConstraint['constraint'] = new ConstraintChain();
            }

            $module_component = strtolower($module_component);

            $rows = SystemPolicyControlListCollection::getPolicies($module_component, EGS_USERNAME);

            if (! empty($rows)) {
                foreach ($rows as $value) {

                    if (empty($value['value'])) {
                        $value['value'] = 'NULL';
                    } elseif ($value['value'] == "'NULL'") {
                        $value['value'] = 'NULL';
                    }

                    if (strtolower(get_class($this)) == $module_component) {
                        // Policy is for this dataobject so just add the constraint
                        $this->_policyConstraint['constraint']->add(new Constraint($value['fieldname'], $value['operator'], $value['value']), $value['type'], ($value['allowed'] === 't' ? FALSE : TRUE));
                        $this->_policyConstraint['name'][] = ($value['allowed'] === 't' ? '' : 'not ') . $value['name'];
                        $this->_policyConstraint['field'][] = $value['fieldname'];

                        if (($value['operator'] == '=' && $value['allowed'] !== 't') || ($value['operator'] == '!=' && $value['allowed'] === 't')) {
                            // save this value to check when creating enumerated arrays
                            $this->enumCheck[$value['fieldname']][$value['value']] = '';
                        }
                    } else {
                        $fk_model = DataObjectFactory::Factory($module_component);
                        // echo 'DataObject('.get_class($this).')::setPolicyConstraint FK Model:'.$module_component.' field:'.$field.'<pre>'.print_r($value, true).'</pre><br>';

                        if (! empty($field) && $fk_model->idField == $value['fieldname']) {
                            // Policy is for foreign key primary key value
                            $c = new Constraint($field, $value['operator'], $value['value']);
                            if (! $this->_policyConstraint['constraint']->find($c)) {
                                $this->_policyConstraint['constraint']->add($c, $value['type'], ($value['allowed'] === 't' ? FALSE : TRUE));
                                $this->_policyConstraint['name'][] = ($value['allowed'] === 't') ? $value['name'] : 'not ' . $value['name'];
                                $this->_policyConstraint['field'][] = $field;
                            }
                        } else {
                            // Policy is for foreign key on non primary key field
                            // so need to add constraint as subquery; this may be inefficient in large data sets!

                            $sql = 'select ' . $fk_model->idField . ' from ' . $fk_model->getTablename() . ' where ' . $fk_model->_policyConstraint['constraint']->__toString();
                            $c = new Constraint($field, 'IN', '(' . $sql . ')');
                            if (! $this->_policyConstraint['constraint']->find($c)) {
                                $this->_policyConstraint['constraint']->add($c, $value['type']);
                                $this->_policyConstraint['name'][] = implode(',', $fk_model->_policyConstraint['name']);
                                $this->_policyConstraint['field'][] = $field;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all the ID Identifier pairs for example to fill a select.
     *
     * @todo Change name!
     */
    function getAll(ConstraintChain $cc = null, $ignore_tree = FALSE, $use_collection = FALSE, $limit = '')
    {
        $db = DB::Instance();
        $tablename = $this->_tablename;

        if ($use_collection) {
            $collection_name = get_class($this) . 'Collection';
            $coln = new $collection_name($this);
            $tablename = $coln->_tablename;
        }

        if (empty($cc)) {
            $cc = new ConstraintChain();
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {
            $cc->add($this->getAccessConstraint('read'));
        }

        $uc = new ConstraintChain();

        if ($this->isField('usercompanyid')) {
            $uc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
        }

        $uc->add($cc);

        if ($this->_policyConstraint['constraint'] instanceof ConstraintChain) {
            $uc->add($this->_policyConstraint['constraint']);
        }

        if (! $ignore_tree && $this->acts_as_tree) {
            return $this->getAllAsTree($cc, $tablename);
        }

        if (is_array($this->identifierField)) {
            $fields = implode(',', $this->identifierField);
        } else {
            $fields = $this->identifierField;
        }

        $query = 'SELECT ' . $this->idField . ', ' . $fields . ' FROM ' . $tablename;
        $constraint = $uc->__toString();

        if (! empty($constraint)) {
            $query .= ' WHERE ' . $constraint;
        }

        $query .= $this->getOrderBy();

        if (! empty($limit)) {
            $query .= ' LIMIT ' . $limit;
        }

        $this->debug('DataObject(' . get_class($this) . ')::getAll : ' . $query);
        // echo 'DataObject('.get_class($this).')::getAll : '.$query.'<br>';

        $results = $db->GetAssoc($query);

        if (is_array($this->identifierField) && count($this->identifierField) > 1) {

            foreach ($results as $key => $fields) {
                $identiferField = rtrim(implode($this->identifierFieldJoin, $fields), $this->identifierFieldJoin);
                $results[$key] = $identiferField;
            }
        }

        if ($this->idField == $this->getIdentifier()) {
            // echo 'DataObject::getAll idField='.$this->idField.' Identifier='.$this->getIdentifier().'<pre>'.print_r($results, true).'</pre><br>';
            foreach ($results as $key => $nothing) {
                $results[$key] = $key;
            }
        }

        if (empty($results)) {
            return array();
        }

        return $results;
    }

    function getQuery($fields = '', ConstraintChain $cc = null, $use_collection = FALSE)
    {
        $db = DB::Instance();
        $tablename = $this->_tablename;

        if ($use_collection) {
            $collection_name = get_class($this) . 'Collection';
            $coln = new $collection_name($this);
            $tablename = $coln->_tablename;
        }

        if (empty($cc)) {
            $cc = new ConstraintChain();
        }

        if ($this->isAccessControlled() && $this->countAccessConstraints('read') > 0) {
            $cc->add($this->getAccessConstraint('read'));
        }

        $uc = new ConstraintChain();

        if ($this->isField('usercompanyid')) {
            $uc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
        }

        $uc->add($cc);

        if (empty($fields)) {
            $fields = '1';
        }

        if (is_array($fields) && ! empty($fields)) {
            $fields = implode(',', $fields);
        }

        if (empty($fields)) {
            $fields = '1';
        }

        $query = 'SELECT ' . $fields . ' FROM ' . $tablename;
        $constraint = $uc->__toString();

        if (! empty($constraint)) {
            $query .= ' WHERE ' . $constraint;
        }

        return $query;
    }

    function getOrderBy()
    {
        $orderstring = '';

        if (! empty($this->orderby)) {

            $orderby = $this->orderby;
            $orderdir = $this->orderdir;

            if (! is_array($orderby)) {
                $orderby = array(
                    $orderby
                );
            }

            if (! is_array($orderdir)) {
                $orderdir = array(
                    $orderdir
                );
            }

            foreach ($orderby as $i => $fieldname) {

                if (! empty($fieldname)) {
                    $orderstring .= $fieldname . ' ' . (! empty($orderdir[$i]) ? $orderdir[$i] : 'ASC') . ', ';
                }
            }

            if (! empty($orderstring)) {
                $orderstring = substr($orderstring, 0, - 2);
                $orderstring = ' ORDER BY ' . $orderstring;
            }
        } else {
            $orderstring = ' ORDER BY ' . $this->getIdentifier();
        }

        return $orderstring;
    }

    private function getAllAsTree($cc = null, $tablename)
    {
        $items = array();
        $this->tree($items, $cc, $tablename);

        return $items;
    }

    private function tree(&$items = array(), $cc = null, $tablename, $parent_id = null, $spacer = '-', $indent = 0)
    {
        $db = DB::Instance();

        if ($cc instanceof ConstraintChain) {
            $where = $cc->__toString();
        } else {
            $where = '';
        }

        $query = 'SELECT ' . $this->idField . ', ' . $this->getIdentifier() . ' as identifier, ' . $this->parent_field . ' FROM ' . $tablename . (empty($where) ? $where : ' WHERE ' . $where);

        if (! empty($this->orderby)) {
            $query .= ' ORDER BY ' . (is_array($this->orderby) ? implode(',', $this->orderby) : $this->orderby);
        }

        $rows = $db->GetAssoc($query);

        $this->debug('DataObject(' . get_class($this) . ')::tree ' . $query);

        $isparent = array();

        foreach ($rows as $id => $row) {
            if (! empty($row[$this->parent_field])) {
                $isparent[$row[$this->parent_field]][$id] = '';
            }
        }

        foreach ($rows as $id => $row) {
            if (empty($row[$this->parent_field])) {
                $items[$id] = $row['identifier'];

                if (isset($isparent[$id])) {
                    $this->getChildTree($id, $items, '', $rows, $isparent);
                }
            }
        }
    }

    private function getChildTree($id, &$items, $level, $rows, $isparent)
    {
        foreach ($isparent[$id] as $child_id => $child_row) {
            $items[$child_id] = $level . '-' . $rows[$child_id]['identifier'];

            if ($isparent[$child_id]) {
                $this->getChildTree($child_id, $items, $level . '-', $rows, $isparent);
            }
        }
    }

    public function getTopLevel($attribute = null)
    {
        if ($attribute != null) {

            if (! isset($this->belongsTo[$attribute])) {
                $this->debug('DataObject(' . get_class($this) . ')::getTopLevel getTopLevel($attribute) must be called for a belongsTo relationship');
                return FALSE;
            }

            $model = DataObjectFactory::Factory($this->belongsTo[$attribute]['model']);

            return $model->getTopLevel();
        }

        $db = &DB::Instance();
        $query = 'SELECT ' . $this->idField . ', ' . $this->getIdentifier() . ' FROM ' . $this->_tablename . ' WHERE ' . $this->parent_field . ' IS NULL';

        return $db->GetAssoc($query);
    }

    public function getChildren()
    {
        $db = &DB::Instance();
        $query = 'SELECT ' . $this->idField . ', ' . $this->getIdentifier() . ' FROM ' . $this->_tablename . ' WHERE ' . $this->parent_field . '=' . $db->qstr($this->{$this->idField});

        // echo 'DataObject('.get_class($this).')::getChildren '.$query.'<br>';

        return $db->GetAssoc($query);
    }

    public function getChildrenAsDOC($doc = null, $sh = null)
    {
        if ($doc == null) {
            $doc_name = get_class($this) . 'Collection';
            $doc = new $doc_name();
        }

        if ($sh == null) {
            $sh = new SearchHandler($doc, FALSE);
        }

        $sh->addConstraint(new Constraint($this->parent_field, '=', $this->{$this->idField}));
        $doc->load($sh);

        return $doc;
    }

    public function getAncestors()
    {
        $db = &DB::Instance();
        $ancestors = array();
        $parent_id = $this->{$this->parent_field};

        while ($parent_id !== FALSE && ! empty($parent_id)) {
            $ancestors[] = $parent_id;
            $query = 'SELECT parent_id FROM ' . $this->_tablename . ' WHERE id=' . $db->qstr($parent_id);
            $parent_id = $db->GetOne($query);
        }

        return $ancestors;
    }

    function getSiblings($id = null, $attribute = null)
    {
        if ($attribute != null) {
            $model = DataObjectFactory::Factory($this->belongsTo[$attribute]['model']);
            return $model->getSiblings($id);
        }

        if ($id == null) {
            $parent_id = $this->parent_id;
        } else {
            $parent_id = $id;
        }

        $db = &DB::Instance();
        $query = 'SELECT ' . $this->idField . ', ' . $this->getIdentifier() . ' FROM ' . $this->_tablename . ' WHERE ' . $this->parent_field . (! empty($parent_id) ? '=' . $db->qstr($parent_id) : ' IS NULL');

        return $db->GetAssoc($query);
    }

    function getSiblingsAsDOC($id = null, $attribute = null)
    {
        if ($attribute != null) {
            $model = DataObjectFactory::Factory($this->belongsTo[$attribute]['model']);
            return $model->getSiblings($id);
        }

        if ($id == null) {
            $parent_id = $this->parent_id;
        } else {
            $parent_id = $id;
        }

        $doc = new get_class($this) . 'Collection';
        $db = &DB::Instance();
        $query = 'SELECT ' . $this->idField . ' FROM ' . $this->_tablename . ' WHERE ' . $this->parent_field . (! empty($parent_id) ? '=' . $db->qstr($parent_id) : ' IS NULL');
        $siblings = $db->GetCol($query);

        foreach ($siblings as $sibling) {
            $do = DataObjectFactory::Factory(get_class($this));
            $do->load($sibling);
            $doc->add($do);
        }

        return $doc;
    }

    function getIdentifier()
    {

        // Backwards compatibility - check for sql concatenation character
        // and return the identifierField if it contains the concatenation character
        if (! is_array($this->identifierField)) {

            if (strpos($this->identifierField, '||')) {
                return $this->identifierField;
            }

            $identifier_fields = array(
                $this->identifierField
            );
        } else {
            $identifier_fields = $this->identifierField;
        }

        // We have an array of one or more fields
        // check they are valid fields for the DO

        foreach ($identifier_fields as $key => $field) {

            if (! $this->isField($field) && ! isset($this->concatenations[$field])) {
                unset($identifier_fields[$key]);
            }
        }

        // Return the valid fields with the concatenation character
        // or if no valid fields,
        // return the first DO field that is not the DO's idField

        if (count($identifier_fields) > 0) {
            return implode('||\'' . $this->identifierFieldJoin . '\'||', $identifier_fields);
        } else {

            foreach ($this->getFields() as $field) {

                if ($field->name != $this->idField) {
                    return $field->name;
                }
            }
        }
    }

    function getDefaultOrderby()
    {
        $this->setCustomModelOrder();

        $ob = $this->orderby;
        $candidates = array(
            'position',
            'index',
            'title',
            'name',
            'subject',
            'surname'
        );

        if (empty($ob)) {

            foreach ($candidates as $candidate) {

                if ($this->isField($candidate)) {
                    $ob = $candidate;
                    break;
                }
            }
        }

        if (empty($ob)) {
            $ob = $this->idField;
        }

        $this->orderby = $ob;

        return $this->orderby;
    }

    function getViewName()
    {
        if (isset($this->_viewname)) {
            return $this->_viewname;
        } else {
            $collection_name = get_class($this) . 'Collection';
            $coln = new $collection_name($this);
            $this->_viewname = $coln->_tablename;
            return $this->_viewname;
        }
    }

    function setViewName($view)
    {
        $this->_viewname = $view;
        $this->_tablenames[] = $view;
    }

    function setView()
    {
        $this->setDisplayFields();
        return $this->_displayFields;
    }

    /**
     * Makes a field an enumeration
     *
     * @param $field string
     *            The field to be made into an enumeration
     * @param
     *            &$options array An array passed-by-reference with a list of options
     */
    public function setEnum($field, $options)
    {
        if ($this->_valid) {
            foreach ($options as $value => $description) {
                if (isset($this->enumCheck[$field][$value])) {
                    unset($options[$value]);
                }
            }

            $this->enums[$field] = $options;
            if (is_object($this->getField($field))) {
                $this->getField($field)->setFormatter(new EnumFormatter($options));
            }
        }
    }

    /**
     * Finds out if a field is an enumeration
     *
     * @param $field string
     *            The field to be checked
     * @return boolean false if not enum, true if is enum
     */
    public function isEnum($field)
    {
        $temp = $this->enums;
        return (isset($temp[$field]));
    }

    /**
     * Gets a list of enumeration options for a given field
     *
     * @param $field string
     *            The field for which to fetch options
     * @return an array of options
     */
    public function getEnumOptions($field)
    {
        return $this->enums[$field];
    }

    /**
     * Gets the key value from enumeration for a given field and options value
     *
     * @param $field string
     *            The field for which to fetch options
     * @param $field string
     *            The options value to search for
     * @return the associated key value or blank if not found
     */
    public function getEnumKey($field, $value)
    {
        $key = array_search($value, $this->enums[$field]);

        if ($key === FALSE) {
            return '';
        } else {
            return $key;
        }
    }

    /**
     * Makes a field uneditable once saved
     *
     * @param $field string
     *            The field to be made into uneditable
     */
    public function setNotEditable($field)
    {
        $this->notEditable[$field] = 1;
    }

    /**
     * Finds out if a field should be protected from editing
     *
     * @param $field string
     *            The field to be checked
     * @return boolean false if editable, true if not editable
     */
    public function isNotEditable($field)
    {
        $temp = $this->notEditable;
        return (isset($temp[$field]));
    }

    /**
     * Hides fields which should be hidden by default
     */
    public function setDefaultHidden()
    {
        $this->hidden['id'] = 1;
        $this->hidden['usercompanyid'] = 1;
        $this->hidden['created'] = 1;
        $this->hidden['createdby'] = 1;
        $this->hidden['lastupdated'] = 1;
        $this->hidden['alteredby'] = 1;
    }

    protected function setDefaultFieldValues()
    {
        $modulecomponent = ModuleComponent::Instance($this, 'M');

        if ($modulecomponent && $modulecomponent->isLoaded()) {
            if (! is_null($modulecomponent->title)) {
                $this->setTitle($modulecomponent->title);
            }

            if (count($modulecomponent->module_defaults) > 0) {
                foreach ($modulecomponent->module_defaults as $default) {

                    if ($this->isField($default->field_name) && $this->_fields[$default->field_name]->user_defaults_allowed) {
                        // override existing defaults
                        $this->_fields[$default->field_name]->dropDefault();
                        $this->getField($default->field_name)->setDefault($default->default_value);
                        $this->_fields[$default->field_name]->display_default_value = $default->default_value;

                        if ($default->enabled == 't') {
                            $this->defaultDisplayFields[] = $default->field_name;
                        } else {
                            $this->setHidden($default->field_name);
                            $this->_fields[$default->field_name]->system_override = TRUE;
                        }
                    }
                }
            }
        }
    }

    public function getDefaultFieldValue($field)
    {
        $modulecomponent = DataObjectFactory::Factory('ModuleComponent');
        $modulecomponent->loadBy(array(
            'name',
            'type'
        ), array(
            strtolower(get_class($this)),
            'M'
        ));

        if ($modulecomponent) {

            foreach ($modulecomponent->module_defaults as $default) {

                if ($default->field_name == $field->name) {
                    return $default->default_value;
                }
            }
        }

        return $field->default_value;
    }

    /**
     * Hides a field
     */
    public function setHidden($field)
    {
        $this->hidden[$field] = 1;
    }

    /**
     * Finds out if a field is hidden
     */
    public function isHidden($field)
    {
        $field = strtolower($field);
        return (isset($this->hidden[$field]) && $this->hidden[$field] === 1);
    }

    public function findAll()
    {
        $search = new SearchHandler($this);
    }

    public function isHandled($field)
    {
        $this->getField($field)->isHandled = TRUE;
    }

    public function addConfirmationField($fieldname, $tag = null)
    {
        if ($tag == null) {
            $tag = 'Confirm ' . ucwords($fieldname);
        }

        $c_fieldname = 'confirm_' . $fieldname;

        $ado_field = new ADOFieldObject();
        $ado_field->type = 'password';
        $ado_field->not_null = 1;

        $this->_fields[$c_fieldname] = new DataField($ado_field);
        $this->_fields[$c_fieldname]->tag = $tag;
        $this->setDefaultValidators();
    }

    public function addField($fieldname, $field)
    {
        $this->_fields[$fieldname] = $field;
    }

    function isHash($fieldname)
    {
        $this->hashes[$fieldname] = array();
    }

    /**
     * The Iterator functions.
     *
     * @todo Change name!
     *
     */
    public function current()
    {
        $field = array_values($this->getDisplayFields());
        $name = $field[$this->_pointer]->name;
        $fields = $this->_fields;
        $field = $fields[$name];

        if ($field->type === 'bool') {
            if ($field->value == 'f') {
                return 'false';
            } else {
                return 'true';
            }
        }

        return $field->value;
    }

    public function next()
    {
        $this->_pointer ++;
    }

    public function key()
    {
        $temp = array_keys($this->_fields);
        return $temp[$this->_pointer];
    }

    public function rewind()
    {
        $this->_pointer = 0;
    }

    public function valid()
    {
        return ($this->_pointer < count($this->_displayFields));
    }

    public function getId()
    {
        $idF = $this->idField;
        $field = $this->_fields[$idF];

        return $field->value;
    }

    protected function setAccessControlled($controlled, $cc = null, $fields = array('owner'))
    {
        if (empty($fields) && (! $cc instanceof ConstraintChain)) {
            // If no fields or constraint, then cannot set access control
            $this->_accessControlled = FALSE;
            return;
        }

        $this->_accessControlled = $controlled;

        // Set constraint to control access
        if (! $cc instanceof ConstraintChain) {
            $cc = new ConstraintChain();
        }

        $fields = (is_array($fields) ? $fields : array(
            $fields
        ));

        $db = DB::Instance();

        // Check each field - only register if it is a field in the object
        foreach ($fields as $key => $field) {
            if (! $this->isField($field)) {
                unset($fields[$key]);
            }
        }
        if (! empty($fields)) {
            $cc->add(new Constraint($db->qstr(EGS_USERNAME), 'in', '(' . implode(',', $fields) . ')'), 'OR');
            $this->_accessFields = $fields;
        }

        $has_role = DataObjectFactory::Factory('hasRole');
        $roles = implode(',', $has_role->getRoleID(EGS_USERNAME));

        // Add constraint for specific instances of an object
        // that belongs to a role that the user is in
        $sql = 'select obj.object_id
                  from objectroles obj
                 where obj.role_id in (' . $roles . ')
                   and obj.object_type = ' . $db->qstr($this->getTableName());

        $sql_read = $sql . ' and obj.read is true';
        $sql_write = $sql . ' and obj.write is true';

        // Read constraint
        $this->_accessContraint['read'] = new ConstraintChain();
        $this->_accessContraint['read']->add($cc);
        $this->_accessContraint['read']->add(new Constraint('id', 'in', '(' . $sql_read . ')'), 'OR');

        // write constraint
        $this->_accessContraint['write'] = new ConstraintChain();
        $this->_accessContraint['write']->add($cc);
        $this->_accessContraint['write']->add(new Constraint('id', 'in', '(' . $sql_write . ')'), 'OR');

        // Add constraint for specific instances of an object
        // that belongs to a role that the user is in
        $sql = 'select obj.username
                  from shared_roles obj
                 where obj.role_id in (' . $roles . ')
                   and obj.object_type = ' . $db->qstr($this->getTableName());

        // Read constraint
        $cc1 = new ConstraintChain();
        $cc1->add(new Constraint('id', 'not in', '(select object_id from objectroles)'));
        $cc2 = new ConstraintChain();
        $sql_read = $sql . ' and obj.read is true';
        foreach ($fields as $field) {
            $cc2->add(new Constraint($field, 'in', '(' . $sql_read . ')'), 'OR');
        }
        $cc1->add($cc2);
        $this->_accessContraint['read']->add($cc1, 'OR');

        // write constraint
        $cc3 = new ConstraintChain();
        $cc3->add(new Constraint('id', 'not in', '(select object_id from objectroles)'));
        $cc4 = new ConstraintChain();
        $sql_write = $sql . ' and obj.write is true';
        foreach ($fields as $field) {
            $cc4->add(new Constraint($field, 'in', '(' . $sql_write . ')'), 'OR');
        }
        $cc3->add($cc4);
        $this->_accessContraint['write']->add($cc3, 'OR');
    }

    public function isAccessControlled()
    {
        return $this->_accessControlled;
    }

    public function countAccessConstraints($accessType)
    {
        return count($this->getAccessConstraint($accessType)->contents);
    }

    public function getAccessConstraint($accessType = '')
    {
        if ($this->_accessContraint[$accessType] instanceof ConstraintChain) {
            // echo 'DataObject::getAccessConstraint type='.$accessType.' '.$this->_accessContraint[$accessType]->__toString().'<br>';
            return $this->_accessContraint[$accessType];
        } else {
            return new ConstraintChain();
        }
    }

    public function isAccessAllowed($id = null, $accessType = '')
    {
        if (! $this->_accessControlled) {
            // Not access controlled so everyone has access.
            return TRUE;
        }

        $access_users = array();

        foreach (! $this->_accessFields as $field) {
            // Does current user match any of the access field values
            if ($this->$field == EGS_USERNAME) {
                return TRUE;
            }
            $access_users[$this->$field] = $this->$field;
        }

        return FALSE;
    }

    public function getAccessFields()
    {
        return $this->_accessFields;
    }

    public function getRolePermissions($_username = '', $_module = '', $_access = 'write')
    {
        // TODO: Access Control is not currently implemented
        // code retained for future implementation but will need validating/amending
        $roles = array();

        if ($this->isLoaded()) {
            // Get permissions specific to this object
            $object_role = DataObjectFactory::Factory('ObjectRole');
            $roles = $object_role->getRoleID($this->{$this->idField}, $this->getTableName(), $_access);
        }

        if (empty($roles)) {
            // Get general permissions for the object type
            $object_role = DataObjectFactory::Factory('SharedRole');
            $roles = $object_role->getRoleID($_username, $this->getTableName(), $_access);
        }

        return $roles;
    }

    public function getUserPermissions($_username = '', $_module = '', $_access = 'write')
    {
        // TODO: Access Control is not currently implemented
        // code retained for future implementation but will need validating/amending
        $users = array();

        $roles = $this->getRolePermissions($_username, $_module, $_access);

        if (! empty($roles)) {
            $has_role = DataObjectFactory::Factory('hasRole');
            $users = $has_role->getUsers($roles);
        }

        return $users;
    }

    public function toArray()
    {
        $array = array();

        foreach ($this->_fields as $fieldname => $field) {

            $array[$fieldname] = array(
                '_name' => $fieldname,
                'type' => $field->type,
                'value' => $field->value,
                'tag' => $field->tag
            );
        }

        return $array;
    }

    public function toJSON()
    {
        if (function_exists('json_encode')) {
            return json_encode($this->toArray());
        }
    }

    public function get_name()
    {
        return get_class($this);
    }

    private function validateIdentifierField()
    {

        // Check that the identifierField is one or more valid fields
        // identifierField has a default value, if not explicitly
        // overridden in the extended DO declaration, the default
        // field may not exist in the specific DO table so set the
        // default to be a valid field
        if (! is_array($this->identifierField)) {

            if (strpos($this->identifierField, '||')) {
                $identifier_fields = explode('||', $this->identifierField);
            } else {
                $identifier_fields = array(
                    $this->identifierField
                );
            }
        } else {
            $identifier_fields = $this->identifierField;
        }

        // We have an array of one or more fields
        // check they are valid fields for the DO

        foreach ($identifier_fields as $key => $field) {

            if (! $this->isField($field)) {
                unset($identifier_fields[$key]);
            }
        }

        // if no fields are valid, set the identifierField
        // to the first DO field that is not the DO's idField

        if (count($identifier_fields) == 0) {

            foreach ($this->getFields() as $field) {

                if ($field->name != $this->idField) {
                    $this->identifierField = $field->name;
                    break;
                }
            }
        }
    }

    public function negate($field)
    {
        $length = FALSE;
        $pad = ' ';
        $zeropad = FALSE;
        $formatting = TRUE;
        $decimals = 0;

        if (is_array($field)) {

            switch (count($field)) {

                case 5:
                    $length = $field[4];
                case 4:
                    $zeropad = $field[3];
                case 3:
                    $formatting = $field[2];
                case 2:
                    $decimals = $field[1];
                case 1:
                    $field = $field[0];
            }
        }

        if ($this->isField($field)) {

            $value = bcmul($this->getField($field)->value, - 1, $decimals);

            if (! $formatting) {
                $value = str_replace(array(
                    ',',
                    '.'
                ), '', $value);
            }

            if ($length) {

                if ($zeropad) {
                    $pad = '0';
                }

                $value = str_pad($value, $length, $pad, STR_PAD_LEFT);
            }

            return $value;
        } else {
            return '';
        }
    }

    public function numberToWords($field)
    {
        $size = '';
        $padding = '';
        $type = STR_PAD_LEFT;

        if (is_array($field)) {

            switch (count($field)) {

                case 4:
                    $type = $field[3];
                case 3:
                    $padding = $field[2];
                case 2:
                    $size = $field[1];
                case 1:
                    $field = $field[0];
            }
        }

        $words = array(
            'zero',
            'one',
            'two',
            'three',
            'four',
            'five',
            'six',
            'seven',
            'eight',
            'nine'
        );
        $amount = '';

        if ($this->isField($field)) {

            $value = bcadd($this->getField($field)->value, 0, 0);

            if ($value < 0) {
                $value = bcmul($value, - 1, 0);
            }

            for ($i = 0; $i < 6; $i ++) {
                $amount = str_pad($words[bcmod($value, 10)], $size, $padding, $type) . $amount;
                $value = bcdiv($value, 10, 2);
            }
        }

        return $amount;
    }

    public function defaultsNotAllowed($field)
    {
        return array_search($field, $this->defaultsNotAllowed);
    }

    public function toJSONArray($array)
    {
        $output = array();

        foreach ($array as $key => $value) {
            $output[] = array(
                'id' => $key,
                'value' => $value
            );
        }

        return ($output);
    }

    public function getLinkRules()
    {
        $hasMany = array();

        foreach ($this->getHasMany() as $name => $detail) {

            if (! isset($this->linkRules[$name])) {
                $hasMany[$name] = $detail;
                $hasMany[$name]['actions'] = array(
                    'link',
                    'new'
                );
            } else {

                $validrule = TRUE;
                $rules = '';

                foreach ($this->linkRules[$name]['rules'] as $rule) {
                    $rules .= isset($rule['logical']) ? $rule['logical'] : (empty($rules) ? '' : '&&');
                    $rules .= '($this->' . $rule['field'] . $rule['criteria'] . ')';
                }

                if (empty($rules) || eval('return ' . $rules . ';')) {
                    $hasMany[$name] = array_merge($detail, $this->linkRules[$name]);
                }
            }
        }

        return $hasMany;
    }

    protected function relatedCount($related_item)
    {
        if (isset($this->hasMany[$related_item])) {

            $db = DB::Instance();
            $qb = new QueryBuilder($db, $this->_templateobject);
            $do = $this->hasMany[$related_item]['do'];
            $model = DataObjectFactory::Factory($do);

            $collectionname = $do . 'Collection';
            $collection = new $collectionname($model);

            if (! $this->isLoaded()) {
                return $collection;
            }

            $handlers = $this->searchHandlers;

            if (! isset($handlers[$related_item])) {
                $sh = new SearchHandler($collection, FALSE);
            } else {
                $sh = $handlers[$related_item];
            }

            unset($sh->fields[strtolower(get_class($this))]);
            unset($sh->fields[strtolower(get_class($this)) . '_id']);

            $sh->addConstraint(new Constraint($this->hasMany[$related_item]['fkfield'], '=', $this->{$this->hasMany[$related_item]['field']}));

            $query = $qb->select($model->_fields)
                ->from($model->_tablename)
                ->where($sh->constraints)
                ->groupby($sh->groupby)
                ->orderby($sh->orderby, $sh->orderdir)
                ->limit($sh->perpage, $sh->offset)
                ->__toString();

            $c_query = $qb->countQuery();
            $num_records = $db->GetOne($c_query);

            if ($num_records === FALSE) {
                throw new Exception($db->ErrorMsg());
            }

            return $num_records;
        }

        return 0;
    }

    public function version()
    {
        return $this->version;
    }

    public function getXML($field, $key = '')
    {
        $xml = simplexml_load_string(unserialize($this->$field));

        if (! $xml) {
            return '';
        } elseif (empty($key)) {
            return $xml;
        } else {

            $data = $xml->xpath('//' . $key);

            if (is_array($data) && count($data) == 1) {
                return $data[0];
            }

            return $data;
        }
    }

    public function getTitle()
    {
        if (empty($this->_title)) {
            return get_class($this);
        }

        return $this->_title;
    }

    public function setTitle($title = '')
    {
        $this->_title = $title;
    }

    public function isLatest($data, &$errors = array())
    {
        if (! empty($data['lastupdated']) && ! is_null($this->lastupdated) && $data['lastupdated'] !== $this->lastupdated) {
            $errors[] = 'Data has been changed by another user - please requery';
            return FALSE;
        }

        return TRUE;
    }

    // ****************
    // MAGIC FUNCTIONS

    // on clone, make a deep copy of this object by cloning internal member;
    public function __clone()
    {

        // if fields array is not empty...
        if (! empty($this->_fields)) {

            // loop through each field ...
            foreach ($this->_fields as $key => $field) {

                // make sure it's an object ...
                if (is_object($this->_fields[$key])) {

                    // and clone itself
                    $this->_fields[$key] = clone $this->_fields[$key];
                }
            }
        }

        // if fields array is not empty...
        if (! empty($this->_displayFields)) {

            // loop through each field ...
            foreach ($this->_displayFields as $key => $display_field) {

                // make sure it's an object ...
                if (is_object($this->_displayFields[$key])) {

                    // and clone itself
                    $this->_displayFields[$key] = clone $this->_displayFields[$key];
                }
            }
        }

        // if searchHandlers array is not empty...
        if (! empty($this->searchHandlers)) {
            // loop through each searchHandler ...
            foreach ($this->searchHandlers as $shkey => $sh) {
                $this->searchHandlers[$shkey] = clone $this->searchHandlers[$shkey];

                $this->searchHandlers[$shkey]->constraints = clone $this->searchHandlers[$shkey]->constraints;
            }
        }

        if ($this->_policyConstraint['constraint'] instanceof ConstraintChain) {
            $this->_policyConstraint['constraint'] = clone $this->_policyConstraint['constraint'];
        }
    }

    // *******************
    // CALLBACK FUNCTIONS
    public function cb_loaded()
    {}

    /*
     * Static Functions
     */
    static function updatePositions($modelname, $id, $fieldname, $new_sequence, $current_sequence = NULL, &$errors = array())
    {
        if ((! ($modelname instanceof DataObject) && ! is_string($modelname)) || ! is_numeric($id) || ! is_string($fieldname) || ! is_numeric($new_sequence)) {
            $errors[] = 'Update Position - Invalid Parameters';
            return FALSE;
        }

        if (is_string($modelname)) {
            // get the model DataObject
            $model = DataObjectFactory::Factory($modelname);
        } else {
            $model = $modelname;
            $modelname = get_class($model);
        }

        if (! $model->isLoaded()) {
            $model->load($id);
        } elseif ($model->{$model->idField} != $id) {
            // The loaded model id field does not equal the supplied id
            $errors[] = 'Update Position - Invalid Id';
            return FALSE;
        }

        if ($new_sequence == $current_sequence) {
            // No update required
            return TRUE;
        }

        $collectionname = $modelname . 'Collection';

        $collection = new $collectionname(DataObjectFactory::Factory($modelname));

        $sh = new SearchHandler($collection, FALSE);

        if (is_null($current_sequence)) {
            // This is an insert so need to increase sequences after inserted sequence
            $sh->addConstraint(new Constraint($fieldname, '>=', $new_sequence));
            $increment = '+1';
        } else {
            // This is an update
            if ($new_sequence > $current_sequence) {
                // Need to shuffle existing values up the list
                $current_sequence ++;
                $sh->addConstraint(new Constraint($fieldname, 'between', $current_sequence . ' and ' . $new_sequence));
                $increment = '-1';
            } else {
                // Need to shuffle existing values down the list
                $current_sequence --;
                $sh->addConstraint(new Constraint($fieldname, 'between', $new_sequence . ' and ' . $current_sequence));
                $increment = '+1';
            }
        }

        $db = DB::Instance();

        $db->StartTrans();

        $model->$fieldname = $new_sequence;

        $result = ($collection->update($fieldname, '(' . $fieldname . $increment . ')', $sh) && $model->save());

        if ($result === FALSE) {
            $errors[] = 'Error updating ' . $fieldname . ' : ' . $db->ErrorMsg();

            $db->FailTrans();
        }

        $db->CompleteTrans();

        return $result;
    }
}

// end of DataObject.php
