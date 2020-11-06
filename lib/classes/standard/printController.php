<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Extends Controller to add output capabilities
 *
 * @package standard
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/



class printController extends Controller
{

    public $rec_count;

    protected $tables = array();

    protected $cols = array();

    protected $logo;

    protected $userInfo;

    protected $pageNumbering;

    protected $reportNameInfo;

    protected $printTitle;

    protected $printSize;

    protected $printOrientation;

    protected $headerline;

    protected $footerline;

    protected $page = array();

    public $printtype = array(
        'pdf' => 'PDF',
        'csv' => 'CSV',
        'xml' => 'XML',
        'text' => 'Text',
        'edi' => 'EDI'
    );

    public $defaultprinttype = 'pdf';

    public $defaultprintaction;

    public $testprint = FALSE;

    protected $printaction = array(
        'view' => 'View',
        'print' => 'Print',
        'save' => 'Save',
        'email' => 'Email',
        'edi' => 'EDI'
    );

    protected $fieldseparater = array(
        ',' => ', (comma)',
        '*' => '* (asterisk)',
        '|' => '| (pipe)',
        ';' => '; (semi-colon)',
        ':' => '; (colon)',
        '!' => '! (exclamation)',
        'chr(9)' => 'Tab'
    );

    protected $textdelimiter = array(
        '' => "None",
        "'" => "' (apostrophe)",
        '"' => '" (double apostrophe)',
        '|' => '| (pipe)',
        '*' => '* (asterisk)'
    );

    protected $wideColumns = array(
        'problem',
        'description'
    );

    public function index(DataObjectCollection $collection, $sh = '', &$c_query = null)
    {
        $display_fields = array();

        $model = $collection->getModel();

        // If selection of display fields is disabled, don't set them up
        if (! $this->search->disable_field_selection) {
            foreach ($model->getFields() as $fieldname => $field) {
                if ($fieldname != 'id' && $fieldname != 'usercompanyid' && substr($fieldname, - 3) != '_id' && ! isset($model->belongsToField[$fieldname]) && ! $model->isHidden($fieldname)) {
                    $display_fields[$fieldname] = $field->tag;
                }
            }

            $selected_fields = array();

            if (isset($this->_data['Search']['display_fields'])) {
                foreach ($this->_data['Search']['display_fields'] as $fieldname => $field) {
                    $selected_fields[$fieldname] = prettify($field);
                }
            } else {
                foreach ($collection->getFields() as $fieldname => $field) {
                    if (substr($fieldname, - 3) != '_id') {
                        $selected_fields[$fieldname] = $field->tag;
                    }
                }
            }

            $this->view->set('selected_fields', $selected_fields);

            $display_fields = array_diff($display_fields, $selected_fields);

            $this->view->set('display_fields', $display_fields);
        }

        if (! isset($this->_data['ajax_print'])) {
            parent::index($collection, $sh, $c_query);
            return;
        }

        showtime('start-controller-index');

        $collection->setParams();

        if (! ($sh instanceof SearchHandler)) {
            $sh = $this->setSearchHandler($collection);
        }

        showtime('sh-extracted');

        // Need to set the orderby of the collection in the searchhandler?
        // But if this is set in the collection, seems to take it
        // so why not here?
        showtime('pre-load');

        // TODO: Printing needs looking at; the model is too complicated and
        // therefore maintenance is difficult. Needs breaking down into more
        // logical discrete units.
        // BEWARE: the following may not work for all cases
        // needs extensive testing; this is implemented for selectorController output
        if ($this->_data['session_key'] == 'undefined') {
            $sh->setLimit(0);
            $collection->load($sh);
            $this->PrintCollection($collection);
        }

        // echo 'controller::index setting printing session data<br>';
        // in this instance we can only pass the data through the session
        // but it's only the collection name and search id :-)
        $_SESSION['printing'][$this->_data['session_key']]['collection'] = get_class($collection);
        $_SESSION['printing'][$this->_data['session_key']]['search_id'] = $this->_data['search_id'];
        exit();

        showtime('end-controller-index');
    }

    public function adjust_selection()
    {
        $params = array();
        $type = $this->_data['type'];
        $selected = empty($_SESSION['selected_output'][$type]) ? array() : $_SESSION['selected_output'][$type];

        if (isset($this->_data['id'])) {

            if (isset($this->_data['email'])) {
                $selected[$this->_data['id']]['email'] = $this->_data['email'];
            }

            if (isset($this->_data['printaction'])) {
                $selected[$this->_data['id']]['printaction'] = $this->_data['printaction'];
            }

            if (isset($this->_data['print_copies'])) {
                $selected[$this->_data['id']]['print_copies'] = $this->_data['print_copies'];
            }

            if (isset($this->_data['select'])) {

                $selected[$this->_data['id']]['select'] = $this->_data['select'];

                if ($selected[$this->_data['id']]['select'] == 'true') {
                    $selected['count'] ++;
                } else {
                    $selected['count'] --;
                }
            }
        }

        $_SESSION['selected_output'][$type] = $selected;

        $this->view->set('value', $selected['count']);
        $this->setTemplateName('text_inner');
    }

    public function select_for_output()
    {

        // Search
        $errors = array();
        $s_data = array();

        // Set context from calling module
        if (isset($this->_data['Search'])) {

            $this->_data['type'] = $this->_data['Search']['type'];

            if (! empty($this->_data['Search']['clear'])) {
                $_SESSION['selected_output'][$this->_data['type']] = array(
                    'count' => 0
                );
            }
        }

        if (! $this->checkParams('type')) {
            sendBack();
        }

        $type = $this->_data['type'];
        $selected = empty($_SESSION['selected_output'][$type]) ? array() : $_SESSION['selected_output'][$type];
        $output_details = $this->output_types[$this->_data['type']];
        $s_data = $output_details['search_defaults'];
        $s_data['type'] = $_GET['type'] = $type;

        $this->setSearch($output_details['search_do'], $output_details['search_method'], $s_data, array(), TRUE);
        // End of search

        if (! isset($this->_data['page']) && ! isset($this->_data['orderby'])) {

            $collection = new $output_details['collection']($this->_templateobject);

            if (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
                $sh = new SearchHandler($collection, FALSE);
            } else {
                $sh = new SearchHandler($collection);
            }

            $sh->setFields($output_details['collection_fields']);
            $cc = $this->search->toConstraintChain();
            $sh->addConstraintChain($cc);
            $sh->extractOrdering();
            $collection->load($sh);

            $selected = empty($_SESSION['selected_output'][$type]) ? array() : $_SESSION['selected_output'][$type];
            $new_selection = array(
                'count' => 0
            );

            foreach ($collection as $detail) {

                if (isset($selected[$detail->id])) {

                    $new_selection[$detail->id] = $selected[$detail->id];

                    if ($selected[$detail->id]['select'] == 'true') {
                        $new_selection['count'] ++;
                    }
                } else {

                    if (isset($output_details['identifier'])) {
                        $new_selection[$detail->id]['description'] = prettify($output_details['identifier']) . ' : ' . $detail->getIdentifierValue();
                    } else {
                        $new_selection[$detail->id]['description'] = $detail->getIdentifierValue();
                    }

                    if (empty($new_selection[$detail->id]['description'])) {
                        $new_selection[$detail->id]['description'] = 'no identifier set';
                    }

                    // Need a beter way of identifying the print action
                    // - in SLCustomer, there is the invoice method enum
                    // - in PrintController there is the printaction enum
                    // These are not equivalent; e.g. the print controller printaction needs to list
                    // methods that can be handled within the code whereas the SLCustomer invoice_method
                    // defines how an invoice is sent which may be handled within the code or externally.
                    // As an example, invoices can be faxed, but this cannot currently be done from within code,
                    // so the invoice must be printed and manually faxed.
                    // Therefore, perhaps the SLCustomer invoice_method Fax needs to be implemented internally
                    // as Print until a fax model is implemented

                    if (! is_null($detail->method)) {

                        switch ($detail->method) {

                            case 'E':
                                $new_selection[$detail->id]['printaction'] = 'email';
                                break;
                            case 'D':
                                $new_selection[$detail->id]['printaction'] = 'edi';
                                break;
                            case 'F':
                            case 'P':
                                $new_selection[$detail->id]['printaction'] = 'print';
                                break;
                            default:
                                $new_selection[$detail->id]['printaction'] = strtolower($detail->method);
                        }
                    } elseif ($detail->email == '') {
                        $new_selection[$detail->id]['printaction'] = 'print';
                    } else {
                        $new_selection[$detail->id]['printaction'] = 'email';
                    }

                    $new_selection[$detail->id]['email'] = $detail->email;
                    $new_selection[$detail->id]['select'] = 'false';
                }
            }

            $_SESSION['selected_output'][$type] = $selected = $new_selection;
        }

        $collection = new $output_details['collection']($this->_templateobject);
        $sh = $this->setSearchHandler($collection);

        $sh->setFields($output_details['collection_fields']);
        parent::index($collection, $sh);

        $this->view->set('type', $this->_data['type']);
        $this->view->set('fields', $output_details['display_fields']);
        $this->view->set('clickaction', 'view');
        $this->view->set('no_ordering', TRUE);
        $this->view->set('collection', $this->view->getTemplateVars(strtolower($collection->getModelName()) . 's'));

        $this->_data['printaction'] = $output_details['printaction'];
        $this->_data['filename'] = $output_details['filename'];

        unset($this->printaction['view']);
        $this->printAction();
        $this->view->set('page_title', $this->getPageName("", $output_details['title']));

        $this->view->set('selected_output', $selected);
        $this->view->set('count_selected', $selected['count']);

        foreach ($this->_modules as $key => $value) {
            $modules[] = $key . '=' . $value;
        }

        $link = implode('&', $modules) . '&controller=' . $this->name . '&action=adjust_selection&type=' . $type;
        $this->view->set('link', $link);

        $sidebar = new SidebarController($this->view);
        $sidebarlist = array();

        $sidebarlist['summary'] = array(
            'tag' => 'Show ' . $this->_data['type'] . 's Summary List',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'output_summary',
                'type' => $type
            )
        );

        $sidebar->addList('action', $sidebarlist);
        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function select_all()
    {
        $type = $this->_data['type'];
        $selected = empty($_SESSION['selected_output'][$type]) ? array() : $_SESSION['selected_output'][$type];

        foreach ($selected as $key => $value) {
            if ($key != 'count') {
                $selected[$key]['select'] = 'true';
            }
        }

        $_SESSION['selected_output'][$type] = $selected;
        $this->setTemplateName('select_for_output');
        $this->select_for_output();
    }

    public function save_selection()
    {
        $flash = Flash::Instance();
        $db = DB::Instance();
        $errors = array();

        $db->StartTrans();

        $type = $this->_data['type'];
        $output_details = $this->output_types[$type];
        $selected = empty($_SESSION['selected_output'][$type]) ? array() : $_SESSION['selected_output'][$type];

        $header_data = array();
        $header_data = $this->_data;

        if (! empty($this->_data['output_header_id'])) {

            $header_id = $this->_data['output_header_id'];
            $detail = new DataObjectCollection(DataObjectFactory::Factory('OutputDetail'));
            $sh = new SearchHandler($detail, FALSE);

            $sh->addConstraintChain(new Constraint('output_header_id', '=', $header_id));
            $detail->delete($this->_data['output_header_id']);

            $header_data['id'] = $header_id;
        }

        $header_data['class'] = $this->modeltype;
        $header_data['process'] = $output_details['printaction'];
        $header = DataObject::Factory($header_data, $errors, 'OutputHeader');

        if ($header && $header->save() && count($errors) == 0) {

            $header_id = $header->id;
            $lines_data = array();

            foreach ($selected as $id => $value) {

                if ($value['select'] == 'true') {
                    $value['output_header_id'] = $header_id;
                    $value['select_id'] = $id;
                    $lines_data[] = $value;
                }
            }

            foreach ($lines_data as $line_data) {

                $line = DataObject::Factory($line_data, $errors, 'OutputDetail');

                if (! $line || count($errors) > 0 || ! $line->save()) {
                    $errors[] = 'Error saving output detail';
                    break;
                }
            }
        } else {
            $errors[] = 'Error saving output header';
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $db->FailTrans();
            $db->CompleteTrans();
            $this->refresh();
        } else {

            $db->CompleteTrans();
            $_SESSION['selected_output'][$type] = array();
            sendTo($this->name, 'output_summary', $this->_modules, array(
                'type' => $type
            ));
        }
    }

