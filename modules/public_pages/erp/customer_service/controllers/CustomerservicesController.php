<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class CustomerServicesController extends printController
{

    protected $version = '$Revision: 1.19 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);
        $this->_templateobject = DataObjectFactory::Factory('SInvoiceLine');
        $this->uses($this->_templateobject);
    }

    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $errors = array();
        $s_data = $this->setSearch();
        $customerservice = new CustomerServiceCollection($this->_templateobject);
        $sh = $customerservice->setSearch($s_data);
        $servicesummary = $customerservice->getServiceSummary($sh);

        $this->search = customerServicesSearch::useDefault($s_data, $errors, $customerservice);

        if (count($errors) > 0) {
            $flash = Flash::Instance();
            $flash->addErrors($errors);
            $this->search->clear();
        }

        $this->view->set('customerservice', $servicesummary);

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', array(
            'all' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => $this->name,
                    'action' => 'index'
                )),
                'tag' => 'All Groups/Customers'
            ),
            'failuresummary' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => $this->name,
                    'action' => 'failureCodeSummary'
                )),
                'tag' => 'View Failure Code Summary'
            ),
            'failurecodes' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => 'csfailurecodes',
                    'action' => 'index'
                )),
                'tag' => 'View Failure Codes'
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('', 'Summary for'));
    }

    public function delete($modelName = null)
    {
        $flash = Flash::Instance();
        $flash->addError('Action not allowed here');
        sendTo($this->name, 'index', $this->_modules);
    }

    public function save($modelName = null, $dataIn = [], &$errors = []) : void
    {
        $flash = Flash::Instance();
        $flash->addError('Action not allowed here');
        sendTo($this->name, 'index', $this->_modules);
    }

    public function detail()
    {
        $errors = array();

        $s_data = $this->setSearch();

        $constrain = FALSE;

        $fields = array(
            'slmaster_id',
            'product_group',
            'cs_failurecode_id',
            'start',
            'end'
        );
        // if failure code is 'any' remove it from the search, we've got other criteria to search by...
        if (isset($this->_data['cs_failurecode_id']) && strtolower((string) $this->_data['cs_failurecode_id']) === 'any') {

            // don't let the standard search do anything with the cs_failurecode_id field
            unset($s_data['cs_failurecode_id']);

            $constrain = TRUE;

            // constrain where failure code has not been set...
            $cc1 = new ConstraintChain();
            $cc1->add(new Constraint('cs_failurecode_id', 'IS', 'NULL'));

            // and another constraint chain for the failed order
            $cc2 = new ConstraintChain();
            $cc2->add(new Constraint('despatch_date', '>', '(due_despatch_date)'));
            $cc2->add(new Constraint('order_qty', '>', '(despatch_qty)'), 'OR');
        }

        $customerservice = new CustomerServiceCollection($this->_templateobject);

        if (! isset($this->_data['orderby']) && ! isset($this->_data['page']) && ! ($this->isPrintDialog() || $this->isPrinting())) {

            $sh = $customerservice->setSearch($s_data, false);
            $sh->setOrderby(array(
                'product_group',
                'customer',
                'order_number',
                'due_despatch_date'
            ), array(
                'ASC',
                'ASC',
                'DESC',
                'DESC'
            ));
        } else {
            // $sh = $customerservice->setSearch($s_data, TRUE);
            // echo '$s_data<pre>'.print_r($s_data, true).'</pre><br>';
            $sh = new SearchHandler($customerservice, TRUE);
        }

        $this->search = customerServicesSearch::useDefault($s_data, $errors, $customerservice);

        if (count($errors) > 0) {
            $flash = Flash::Instance();
            $flash->addErrors($errors);
            $this->search->clear();
        }

        if (isset($this->search) && ! isset($this->_data['ajax_print']) && ! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {

            // cache the search string

            $search_string_array = array(
                'fop' => $this->search->toString('fop'),
                'html' => $this->search->toString('html')
            );

            $_SESSION['search_strings'][EGS_USERNAME][$sh->search_id] = $search_string_array;
        }

        if ($constrain === TRUE) {
            // add the two constraint chains seperately
            $sh->addConstraintChain($cc1);
            $sh->addConstraintChain($cc2);
        }

        $sh->extractOrdering();
        $sh->setFields(array(
            'id',
            'product_group',
            'customer',
            'stitem',
            'order_number',
            'despatch_number',
            'due_despatch_date',
            'despatch_date',
            'order_qty',
            'despatch_qty',
            'failurecode',
            'cs_failurecode_id',
            'cs_failure_note'
        ));

        if (isset($this->search) && ($this->isPrintDialog() || $this->isPrinting())) {
            $sh->setLimit(0);
            $customerservice->load($sh);
            return parent::printCollection($customerservice);
        } else {
            $sh->extractPaging();
            $customerservice->load($sh);
        }

        $this->view->set('num_records', $customerservice->num_records);
        $this->view->set('num_pages', $customerservice->num_pages);
        $this->view->set('cur_page', $customerservice->cur_page);

        $this->view->set(strtolower((string) $customerservice->getModelName()) . 's', $customerservice);

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', array(
            'all' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => $this->name,
                    'action' => 'index'
                )),
                'tag' => 'Customer Service Summary'
            ),
            'failurecodes' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => 'csfailurecodes',
                    'action' => 'index'
                )),
                'tag' => 'View Failure Codes'
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function failureCodeSummary()
    {
        $errors = array();
        $s_data = $this->setSearch();
        $customerservice = new CustomerServiceCollection($this->_templateobject);
        $sh = $customerservice->setSearch($s_data);
        $servicesummary = $customerservice->failureCodeSummary($sh);

        $this->search = customerServicesSearch::failureCodes($s_data, $errors, $customerservice);

        if (count($errors) > 0) {
            $flash = Flash::Instance();
            $flash->addErrors($errors);
            $this->search->clear();
        }

        $this->view->set('customerservice', $servicesummary);
        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', array(
            'all' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => $this->name,
                    'action' => 'index'
                )),
                'tag' => 'Customer Service Summary'
            ),
            'failurecodes' => array(
                'link' => array_merge($this->_modules, array(
                    'controller' => 'csfailurecodes',
                    'action' => 'index'
                )),
                'tag' => 'View Failure Codes'
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('page_title', $this->getPageName('Failure Codes', 'Summary for'));
    }

    public function updatefailure()
    {
        $customerservice = new CustomerServiceCollection($this->_templateobject);
        $s_data = array(
            'id' => $this->_data['id']
        );
        $sh = $customerservice->setSearch($s_data);

        $customerservice->load($sh);
        $this->view->set('customerservice', $customerservice);

        $failurecodes = DataObjectFactory::Factory('CSFailureCode');
        $codeslist = array(
            '' => ''
        );
        $codeslist += $failurecodes->getInUse();

        $this->view->set('failurecodes', $codeslist);
    }

    public function savefailure()
    {
        if (! $this->CheckParams($this->modeltype)) {
            sendBack();
        }

        $flash = Flash::Instance();

        $errors = array();

        $cs_failure = DataObjectFactory::Factory('SODespatchLine');

        if ($cs_failure->update(
            $this->_data[$this->modeltype]['id'],
            ['cs_failurecode_id', 'cs_failure_note'],
            [$this->_data[$this->modeltype]['cs_failurecode_id'], $this->_data[$this->modeltype]['cs_failure_note']])
            ) {
            $flash->addMessage('Failure Code successfully amended');
        } else {
            $flash->addMessage('Failed to amend Failure Code');
        }

        sendTo($this->name, 'detail', $this->_modules, array(
            'product_group' => $this->_data[$this->modeltype]['product_group'],
            'slmaster_id' => $this->_data[$this->modeltype]['slmaster_id']
        ));
    }

    public function printCustomerService()
    {

        // set options
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => '',
                'csv' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'report' => 'customer_service',
            'filename' => $this->generate_collection_name(TRUE)
        );

        // we use status in other print functions, however here we base it on if ajax print is or isn't set
        if (! $this->isPrinting()) {
            return $options;
        }

        $errors = array();

        $s_data = $this->setSearch();

        $customerservice = new CustomerServiceCollection($this->_templateobject);

        $sh = $customerservice->setSearch($s_data);

        $sh->setLimit(0);

        $servicesummary = $customerservice->getServiceSummary($sh);

        $count = 0;
        $data = array();
        foreach ($servicesummary as $group => $detail) {
            foreach ($detail as $key => $fields) {
                if ($key == 'total') {
                    $fields['title'] = $group;
                    $data[$count]['group'][$key] = $fields;
                } else {
                    $fields['title'] = $prev_group = $group;
                    $csv_data[$count] = $data[$count]['group'][$key]['data'] = $fields;
                }

                $count ++;
            }
        }
        if ($this->_data['print']['printtype'] === 'csv') {
            // generate the csv and add it to the options array
            $options['csv_source'] = $this->generate_csv($this->_data['print'], $csv_data, array(
                'customer',
                'ontime',
                'infull',
                'ontime_infull',
                'count',
                'title'
            ));
        } else {
            $extra['title'] = 'Customer Service ';

            $extra['customerservice'] = $data;

            // generate the xml and add it to the options array
            $options['xmlSource'] = $this->generateXML(array(
                'extra' => $extra
            ));
        }

        // fire the print output, echo the output JSON for jQuery to handle
        echo $this->generate_output($this->_data['print'], $options);
        exit();
    }

    public function printFailureCodes()
    {

        // set options
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => '',
                'csv' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'report' => 'cs_failure_codes',
            'filename' => $this->generate_collection_name(TRUE)
        );

        // we use status in other print functions, however here we base it on if ajax print is or isn't set
        if (! $this->isPrinting()) {
            return $options;
        }

        $errors = array();

        $s_data = $this->setSearch();

        $customerservice = new CustomerServiceCollection($this->_templateobject);

        $sh = $customerservice->setSearch($s_data);

        $servicesummary = $customerservice->failureCodeSummary($sh);

        $count = 0;
        $data = array();

        foreach ($servicesummary as $group => $detail) {
            foreach ($detail as $key => $fields) {
                if ($fields['description'] == ' - ') {
                    $fields['description'] = 'No Failure Codes';
                }

                if ($this->_data['print']['printtype'] === 'csv') {
                    $data[$count] = $fields;
                } else {
                    $data[$count]['data'] = $fields;
                }

                $count ++;
            }
        }

        if ($this->_data['print']['printtype'] === 'csv') {
            // generate the csv and add it to the options array
            $options['csv_source'] = $this->generate_csv($this->_data['print'], $data, array(
                'Failure Description',
                'Count',
                'Period'
            ));
        } else {
            $extra['title'] = 'Customer Service Failure Code Summary ';

            $extra['cs_failure_codes'] = $data;

            // generate the xml and add it to the options array
            $options['xmlSource'] = $this->generateXML(array(
                'extra' => $extra
            ));
        }

        // fire the print output, echo the output JSON for jQuery to handle
        echo $this->generate_output($this->_data['print'], $options);
        exit();
    }

    /*
     * Protected Functions
     */
    protected function setSearch($do = null, $method = null, $defaults = array(), $params = array(), $use_saved_search = \false)
    {
        $s_data = array();

        // Preserve any search criteria selection so that the context is maintained
        if (isset($this->_data['Search']) && ! isset($this->_data['Search']['clear'])) {
            $s_data = $this->_data['Search'];
        } elseif (! isset($this->_data['orderby']) && ! isset($this->_data['page']) && ! (isset($this->search) && isset($this->_data['ajax_print'])) && ! ($this->isPrintDialog() || $this->isPrinting())) {

            // Either this is the first entry to the page or the search has been cleared
            // and orderby or paging is not selected
            // so set context from calling module

            $fields = array(
                'slmaster_id',
                'product_group',
                'cs_failurecode_id',
                'start',
                'end'
            );

            foreach ($fields as $field) {

                if (isset($this->_data[$field])) {
                    $s_data[$field] = $this->_data[$field];
                } else {
                    $s_data[$field] = '';
                }
            }
        }

        return $s_data;
    }

    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((empty($base) ? 'customer_services' : $base), $action);
    }
}

// End of CustomerServicesController
