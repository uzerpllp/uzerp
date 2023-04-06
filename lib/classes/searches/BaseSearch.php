<?php

/**
 * Provides the basic functionality for representing both search fields on a form,
 * and a ConstraintChain for passing to the database
 *
 * @package searches
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class BaseSearch
{

    protected $cleared = FALSE;

    private $groups = array(
        'basic',
        'advanced',
        'hidden'
    );

    protected $fields = array();

    protected $defaults = array();

    public $display_fields = array();

    /**
     * @var boolean Set TRUE to disable user display field selection
     */
    public $disable_field_selection = FALSE;

    public function __construct($defaults = array())
    {
        foreach ($this->groups as $group) {
            $this->fields[$group] = array();
        }

        $this->defaults = $defaults;
    }

    /**
     *
     * @param $search_data array
     * @return void Takes an array representing (typically) $_POST['Search'] and assigns the values to te appropriate fields
     */
    public function setSearchData(&$search_data = null, &$errors, $search_name = 'default')
    {

        // Set the Search Id Field
        $this->addSearchField('search_id', 'search_id', 'hidden', '', 'hidden', FALSE);

        // Use the Search Id to construct a unique search save name
        $default_search_name = $search_name;

        if (isset($search_data['search_id'])) {
            $search_name = $search_name . $search_data['search_id'];
        } elseif (isset($this->defaults['search_id'])) {
            $search_name = $search_name . $this->defaults['search_id'];
        }

        if (! empty($search_data['clear'])) {

            if (isset($_SESSION['searches'][get_class($this)][$search_name])) {

                debug('BaseSearch::setSearchData ' . get_class($this) . ' ' . $search_name . ':clearing saved search');
                unset($_SESSION['searches'][get_class($this)][$search_name]);
                unset($_SESSION['searches'][get_class($this)][$default_search_name]);
            }

            $search_data = $this->defaults;
        }

        $save_search = TRUE;

        if ($search_data === null || empty($search_data) || (isset($search_data['search_id']) && count($search_data) == 1)) {

            if (isset($_SESSION['searches'][get_class($this)][$search_name])) {

                $search_data = $_SESSION['searches'][get_class($this)][$search_name];
                $save_search = FALSE;
                debug('BaseSearch::setSearchData ' . get_class($this) . ' ' . $search_name . ':loading saved search ' . print_r($search_data, TRUE));
            } elseif (isset($_SESSION['searches'][get_class($this)][$default_search_name])) {
                if (is_array($_SESSION['searches'][get_class($this)][$default_search_name])) {
                    $search_data += $_SESSION['searches'][get_class($this)][$default_search_name];
                }
                debug('BaseSearch::setSearchData ' . get_class($this) . ' ' . $search_name . ':loading default search ' . print_r($search_data, TRUE));
            } else {
                $search_data = $this->defaults;
            }
        }

        if ($search_data !== null && count($search_data) > 0) {

            foreach ($this->fields as $group) {

                foreach ($group as $fieldname => $searchField) {

                    if (isset($search_data[$fieldname])) {

                        if ($searchField->isValid($search_data[$fieldname], $errors)) {
                            $searchField->setValue($search_data[$fieldname]);
                        }
                    }
                }
            }

            if (count($errors) == 0 && $save_search) {

                debug('BaseSearch::setSearchData ' . get_class($this) . ' ' . $search_name . ':saving search in session ' . print_r($search_data, TRUE));

                $_SESSION['searches'][get_class($this)][$search_name] = $search_data;
                if (isset($search_data['search_id'])) {
                    unset($search_data['search_id']);
                }
                $_SESSION['searches'][get_class($this)][$default_search_name] = $search_data;
            }

            if (isset($search_data['display_fields'])) {
                $this->display_fields = $search_data['display_fields'];
            }
        }
    }

    /**
     *
     * @param $fieldname string
     *            [ @param $label string ] is defaulted to prettify($fieldname) when requested
     *            [ @param $type string ] defaults to 'contains'
     *            [ @param $default mixed ] is defaulted to '' (subclasses have option to over-ride the default default...)
     * @return boolean
     * @see SearchField::Factory Adds a searchfield to the search, constructed with the given paramaters
     */
    public function addSearchField($fieldname, $label = null, $type = "contains", $default = null, $group = 'basic', $do_constraint = TRUE)
    {
        $this->testGroup($group);
        $field = SearchField::Factory($fieldname, $label, $type, $default, $do_constraint);

        return $this->addField($fieldname, $group, $field);
    }

    /**
     *
     * @param $fieldname string
     *            [ @param $groupname string ]
     * @return boolean Removes the searchfield attached to the given fieldname.
     *         If a groupname is given, then the removal is quicker
     */
    public function removeSearchField($fieldname, $groupname = null)
    {
        if ($groupname !== null) {

            if (isset($this->fields[$groupname][$fieldname])) {
                unset($this->fields[$groupname][$fieldname]);
                return TRUE;
            } else {
                throw new Exception('Tried to remove field from group that doesn\'t exist: ' . $fieldname . ' from ' . $groupname);
                return FALSE;
            }
        } else {

            foreach ($this->groups as $groupname) {

                if (isset($this->fields[$groupname][$fieldname])) {
                    unset($this->fields[$groupname][$fieldname]);
                    return TRUE;
                }
            }

            throw new Exception('Tried to remove field that doesn\'t exist: ' . $fieldname);
            return FALSE;
        }
    }

    /**
     * Tests the given $groupname against the list of allowable groupnames
     * 
     * @param $groupname string
     * @return void
     */
    private function testGroup($groupname)
    {
        if (! in_array($groupname, $this->groups)) {
            throw new Exception('$group should be either "basic" or "advanced"');
        }
    }

    /**
     *
     * @param $fieldname string
     * @param $field SearchField
     *            Add an already made field to the Search
     */
    public function addField($fieldname, $group, SearchField $field)
    {
        $this->testGroup($group);
        $this->fields[$group][$fieldname] = $field;
    }

    /**
     *
     * @param $fieldname string
     * @param $value mixed
     * @return void Set the value of a field within the search
     * @see SearchField::setValue()
     */
    protected function setValue($fieldname, $value)
    {
        if (isset($this->fields[$fieldname])) {
            $this->fields[$fieldname]->setValue($value);
        }
    }

    public function getValue($fieldname)
    {
        foreach ($this->fields as $group) {

            if (isset($group[$fieldname])) {
                return $group[$fieldname]->getValue();
            }
        }

        return null;
    }

    /**
     *
     * @param
     *            $fieldname
     * @param
     *            $value
     * @return boolean For CheckboxSearchFields (and anything similar) that are representing an equality,
     *         rather than a boolean field, e.g. 'My Tickets' checks for person_id=12 c.f. completed=true
     *
     */
    protected function setOnValue($fieldname, $value)
    {
        foreach ($this->fields as $i => $group) {

            if (isset($group[$fieldname])) {
                $this->fields[$i][$fieldname]->setOnValue($value);
                return TRUE;
            }
        }

        throw new Exception('Fieldname not found: ' . $fieldname);
        return FALSE;
    }

    protected function setOffValue($fieldname, $value)
    {
        foreach ($this->fields as $i => $group) {

            if (isset($group[$fieldname])) {
                $this->fields[$i][$fieldname]->setOffValue($value);
                return TRUE;
            }
        }

        throw new Exception('Fieldname not found: ' . $fieldname);
        return FALSE;
    }

    /**
     *
     * @param $options array
     * @return boolean Allows the setting of options for a Select-type searchfield (or any other type that wants to accept options)
     */
    public function setOptions($fieldname, $options)
    {
        foreach ($this->fields as $i => $group) {

            if (isset($group[$fieldname])) {
                $this->fields[$i][$fieldname]->setOptions($options);
                return TRUE;
            }
        }

        throw new Exception('Fieldname not found: ' . $fieldname);
        return FALSE;
    }

    public function setBreadcrumbs($fieldname, $do = '', $parent = '', $value = '', $name = '', $descriptor = '', $data = array())
    {
        foreach ($this->fields as $i => $group) {

            if (isset($group[$fieldname])) {
                $this->fields[$i][$fieldname]->setBreadcrumbs($do, $parent, $value, $name, $descriptor, $data);
                return TRUE;
            }
        }

        throw new Exception('Fieldname not found: ' . $fieldname);
        return FALSE;
    }

    /**
     *
     * @param $fieldname string
     * @param $constraint Constraint(Chain)
     *            Attach a constrant to the specified field. This allows for the altering of the constraints built by the SearchFields
     *            (if the field knows what to do with a different constraint- only 'hide' checkboxes do at the moment)
     */
    public function setConstraint($fieldname, $constraint)
    {
        foreach ($this->fields as $i => $group) {

            if (isset($group[$fieldname])) {
                $this->fields[$i][$fieldname]->setConstraint($constraint);
            }
        }
    }

    /**
     *
     * @param
     *            void
     * @return string Returns an HTML string representing the series of form fields and labels
     * @see SearchField::toHTML()
     */
    public function toHTML($group = 'basic')
    {
        $this->testGroup($group);
        $html = '';

        foreach ($this->fields[$group] as $searchField) {
            $html .= $searchField->toHTML() . "\n";
        }

        return $html;
    }

    public function toString($output_type = 'html')
    {
        $parts_string = '';
        $parts = array();

        foreach ($this->fields as $type => $fields) {

            // we don't want to output the hidden field values
            if ($type === 'hidden') {
                continue;
            }

            foreach ($fields as $field) {

                // Sanitize the value for display
                $value = uzh($field->getCurrentValue());

                $block_start = '';
                $block_end = '';
                $field_start = '';
                $field_end = '';

                $default = $field->getDefault();

                // no point in continuing with an empty value
                if ((empty($value) || $value === FALSE) && empty($default)) {
                    continue;
                }

                if (strtolower($output_type) === 'html') {
                    $block_start = '<span class="search-field" data-fieldname="' . $field->getFieldname() . '">';
                    $block_end = '</span>';
                    $field_start = '<strong>';
                    $field_end = '</strong>';
                }

                if (strtolower($output_type) === 'fop') {
                    $field_start = '<fo:inline font-weight="bold">';
                    $field_end = '</fo:inline>';
                }

                $inner_html = $field_start . prettify($field->getLabel()) . $field_end . ": " . $value;

                if (! empty($value)) {
                    $parts[] = $block_start . $inner_html . $block_end;
                }
            }
        }

        if (! empty($parts)) {
            $parts_string = implode(', ', $parts);
        }

        return $parts_string;
    }

    /**
     *
     * @param $groupname string
     * @return boolean Returns true if the groupname specified has any searchfields
     */
    public function hasFields($groupname)
    {
        return (count($this->fields[$groupname]) > 0);
    }

    /**
     *
     * @param
     *            void
     * @return ConstraintChain Returns a constraint chain representing the search in it's current state
     *         This takes into account both selected and default values, as appropriate
     * @see SearchField::toConstraint()
     */
    public function toConstraintChain()
    {
        $cc = new ConstraintChain();

        if ($this->cleared) {
            return $cc;
        }

        debug('BaseSearch::toConstraintChain Fields: ' . print_r($this->fields, TRUE));

        // Certain hidden fields need to be excluded from the constraint
        foreach ($this->fields as $group => $group_data) {

            foreach ($group_data as $field => $searchField) {

                if ($searchField->doConstraint()) {

                    $c = $searchField->toConstraint();

                    if ($c !== FALSE) {
                        $cc->add($c);
                    }
                }
            }
        }

        debug('BaseSearch::toConstraintChain Constraints: ' . print_r($cc, TRUE));
        return $cc;
    }

    public function clear($search_name = 'default')
    {
        if (isset($_SESSION['searches'][get_class($this)][$search_name])) {
            debug('BaseSearch::setSearchData ' . get_class($this) . ' ' . $search_name . ':clearing saved search');
            unset($_SESSION['searches'][get_class($this)][$search_name]);
        }

        $this->cleared = TRUE;
    }
}

// end of BaseSearch.php