    public function output_summary()
    {
        $collection = new DataObjectCollection(DataObjectFactory::Factory('OutputHeader'));
        $sh = $this->setSearchHandler($collection);
        $cc = new ConstraintChain();

        $sh->addConstraint(new Constraint('type', '=', $this->_data['type']));
        parent::index($collection, $sh);

        $sidebar = new SidebarController($this->view);
        $sidebarlist = array();

        $sidebarlist['select'] = array(
            'tag' => 'Send ' . $this->_data['type'] . 's',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'select_for_output',
                'type' => $this->_data['type']
            )
        );

        $sidebarlist['edilog'] = array(
            'tag' => 'EDI Log',
            'link' => array(
                'module' => 'edi',
                'controller' => 'editransactionlogs',
                'action' => 'index'
            )
        );

        $sidebar->addList('action', $sidebarlist);
        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function output_detail()
    {
        $header = DataObjectFactory::Factory('OutputHeader');

        $header->load($this->_data['id']);
        $this->view->set('outputheader', $header);

        $output_details = new OutputDetailCollection(DataObjectFactory::Factory('OutputDetail'));

        if (! isset($this->_data['orderby']) && ! isset($this->_data['page'])) {
            $sh = new SearchHandler($output_details, FALSE);
        } else {
            $sh = new SearchHandler($output_details);
        }

        $sh->addConstraintChain(new Constraint('output_header_id', '=', $this->_data['id']));
        $sh->extract();
        $output_details->load($sh);

        $this->view->set('outputdetails', $output_details);
        $this->view->set('num_records', $output_details->num_records);
        $this->view->set('num_pages', $output_details->num_pages);
        $this->view->set('cur_page', $output_details->cur_page);

        $sidebar = new SidebarController($this->view);
        $sidebarlist = array();

        $sidebarlist['summary'] = array(
            'tag' => 'Show ' . $header->type . 's Summary List',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'output_summary',
                'type' => $header->type
            )
        );

        $sidebarlist['edilog'] = array(
            'tag' => 'EDI Log',
            'link' => array(
                'module' => 'edi',
                'controller' => 'editransactionlogs',
                'action' => 'index'
            )
        );

        $sidebar->addList('action', $sidebarlist);
        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function process_output()
    {
        set_time_limit(0);

        $flash = Flash::Instance();
        $errors = array();
        $header = DataObjectFactory::Factory('OutputHeader');

        $header->load($this->_data['id']);

        switch ($this->_data['type']) {

            case 'cancel':
                if (! $header->delete()) {
                    $errors[] = 'Failed to delete entry';
                }
                break;

            case 'process':

                if ($header) {

                    $counter = 0;
                    $data = array(
                        'filename' => $header->filename,
                        'printtype' => $header->printtype,
                        'printer' => $header->printer,
                        'emailtext' => $header->emailtext,
                        'fieldnames' => $header->fieldnames,
                        'fieldseparater' => $header->fieldseparater,
                        'textdelimiter' => $header->textdelimiter
                    );

                    $progressbar = new progressBar($header->process);

                    $controller = $this;

                    $callback = function ($detail, $key) use(&$data, &$counter, &$errors, $controller, $header) {
                        $data['printaction'] = $detail->printaction;
                        $data['email'] = $detail->email;
                        $data['print_copies'] = $detail->print_copies;

                        // filename can NOT be the same for any of the files in this loop, this is because
                        // printIPP gets confused when trying to print the same file...

                        $data['filename'] = $header->filename . "-" . $counter ++;

                        $result = call_user_func(array(
                            $controller,
                            $header->process
                        ), $detail->select_id, $data);

                        // we cannot always expect an array back
                        if (is_array($result)) {
                            $message = $result['message'];
                            $status = $result['status'];
                        } else {
                            $status = $result;
                        }

                        if (! $status) {
                            $errors[] = $message;
                            $detail->status = 'Failed';
                        } else {
                            $detail->status = 'OK';
                        }

                        $detail->save();
                    };

                    if ($progressbar->process($header->output_details, $callback) === FALSE || count($errors) > 0) {
                        $errors[] = 'Error outputing ' . $header->type . ' batch';
                    }

                    if (! $header->update($header->id, 'processed', TRUE)) {
                        $errors[] = 'Error marking process as complete';
                    }
                }

                break;
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);
        }

