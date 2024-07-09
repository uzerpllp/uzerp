<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanysController extends printController
{
    protected $version = '$Revision: 1.37 $';

    protected $_templateobject;

    public static $nav_list = [
        'companies' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'companys',
                'action' => 'index',
            ],
            'tag' => 'view_companies',
        ],
        'companybycategory' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'companys',
                'action' => 'viewcategories',
            ],
            'tag' => 'view_company_by_category',
        ],
        'people' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'persons',
                'action' => 'index',
            ],
            'tag' => 'view_people',
        ],
        'leads' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'leads',
                'action' => 'index',
            ],
            'tag' => 'view_leads',
        ],
        'spacer',
        'new' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'companys',
                'action' => 'new',
            ],
            'tag' => 'new_company',
        ],
        'new_lead' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'leads',
                'action' => 'new',
            ],
            'tag' => 'new_lead',
        ],
        'new_person' => [
            'link' => [
                'module' => 'contacts',
                'controller' => 'persons',
                'action' => 'new',
            ],
            'tag' => 'new_person',
        ],
    ];

    public static $related = [];

    public static $idField;

    public static $company_id;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Company');
        $this->uses($this->_templateobject);
        $this->related['addresses'] = [
            'clickaction' => 'edit',
        ];
    }

    public function viewcategories()
    {
        unset($this->related['addresses']);
        $this->view->set('clickaction', 'view');
        $s_data = [];
        $this->setSearch('CompanyCategorySearch', 'useDefault', $s_data);

        $this->_templateobject = new CompanyInCategories();
        $this->uses($this->_templateobject);
        $this->idField = 'company_id';

        $collection = new CompanyInCategoriesCollection($this->_templateobject);
        $collection->orderby = ['company'];
        $sh = $this->setSearchHandler($collection);

        // id field from the collection used for the click action
        $collection->idField = 'company_id';

        $systemCompany = DataObjectFactory::Factory('Company');
        $systemCompany->load(COMPANY_ID);

        $_company_ids = $systemCompany->getSystemRelatedCompanies([
            $systemCompany->id => $systemCompany->getIdentifierValue(),
        ]);

        $sh->addConstraint(new Constraint('id', 'NOT IN', '(' . implode(',', array_keys($_company_ids)) . ')'));

        parent::index($collection, $sh);
        $this->view->set('page_title', 'Company by Category');

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('Actions', $this::$nav_list);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    #[\Override]
    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $this->view->set('clickaction', 'view');

        $s_data = [];

        $this->setSearch('CompanySearch', 'useDefault', $s_data);

        $collection = new CompanyCollection($this->_templateobject);

        $sh = $this->setSearchHandler($collection);

        $systemCompany = DataObjectFactory::Factory('Company');
        $systemCompany->load(COMPANY_ID);

        $_company_ids = $systemCompany->getSystemRelatedCompanies([
            $systemCompany->id => $systemCompany->getIdentifierValue(),
        ]);

        $sh->addConstraint(new Constraint('id', 'NOT IN', '(' . implode(',', array_keys($_company_ids)) . ')'));

        parent::index($collection, $sh);

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('Actions', $this::$nav_list);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    #[\Override]
    public function view()
    {
        $company = $this->_templateobject;
        $companyidfield = $company->idField;
        $company->load($this->_data[$companyidfield], true);

        if (! $company->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to view this contact.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $companyid = $company->id;
        $partyid = $company->party_id;
        $sidebar = new SidebarController($this->view);

        $sidebar->addList(
            'currently_viewing',
            [
                $company->name => [
                    'tag' => $company->name,
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'companys',
                        'action' => 'view',
                        $companyidfield => $company->$companyidfield,
                    ],
                ],
                'edit' => [
                    'tag' => 'Edit',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'companys',
                        'action' => 'edit',
                        $companyidfield => $company->$companyidfield,
                    ],
                ],
            ]
        );

        $sidebar->addList(
            'related_items',
            [
                'relatedcompanies' => [
                    'tag' => 'related_companies',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'companys',
                        'action' => 'viewrelatedcompanies',
                        'parent_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'companys',
                        'action' => 'new',
                        'parent_id' => $companyid,
                    ],
                ],
                'people' => [
                    'tag' => 'People',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'persons',
                        'action' => 'viewcompany',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'persons',
                        'action' => 'new',
                        'company_id' => $companyid,
                    ],
                ],
                'spacer',
                'opportunities' => [
                    'tag' => 'Opportunities',
                    'link' => [
                        'module' => 'crm',
                        'controller' => 'opportunitys',
                        'action' => 'viewcompany',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'crm',
                        'controller' => 'opportunitys',
                        'action' => 'new',
                        'company_id' => $companyid,
                    ],
                ],
                'activities' => [
                    'tag' => 'Activities',
                    'link' => [
                        'module' => 'crm',
                        'controller' => 'activitys',
                        'action' => 'viewcompany',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'crm',
                        'controller' => 'activitys',
                        'action' => 'new',
                        'company_id' => $companyid,
                    ],
                ],
                'tickets' => [
                    'tag' => 'Tickets',
                    'link' => [
                        'module' => 'ticketing',
                        'controller' => 'tickets',
                        'action' => 'viewcompany',
                        'originator_company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'ticketing',
                        'controller' => 'tickets',
                        'action' => 'new',
                        'originator_company_id' => $companyid,
                    ],
                ],
                'spacer',
                'projects' => [
                    'tag' => 'Projects',
                    'link' => [
                        'module' => 'projects',
                        'controller' => 'projects',
                        'action' => 'viewcompany',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'projects',
                        'controller' => 'projects',
                        'action' => 'new',
                        'company_id' => $companyid,
                    ],
                ],
                'spacer',
                'notes' => [
                    'tag' => 'Notes',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partynotes',
                        'action' => 'viewcompany',
                        'party_id' => $partyid,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partynotes',
                        'action' => 'new',
                        'party_id' => $partyid,
                    ],
                ],
                'spacer',
                'attachments' => [
                    'tag' => 'Attachments',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'companyattachments',
                        'action' => 'index',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'companyattachments',
                        'action' => 'new',
                        'data_model' => 'company',
                        'entity_id' => $companyid,
                    ],
                ],
                'spacer',
                'addresses' => [
                    'tag' => 'Addresses',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partyaddresss',
                        'action' => 'viewcompany',
                        'party_id' => $partyid,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partyaddresss',
                        'action' => 'new',
                        'party_id' => $partyid,
                    ],
                ],
                'spacer',
                'phone' => [
                    'tag' => 'Phone',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'viewcompany',
                        'party_id' => $partyid,
                        'type' => 'T',
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'new',
                        'party_id' => $partyid,
                        'type' => 'T',
                    ],
                ],
                'fax' => [
                    'tag' => 'Fax',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'viewcompany',
                        'party_id' => $partyid,
                        'type' => 'F',
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'new',
                        'party_id' => $partyid,
                        'type' => 'F',
                    ],
                ],
                'email' => [
                    'tag' => 'Email',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'viewcompany',
                        'party_id' => $partyid,
                        'type' => 'E',
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'new',
                        'party_id' => $partyid,
                        'type' => 'E',
                    ],
                ],
                'spacer',
                'meetings' => [
                    'tag' => 'Meetings',
                    'link' => [
                        'module' => 'calendar',
                        'controller' => 'calendarevents',
                        'action' => 'viewcompany',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'calendar',
                        'controller' => 'calendarevents',
                        'action' => 'new',
                        'company_id' => $companyid,
                    ],
                ],
                'calls' => [
                    'tag' => 'Calls',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'loggedcalls',
                        'action' => 'viewcompany',
                        'company_id' => $companyid,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'loggedcalls',
                        'action' => 'new',
                        'company_id' => $companyid,
                    ],
                ],
            ]
        );

        $ao = &AccessObject::Instance(EGS_USERNAME);

        if ($ao->hasPermission('crm')) {
            $this->view->set('crm_access', true);
        }

        $category = DataObjectFactory::Factory('CompanyInCategories');
        $this->view->set('categories', implode(',', $category->getCategorynames($company->id)));

        $current_categories = $category->getCategoryID($company->{$company->idField});

        $ledger_category = DataObjectFactory::Factory('LedgerCategory');

        $can_delete = true;

        if (count($current_categories) > 0) {
            foreach ($ledger_category->getCompanyTypes($current_categories) as $model_name => $model_detail) {
                $do = DataObjectFactory::Factory($model_name);

                $do->loadBy('company_id', $company->{$company->idField});

                if ($do->isLoaded()) {
                    $can_delete = false;
                    $sidebar->addList(
                        'related_items',
                        [
                            $model_name => [
                                'tag' => $do->getTitle(),
                                'link' => [
                                    'module' => $model_detail['module'],
                                    'controller' => $model_detail['controller'],
                                    'action' => 'view',
                                    $do->idField => $do->{$do->idField},
                                ],
                            ],
                        ]
                    );
                } else {
                    $sidebar->addList(
                        'related_items',
                        [
                            $model_name => [
                                'tag' => $do->getTitle(),
                                'new' => [
                                    'module' => $model_detail['module'],
                                    'controller' => $model_detail['controller'],
                                    'action' => 'new',
                                    'company_id' => $company->{$company->idField},
                                ],
                            ],
                        ]
                    );
                }
            }
        }

        // No need to show a delete action. If this company account
        // is linked to an SL or PL master, then delete will be blocked
        // by a DB contraint.
        if ($can_delete === true && $company->isSystemCompany() === false) {
            $sidebar->addList(
                'currently_viewing',
                [
                    'delete' => [
                        'tag' => 'Delete',
                        'link' => [
                            'module' => 'contacts',
                            'controller' => 'companys',
                            'action' => 'delete',
                            $companyidfield => $companyid,
                        ],
                        'class' => 'confirm',
                        'data_attr' => [
                            'data_uz-confirm-message' => "Delete {$company->name}?|This will also delete projects, people and associated contact and CRM records. It cannot be undone.",
                            'data_uz-action-id' => $companyid,
                        ],
                    ],
                ]
            );
        }

        if ($company instanceof Company) {
            $pl = new PreferencePageList('recently_viewed_companies' . EGS_COMPANY_ID);
            $pl->addPage(new Page([
                'module' => 'contacts',
                'controller' => $this->name,
                'action' => 'view',
                $company->idField => $companyid,
            ], 'company', $company->name));
            $pl->save();
        }

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('page_title', $this->getPageName('Company'));
    }

    #[\Override]
    public function delete($modelName = null)
    {
        $this->checkRequest(['post'], true);

        $flash = Flash::Instance();
        $errors = [];

        $company = $this->_templateobject;
        $company->load($this->_data['id']);

        if (! $company->isLoaded() || $company->isSystemCompany()) {
            $flash->addError('You do not have permission to delete this contact.');
            sendTo($this->name, 'view', $this->_modules, [
                $company->idField => $company->{$company->idField},
            ]);
            return;
        }

        $pl = new PreferencePageList('recently_viewed_companies' . EGS_COMPANY_ID);
        $pl->removePage(new Page([
            'module' => 'contacts',
            'controller' => $this->name,
            'action' => 'view',
            $company->idField => $company->{$company->idField},
        ], 'company', $company->getIdentifierValue()));
        $pl->save();

        if (! $company->delete(null, $errors)) {
            $errors[] = 'Error deleting ' . $company->getIdentifierValue();
            $flash->addErrors($errors);
            sendTo($this->name, 'view', $this->_modules, [
                $company->idField => $company->{$company->idField},
            ]);
        }
        $flash->addMessage($company->getIdentifierValue() . ' deleted successfully');
        sendTo($this->name, 'index', $this->_modules);
    }

    #[\Override]
    public function edit()
    {
        $company = $this->_templateobject;
        $companyid = '';
        if (isset($this->_data[$company->idField])) {
            $companyid = $this->_data[$company->idField];
        } elseif (isset($this->_data[get_class($this->_templateobject)][$company->idField])) {
            $companyid = $this->_data[get_class($this->_templateobject)][$company->idField];
        }
        if (empty($companyid)) {
            sendTo();
        }
        $company->load($companyid);

        if (! $company->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to edit this contact.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $cic = DataObjectFactory::Factory('CompanyInCategories');

        $selected = $cic->getCategoryID($companyid);

        $this->view->set('selected_categories', $selected);

        parent::edit();
    }

    #[\Override]
    public function _new()
    {
        $ao = &AccessObject::Instance(EGS_USERNAME);
        if ($ao->hasPermission('crm')) {
            $this->view->set('crm_access', true);
        }

        $categories = DataObjectFactory::Factory('Contactcategory');

        $this->view->set('contact_categories', $categories->getCompanyCategories());

        parent::_new();
    }

    #[\Override]
    public function save($modelName = null, $dataIn = [], &$errors = []): void
    {
        $flash = Flash::Instance();

        $errors = [];

        $modelname = get_class($this->_templateobject);

        if (! $this->checkParams($modelname)) {
            sendBack();
        }

        $company = $this->_templateobject;
        $companydata = $this->_data[$modelname];
        $companyidfield = $company->idField;
        $companyid = '';

        if (isset($companydata[$companyidfield]) && ! empty($companydata[$companyidfield])) {
            $companyid = $companydata[$companyidfield];
            $company->load($companyid);
            if ($company === false) {
                $flash->addError('Could not load Company for id=' . $companyid . ' - Abandoned');
                sendBack();
            }
        }

        // We need to generate the account number here, if enabled.
        // When a new Company is saved, the model passed to the Autohandler is empty.
        $system_prefs = SystemPreferences::instance();
        $autoGenerate = $system_prefs->getPreferenceValue('auto-account-numbering', 'contacts');
        if (($this->_data[$modelname]['accountnumber'] == "")
        && (! empty($autoGenerate) && $autoGenerate === 'on')) {
            $this->_data[$modelname]['accountnumber'] = $company->createAccountNumber($companydata['name']);
        }

        $db = &DB::Instance();
        $db->StartTrans();

        $partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');

        foreach ($partycontactmethod->getEnumOptions('type') as $key => $type) {
            if (isset($this->_data[$type]['PartyContactMethod'])
                 && isset($this->_data[$type]['Contactmethod'])
                 && empty($this->_data[$type]['Contactmethod']['contact'])) {
                if (! empty($this->_data[$type]['PartyContactMethod'][$partycontactmethod->idField])) {
                    $partycontactmethod->delete($this->_data[$type]['PartyContactMethod'][$partycontactmethod->idField], $errors);
                }
                unset($this->_data[$type]);
            }
        }
        if (count($errors) == 0 && parent::save($modelname, $this->_data, $errors)) {
            foreach ($this->saved_models as $model) {
                if (isset($model[$modelname])) {
                    $company = $model[$modelname];
                    break;
                }
            }
            $this->company_id = $company->$companyidfield;
            if (isset($companydata['crm'])) {
                $crm_data = $companydata['crm'];
                $ao = &AccessObject::Instance(EGS_USERNAME);
                if ($ao->hasPermission('crm')) {
                    $crm_data['company_id'] = $company->$companyidfield;
                    parent::save('CompanyCrm', $crm_data);
                }
            }

            $company_category = DataObjectFactory::Factory('CompanyInCategories');
            $current_categories = $company_category->getCategoryID($company->$companyidfield);

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

            $ledger_types = $ledger_category->checkCompanyUsage($company->$companyidfield);

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
                $result = $company_category->delete(array_keys($delete_categories), $errors);
            }

            if (! empty($insert_categories) && $result) {
                // No errors and some new categories to assign to the company
                $result = $company_category->insert($insert_categories, $company->$companyidfield);
            }

            // Make Company and associated People inactive
            try {
                $result = $company->makeInactive();
            } catch (Exception $e) {
                $flash->addWarning($e->getMessage());
                if ($e->getCode == 0) {
                    $result = true;
                }
            }

            if ($result) {
                // All OK
                $db->CompleteTrans();
                sendTo($this->name, 'view', $this->_modules, [
                    $companyidfield => $company->$companyidfield,
                ]);
            }
        }

        // Errors
        $flash->addErrors($errors);
        $db->FailTrans();
        $db->CompleteTrans();
        $this->refresh();
    }

    public function viewBranches()
    {
        $branches = new CompanyCollection($this->_templateobject);

        $sh = new SearchHandler($branches, false);

        $sh->extract();

        $sh->addConstraint(new Constraint('parent_id', '=', $this->_data['parent_id']));

        $branches->load($sh);

        $this->_templateName = $this->getTemplateName('view_related');

        $this->view->set('clickaction', 'view');
        $this->view->set('related_collection', $branches);
        $this->view->set('num_pages', $branches->num_pages);
        $this->view->set('cur_page', $branches->cur_page);
        $this->view->set('no_ordering', true);
    }

    public function getCompanyEmailList($_id = '')
    {
        // ATTENTION - this ajax call has an error
        // INFACT it

        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $customer = $this->_templateobject;
        $customer->load($_id);
        $emails = [
            '0' => 'None',
        ];
        foreach ($customer->getEmailAddresses() as $emailaddresses) {
            $emails[$emailaddresses->id] = $emailaddresses->contact;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $emails);
            $this->setTemplateName('select_options');
        } else {
            return $emails;
        }
    }

    #[\Override]
    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'company' : $base), $type);
    }
}

// End of CompanysController
