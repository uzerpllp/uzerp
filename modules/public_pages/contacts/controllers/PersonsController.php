<?php

/**
 *	Persons Controller
 *
 *  @package contacts
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class PersonsController extends printController
{
    protected $version = '$Revision: 1.43 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Person');

        $this->uses($this->_templateobject);
    }

    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $s_data = [];

        $this->setSearch('PeopleSearch', 'useDefault', $s_data);

        $this->view->set('clickaction', 'view');

        $people = new PersonCollection($this->_templateobject);

        $sh = $this->setSearchHandler($people);

        $systemCompany = DataObjectFactory::Factory('Company');
        $systemCompany->load(COMPANY_ID);

        $_company_ids = $systemCompany->getSystemRelatedCompanies([
            $systemCompany->id => $systemCompany->getIdentifierValue(),
        ]);

        // Exclude people attached to system Company accounts but include people with no company
        $cc = new ConstraintChain();
        $cc->add(new Constraint('company_id', 'NOT IN', '(' . implode(',', array_keys($_company_ids)) . ')'));
        $cc->add(new Constraint('company_id', 'IS', 'NULL'), 'OR');

        $sh->addConstraint($cc);

        parent::index($people, $sh);

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('Actions', CompanysController::$nav_list);
        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function _new()
    {
        parent::_new();

        // Get the Person Object
        $person = $this->_uses[$this->modeltype];

        // get the default/current selected company
        $company_id = '';

        $addresses = [
            '' => 'Enter new address',
        ];

        $_person_id = '';

        if ($person->isLoaded()) {
            $company_id = $person->company_id;

            $pic = DataObjectFactory::Factory('PeopleInCategories');
            $selected = $pic->getCategoryID($person->{$person->idField});
            $this->view->set('selected_categories', $selected);

            $_person_id = $person->{$person->idField};
            $company_id = $person->company_id;
        } elseif (isset($this->_data['company_id'])) {
            $company_id = $this->_data['company_id'];
        } elseif (isset($this->_data[$this->modeltype]['company_id'])) {
            $company_id = $this->_data[$this->modeltype]['company_id'];
        }

        $addresses = $addresses + $this->getAddresses($company_id, $_person_id);
        $this->view->set('addresses', $addresses);

        $categories = DataObjectFactory::Factory('Contactcategory');
        $this->view->set('contact_categories', $categories->getPersonCategories());

        $address = DataObjectFactory::Factory('address');

        $company = DataObjectFactory::Factory('Company');

        if (! empty($company_id)) {
            $company->load($company_id);
            $this->view->set('company', $company->name);
            $this->_data['company_id'] = $company_id;
            $this->view->set('reports_to', $this->getAllByCompany($company_id));
            $this->view->set('phone', $company->phone->contactmethod);
            $this->view->set('mobile', $company->mobile->contactmethod);
            $this->view->set('fax', $company->fax->contactmethod);
            $this->view->set('email', $company->email->contactmethod);
        } else {
            $this->view->set('company', '');
            $this->view->set('reports_to', $this->getAllByCompany(''));
            $this->view->set('phone', DataObjectFactory::Factory('contactmethod'));
            $this->view->set('mobile', DataObjectFactory::Factory('contactmethod'));
            $this->view->set('fax', DataObjectFactory::Factory('contactmethod'));
            $this->view->set('email', DataObjectFactory::Factory('contactmethod'));
            $this->view->set('address', DataObjectFactory::Factory('address'));
        }

        if ($person->isLoaded()) {
            $address->{$address->idField} = $person->main_address->address_id;
        }

        if ($person->phone->contactmethod->isLoaded()) {
            $this->view->set('phone', $person->phone->contactmethod);
        }

        if ($person->mobile->contactmethod->isLoaded()) {
            $this->view->set('mobile', $person->mobile->contactmethod);
        }

        if ($person->fax->contactmethod->isLoaded()) {
            $this->view->set('fax', $person->fax->contactmethod);
        }

        if ($person->email->contactmethod->isLoaded()) {
            $this->view->set('email', $person->email->contactmethod);
        }

        $this->view->set('address', $address);

        if (isset($this->_data['dialog'])) {
            // Displaying a dialog to add a person, so url path for the
            // modules JS file to the view.
            $js = getModuleJS($this->module);
            $this->view->set('modulejs', $js);
        }
    }

    public function view()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $person = $this->_uses[$this->modeltype];
        $person_id = $person->{$person->idField};
        $party_id = $person->party_id;

        $company = DataObjectFactory::Factory('Company');
        $slcustomer = DataObjectFactory::Factory('SLCustomer');

        if ($person->isLoaded()) {
            $company->load($person->company_id);
            $slcustomer->loadBy('company_id', $person->company_id);
        }

        if (! $person->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to view this person.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $sidebar = new SidebarController($this->view);

        // Need loose coupling method - use person categories?
        $employee = DataObjectFactory::Factory('Employee');
        $employee->loadBy('person_id', $person_id);

        if ($employee->isLoaded()) {
            $sidebar->addList('currently_viewing', [
                'view' => [
                    'tag' => 'view ' . $person->fullname,
                    'link' => [
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'view',
                        'id' => $person_id,
                    ],
                ],
            ]);
        } else {
            $sidebar->addList(
                'currently_viewing',
                [
                    $person->fullname => [
                        'tag' => $person->fullname,
                        'link' => [
                            'module' => 'contacts',
                            'controller' => 'persons',
                            'action' => 'view',
                            'id' => $person_id,
                        ],
                    ],
                    'edit' => [
                        'tag' => 'Edit',
                        'link' => [
                            'module' => 'contacts',
                            'controller' => 'persons',
                            'action' => 'edit',
                            'id' => $person_id,
                        ],
                    ],
                    'delete' => [
                        'tag' => 'Delete',
                        'link' => [
                            'module' => 'contacts',
                            'controller' => 'persons',
                            'action' => 'delete',
                            'id' => $person_id,
                        ],
                        'class' => 'confirm',
                        'data_attr' => [
                            'data_uz-confirm-message' => "Delete {$person->fullname}?|This will also delete associated contact and CRM records. It cannot be undone.",
                            'data_uz-action-id' => $person_id,
                        ],
                    ],
                ]
            );
        }

        $items = [];
        $ao = AccessObject::Instance();

        if ($ao->hasPermission('crm')) {
            $items += [
                'opportunities' => [
                    'tag' => 'Opportunities',
                    'link' => [
                        'module' => 'crm',
                        'controller' => 'opportunitys',
                        'action' => 'viewperson',
                        'person_id' => $person_id,
                    ],
                    'new' => [
                        'module' => 'crm',
                        'controller' => 'opportunitys',
                        'action' => 'new',
                        'person_id' => $person_id,
                    ],
                ],
                'activities' => [
                    'tag' => 'Activities',
                    'link' => [
                        'module' => 'crm',
                        'controller' => 'activitys',
                        'action' => 'viewperson',
                        'person_id' => $person_id,
                    ],
                    'new' => [
                        'module' => 'crm',
                        'controller' => 'activitys',
                        'action' => 'new',
                        'person_id' => $person_id,
                    ],
                ],
            ];
        }

        if ($ao->hasPermission('ticketing')) {
            $items += [
                'tickets' => [
                    'tag' => 'Tickets',
                    'link' => [
                        'module' => 'ticketing',
                        'controller' => 'tickets',
                        'action' => 'viewcompany',
                        'originator_person_id' => $person_id,
                    ],
                    'new' => [
                        'module' => 'ticketing',
                        'controller' => 'tickets',
                        'action' => 'new',
                        'originator_person_id' => $person_id,
                    ],
                ],
            ];
        }

        if (isModuleAdmin('projects')) {
            $items += [
                'resource_template' => [
                    'tag' => 'Resource Template',
                    'link' => [
                        'module' => 'projects',
                        'controller' => 'resourcetemplate',
                        'action' => 'viewperson',
                        'person_id' => $person_id,
                    ],
                    'new' => [
                        'module' => 'projects',
                        'controller' => 'resourcetemplate',
                        'action' => 'new',
                        'person_id' => $person_id,
                    ],
                ],
            ];
        }

        $items += [
            'spacer',
            'notes' => [
                'tag' => 'Notes',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'partynotes',
                    'action' => 'viewperson',
                    'party_id' => $party_id,
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'partynotes',
                    'action' => 'new',
                    'party_id' => $party_id,
                ],
            ],
            'spacer',
            'attachments' => [
                'tag' => 'Attachments',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'personattachments',
                    'action' => 'index',
                    'person_id' => $person_id,
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'personattachments',
                    'action' => 'new',
                    'data_model' => 'person',
                    'entity_id' => $person_id,
                ],
            ],
            'spacer',
            'addresses' => [
                'tag' => 'Addresses',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'partyaddresss',
                    'action' => 'viewperson',
                    'party_id' => $party_id,
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'partyaddresss',
                    'action' => 'new',
                    'party_id' => $party_id,
                ],
            ],
            'spacer',
            'phone' => [
                'tag' => 'Phone',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'viewperson',
                    'party_id' => $party_id,
                    'type' => 'T',
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'new',
                    'party_id' => $party_id,
                    'type' => 'T',
                ],
            ],
            'mobile' => [
                'tag' => 'Mobile',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'viewperson',
                    'party_id' => $party_id,
                    'type' => 'M',
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'new',
                    'party_id' => $party_id,
                    'type' => 'M',
                ],
            ],
            'fax' => [
                'tag' => 'Fax',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'viewperson',
                    'party_id' => $party_id,
                    'type' => 'F',
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'new',
                    'party_id' => $party_id,
                    'type' => 'F',
                ],
            ],
            'email' => [
                'tag' => 'Email',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'viewperson',
                    'party_id' => $party_id,
                    'type' => 'E',
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'partycontactmethods',
                    'action' => 'new',
                    'party_id' => $party_id,
                    'type' => 'E',
                ],
            ],
            'spacer',
            'meetings' => [
                'tag' => 'Meetings',
                'link' => [
                    'module' => 'calendar',
                    'controller' => 'calendarevents',
                    'action' => 'viewperson',
                    'person_id' => $person_id,
                ],
                'new' => [
                    'module' => 'calendar',
                    'controller' => 'calendarevents',
                    'action' => 'new',
                    'person_id' => $person_id,
                ],
            ],
            'calls' => [
                'tag' => 'Calls',
                'link' => [
                    'module' => 'contacts',
                    'controller' => 'loggedcalls',
                    'action' => 'viewperson',
                    'person_id' => $person_id,
                ],
                'new' => [
                    'module' => 'contacts',
                    'controller' => 'loggedcalls',
                    'action' => 'new',
                    'person_id' => $person_id,
                ],
            ],
        ];

        if ($slcustomer->isLoaded()) {
            $items += [
                'sorders' => [
                    'tag' => 'Sales Orders/Quotes',
                    'link' => [
                        'module' => 'sales_order',
                        'controller' => 'sorders',
                        'action' => 'viewperson',
                        'person_id' => $person_id,
                    ],
                    'new' => [
                        'module' => 'sales_order',
                        'controller' => 'sorders',
                        'action' => 'new',
                        'person_id' => $person_id,
                        'slmaster_id' => $slcustomer->id,
                    ],
                ],
            ];
        }

        $sidebar->addList('related_items', $items);

        $category = DataObjectFactory::Factory('peopleInCategories');
        $this->view->set('categories', implode(',', $category->getCategorynames($person_id)));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $category = DataObjectFactory::Factory('PeopleInCategories');
        $this->view->set('categories', implode(',', $category->getCategorynames($person_id)));

        if ($person instanceof Person) {
            $pl = new PreferencePageList('recently_viewed_people' . EGS_COMPANY_ID);
            $pl->addPage(new Page([
                'module' => 'contacts',
                'controller' => 'persons',
                'action' => 'view',
                'id' => $person_id,
            ], 'person', $person->firstname . ' ' . $person->surname));
            $pl->save();
        }
    }

    /**
     * View and search company related people
     *
     * Called from the related items sidebar when viewing a company
     */
    public function viewcompany()
    {
        $s_data = [];
        if (isset($this->_data['company_id'])) {
            $s_data['company_id'] = $this->_data['company_id'];
        }

        // Initially and on clear, show ALL people
        $s_data['end_date'] = '';

        $this->setSearch('PeopleSearch', 'useDefault', $s_data);
        $this->view->set('clickaction', 'view');

        $this->_templateobject->setDefaultDisplayFields(
            [
                'name' => 'Name',
                'end_date',
                'jobtitle' => 'Job Title',
                'phone' => 'Phone',
                'mobile' => 'Mobile',
                'email' => 'Email',
            ]
        );
        $people = new PersonCollection($this->_templateobject);
        $sh = $this->setSearchHandler($people);
        $cc = new ConstraintChain();
        $cc->add(new Constraint('company_id', '=', $this->_data['company_id']));
        $sh->addConstraint($cc);

        if (isset($this->search)) {
            if ($this->isPrintDialog()) {
                $_SESSION['printing'][$this->_data['index_key']]['search_id'] = $sh->search_id;
                return $this->printCollection();
            } elseif ($this->isPrinting()) {
                $_SESSION['printing'][$this->_data['index_key']]['search_id'] = $sh->search_id;
                $sh->setLimit(0);
                $people->load($sh);
                $this->printCollection($people);
                exit;
            }
        }
        parent::index($people, $sh);
    }

    public function delete($modelName = null)
    {
        $this->checkRequest(['post'], true);

        $flash = Flash::instance();

        $person = $this->_templateobject;

        $person_idfield = $person->idField;

        if (isset($this->_data[$person_idfield]) && ! empty($this->_data[$person_idfield])) {
            $person->load($this->_data[$person->idField]);

            if (! $person->isLoaded()) {
                $flash->addError('You do not have permission to delete this person.');
                sendTo($this->name, 'index', $this->_modules);
                return;
            }

            $company = DataObjectFactory::Factory('Company');
            $company->load($person->company_id);

            $company_id = $person->company_id;
            $company_idfield = $company->idField;

            if (parent::delete($person)) {
                if ($company->isLoaded()) {
                    sendTo('Companys', 'view', $this->_modules, [
                        $company_idfield => $company_id,
                    ]);
                } else {
                    sendTo($this->name, 'index', $this->_modules);
                }
            }

            sendTo($this->name, 'view', $this->_modules, [
                $person_idfield => $this->_data[$person_idfield],
            ]);
        }
    }

    /**
     * Save Person
     *
     * @param string $modelName
     * @param array $dataIn
     * @param array $errors
     */
    public function save($modelName = null, $dataIn = [], &$errors = [])
    {
        $errors = [];

        $person = $this->_templateobject;

        $personmodel = get_class($person);

        if (! $this->checkParams($personmodel)) {
            sendBack();
        }

        $persondata = $this->_data[$personmodel];

        $personidfield = $person->idField;

        if (isset($persondata[$person->idField])) {
            $personid = $persondata[$personidfield];
        } else {
            $personid = '';
        }

        if (! empty($personid)) {
            $person->load($personid);

            if (! $person->isLoaded()) {
                $flash = Flash::instance();
                $flash->addError('You do not have permission to edit this person.');
                sendTo($this->name, 'index', $this->_modules);
                return;
            }
        }
        $flash = Flash::Instance();

        $db = &DB::Instance();
        $db->StartTrans();

        if (isset($this->_data['Address']) && ! empty($this->_data['Address']['id'])) {
            // Selected pre-existing address
            unset($this->_data['Address']);
        }

        if (isset($this->_data['PartyAddress']) && isset($this->_data['Address'])) {
            $partyaddress = DataObjectFactory::Factory('PartyAddress');
            $partyaddress->checkAddress($this->_data);
        }

        $partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
        foreach ($partycontactmethod->getEnumOptions('type') as $key => $type) {
            if (isset($this->_data[$type]['PartyContactMethod']) && isset($this->_data[$type]['Contactmethod'])) {
                if (empty($this->_data[$type]['Contactmethod']['contact'])) {
                    if (! empty($this->_data[$type]['PartyContactMethod'][$partycontactmethod->idField])) {
                        $partycontactmethod->delete($this->_data[$type]['PartyContactMethod'][$partycontactmethod->idField], $errors);
                    }
                    unset($this->_data[$type]);
                } else {
                    $partycontactmethod->check($this->_data[$type]);
                }
            }
        }

        if (count($errors) == 0 && parent::save($personmodel, $this->_data, $errors)) {
            foreach ($this->saved_models as $model) {
                if (isset($model[$personmodel])) {
                    $person = $model[$personmodel];
                    break;
                }
            }

            // Now get the saved Person details
            $person_id = $person->$personidfield;

            $people_category = DataObjectFactory::Factory('PeopleInCategories');
            $current_categories = $people_category->getCategoryID($person_id);

            $check_categories = [];
            $delete_categories = [];
            $insert_categories = [];
            $new_categories = [];

            if (isset($this->_data['ContactCategories'])) {
                $delete_categories = array_diff($current_categories, $this->_data['ContactCategories']['category_id']);
                $insert_categories = array_diff($this->_data['ContactCategories']['category_id'], $current_categories);
                $new_categories = array_diff($current_categories, $delete_categories);
                $new_categories += $insert_categories;
            } else {
                $delete_categories = $current_categories;
            }

            $ledger_category = DataObjectFactory::Factory('LedgerCategory');

            $ledger_types = $ledger_category->checkPersonUsage($person_id);

            foreach ($ledger_types as $ledger_type => $categories) {
                if ($categories['exists'] && ! array_intersect($categories['categories'], $new_categories)) {
                    foreach (array_intersect($categories['categories'], $delete_categories) as $category_id) {
                        $category = DataObjectFactory::Factory('ContactCategory');
                        $category->load($category_id);
                        $errors[$category->name] = 'Cannot remove category ' . $category->name . ' - ' . $ledger_type . ' entry exists';
                    }
                }
            }

            $result = (count($errors) == 0);

            if (! empty($delete_categories) && $result) {
                // All OK, so delete the associations
                $result = $people_category->delete(array_keys($delete_categories), $errors);
            }

            if (! empty($insert_categories) && $result) {
                // No errors and some new categories to assign to the person
                $result = $people_category->insert($insert_categories, $person_id);
            }

            if ($result) {
                // All OK
                $db->CompleteTrans();
                $slmaster = new SLCustomer();
                $slmaster->loadBy('company_id', $this->_data['Person']['company_id']);
                sendTo($this->name, 'view', $this->_modules, [
                    $personidfield => $person_id,
                    'slmaster_id' => $slmaster->id,
                ]);
            }
        }

        $flash = Flash::Instance();
        $flash->addErrors($errors);
        $db->FailTrans();
        $db->CompleteTrans();
        $this->refresh();
    }

    public function import()
    {
        $this->view->set('what', 'people');
        $valid_fields = [
            'title',
            'firstname',
            'surname',
            'phone',
            'fax',
            'email',
            'department',
            'jobtitle',
            'company',
            'street1',
            'street2',
            'street3',
            'town',
            'county',
            'postcode',
        ];
        $this->view->set('fields', $valid_fields);
        // $this->view->set('callback','custom_setup');
        $this->view->set('js_extension', $this->custom_setup(true));
    }

    public function do_import()
    {
        $filename = $_FILES['file']['tmp_name'];
        $address_fields = [
            'street1',
            'street2',
            'street3',
            'town',
            'county',
            'postcode',
        ];

        $req_address_fields = [
            'street1',
            'town',
            'county',
            'postcode',
        ];

        $db = &DB::Instance();

        $flash = Flash::Instance();

        $db->StartTrans();

        $columnheadings = false;

        if (isset($this->_data['contains_headings'])) {
            $columnheadings = true;
        } elseif (count($this->_data['headings']) > 0) {
            $columnheadings = $this->_data['headings'];
        }

        $data = parse_csv_file($filename, $columnheadings);

        $co_loaded = false;

        $errors = [];

        $try_address = false;

        if (in_array_all($req_address_fields, $columnheadings)) {
            $try_address = true;
        }

        foreach ($data as $person_data) {
            $company = DataObjectFactory::Factory('Company');

            if (is_array($columnheadings) && in_array('company', $columnheadings)) {
                if (isset($this->_data['unique_companies'])) {
                    $co_loaded = $company->loadBy('name', $person_data['company']);
                }

                if ($co_loaded === false) {
                    $co_data = [
                        'name' => $person_data['company'],
                    ];

                    $company = DataObject::Factory($co_data, $errors, 'Company');

                    if ($company !== false) {
                        $company->save();
                    }
                }

                if ($company !== false) {
                    $person_data['company_id'] = $company->id;
                }
            }

            parent::save('Person', $person_data);

            if ($try_address && in_array_all($req_address_fields, array_keys($person_data))) {
                $address_data = [];

                foreach ($address_fields as $fieldname) {
                    if (isset($person_data[$fieldname])) {
                        $address_data[$fieldname] = $person_data[$fieldname];
                    }
                }

                $address_data['main'] = true;
                $address_data['name'] = 'Main';
                $address_data['person_id'] = $this->_data['id'];
                $address_data['countrycode'] = 'GB';

                $address = DataObject::Factory($address_data, $errors, 'Personaddress');

                if ($address !== false) {
                    $address->save();
                }
            }
        }

        $success = $db->CompleteTrans();

        if ($success) {
            $flash->clearMessages();
            $flash->addMessage(count($data) . ' contacts imported');
            $this->import();
            $this->setTemplateName('import');
        } else {
            $flash->addErrors($errors);
            $this->import();
            $this->setTemplateName('import');
        }
    }

    public function custom_setup($return = false)
    {
        $output = <<<EOF
		Object.extend(ImportWizard.prototype, {
			postInit: function() {
				this.addRow(
					[
						Builder.node('label',{htmlFor:'unique_companies'},'Keep company names unique'),
						Builder.node('input',{type:'checkbox',className:'checkbox',checked:'checked',name:'unique_companies',id:'unique_companies'})
					]
				);
			}
		});
EOF;
        if ($return) {
            return $output;
        }
        header("Content-type: text/javascript");
        echo $output;
        exit();
    }

    /*
     * Ajax Functions
     */
    /* not sure if there is a method to achieve this already */
    public function getAllByCompany($_company_id = '', $_exclude_id = '', $_none = false)
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['company_id'])) {
                $_company_id = $this->_data['company_id'];
            }
            if (! empty($this->_data['exclude_id'])) {
                $_exclude_id = $this->_data['exclude_id'];
            }
            if (! empty($this->_data['none'])) {
                $_none = $this->_data['none'];
            }
        }

        $cc = new ConstraintChain();
        $cc->add(new Constraint('company_id', '=', $_company_id));
        if (! empty($_exclude_id)) {
            $cc->add(new Constraint('id', '!=', $_exclude_id));
        }

        $this->_templateobject->belongsTo('Person', 'reports_to', 'person_reports_to', $cc, "surname || ', ' || firstname");

        if (! $_none) {
            $smarty_params = [
                'nonone' => 'true',
            ];
        }
        $depends = [
            'company_id' => $_company_id,
        ];

        return $this->getOptions($this->_templateobject, 'reports_to', 'getAllByCompany', 'getOptions', $smarty_params, $depends);
    }

    public function getallids()
    {
        $personoverview = new PersonCollection($this->_templateobject);

        $sh = new SearchHandler($personoverview, false);

        $sh->AddConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
        $sh->AddConstraint(new Constraint('usernameaccess', '=', EGS_USERNAME));

        $sh->setLimit(1);

        $return = $personoverview->load($sh);

        echo json_encode($return);
        exit();
    }

    public function getinformationbyid()
    {
        $person = $this->_templateobject;
        $person->load($this->_data['id']);
        $data = [];
        $data['firstname'] = $person->firstname;
        $data['lastname'] = $person->surname;
        $data['company'] = $person->company;
        $data['job_title'] = $person->jobtitle;
        $data['department'] = $person->department;
        $data['phone'] = $person->phone->contact;
        $data['mobile'] = $person->mobile->contact;
        $data['fax'] = $person->fax->contact;
        $data['email'] = $person->email->contactmethod;
        $data['address1'] = $person->address->street1;
        $data['address2'] = $person->address->street2;
        $data['address3'] = $person->address->street3;
        $data['town'] = $person->address->town;
        $data['county'] = $person->address->county;
        $data['postcode'] = $person->address->postcode;
        $data['country'] = $person->address->country;
        echo json_encode($data);
        exit();
    }

    public function getAddresses($_company_id = '', $_person_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['company_id'])) {
                $_company_id = $this->_data['company_id'];
            }
            if (! empty($this->_data['person_id'])) {
                $_person_id = $this->_data['person_id'];
            }
            if (! empty($this->_data['fulladdress'])) {
                $_fulladdress = $this->_data['fulladdress'];
            }
        }

        $addresses = $this->getCompanyAddresses($_company_id);

        if (! empty($_person_id)) {
            $addresses += $this->getPersonAddresses($_person_id);
        }

        if (isset($this->_data['ajax'])) {
            $addresses = [
                '' => 'Enter new address',
            ] + $addresses;
            if (! empty($_fulladdress)) {
                $this->view->set('value', $_fulladdress);
            }
            $this->view->set('options', $addresses);
            $this->setTemplateName('select_options');
        } else {
            return $addresses;
        }
    }

    public function getAddress($_address_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['address_id'])) {
                $_address_id = $this->_data['address_id'];
            }
        }

        $address = DataObjectFactory::Factory('Address');
        $address->load($_address_id);

        $this->view->set('address', $address);

        if (isset($this->_data['ajax'])) {
            $this->setTemplateName('address');
        } else {
            return $this->view->fetch($this->getTemplateName('address'));
        }
    }

    /*
     * Protected Functions
     */
    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'person' : $base), $type);
    }

    /*
     * Private Functions
     */
    private function getCompanyAddresses($_company_id = '')
    {
        $addresses = DataObjectFactory::Factory('companyaddress');

        return $addresses->getAddresses($_company_id);
    }

    private function getPersonAddresses($_person_id = '')
    {
        $addresses = DataObjectFactory::Factory('personaddress');

        return $addresses->getAddresses($_person_id);
    }
}

function parse_csv_file($file, &$columnheadings = false, $delimiter = ',', $enclosure = null)
{
    $row = 1;
    $row_count = 0;
    $rows = [];
    $handle = fopen($file, 'r') or die("couldn't open $file");

    while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
        if ($columnheadings === true && $row == 1) {
            $columnheadings = $data;
        } elseif ($columnheadings === true) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $rows[$row_count][$columnheadings[$key]] = $value;
            }
            $row_count++;
        } elseif (is_array($columnheadings)) {
            foreach ($data as $key => $value) {
                $rows[$row_count][$columnheadings[$key]] = $value;
            }
            $row_count++;
        } else {
            $rows[] = $data;
        }
        $row++;
    }
    fclose($handle);
    return $rows;
}

function in_array_all($needles, $haystack)
{
    foreach ($needles as $needle) {
        if (! in_array($needle, $haystack)) {
            return false;
        }
    }

    return true;
}

// End of PersonsController