        sendTo($this->name, 'output_summary', $this->_modules, array(
            'type' => $header->type
        ));
    }

    public function printAction()
    {
        $this->view->set('printtype', $this->printtype);
        $this->view->set('defaultprinttype', $this->defaultprinttype);
        $this->view->set('defaultprintaction', $this->defaultprintaction);
        $this->view->set('testprint', $this->testprint);
        $this->view->set('printaction', $this->printaction);
        $this->view->set('fieldseparater', $this->fieldseparater);
        $this->view->set('textdelimiter', $this->textdelimiter);
        $this->view->set('redirect', $this->_data['printaction']);

        if (isset($this->_data)) {

            $ignore_keys = array(
                'module',
                'submodule',
                'controller',
                'action',
                'printaction',
                'filename'
            );
            $query_data = array();

            foreach ($this->_data as $key => $data) {

                if (! in_array($key, $ignore_keys)) {
                    $query_data[$key] = $data;
                }
            }

            $this->view->set('query_data', $query_data);
        }

        if (isset($this->_data['filename'])) {
            $filename = $this->_data['filename'];
        } elseif (isset($this->defaultfilename)) {
            $filename = $this->defaultfilename;
        } else {
            $filename = 'tmp';
        }

        $this->view->set('filename', $filename);
        $this->view->set('page_title', $this->getPageName("", $this->_data['printaction'] . ' for '));
        $this->view->set('printers', $this->selectPrinters());

        $userPreferences = UserPreferences::instance(EGS_USERNAME);
        $this->view->set('default_printer', $this->getDefaultPrinter());

        $this->view->set('emailtext', $this->email_signature());
    }

    public function email_signature()
    {
        $syscompany = DataObjectFactory::Factory('Systemcompany');
        $syscompany->load(EGS_COMPANY_ID);

        $company = $syscompany->systemcompany;

        $email_signature = chr(10) . chr(10) . chr(10) . '--' . chr(10) . $company->name . chr(10) . $company->getAddress()->fulladdress . chr(10) . "Company No: " . $company->companynumber . chr(10) . "Tel. No:    " . $company->getContactDetail('T') . chr(10) . "Fax. No:    " . $company->getContactDetail('F') . chr(10);

        $email_signature .= (! is_null($company->vatnumber)) ? $company->tax_description . ' ' . $company->vatnumber . chr(10) : '';

        $note = $company->getNote('email');
        $email_signature .= (! empty($note)) ? chr(10) . $note . chr(10) : '';

        return $email_signature;
    }

    public function printDialog()
    {

        // get the output options from the original controller action, we pass the dialog
        // string as the status so we simple get the options back, and not invoke a report (yet)
        $options = call_user_func(array(
            $this,
            $this->_data['printaction']
        ), 'dialog');

        // process the specified print types options
        if (isset($options['default_print_action'])) {
            $this->defaultprinttype = $options['default_print_type'];
        }

        if (isset($options['type']) && ! empty($options['type'])) {

            $print_type = $options['type'];

            // loop through given array, if no value is specified take the title from the printtype array
            foreach ($print_type as $key => $value) {

                if (empty($value)) {
                    $print_type[$key] = $this->printtype[$key];
                }
            }

            // check if the default value exists within the given array, if not use key()
            if (! in_array($this->defaultprinttype, $print_type)) {
                $default_print_type = key($print_type);
            }
        } else {
            $print_type = $this->printtype;
            $default_print_type = $this->defaultprinttype;
        }

        // set print types / default print type to options
        $options['print_type'] = $print_type;
        $options['default_print_type'] = $default_print_type;

        // get the printing preferences
        $options = $this->get_printing_preferences($options);

        // process the specified print action options
        if (isset($options['default_print_action'])) {
            $this->defaultprintaction = $options['default_print_action'];
        }

        if (isset($options['actions']) && ! empty($options['actions'])) {

            $print_action = $options['actions'];

            // loop through given array, if no value is specified take the title from the printtype array
            foreach ($print_action as $key => $value) {

                if (empty($value)) {
                    $print_action[$key] = $this->printaction[$key];
                }
            }

            // check if the default value exists within the given array, if not use key()
            if (! in_array($this->defaultprintaction, $print_action)) {
                $default_print_action = key($print_action);
            }
        } else {
            $print_action = $this->printaction;
            $default_print_action = $this->defaultprintaction;
        }

        // set print action / default print action to options
        $options['print_action'] = $print_action;
        $options['default_print_action'] = $default_print_action;
        $options['testprint'] = $this->testprint;
        $options['field_separater'] = $this->fieldseparater;
        $options['text_delimiter'] = $this->textdelimiter;

        // loop through the remaining items within $this->_data, if an item doesn't appear in the
        // ignore keys then output that to smarty

        if (isset($this->_data)) {

            // the original (read: following) method of outputting data to the dialog
            // is wrong, we cannot output arrays for example. This should be pahsed out

            $ignore_keys = array(
                'module',
                'submodule',
                'controller',
                'action',
                'printaction',
                'filename'
            );

            $query_data = array();

            foreach ($this->_data as $key => $data) {

                if (! in_array($key, $ignore_keys)) {
                    $query_data[$key] = $data;
                }
            }

            $this->view->set('query_data', $query_data);

            // to allow us to store complex arrays, lets JSON+base64 encode our array and output to smarty
            $this->view->set('encoded_query_data', base64_encode(json_encode($query_data)));
        }

        // determine and output the filename
        if (isset($options['filename'])) {
            $filename = $options['filename'];
        } elseif (isset($this->defaultfilename)) {
            $filename = $this->defaultfilename;
        } else {
            $filename = 'tmp';
        }

        $this->view->set('page_title', $this->getPageName("", $this->_data['printaction'] . ' for '));

        $options['filename'] = $filename;
        $options['printers'] = $this->selectPrinters();

        // discover and set the default printer
        $userPreferences = UserPreferences::instance(EGS_USERNAME);
        $options['default_printer'] = $this->getDefaultPrinter();

        // generate and output the email text
        $options['email_text'] = $this->email_signature();

        // set options
        $this->view->set('redirect', $this->_data['printaction']);
        $this->view->set('options', $options);
    }

    public function setPrintParams($data, &$printparams = array(), &$errors = array())
    {
        $printparams['printtype'] = $data['printtype'];
        $printparams['printaction'] = $data['printaction'];
        $printparams['filename'] = isset($data['filename']) ? $data['filename'] : '';

        if ($printparams['printaction'] == 'Email' || $printparams['printaction'] == 'Save') {

            if ($printparams['filename'] == '') {
                $printparams['filename'] = $printparams['defaultfilename'];
            }
        }

        $printparams['email'] = isset($data['email']) ? $data['email'] : '';
        $printparams['emailtext'] = isset($data['emailtext']) ? $data['emailtext'] : '';

        if ($printparams['printaction'] == 'Email' && $printparams['email'] == '') {
            $errors[] = 'Requires an email address';
        }

        if (count($errors) > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function PrintDocument()
    {
        // this is where FOP will need to take place
        // get the XML for the collection, pass it off to an XSLT, could easily use the dialog
        if (! $this->loadData()) {
            sendback();
        }

        $do = $this->_uses[$this->modeltype];

        // set options
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => ''
            ),
            // 'csv' => ''
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'report' => 'Document',
            'filename' => get_class($do) . '_' . $do->{$do->idField}
        );

        // we use status in other print functions, however here we base it on if ajax print is or isn't set
        if (! $this->isPrinting()) {
            return $options;
        }

        $form_data = $this->decode_original_form_data($this->_data['encoded_query_data']);

        $data = array();
        $tags = array();

        if (isset($form_data['fields'])) {
            $data[$do->idField] = $do->{$do->idField};

            foreach ($form_data['fields'] as $fieldname => $tag) {
                $data[$fieldname] = $do->getFormatted($fieldname);
                $tags[$fieldname] = $tag;
            }
        }

        // TODO: Loop round $do->getFields and add any fields not in $data?

        $options['xslVars']['REPORT_TITLE'] = $do->getTitle();

        $options['xslSource'] = ReportDefinition::getDefinition($this->_data['print']['report'])->definition;

        $options['xmlSource'] = $this->build_custom_xml(array(
            $data
        ), $tags);

        // fire the print output, echo the output JSON for jQuery to handle
        echo $this->generate_output($this->_data['print'], $options);
        exit();
    }

    public function reportOptions()
    {
        $report = ReportDefinition::getDefinition('PrintCollection');

        // set options
        return array(
            'type' => array(
                'pdf' => '',
                'xml' => '',
                'csv' => ''
            ),
            'report_type' => ReportType::getReportTypes(),
            'default_report_type' => $report->report_type_id,
            'report_name' => $this->getReportsByType($report->report_type_id),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'report' => $report->name,
            'filename' => $this->generate_collection_name(TRUE)
        );
    }

    public function PrintCollection($collection = NULL)
    {
        // this is where FOP will need to take place
        // get the XML for the collection, pass it off to an XSLT, could easily use the dialog

        // set options
        $options = $this->reportOptions();

        // we use status in other print functions, however here we base it on if ajax print is or isn't set
        if (! $this->isPrinting()) {
            return $options;
        }
        $search_id = $_SESSION['printing'][$this->_data['session_key']]['search_id'];

        // if no collection has been passed in...
        if (! ($collection instanceof DataObjectCollection)) {

            // go and collect the collection name + search id from the session
            $collection_name = $_SESSION['printing'][$this->_data['session_key']]['collection'];

            // and load up the collection, set limit to zero
            $collection = new $collection_name();
            $sh = $this->setSearchHandler($collection, $search_id, TRUE);

            $sh->setLimit(0);

            // if it's a csv document, only output the query
            if ($this->_data['print']['printtype'] === 'csv') {
                $query = $collection->generate_query($sh);
                $query = $query['query'];
            } else {
                $this->load_collection($collection, $sh);
            }
        } else {

            // if it's a csv document, only output the query
            if ($this->_data['print']['printtype'] === 'csv') {
                $query = $collection->query;
            }
        }

        // collection search string
        $search_options = $_SESSION['search_strings'][EGS_USERNAME][$search_id]['fop'];

        if (! empty($search_options)) {
            $options['xslVars']['search_string'] = "Search options: " . $search_options;
        } else {
            $options['xslVars']['search_string'] = '';
        }

        if ($this->_data['print']['printtype'] === 'csv') {
            $options['query'] = $query;
        } else {
            $resources = $this->build_print_resources($collection);
            $options['xmlSource'] = $resources['xml'];
            $options['xslSource'] = $resources['xsl'];
        }

        $this->_data['print']['attributes']['orientation-requested'] = 'landscape';

        // fire the print output, echo the output JSON for jQuery to handle
        echo $this->generate_output($this->_data['print'], $options);
        exit();
    }

    public function getWideColumns()
    {
        return $this->wideColumns;
    }

    public function getPrintActions()
    {
        return $this->printaction;
    }

    public function getPrintTypes()
    {
        return $this->printtype;
    }

    public function getDefaultPrinter()
    {
        $userPreferences = UserPreferences::instance(EGS_USERNAME);
        return $userPreferences->getPreferenceValue('default_printer', 'shared');
    }

    public static function selectPrinters()
    {
        $printers = array();
        $ipp = new CupsPrintIPP();

        self::setPrinterLog($ipp);

        if ($ipp->getPrinters()) {

            foreach ($ipp->available_printers as $key => $printer) {

                $parts = array();

                $ipp->setPrinterURI($ipp->available_printers[$key]);
                $ipp->getPrinterAttributes();

                // use description if available, otherwise use name
                if (! empty($ipp->printer_attributes->printer_info->_value0)) {
                    $parts[] = $ipp->printer_attributes->printer_info->_value0;
                } else {
                    $parts[] = $ipp->printer_attributes->printer_name->_value0;
                }

                // set location
                if (! empty($ipp->printer_attributes->printer_location->_value0)) {
                    $parts[] = $ipp->printer_attributes->printer_location->_value0;
                }

                $printers[$printer] = implode(' - ', $parts);
            }
        }

        return $printers;
    }

    /*
     * Set the search fields and load the collection
     *
     * @access protected
     * @param DataObjectCollection $collection
     */
    protected function load_collection(&$collection, $sh)
    {
        $data = $this->decode_original_form_data($this->_data['encoded_query_data']);

        if (isset($data['Search']['display_fields'])) {
            foreach ($sh->fields as $fieldname => $field) {
                if ($fieldname != 'id' && $fieldname != 'usercompanyid' && substr($fieldname, - 3) != '_id' & ! in_array($fieldname, $data['Search']['display_fields'])) {
                    unset($sh->fields[$fieldname]);
                }
            }

            $fields = $sh->fields;

            foreach ($data['Search']['display_fields'] as $fieldname) {
                if (! isset($sh->fields[$fieldname])) {
                    $fields[$fieldname] = $fieldname;
                }
            }

            $sh->setFields($fields);
        }

        $collection->load($sh);
    }

    protected function setBreakLevels($_measure_fields = array(), $_aggregate_fields = array())
    {
        foreach ($_aggregate_fields as $field => $formatting) {
            $aggregate_fields[$field] = 0;
            $field_formatting[$field] = $formatting;
        }

        $measure_fields = array_merge(array(
            'report' => ''
        ), $_measure_fields);

        $this->view->set('measure_fields', $measure_fields);
        $this->view->set('reverse_measure_fields', array_reverse($measure_fields, TRUE));
        $this->view->set('aggregate_fields', $aggregate_fields);
        $this->view->set('total_fields', array());
        $this->view->set('field_formatting', $field_formatting);

        $_SESSION['printing'][$this->_data['index_key']]['break']['measure_fields'] = $measure_fields;
        $_SESSION['printing'][$this->_data['index_key']]['break']['aggregate_fields'] = $aggregate_fields;
        $_SESSION['printing'][$this->_data['index_key']]['break']['field_formatting'] = $field_formatting;
    }

    // ****************
    // FOP FUNCTIONS

    /**
     * Generated the output (usually, but not always, using FOP)
     *
     * @access public
     * @param array $params
     * @param array $options
     * @return string (json)
     */
    public function generate_output($params, $options)
    {

        /*
         * params (array): paramaters passed from the print dialog
         * options (array): options passed from the original action
         */
        $output_debug_path = get_config('OUTPUT_DEBUG_PATH');

        // make sure a print type has been set
        if (isset($params['printtype']) && $params['printtype'] != '') {
            $data_type = $params['printtype'];
        } else {

            return $this->build_print_dialog_response(FALSE, array(
                'message' => 'Error: No print type specified'
            ));
        }

        // make sure we have a decent filename
        if ((! isset($params['filename']) || in_array($params['filename'], array(
            '',
            'tmp'
        ))) && isset($options['filename']) && $options['filename'] != '') {
            $params['filename'] = $options['filename'];
        }

        // if we're still left with no name, make one up
        if ($params['filename'] == '') {
            $params['filename'] = 'no_name_' . mt_rand();
        }

        // get the array of paths / filenames etc
        $paths = $this->get_paths($params['filename'], $data_type);

        // if the directory doesn't exist...
        if (! is_dir($paths['type_path'])) {

            // ...create it recursively
            if (! mkdir($paths['type_path'], 0777, TRUE)) {

                return $this->build_print_dialog_response(FALSE, array(
                    'message' => 'Error: Could not create data directory'
                ));
            }
        }

        // we also need to ensure
        if ($data_type === 'pdf') {

            // if the directory doesn't exist...
            if (! is_dir($paths['type_path'] . 'thumbs/')) {

                // ...create it recursively
                if (! mkdir($paths['type_path'] . 'thumbs/', 0777, TRUE)) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: Could not create PDF thumnail directory'
                    ));
                }
            }
        }

        // if the temp directory doesn't exist...
        if (! is_dir($paths['temp_path'])) {

            // ...create it recursively
            if (! mkdir($paths['temp_path'], 0777, TRUE)) {

                return $this->build_print_dialog_response(FALSE, array(
                    'message' => 'Error: Could not create temp directory'
                ));
            }
        }

        /*
         * we only need to process the XML if we're
         *
         * we don't need xml for text or csv? Need a better way of doing this
         */
        if ((! isset($options['requires_xml']) || $options['requires_xml'] !== FALSE) && ! in_array($data_type, array(
            'csv',
            'txt'
        ))) {

            // set / get the xml
            $xml = '';

            if (isset($options['xmlSource'])) {
                $xml = $options['xmlSource'];
            }

            // sanatise XML, replace £$€& with their ASCII character codes
            $xml = $this->sanatise_xml($xml);

            // check if the xml is loaded
            if (empty($xml)) {

                return $this->build_print_dialog_response(FALSE, array(
                    'message' => 'Error: XML data is empty'
                ));
            }
        }

        // if we're requesting an fop document (e.g. PDF, Text etc) we need to fetch the XML, XSL and then process it
        switch (strtolower($data_type)) {

            case "text":
                // for now lets assume we're handling an array of lines
                if (! isset($options['txtArray']) || count($options['txtArray']) <= 0) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: No data to output as text file'
                    ));
                }

                $fhandle = fopen($paths['temp_file_path'], 'w');

                foreach ($options['txtArray'] as $line) {
                    fwrite($fhandle, $line . "\r\n");
                }

                fclose($fhandle);
                break;

            case "csv":

                // we're either dealing with a pre-build csv or a query...
                if (isset($options['csv_source'])) {

                    // pre-built

                    // create temp file for CSV
                    $csv_file = tempnam('/tmp', 'CSV');
                    chmod($csv_file, 0777);

                    // write the data to the file, we can test if the process has been successful
                    if (file_put_contents($csv_file, $options['csv_source']) === FALSE) {

                        return $this->build_print_dialog_response(FALSE, array(
                            'message' => 'Error: Could not create CSV document'
                        ));
                    }
                } else {
                    // query

                    // check the query has been passed through
                    if (! isset($options['query']) || empty($options['query'])) {

                        return $this->build_print_dialog_response(FALSE, array(
                            'message' => 'Error: No query specified'
                        ));
                    }

                    // discover the correct CSV class to use
                    $injector = DataObjectFactory::Factory('InjectorClass');
                    $injector->loadBy('name', 'CSV');

                    $csv_class = $injector->class_name;

                    // load CSV and generate the output
                    $csv = new $csv_class();
                    $csv_file = $csv->go($options['query'], $params);
                }

                // copy the file from the tmp directory, we can test if the process has been successful
                if (! @copy($csv_file, $paths['temp_file_path'])) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: Could not create CSV document'
                    ));
                }

                // the output file will have no privs, for now set them, see how Dave has done this on the existing system
                if (! chmod($paths['temp_file_path'], 0777)) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: Cannot update file permissions'
                    ));
                }
                break;

            case "xml":
                // we must be dealing with XML, save it to a file
                $fhandle = fopen($paths['temp_file_path'], 'w');

                fwrite($fhandle, $xml);
                fclose($fhandle);
                break;

            default:
                // get the xsl
                if (isset($options['xslSource'])) {
                    $xsl = $options['xslSource'];
                } elseif (! empty($params['report'])) {
                    $xsl = ReportDefinition::getDefinition($params['report'])->definition;
                } elseif (! empty($options['report'])) {
                    $xsl = ReportDefinition::getDefinition($options['report'])->definition;
                }

                // check if the xsl is loaded
                if (empty($xsl)) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: XSL definition is empty'
                    ));
                }

                // set a few common xsl vars
                $options['xslVars']['footer_string'] = 'Printed on ' . date('d/m/Y') . ' by ' . EGS_USERNAME;

                // pass xsl through processor
                $xsl = $this->process_xsl($xsl, $options['xslVars']);

                // sanatise XSL
                $xsl = $this->sanatise_xsl($xsl);

                // debug mode?
                // this has to be after ALL xsl processing, otherwise we don't get a clear representation of the definition
                if (! empty($output_debug_path)) {

                    $fhandle = fopen($output_debug_path . time() . '.xsl', 'w');
                    fwrite($fhandle, $xsl);
                    fclose($fhandle);

                    $fhandle = fopen($output_debug_path . time() . '.xml', 'w');
                    fwrite($fhandle, $xml);
                    fclose($fhandle);
                }

                // load fop and generate the output
                $fop = new FOP($xml, $xsl);
                $fop_file = $fop->go();

                // ATTN: this should go to users tmp
                // copy the file from the tmp directory, we can test if the process has been successful

                if (! @copy($fop_file, $paths['temp_file_path'])) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: Apache FOP cannot create document'
                    ));
                }

                // merge contents
                // if we've requested the file to be merged there's no point in continuing with
                // saving / printing / emailing etc

                if (isset($options['merge_file_name'])) {

                    if (empty($options['merge_file_name'])) {

                        return $this->build_print_dialog_response(FALSE, array(
                            'message' => 'Error: Merge file not specified'
                        ));
                    }

                    $merge_path = $paths['temp_path'] . $options['merge_file_name'];

                    // append files
                    $response = PDFTools::append($paths['temp_file_path'], $merge_path);

                    // check response, return to callee
                    if ($response === TRUE) {
                        return $this->build_print_dialog_response(TRUE);
                    } else {

                        return $this->build_print_dialog_response(FALSE, array(
                            'message' => 'Error: Could not merge files'
                        ));
                    }
                }

                // debug mode?
                // output a copy of the PDF so we can see it in the debug

                if (! empty($output_debug_path)) {
                    copy($paths['temp_file_path'], $output_debug_path . time() . '.pdf');
                }

                // the output file will have no privs, for now set them, see how Dave has done this on the existing system
                if (! chmod($paths['temp_file_path'], 0777)) {

                    return $this->build_print_dialog_response(FALSE, array(
                        'message' => 'Error: Cannot update file permissions'
                    ));
                }
                break;
        }

        // success / failure
        if (! file_exists($paths['temp_file_path'])) {

            return $this->build_print_dialog_response(FALSE, array(
                'message' => 'Error: Document not generated'
            ));
        }

        $output_path = $paths['temp_file_path'];
        $params['paths'] = $paths;

        // a lowercase version of printaction
        $print_action = strtolower($params['printaction']);

        // if we're one of the appropriate actions...
        if (in_array($print_action, array(
            'quick_output',
            'save'
        ))) {

            // copy the file to the user's storage
            if (! @copy($paths['temp_file_path'], $paths['user_file_path'])) {

                return $this->build_print_dialog_response(FALSE, array(
                    'message' => 'Error: Could not copy file to user storage'
                ));
            }

            // update the output path
            $output_path = $paths['user_file_path'];
        }

        // get the printing prefs, pdf preview + browser printing
        $options = $this->get_printing_preferences($options);

        if (! isset($params['attributes'])) {
            $params['attributes'] = array();
        }

        // ****************
        // OUTPUT ACTIONS

        switch ($print_action) {

            case 'print':

                // check the time the print job started
                $print_start = time();

                // send the file to be printed
                $this->output_file_to_printer($output_path, $params['printer'], $params['print_copies'], $params['attributes']);

                // IPP printing has been known to stall and not respond back when printing
                // a document until it hits what looks like a self imposed 60 second timeout
                // if the elapsed time is too long output a warning to the user

                if (time() - $print_start > 50) {
                    $message = '<p><strong>Document printed but with no response.</strong></p><p>Check the printer for your document.</p>';
                } else {
                    $message = '<p>Document successfully printed</p>';
                }

                return $this->build_print_dialog_response(TRUE, array(
                    'message' => $message
                ));

                break;

            case 'quick_output':

                $parts = array();

                if ($options['pdf_browser_printing'] === TRUE) {
                    $parts[1] = '<strong>print</strong> using the button below, alternatively to ';
                    $parts[2] = 'view, save and email';
                } else {
                    $parts[1] = '';
                    $parts[2] = 'view, save, email and print';
                }

                $message = '<p>You can %s<strong>%s</strong> using the <strong>open</strong> button below.</p>';

                $message = sprintf($message, $parts[1], $parts[2]);

                return $this->build_print_dialog_response(TRUE, array(
                    'action' => strtolower($params['printaction']),
                    'location' => $paths['user_http_path'],
                    'filename' => $paths['filename'],
                    'message' => $message
                ), $params);

                break;

            default:
            case 'view':
            case 'save':

                // set default file path
                $file_path = $paths['temp_http_path'];

                // if we're saving, do something different
                if ($print_action === 'save') {

                    // disable print button
                    $params['no_print'] = TRUE;

                    // change file path to user location
                    $file_path = $paths['user_http_path'];
                }

                return $this->build_print_dialog_response(TRUE, array(
                    'action' => strtolower($params['printaction']),
                    'location' => $file_path,
                    'filename' => $paths['filename']
                ), $params);

                break;

            case 'edi':
                // this feature isn't coded yet
                // return $this->outputByEDI();
                break;

            case 'email':
                // stop me from sending emails out to suppliers / customers
                if (FALSE) {
                    return;
                }

                $errors = array();

                $email_params = array(
                    'file_path' => $paths['temp_file_path'], // absolute file path to temp (or other) file
                    'file_name' => $params['filename'], // just the filename we want to label the file as
                    'file_type' => $params['printtype'],
                    'emailtext' => $params['emailtext'],
                    'email' => $params['email'],
                    'subject' => $params['email_subject'],
                    'replyto' => $params['replyto']
                );

                // Reply to address will not be in $params when coming from process_output.
                // For example, when outputting customer statements. Set it from the options instead.
                if (isset($options['replyto'])) {
                    $email_params['replyto'] = $options['replyto'];
                }

                $email = $this->output_file_to_email($email_params, $errors);
                $message = '';

                $message = "<p>Document " . $message . "successfully emailed</p>";

                if (! $email) {
                    $message = "<p>Document NOT successfully emailed</p>";
                    $message .= "<ul>";
                    foreach ($errors as $er) {
                        $message .= "<li>{$er}</li>";
                    }
                    $message .= "</ul>";
                }

                return $this->build_print_dialog_response($email, array(
                    'message' => $message
                ));
                break;
        }
    }

    /**
     * Sanatise an xml string
     *
     * @access public
     * @param string $xml
     * @return string (xml)
     */
    public function sanatise_xml($xml)
    {
        return $this->sanatise($xml);
    }

    /**
     * Sanatise an xsl string
     *
     * @access public
     * @param string $xsl
     * @return string (xsl)
     */
    public function sanatise_xsl($xsl)
    {
        return $this->sanatise($xsl);
    }

    /**
     * Sanatise markup, convert various problematic characters
     *
     * FOP can be picky about what characters it accepts, in most instances
     * for example a single & can prevent a report from generating.
     *
     * @access protected
     * @param string $markup
     * @return string
     */
    protected function sanatise($markup)
    {

        // we need to escape &# to prevent any of our other replace functions from crippling them.
        $markup = str_replace("&#", "^#", $markup);

        // convert all single ampersands
        $markup = str_replace("&amp;", "^#38;", $markup);
        $markup = str_replace("&", "^#38;", $markup);

        // un-escape &#
        $markup = str_replace("^#", "&#", $markup);

        $find_chars = array(
            "£",
            "€",
            "$"
        );
        $replace_chars = array(
            "&#163;",
            "&#8364;",
            "&#36;"
        );

        return str_replace($find_chars, $replace_chars, $markup);
    }

    /**
     * Process an xsl string, replacing custom placeholders with code
     *
     * @access public
     * @param string $xsl
     * @param array $xslVars
     * @return string (xsl)
     */
    public function process_xsl($xsl, $xslVars = array())
    {

        // get all report parts
        $parts = new ReportPartCollection(DataObjectFactory::Factory('ReportPart'));
        $sh = new SearchHandler($parts, FALSE);

        $parts->load($sh);

        // fop doesn't like https connections
        $host = "http://" . $_SERVER["SERVER_NAME"];

        if ($_SERVER['HTTPS'] == on) {
            $port = '';
        } else {
            $port = $_SERVER["SERVER_PORT"];
        }

        // we might have server info, such as host and port in the report part
        $find = array(
            '{HOST}',
            '{PORT}'
        );
        $replace = array(
            $host,
            $port
        );

        // loop through them, add them to the xslVars array
        foreach ($parts->getArray() as $key => $value) {

            $value['value'] = str_replace($find, $replace, $value['value']);

            // set the value to the xsl vars
            $xslVars[$value['name']] = $value['value'];
        }

        // loop through each part of the array and replace and known parts with their values
        foreach ($xslVars as $key => $value) {

            // find both lower and upper case keys
            $find = array(
                "<!--[" . $key . "]-->",
                "<!--[" . strtoupper($key) . "]-->"
            );

            $xsl = str_replace($find, $value, $xsl);
        }

        return $xsl;
    }

    /**
     * Generate xml based on options
     *
     * @access public
     * @param array $options
     * @return string (xml)
     */
    public function generate_xml($options)
    {

        /*
         * Because of the nature of this function (how it's highly customisable and recursive)
         * it will only have one parameter and this is an array of options, much like jQuery
         * this array will merge with a defaults array, thus protecting the function from too
         * little data.
         */

        // set default options
        $defaults = array(
            'model' => array(),
            'extra' => array(),
            'load_relationships' => TRUE,
            'relationship_whitelist' => NULL,
            'first_pass' => TRUE,
            'tabs' => 1,
            'call_model_func' => array()
        );

        // merge passed options with defaults
        $options = array_merge($defaults, $options);

        // convert the contents of options->model to an array if it isn't already
        if (! is_array($options['model'])) {
            $options['model'] = array(
                $options['model']
            );
        }

        // in various parts of this function we only want to output xml if on the first_pass

        // set a few vars
        $rels = array();
        $basetabs = str_repeat("\t", $options['tabs']);

        if ($options['first_pass'] === TRUE) {
            $xml = "<data>\r\n";
        }

        foreach ($options['model'] as $name => $model) {

            /*
             * NOTES
             *
             * Current code is based around the use of a DO, but infact we can use either a DO or a DOC->getcontents,
             * the problem is the current workflow doesn't really work for recursive processing of a DOC, for instance
             * the relationships for each DO of a DOC aren't applied correctly
             */

            // (re)set a few vars
            $rels = array();
            $lineData = array();
            $model_name = get_class($model);

            if (! is_numeric($name)) {
                $element_name = $name;
            } else {
                $element_name = $model_name;
            }

            // get headers
            $headers = $model->getFields();

            // get any relationships (hasOne, hadMany etc)
            // for the sake of simplicity lets only do this on the first model
            // ...simplicity, and to be kind to the memory limit / cpu time out
            if ($options['load_relationships'] === TRUE && $model instanceof DataObject) {
                $rels += $model->getHasMany();
                $rels += $model->getHasOne();
            }

            // construct line
            // are we dealing with a DataObject or a DataObjectCollection?

            if ($model instanceof DataObject) {

                // get the headings for the DO
                // construct line data
                $lineData[] = $this->construct_line($model, $headers);
            } elseif ($model instanceof DataObjectCollection) {

                // remove the string 'Collection' from the model name
                $element_name = substr($element_name, 0, - 10);

                // construct line data
                foreach ($model as $k => $v) {

                    // send each model back up to generate_xml(), recursively build the data
                    // this method has, however, proven to take a little while, and maximum
                    // execution time errors have occured
                    $xml .= $this->generate_xml(array(
                        'model' => $v,
                        'first_pass' => FALSE,
                        'load_relationships' => $options['load_relationships'],
                        'relationship_whitelist' => $options['relationship_whitelist'],
                        'call_model_func' => $options['call_model_func']
                    ));
                }
            }

            /*
             * We would only ever get here if we're dealing with a DataObject, a collection
             * would have been picked up and recursively looped through it's DataObject contents.
             * With this in mine $lineData probably doesn't need to be an nested array
             */

            foreach ($lineData as $line_key => $line_value) {

                // open model based element
                $xml .= $basetabs . "<" . $element_name . ">\r\n";

                // construct basic XML of data
                foreach ($headers as $header => $title) {
                    $xml .= $basetabs . "\t<" . $header . ">" . str_replace([
                        "&",
                        "<"
                    ], [
                        "&amp;",
                        "&#60;"
                    ], $line_value[$header]) . "</" . $header . ">\r\n";
                }

                // if any user specified functions exist for the model call them
                // we should only enter this if we're dealing with a dataobject

                if (! empty($options['call_model_func'][$model_name])) {

                    foreach ($options['call_model_func'][$model_name] as $key => $value) {
                        $xml .= $basetabs . "\t<" . $value . ">" . $options['model'][0]->$value() . "</" . $value . ">\r\n";
                    }
                }

                // loop through the realtionships of the model, recursively converting data to xml
                foreach ($rels as $key => $value) {

                    if (is_null($options['relationship_whitelist']) || (is_array($options['relationship_whitelist']) && in_array($key, $options['relationship_whitelist']))) {

                        /*
                         * Surely the only model to get this far will be a DataObject?
                         * Yes they will you idiot! Rels, not models! D'oh!
                         */

                        if ($model->$key instanceof DataObject) {

                            $xml .= $this->generate_xml(array(
                                'model' => $model->$key,
                                'tabs' => $options['tabs'] + 1,
                                'load_relationships' => FALSE,
                                'first_pass' => FALSE,
                                'call_model_func' => $options['call_model_func']
                            ));
                        } elseif ($model->$key instanceof DataObjectCollection) {

                            foreach ($model->$key as $k => $v) {

                                $xml .= $this->generate_xml(array(
                                    'model' => $v,
                                    'tabs' => $options['tabs'] + 1,
                                    'load_relationships' => FALSE,
                                    'first_pass' => FALSE,
                                    'call_model_func' => $options['call_model_func']
                                ));
                            }
                        }
                    }
                }

                // close model based element
                $xml .= $basetabs . "</" . $element_name . ">\r\n";
            }
        }

        // build extra data, intergrate it with the existing XML
        if (! empty($options['extra'])) {
            $xml .= $basetabs . "<extra>\r\n";
            $xml .= $this->recursive_generate_xml($options['extra'], 2);
            $xml .= $basetabs . "</extra>\r\n";
        }

        if ($options['first_pass'] === TRUE) {
            $xml .= "</data>\r\n";
        }

        // return the processed xml
        return $xml;
    }

    /**
     * Generate csv based on options
     *
     * @access public
     * @param array $params
     * @param array $data
     * @param array $headings
     * @return string (csv)
     */
    public function generate_csv($params, $data, $headings = array())
    {
        $output = '';
        $field_separater = $params['fieldseparater'];
        $text_delimiter = $params['textdelimiter'];

        // if set, output headings
        if (isset($params['fieldnames'])) {

            $line = '';

            foreach ($headings as $heading) {
                $line .= $text_delimiter . addslashes($heading) . $text_delimiter . $field_separater;
            }

            $output .= trim($line, $field_separater) . "\n";
        }

        // loop through data, output lines
        foreach ($data as $row) {

            $line = '';

            foreach ($row as $field) {
                $line .= $text_delimiter . addslashes($field) . $text_delimiter . $field_separater;
            }

            // don't trim... just remove the very last field seperator character
            // we don't rtrim (especially not trim) because it loses empty fields

            $output .= substr($line, 0, (strlen($line) - strlen($field_separater))) . "\n";
        }

        return $output;
    }

    /**
     * Recusively generate xml based on array
     *
     * @access public
     * @param array $options
     * @return string (xml)
     */
    public function recursive_generate_xml($array, $level = 0)
    {

        // ATTN: change the variable ^
        $output = '';
        $tabs = str_repeat("\t", $level);

        foreach ($array as $key => $value) {

            if (! is_numeric($key)) {

                $output .= $tabs . "<" . $key . ">";

                if (is_array($value)) {
                    $output .= "\r\n";
                }
            }

            if (is_array($value)) {
                $output .= "\r\n";
                $output .= $this->recursive_generate_xml($value, $level + 1);
                $output .= "\r\n";
            } else {
                $output .= $value;
            }

            if (! is_numeric($key)) {

                if (is_array($value)) {
                    $output .= $tabs;
                }

                $output .= "</" . $key . ">" . "\r\n";
            }
        }

        return $output;
    }

    /**
     * Construct a line, resolving enums, foriegn values etc
     *
     * @access public
     * @param mixed $data
     * @param array $table
     * @return array
     */
    public function construct_line($data, $table)
    {
        $rowData = array();

        foreach ($table as $key => $value) {

            if ($key == 'paging' || $value == '{PAGENUM}') {
                $rowData[$key] = $this->currentPage;
            } elseif (($data instanceof DataObject) && $data->isField($key)) {

                if ($data->isEnum($key)) {
                    // enumerated fields
                    $field = $data->getField($key);
                    $rowData[$key] = $field->formatted;
                } else {

                    // date, timestamp
                    if (is_object($data->getField($key)) && $data->getField($key)->type == 'date') {
                        $rowData[$key] = is_null($data->$key) ? '' : un_fix_date($data->$key);
                    } elseif (is_object($data->getField($key)) && $data->getField($key)->type == 'timestamp') {
                        $rowData[$key] = is_null($data->$key) ? '' : date(DATE_TIME_FORMAT, strtotime($data->$key));
                    } elseif (is_object($data->getField($key))) {

                        // $rowData[$key]=$data->$key;
                        // The currency symbol stored in the database is not displaying properly.
                        // The database encoding is UTF8 and when displayed in the pdf document
                        // either &pound; is displayed or Â£
                        // $rowData[$key]=utf8_decode($data->getField($key)->formatted);
                        // Need this conversion to get the Euro symbol to print in pdf!!!
                        // Also see EGSpdf for setting the iso-8859-15 Euro character position to pdf Euro

                        // If we encode the line FOP cannot output the document -> http://apps.severndelta.co.uk/dokuwiki/doku.php/encoding_characters_in_xml
                        $rowData[$key] = $data->getFormatted($key, FALSE); // is this causeing problems £ --> #iconv('UTF-8', 'iso-8859-15//TRANSLIT', $data->getFormatted($key, false));

                        // $rowData[$key]=$data->getField($key)->value;
                    } else {
                        $rowData[$key] = $data->getField($key);
                    }
                }
            } elseif ($data instanceof DataObject && method_exists($data, $key)) {

                // the field name in $key is a class method in the $data object
                if (isset($value['value'])) {
                    $rowData[$key] = call_user_func(array(
                        $data,
                        $key
                    ), $value['value']);
                } else {
                    $rowData[$key] = call_user_func(array(
                        $data,
                        $key
                    ));
                }
            } elseif (is_array($data)) {

                // the data is an array
                if ($data[$key] == '{PAGENUM}') {
                    $rowData[$key] = $this->currentPage;
                } else {
                    $rowData[$key] = $data[$key];
                }
            } else {

                // the data is a constant
                if (isset($value['value']) && $value['value'] == '{PAGENUM}') {
                    $value['value'] = $this->currentPage;
                }

                $rowData[$key] = (isset($value['value'])) ? $value['value'] : (isset($value['title']) ? $value['title'] : prettify($key));
            }
        }

        return $rowData;
    }

    /**
     * builds the print dialog response
     *
     * @access protected
     * @param boolean $status
     * @param array $extra
     * @return boolean
     */
    protected function build_print_dialog_response($status, $extra = array(), $params = array())
    {

        // we're returning JSON, so lets tell the browser
        // this is required for $.ajax to be able to intelligently guess what the dataType is
        header('Content-type: application/json');

        // set a few variables
        $options = array();

        if (! empty($params)) {
            $options['filetype'] = $params['printtype'];
        }

        if (! empty($params['paths'])) {
            $options['paths'] = $params['paths'];
        }

        if (! empty($params['refresh_page'])) {
            $options['refresh_page'] = $params['refresh_page'];
        }

        $options['status'] = $status;

        if (is_array($extra) && ! empty($extra)) {
            $options += $extra;
        }

        // we only want to get the pdf preview, pdf browser printing and success html is status is true
        if ($status === TRUE) {

            if ($options['filetype'] === 'pdf') {
                $options = $this->get_printing_preferences($options);
            }

            if (in_array($params['printaction'], array(
                'quick_output',
                'view',
                'save'
            )) && $options['pdf_browser_printing'] === TRUE && ! isset($params['no_print'])) {
                $options['buttons']['print'] = TRUE;
            }

            if (in_array($params['printaction'], array(
                'quick_output',
                'view',
                'save'
            ))) {
                $options['buttons']['open'] = TRUE;
            }

            // make options available to smarty
            $this->view->set('options', $options);

            // get smarty to parse the template but return the html so we can add it to the array
            $options['html'] = $this->view->fetch($this->getTemplateName('print_success'));
        }

        audit(print_r($this->_data, TRUE) . print_r($response, TRUE));

        return json_encode($options);
    }

    /**
     * Decodes a base64 + json array from the original print dialog form
     *
     * @access protected
     * @param string $data
     * @return string
     */
    protected function decode_original_form_data($data)
    {

        /*
         * The data from the first stage of the dialog is not fully available to the secone stage
         * The system attempts to loop though the $this->_data, however this only works for a single
         * level of key / value pairs, and not for more complex nested arrays.
         *
         * This method instead json_encodes the data, then base64_encodes that into a long string.
         * This data is passed from stage one to two and is thus available to the generate function,
         * usually available be passing $this->_data['encoded_query_data'] to this function
         */
        return json_decode(base64_decode($data), TRUE);
    }

    /**
     * Gets the unix path for a users data area based on a given filetype
     *
     * @access public
     * @param string $type
     * @return string
     */
    public function get_filetype_path($type)
    {
        return DATA_USERS_ROOT . EGS_USERNAME . '/' . strtoupper($type) . '/';
    }

    /**
     * Gets the http path for a users data area based on a given filetype
     *
     * @access public
     * @param string $type
     * @return string
     */
    public function get_filetype_link($type)
    {
        return DATA_USERS_URL . EGS_USERNAME . '/' . strtoupper($type) . '/';
    }

    /**
     * Generated the html for the pdf preview
     *
     * @access public
     * @return string (via echo/ajax)
     */
    public function build_pdf_preview()
    {
        $file = $this->_data['filename'];
        $location = $this->_data['location'];
        $page_count = $this->get_pdf_page_count($file);
        $thumbs = $this->generate_pdf_thumbnails($file, 10);
        $thumbs_html = '';
        $output = '';
        $plural = get_plural_string(count($thumbs), 's');
        $title_str = '<li>PDF Preview (%s page%s)</li>';
        $page_str = '<li>Displaying first %s page%s</li>';

        // if page count is not valid, output basic title
        if ($page_count === FALSE) {
            $output .= '<li>PDF Preview</li>';
        } else {

            $output .= sprintf($title_str, $page_count, $plural);
        }

        if (! empty($thumbs)) {

            foreach ($thumbs as $thumbnail) {
                $output .= '<li><a href="' . $location . '"><img src="' . $thumbnail . '" /></a></li>';
            }
        }

        $output .= sprintf($page_str, count($thumbs), $plural);

        echo $output;
        exit();
    }

    /**
     * Gets the pdf page count
     *
     * @param string $file_path
     */
    public function get_pdf_page_count($file_path)
    {

        // we need pdfinfo for this task
        if (HAS_PDFINFO === TRUE) {

            // ATTN: we need to sanatise the file path variable
            $page_count = trim(exec("pdfinfo " . $file_path . " | grep Pages | awk '{print $2}'"));

            if (empty($page_count)) {
                return FALSE;
            }

            return $page_count;
        }

        return FALSE;
    }

    /**
     * Gets the thumbnails from a pdf depending on the limit vs pages
     *
     * @access public
     * @param string $file
     * @param integer $limit
     * @return array
     */
    public function generate_pdf_thumbnails($file, $limit = 2)
    {

        // file exists?

        // is the count numeric and above 0?
        $at_end = FALSE;
        $str_command = "convert %s[%s] -colorspace RGB -scale %sx%s %s";
        $counter = 0;
        $scale = 30;
        $thumbnails = array();
        $timestamp = time();

        while ($counter < $limit && $at_end !== TRUE) {

            $thumb_name = $timestamp . '_' . ($counter) . '.png';
            $thumb_path = $this->get_filetype_path('pdf') . 'thumbs/' . $thumb_name;
            $thumb_link = $this->get_filetype_link('pdf') . 'thumbs/' . $thumb_name;

            $command = sprintf($str_command, $file, $counter, $scale . "%", $scale . "%", $thumb_path);

            // go get the thumnail
            exec($command);

            if (file_exists($thumb_path)) {
                $thumbnails[] = $thumb_link;
            } else {
                $at_end = TRUE;
            }

            $counter ++;
        }

        return $thumbnails;
    }

    /**
     * Gets the printing preferences
     *
     * @access public
     * @return array
     */
    public function get_printing_preferences($options)
    {
        $prefs = array(
            'pdf_preview' => FALSE,
            'pdf_browser_printing' => FALSE
        );

        // the success page structure will depend on pdf-preview and pdf-browser-printing
        // being enabled, go fetch these values from the users preferences

        $userPreferences = UserPreferences::instance();
        $pdf_preview = $userPreferences->getPreferenceValue('pdf-preview', 'shared');
        $pdf_browser_printing = $userPreferences->getPreferenceValue('pdf-browser-printing', 'shared');

        // set pdf_preview + pdf_browser_printing states

        if ((! empty($pdf_preview) && $pdf_preview == 'on') && HAS_CONVERT === TRUE) {
            $prefs['pdf_preview'] = TRUE;
        }

        if (! empty($pdf_browser_printing) && $pdf_browser_printing == 'on') {
            $prefs['pdf_browser_printing'] = TRUE;
        }

        if ($prefs['pdf_preview'] === TRUE) {

            $prefs['pdf_preview_build_link'] = htmlspecialchars_decode(link_to(array(
                'module' => $this->module,
                'controller' => $this->name,
                'action' => 'build_pdf_preview'
            ), FALSE, FALSE));

            $prefs['pdf_preview_location'] = $options['paths']['temp_file_path'];
        }

        // merge prefs back in with options
        $options += $prefs;

        return $options;
    }

    public function get_paths($file_name, $file_type)
    {
        $paths = array();

        // set filenames
        $paths['file_name'] = $file_name . '.' . strtolower($file_type);
        $paths['temp_name'] = mt_rand(000, 999) . '_' . time() . '.' . strtolower($file_type);

        // set various file paths
        $paths['data_path'] = 'data/users/';
        $paths['user_path'] = $paths['data_path'] . EGS_USERNAME . '/';
        $paths['type_path'] = $paths['user_path'] . strtoupper($file_type) . '/';
        $paths['temp_path'] = $paths['user_path'] . 'TMP/';

        // set http / file paths
        $paths['user_http_path'] = SERVER_ROOT . '/' . $paths['type_path'] . $paths['file_name'];
        $paths['temp_http_path'] = SERVER_ROOT . '/' . $paths['temp_path'] . $paths['temp_name'];
        $paths['user_file_path'] = FILE_ROOT . $paths['type_path'] . $paths['file_name'];
        $paths['temp_file_path'] = FILE_ROOT . $paths['temp_path'] . $paths['temp_name'];

        return $paths;
    }

    // ****************
    // OUTPUT FUNCTIONS

    /**
     * Outputs the file to an email address
     *
     * @access public
     * @param array $params
     * @param
     *            array &$errors
     * @return boolean
     */
    public function output_file_to_email($params, &$errors = array())
    {

        // for non-production environment, set in config.php
        if (get_config('DEV_PREVENT_EMAIL') === TRUE) {
            return TRUE;
        }

        $file = $params['file_path'];
        $fname = basename($params['file_name']) . '.' . $params['file_type'];
        $data = file_get_contents($file);
        $user = DataObjectFactory::Factory('user');

        if (! $user->load($_SESSION['username'])) {
            return FALSE;
        }

        if (! empty($params['replyto'])) {
            $contact = $params['replyto'];
        } elseif (! is_null($user->person_id)) {
            $person = DataObjectFactory::Factory('Person');
            $person->load($user->person_id);
            $contact = $person->email->contactmethod;
        }

        if (empty($contact)) {
            $contact = $user->email;
        }

        if (empty($contact)) {

            $errors[] = 'No return email address found - please contact the system administrator';
            return FALSE;
        }

        $from = $replyto = $contact;
        $address_list = array_map('trim', explode(',', $params['email']));
        $mail = new PHPMailer(true);
        try {
            $mail->setFrom($from);
            $mail->addReplyTo($replyto);
            foreach ($address_list as $recipient) {
                $mail->addAddress($recipient);
            }
            $mail->Subject = $params['subject'];
            $mail->Body = $params['emailtext'];
            $mail->addAttachment($file);
            $mail->send();
            return true;
        } catch (Exception $e) {
            $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }

    /**
     * Outputs the file to a printer
     *
     * @access public
     * @param string $file
     * @param string $printer
     */
    public function output_file_to_printer($file, $printer, $copies = 1, $attributes = array())
    {

        // for non-production environment, set in config.php
        if (get_config('DEV_PREVENT_PRINT') === TRUE) {
            return;
        }

        $ipp = new CupsPrintIPP();
        $this->setPrinterLog($ipp);
        $ipp->setPrinterURI($printer);
        $ipp->setData($file);
        $ipp->setCopies($copies);
        foreach ($attributes as $attribute => $value) {
            $ipp->setAttribute($attribute, $value);
        }
        $ipp->printJob("printer");
    }

    // some other functions, perhaps that belong in Controller.php
    function formatAddress($address)
    {

        /* FOP: return the address as an array for easier output */
        $output = array();
        $parts = array(
            "street1",
            "street2",
            "street3",
            "town",
            "county",
            "postcode",
            "country"
        );

        foreach ($parts as $part) {

            if (! is_null($address->$part)) {
                $output[$part] = $address->$part;
            }
        }

        return $output;
    }

    public function getCompany($id = '')
    {
        static $company;

        if (empty($id)) {

            if (empty($company) || $company->id != COMPANY_ID) {

                $company = DataObjectFactory::Factory('Company');
                $company->load(COMPANY_ID);
                $this->company = $company;
            }
        } else {

            if (empty($company) || $company->id != $id) {
                $this->company = DataObjectFactory::Factory('Company');
                $this->company->load($id);
            }
        }

        return $this->company;
    }

    public function getCompanyName()
    {
        return $this->getCompany()->name;
    }

    public function vatnumber($id = '')
    {
        return $this->getCompany($id)->vatnumber;
    }

    public function companynumber($id = '')
    {
        return $this->getCompany($id)->companynumber;
    }

    public function getCompanyAddress($id = '', $name = '', $type = '')
    {
        $company = $this->getCompany($id);

        // might want an option here to support other formats
        // 1) Print address lines including blank lines
        // 2) Print address lines excluding blank lines, spacing x lines after
        // 3) This format - print address lines excluding blank lines

        if ($company->isLoaded()) {
            return $company->getAddress($name = '', $type = '');
        } else {
            return DataObjectFactory::Factory('Address');
        }
    }

    public function getContactDetails($type, $name = '')
    {
        $company = $this->getCompany();

        if ($company->id != COMPANY_ID) {
            return '';
        }

        if ($company->isLoaded()) {
            return $company->getContactDetail($type, $name = '');
        }

        // might want an option here to support other formats
        // 1) Print address lines including blank lines
        // 2) Print address lines excluding blank lines, spacing x lines after
        // 3) This format - print address lines excluding blank lines

        return '';
    }

    public function getCompanyDetails()
    {
        $company = $this->getCompany();

        $details = array();

        $details['tel'] = 'Tel: ' . $this->getContactDetails('T');
        $details['fax'] = 'Fax: ' . $this->getContactDetails('F');
        $details['email'] = 'Email: ' . $this->getContactDetails('E');
        $details['website'] = 'Web Site: ' . $company->website;
        $details['vat_number'] = 'VAT Number: ' . $company->vatnumber;
        $details['company_number'] = 'Company Number: ' . $company->companynumber;
        $details['co_additonal_text1'] = $company->text1;
        $details['co_additonal_text2'] = $company->text2;

        return $details;
    }

    public function getReportsByType($_report_type_id = '')
    {
        // used by ajax to get the Report Definitions List
        if (isset($this->_data['ajax']) && empty($_report_type_id)) {
            $ajax = true;
            if (! empty($this->_data['report_type_id'])) {
                $_report_type_id = $this->_data['report_type_id'];
            }
        } else {
            $ajax = false;
        }

        $list = ReportDefinition::getReportsByType($_report_type_id);
        $defs = [];
        foreach ($list as $key => $report) {
            $defs[$report] = $report;
        }

        if ($ajax) {
            $this->view->set('options', $defs);
            $this->setTemplateName('select_options');
        } else {
            return $defs;
        }
    }
    // ATTN: these need to be moved?
    public function isPrintDialog()
    {
        return (isset($this->_data['action']) && strtolower($this->_data['action']) == 'printdialog');
    }

    public function isPrinting()
    {
        return isset($this->_data['ajax_print']);
    }

    // public?
    public function build_print_resources(DataObjectCollection $collection, $args = array())
    {
        // by this point the collection must already have been loaded

        // create report title
        $report_title = $this->generate_collection_name(FALSE, $collection->title);

        // get the headings from the collection, loop through and remove and id fields
        $coll_headers = array();
        $fields = $collection->getHeadings();

        foreach ($fields as $field => $title) {
            if (substr($field, - 2) != 'id') {
                $coll_headers[$field] = $title;
            }
        }

        // allow extra headers to be added
        if (isset($args['coll_headers'])) {
            $coll_headers = array_merge($coll_headers, $args['coll_headers']);
        }

        $col_widths = '';

        if (isset($this->_data['col_widths'])) {
            $col_widths = $this->_data['col_widths'];
        }

        $measure_fields = array();
        // get the data
        if (isset($_SESSION['printing'][$this->_data['index_key']]['break']['measure_fields'])) {
            $measure_fields = $_SESSION['printing'][$this->_data['index_key']]['break']['measure_fields'];
        }

        $aggregate_fields = array();
        if (isset($_SESSION['printing'][$this->_data['index_key']]['break']['aggregate_fields'])) {
            $aggregate_fields = $_SESSION['printing'][$this->_data['index_key']]['break']['aggregate_fields'];
        }

        if (! empty($measure_fields) || ! empty($aggregate_fields)) {
            $field_formatting = array();
            if (isset($_SESSION['printing'][$this->_data['index_key']]['break']['field_formatting'])) {
                $field_formatting = $_SESSION['printing'][$this->_data['index_key']]['break']['field_formatting'];
            }

            $default_options = array(
                'normal_field_label' => '',
                'normal_display_field' => TRUE,
                'normal_break_on' => FALSE,
                'normal_method' => 'dont_total',
                'normal_total' => 'none', // default on the report level
                'normal_enable_formatting' => FALSE,
                'normal_decimal_places' => 0,
                'normal_red_negative_numbers' => FALSE,
                'normal_thousands_seperator' => FALSE
            );

            foreach ($fields as $field => $title) {
                foreach ($default_options as $option => $value) {
                    if (! isset($field_formatting[$field][$option])) {
                        if ($option == 'normal_break_on' && isset($measure_fields[$field])) {
                            $field_formatting[$field][$option] = TRUE;
                        } else {
                            $field_formatting[$field][$option] = $value;
                        }
                    }
                }
            }

            $formatting_options = array_merge($default_options, $field_formatting);
            $arr_data = $this->generate_subtotals($collection, $coll_headers, $measure_fields, $aggregate_fields, array_keys($coll_headers), $formatting_options);
            $coll_data = $arr_data['data_arr'];
            $subtotal_keys = $arr_data['sub_total_keys'];
        } else {
            if ($collection instanceof DataObjectCollection || is_array($collection)) {

                foreach ($collection as $detailRow) {
                    $printDetail[] = $this->construct_line($detailRow, $coll_headers);
                }

                $coll_data = $printDetail;
                $formatting_options = array();
                $subtotal_keys = array();
            }
        }

        // build the custom XSL
        // $xsl = $this->build_custom_xsl($collection, 'CustomReport', $report_title, $coll_headers, $col_widths, $formatting_options);
        $xsl = $this->build_custom_xsl($collection, $this->_data['print']['report'], $report_title, $coll_headers, $col_widths, $formatting_options);

        if ($xsl === FALSE) {
            return FALSE;
        }

        // construct basic XML of data
        $xml = $this->build_custom_xml($coll_data, $coll_headers, $formatting_options, $subtotal_keys);

        return array(
            'xml' => $xml,
            'xsl' => $xsl
        );
    }

    public function build_custom_xml($data, $headings, $options = array(), $sub_total_keys = array())
    {

        // construct basic XML of data
        $xml = "<data>\r\n";

        foreach ($data as $key => $row) {

            // if row is a subtotal, construct an appropriate attribute
            $row_class = '';

            if (isset($sub_total_keys[$key])) {
                $row_class = 'sub_total="true"';
            }

            // build the xml
            // cannot utalise all of the output functions as we need to do some specific stuff
            $xml .= "\t" . "<record " . $row_class . ">" . "\n";

            foreach ($row as $field => $value) {
                // less-than causes issues in XML
                $value = str_replace('<', '&#60;', $value);
                $cell_class = array();

                if (! empty($headings[$field])) {
                    $cell_class[] = 'tag="' . $headings[$field] . '"';
                } else {
                    $cell_class[] = 'tag="' . prettify($field) . '"';
                }

                if (isset($options[$field]['normal_red_negative_numbers']) && $options[$field]['normal_red_negative_numbers'] == "true" && $value < 0) {
                    $cell_class[] = 'negative_number="true"';
                }

                if ($options[$field]['normal_enable_formatting'] === 'true') {

                    if (isset($options[$field]['normal_justify'])) {
                        $cell_class[] = 'text-align="' . $options[$field]['normal_justify'] . '"';
                    }
                }

                $xml .= "\t\t" . "<" . $field . " " . implode(' ', $cell_class) . ">" . $value . "</" . $field . ">" . "\n";
            }

            $xml .= "\t</record>\r\n";
        }

        $xml .= "</data>\r\n";

        return $xml;
    }

    public function build_custom_xsl($collection, $definition_name, $title, $col_headers, $col_widths, $options = array())
    {
        // $collection is not used: why do we need it?
        // get the XSL
        $xsl = ReportDefinition::getDefinition($definition_name)->definition;

        if (! empty($xsl)) {

            // define / generate the various xsl vars that are to be replaced

            $xslVars['REPORT_TITLE'] = $title;

            $column_definitions = '<fo:table-column column-width="proportional-column-width(%s)"/>';

            $column_headings = '<fo:table-cell padding="1mm" border-width="1pt" border-style="solid" text-align="%s" >' . "\r\n";
            $column_headings .= '    <fo:block >%s</fo:block>' . "\r\n";
            $column_headings .= '</fo:table-cell>';

            $row_cells = '<fo:table-cell padding="1mm" border-style="solid" border-width="1pt" >' . "\r\n";
            $row_cells .= '    <!-- check if we\'re dealing with a total row -->' . "\r\n";
            $row_cells .= '    <xsl:if test="%s/@negative_number=\'true\'">' . "\r\n";
            $row_cells .= '        <xsl:attribute name="color">red</xsl:attribute>' . "\r\n";
            $row_cells .= '    </xsl:if>' . "\r\n";
            $row_cells .= '    <xsl:if test="%s/@text-align=\'right\'">' . "\r\n";
            $row_cells .= '        <xsl:attribute name="text-align">right</xsl:attribute>' . "\r\n";
            $row_cells .= '    </xsl:if>' . "\r\n";
            $row_cells .= '    <fo:block>' . "\r\n";
            $row_cells .= '        <xsl:value-of select="%s"/>' . "\r\n";
            $row_cells .= '    </fo:block>' . "\r\n";
            $row_cells .= '</fo:table-cell>';

            $column_widths = array();

            // echo 'getMinColWidths '.microtime(TRUE).'<pre>'.print_r($collection->getMinColWidths($col_headers), true).'</pre>'.microtime(TRUE).'<br>';

            if (isset($this->_data['col_widths']) && ! empty($this->_data['col_widths'])) {

                // parse the column widths into an array
                $column_widths = $this->parse_column_widths($this->_data['col_widths']);

                // echo 'parse_column_widths<pre>'.print_r($column_widths, true).'</pre><br>';

                // calculate an average width, just in case a column width isn't specified
                // an average of the specified ones should be fine

                $total_widths = 0;
                $average_width = 50; // failsafe

                foreach ($column_widths as $width) {
                    $total_widths += $width;
                }

                $failsafe_width = $total_widths / count($column_widths);
            } else {
                $failsafe_width = 50;
            }

            foreach ($col_headers as $key => $value) {

                if (isset($column_widths[$key])) {
                    $column_width = $column_widths[$key];
                } else {
                    $column_width = $failsafe_width;
                }

                $text_align = "left";

                if ($options[$key]['normal_enable_formatting'] == 'true' && isset($options[$key]['normal_justify'])) {
                    $text_align = $options[$key]['normal_justify'];
                }

                $xslVars['REPORT_COLUMN_DEFINITIONS'] .= sprintf($column_definitions, $column_width) . "\r\n";
                $xslVars['REPORT_COLUMN_HEADINGS'] .= sprintf($column_headings, $text_align, $value) . "\r\n";
                $xslVars['REPORT_ROW_CELLS'] .= sprintf($row_cells, $key, $key, $key) . "\r\n";
            }

            $xslVars['COLLECTION_NAME'] = $collection->getModelName();
            // process xsl
            $xsl = $this->process_xsl($xsl, $xslVars);
        } else {
            $xsl = FALSE;
        }

        return $xsl;
    }

    public function generate_collection_name($filename_safe = FALSE, $title = '')
    {

        // we'll be outputting different strings if it's a filename or not
        if ($filename_safe) {

            // generate filename, remove preceeding _ from title, add date format
            $report_title = ltrim($this->getPageName('', ''), '_') . ' ' . date(DATE_TIME_FORMAT);

            // find / replace various characters
            $find = array(
                '/',
                ' '
            );
            $replace = array(
                '-',
                '_'
            );
            $report_title = strtolower(str_replace($find, $replace, $report_title));

            // make the string safe for a filename
            $report_title = preg_replace('/[^a-zA-Z0-9\s_-]/', '', $report_title);
        } else {

            // create report title
            $report_title = 'List of ' . (empty($title) ? prettify($this->getPageName('', '')) : $title) . ', Printed on ' . date(DATE_TIME_FORMAT);
        }

        return $report_title;
    }

    public function parse_column_widths($widths)
    {
        $column_widths = array();

        if (isset($this->_data['col_widths'])) {

            // trim the last delimiter, just to ensure we don't get a blank item in the array
            $temp_widths = trim($this->_data['col_widths'], '|');
            $temp_widths = explode('|', $temp_widths);

            foreach ($temp_widths as $key => $value) {

                $field = explode('=', $value);
                $column_widths[$field[0]] = $field[1];
            }
        }

        return $column_widths;
    }

    protected function generate_subtotals($collection, $headings, $measure_fields, $aggregate_fields, $heading_keys, $field_formatting = array())
    {

        /*
         * NOTE: througout this function we deal with arrays that may only have
         * a key, or where the value is irrevelent to the context. Therefore we
         * use the variable $blank to hold this useless value variable, allowing
         * the key to remain the key.
         */

        // turn the measure fields around
        $reverse_measure_fields = array_reverse($measure_fields, TRUE);

        // strip the value from the measure fields

        foreach ($measure_fields as $key => $value) {
            $measure_fields[$key] = '';
        }

        // create a total levels array
        $total_levels = $reverse_measure_fields;
        unset($total_levels['result']);

        // loop through the field formatting, removing the current item if
        // it doesn't exist as an aggregate field

        // ATTN: this is disabled, might enable to prevent formatting measures for example

        // foreach ($field_formatting as $key=>$value) {
        // if (empty($value['normal_method']) || $value['normal_method']=='dont_total') {
        // unset($field_formatting[$key]);
        // }
        // }

        // set a few vars
        $data_arr = array();
        $sub_total_keys = array();
        $total_fields = array();
        $row_counter = 0;
        $col_counter = 0;
        $counter = 0;

        // don't get the collection headings... these will include filter fields
        // that we may not want to display
        // $fields=$collection->getHeadings();

        $fields = $headings;

        if ($collection->num_records > 0) {

            foreach ($collection as $model) {

                // set break level
                $break = '';

                // check for a break level

                // ATTN: should this be key=>value?!
                foreach ($fields as $key => $fieldname) {

                    if ($break === '' && isset($measure_fields[$key])) {

                        if ($model->$key === '') {
                            $model_value = 'None';
                        } else {
                            $model_value = $model->$key;
                        }

                        if ($measure_fields[$key] != '' && $measure_fields[$key] != $model_value) {
                            $break = $key;
                        }
                    }
                }

                if ($break != '') {

                    // break found so output break totals
                    $previous_measure = '';

                    foreach ($reverse_measure_fields as $measure_name => $blank) {

                        // Roll Up totals from lower levels
                        if ($previous_measure != '') {

                            foreach ($aggregate_fields as $aggregate_name => $blank) {
                                $key = $aggregate_name . $previous_measure;
                                $previous_total = $total_fields[$key];
                                $total_fields[$key] = 0;
                                $key = $aggregate_name . $measure_name;
                                $new_total = $total_fields[$key] + $previous_total;
                                $total_fields[$key] = $new_total;
                            }
                        }

                        // output break on total row
                        if ($break != '' && ! empty($aggregate_fields)) {

                            // START ROW

                            foreach ($fields as $key => $fieldname) {

                                if ($measure_name == $key) {
                                    $sub_total_keys[$row_counter] = true;
                                }

                                // OUTPUT THE TOTAL?

                                $total_level = $field_formatting[$key]['normal_total'];

                                $display_total_level = FALSE;

                                if (! in_array($total_level, array(
                                    "false",
                                    "none"
                                ))) {

                                    if (isset($aggregate_fields[$key]) && (($total_level === 'report' || $total_level === TRUE) || ($measure_name !== 'report' && $total_levels[$total_level] <= $total_levels[$measure_name]))) {
                                        $display_total_level = TRUE;
                                    }
                                }

                                // START CELL
                                if ($measure_name == $key) {

                                    if ($measure_fields[$key] == 'None') {
                                        $data_arr[$row_counter][$heading_keys[$col_counter]] = 'Total';
                                    } else {
                                        $data_arr[$row_counter][$heading_keys[$col_counter]] = 'Total ' . $measure_fields[$key];
                                    }
                                } elseif (isset($aggregate_fields[$key]) && $display_total_level === TRUE) {

                                    if (! empty($field_formatting[$key])) {

                                        $formatted_number = $this->reporting_number_format(array(
                                            'number' => $total_fields[$key . $measure_name],
                                            'options' => $field_formatting[$key]
                                        ));

                                        $data_arr[$row_counter][$heading_keys[$col_counter]] = '' . $formatted_number;
                                    } else {
                                        $data_arr[$row_counter][$heading_keys[$col_counter]] = '' . $total_fields[$key . $measure_name];
                                    }
                                } else {
                                    $data_arr[$row_counter][$heading_keys[$col_counter]] = '';
                                }

                                // END CELL
                                $col_counter ++;
                            }

                            // END ROW
                            $row_counter ++;
                            $col_counter = 0;

                            $previous_measure = $measure_name;
                        } else {
                            $previous_measure = '';
                        }

                        if ($break == $measure_name) {
                            // At the break level so stop here
                            $break = '';
                        }
                    }
                }
                // Now output the detail line * }
                // START ROW
                $break = FALSE;

                foreach ($fields as $field_slug => $fieldname) {

                    if (isset($measure_fields[$field_slug])) {
                        $measure_field = $field_slug;
                    } else {

                        // Add aggregate value to lowest break level total
                        if (isset($aggregate_fields[$field_slug])) {
                            $key = $field_slug . $measure_field;
                            $new_total = $total_fields[$key] + $model->$field_slug;
                            $total_fields[$key] = $new_total;
                        }
                    }

                    if (isset($measure_fields[$field_slug]) && $measure_fields[$field_slug] != $model->$field_slug) {
                        $break = TRUE;
                    }

                    // START CELL

                    // Print the field value if it is not a break field or break has occurred at this or a higher level
                    if ($model->isEnum($field_slug)) {
                        $data_arr[$row_counter][$heading_keys[$col_counter]] = $model->getFormatted($field_slug);
                    } else {

                        if (! empty($field_formatting[$field_slug])) {

                            $formatted_number = $this->reporting_number_format(array(
                                'number' => $model->getFormatted($field_slug),
                                'options' => $field_formatting[$field_slug]
                            ));
                            $data_arr[$row_counter][$heading_keys[$col_counter]] = '' . $formatted_number;
                        } else {
                            $data_arr[$row_counter][$heading_keys[$col_counter]] = '' . $model->getFormatted($field_slug);
                        }
                    }

                    // END CELL
                    $col_counter ++;

                    if (isset($measure_fields[$field_slug])) {

                        if ($model->$field_slug == '') {
                            $measure_fields[$field_slug] = 'None';
                        } else {
                            $measure_fields[$field_slug] = $model->$field_slug;
                        }
                    }
                }

                // END ROW
                $row_counter ++;
                $col_counter = 0;
            } // this is just a closing foreach...

            // Now force break on report and output final totals

            $previous_measure = '';

            if (! empty($aggregate_fields)) {

                foreach ($reverse_measure_fields as $measure_name => $blank) {

                    // keep a status to check if a total row has values
                    // if it doesn't have any at the end, we don't output the line

                    $total_row_has_values = FALSE;

                    // Roll Up totals from lower levels
                    if ($previous_measure != '') {

                        foreach ($aggregate_fields as $aggregate_name => $blank) {
                            $key = $aggregate_name . $previous_measure;
                            $previous_total = $total_fields[$key];
                            $key = $aggregate_name . $measure_name;
                            $new_total = $total_fields[$key] + $previous_total;
                            $total_fields[$key] = $new_total;
                        }
                    }

                    // START ROW

                    $total_row_counter = 0;

                    foreach ($fields as $field_slug => $fieldname) {

                        $total_row_counter ++;

                        // OUTPUT THE TOTAL?

                        $display_total_level = FALSE;
                        $total_level = $field_formatting[$field_slug]['normal_total'];
                        // if not set, default to the overall 'normal_total' setting
                        if (empty($total_level)) {
                            $total_level = $field_formatting['normal_total'];
                        }

                        if (! in_array($total_level, array(
                            "false",
                            "none"
                        ))) {

                            // the structure of this if statement is horrible
                            if (isset($aggregate_fields[$field_slug]) && (($total_level === 'report' || $total_level === TRUE) || ($measure_name !== 'report' && $total_levels[$total_level] <= $total_levels[$measure_name]))) {
                                $display_total_level = TRUE;
                            }
                        }

                        if ($measure_name == $field_slug || $measure_name == 'report') {
                            $sub_total_keys[$row_counter] = TRUE;
                        }

                        // START CELL
                        if ($measure_name == 'report' && $total_row_counter == 1) {
                            $data_arr[$row_counter][$heading_keys[$col_counter]] = 'Report Total';
                        } elseif ($measure_name == $field_slug) {
                            $data_arr[$row_counter][$heading_keys[$col_counter]] = 'Total ' . $measure_fields[$field_slug];
                        } elseif (isset($aggregate_fields[$field_slug]) && $display_total_level !== FALSE) {

                            // we have a value!
                            $total_row_has_values = TRUE;

                            $key = $field_slug . $measure_name;

                            $total_value = 0;

                            // the key variable works fine if the field has a break on... but if there is no break
                            // we're stuck without a total value, in that instance we would use just the field slug
                            // to get the value

                            if (isset($total_fields[$key])) {
                                $total_value = $total_fields[$key];
                            } elseif (isset($total_fields[$field_slug])) {
                                $total_value = $total_fields[$field_slug];
                            }

                            if (isset($field_formatting[$field_slug])) {

                                $formatted_number = $this->reporting_number_format(array(
                                    'number' => $total_value,
                                    'options' => $field_formatting[$field_slug]
                                ));

                                $data_arr[$row_counter][$heading_keys[$col_counter]] = '' . $formatted_number;
                            } else {
                                $data_arr[$row_counter][$heading_keys[$col_counter]] = '' . $total_value;
                            }
                        } else {
                            $data_arr[$row_counter][$heading_keys[$col_counter]] = '';
                        }

                        // END CELL
                        $col_counter ++;
                    }
                    // END ROW

                    // a quick check to see if we should keep this total line
                    if ($total_row_has_values === FALSE) {
                        unset($data_arr[$row_counter]);
                        unset($sub_total_keys[$row_counter]);
                    }

                    $row_counter ++;
                    $col_counter = 0;
                    $previous_measure = $measure_name;
                }
            }
        } else {
            return FALSE;
        }

        return array(
            'data_arr' => $data_arr,
            'sub_total_keys' => $sub_total_keys
        );
    }

    protected function reporting_number_format($params)
    {
        if ($params['options']['normal_enable_formatting']) {

            // set a few vars
            $class = array();

            $decimals = 2;

            if (isset($params['options']['normal_decimal_places'])) {
                $decimals = $params['options']['normal_decimal_places'];
            }

            $dec_point = ".";

            $thousands_sep = "";

            if (isset($params['options']['normal_thousands_seperator']) && $params['options']['normal_thousands_seperator'] == "true") {
                $thousands_sep = ",";
            }

            if (isset($params['options']['normal_justify'])) {
                $class[] = 'justify-' . $params['options']['normal_justify'];
            }

            if (isset($params['options']['normal_red_negative_numbers']) && $params['options']['normal_red_negative_numbers'] == "true") {
                $red_negative_numbers = TRUE;
            }

            if (is_numeric($params['number'])) {

                // we don't want to apply the nagative numbers check if we're printing... this needs to be done elsewhere
                if ($params['number'] < 0 && $red_negative_numbers == "true" && ! isset($this->_data['ajax_print'])) {
                    $class[] = 'red';
                    // $output=number_format($params['number'],$decimals,$dec_point,$thousands_sep);
                }

                $output = number_format($params['number'], $decimals, $dec_point, $thousands_sep);
            } else {

                $output = $params['number'];
            }

            // if we're not ajax printing, wrap the output in a span element with class
            if (! isset($this->_data['ajax_print'])) {
                $output = '<span class="' . implode(" ", $class) . '">' . $output . '</span>';
            }
        } else {
            // no formatting, just output the original number
            $output = $params['number'];
        }

        return $output;
    }

    protected function output_details_sidebar($sidebar, $params = array())
    {
        $actions = array();

        $actions['output_detail'] = array(
            'link' => array_merge(array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'printaction' => 'printDocument',
                'data_object' => $this->modeltype
            ), $params),
            'tag' => 'output_detail'
        );

        $sidebar->addList('Reports', $actions);
    }

    // ****************
    // ALIAS FUNCTIONS

    // These functions exist because I felt the naming conventions previously used were wrong
    // I wanted to try and get the naming conventions back to a respectable state but didn't
    // want to risk old code being left out. So below are legacy functions pointing to new ones,
    // these should be removed when grepping their names returns zero hits (excluding these instances)
    // Should probably throw a PHP warning/notice here, just to nag us
    // oldName » new_name

    // ATTN: grep these functions please

    // returnResponse » build_print_dialog_response
    protected function returnResponse($status, $extra = array())
    {

        // trigger deprecated error
        trigger_error("Function returnResponse() is deprecated, please us build_print_dialog_response()", E_USER_DEPRECATED);

        return $this->build_print_dialog_response($status, $extra);
    }

    // constructOutput » generate_output
    public function constructOutput($params, $options)
    {

        // trigger deprecated error
        trigger_error("Function constructOutput() is deprecated, please us generate_output()", E_USER_DEPRECATED);

        return $this->generate_output($params, $options);
    }

    // generateXML » generate_xml
    public function generateXML($options)
    {

        // trigger deprecated error
        trigger_error("Function generateXML() is deprecated, please us generate_xml()", E_USER_DEPRECATED);

        return $this->generate_xml($options);
    }

    private function setPrinterLog($ipp)
    {
        $ipp_log_path = get_config('IPP_LOG_PATH');
        $ipp_log_type = get_config('IPP_LOG_TYPE');
        $ipp_log_level = get_config('IPP_LOG_LEVEL');

        if (! empty($ipp_log_path) && empty($ipp_log_type)) {
            $ipp_log_type = (strpos($ipp_log_path, '@') ? 'e-mail' : 'file');
        }

        $ipp->setLog($ipp_log_path, $ipp_log_type, $ipp_log_level);
    }
}

// end of PrintController.php
