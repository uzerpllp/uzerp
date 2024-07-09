<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class LeadsController extends printController
{
    protected $version = '$Revision: 1.21 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Lead');
        $this->uses($this->_templateobject);

        $this->related['addresses'] = [
            'clickaction' => 'edit',
        ];
    }

    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $this->view->set('clickaction', 'view');

        $s_data = [];

        $this->setSearch('CompanySearch', 'leads', $s_data);

        parent::index($t = new LeadCollection($this->_templateobject));

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', CompanysController::$nav_list);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

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

    public function delete($modelName = null)
    {
        $this->checkRequest(['post'], true);

        $flash = Flash::Instance();

        $company = $this->_uses['Lead'];

        $company->load($this->_data['id']);

        if (! $company->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to delete this lead.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $pl = new PreferencePageList('recently_viewed_leads' . EGS_COMPANY_ID);
        $pl->removePage(new Page([
            'module' => 'contacts',
            'controller' => 'leads',
            'action' => 'view',
            'id' => $company->id,
        ], 'company', $company->name));
        $pl->save();

        parent::delete('Company');

        sendTo('Leads', 'index', ['contacts']);
    }

    public function edit()
    {
        $company = $this->_uses['Lead'];

        if (! isset($this->_data[$company->idField])) {
            sendTo();
        }

        $company->load($this->_data[$company->idField]);

        if (! $company->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to edit this lead.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $addresslist = [];

        if ($company) {
            foreach ($company->addresses as $address) {
                $addresslist[$address->id] = $address->address;
            }
        }

        $this->view->set('addresses', $addresslist);

        $cic = DataObjectFactory::Factory('CompanyInCategories');
        $selected = $cic->getCategoryID($company->id);

        $this->view->set('selected_categories', $selected);

        parent::edit();
    }

    public function view()
    {
        $company = $this->_uses['Lead'];
        $company->load($this->_data[$company->idField], true);

        if (! $company->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to view this lead.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $sidebar = new SidebarController($this->view);

        $sidebar->addList(
            'currently_viewing',
            [
                $company->name => [
                    'tag' => $company->name,
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'leads',
                        'action' => 'view',
                        'id' => $company->id,
                    ],
                ],
                'edit' => [
                    'tag' => 'Edit',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'leads',
                        'action' => 'edit',
                        'id' => $company->id,
                    ],
                ],
                'delete' => [
                    'tag' => 'Delete',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'leads',
                        'action' => 'delete',
                        'id' => $company->id,
                    ],
                    'class' => 'confirm',
                    'data_attr' => [
                        'data_uz-confirm-message' => "Delete {$company->name}?|This will also delete people and associated contact and CRM records. It cannot be undone.",
                        'data_uz-action-id' => $company->id,
                    ],
                ],
                'convert_to_account' => [
                    'tag' => 'convert_to_account',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'leads',
                        'action' => 'converttoaccount',
                        'id' => $company->id,
                    ],
                ],
            ]
        );

        $sidebar->addList(
            'related_items',
            [
                'people' => [
                    'tag' => 'People',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'persons',
                        'action' => 'viewcompany',
                        'company_id' => $company->id,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'persons',
                        'action' => 'new',
                        'company_id' => $company->id,
                    ],
                ],
                'spacer',
                'opportunities' => [
                    'tag' => 'Opportunities',
                    'link' => [
                        'module' => 'crm',
                        'controller' => 'opportunitys',
                        'action' => 'viewcompany',
                        'company_id' => $company->id,
                    ],
                    'new' => [
                        'module' => 'crm',
                        'controller' => 'opportunitys',
                        'action' => 'new',
                        'company_id' => $company->id,
                    ],
                ],
                'activities' => [
                    'tag' => 'Activities',
                    'link' => [
                        'module' => 'crm',
                        'controller' => 'activitys',
                        'action' => 'viewcompany',
                        'company_id' => $company->id,
                    ],
                    'new' => [
                        'module' => 'crm',
                        'controller' => 'activitys',
                        'action' => 'new',
                        'company_id' => $company->id,
                    ],
                ],
                'notes' => [
                    'tag' => 'Notes',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partynotes',
                        'action' => 'viewcompany',
                        'party_id' => $company->party_id,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partynotes',
                        'action' => 'new',
                        'party_id' => $company->party_id,
                    ],
                ],
                'spacer',
                'attachments' => [
                    'tag' => 'Attachments',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partyattachments',
                        'action' => 'viewcompany',
                        'party_id' => $company->party_id,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partyattachments',
                        'action' => 'new',
                        'entity_table' => 'party',
                        'entity_id' => $company->party_id,
                    ],
                ],
                'spacer',
                'addresses' => [
                    'tag' => 'Addresses',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partyaddresss',
                        'action' => 'viewcompany',
                        'party_id' => $company->party_id,
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partyaddresss',
                        'action' => 'new',
                        'party_id' => $company->party_id,
                    ],
                ],
                'spacer',
                'phone' => [
                    'tag' => 'Phone',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'viewcompany',
                        'party_id' => $company->party_id,
                        'type' => 'T',
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'new',
                        'party_id' => $company->party_id,
                        'type' => 'T',
                    ],
                ],
                'fax' => [
                    'tag' => 'Fax',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'viewcompany',
                        'party_id' => $company->party_id,
                        'type' => 'F',
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'new',
                        'party_id' => $company->party_id,
                        'type' => 'F',
                    ],
                ],
                'email' => [
                    'tag' => 'Email',
                    'link' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'viewcompany',
                        'party_id' => $company->party_id,
                        'type' => 'E',
                    ],
                    'new' => [
                        'module' => 'contacts',
                        'controller' => 'partycontactmethods',
                        'action' => 'new',
                        'party_id' => $company->party_id,
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
                        'company_id' => $company->id,
                    ],
                    'new' => [
                        'module' => 'calendar',
                        'controller' => 'calendarevents',
                        'action' => 'new',
                        'company_id' => $company->id,
                    ],
                ],
            ]
        );

        $ao = &AccessObject::Instance(EGS_USERNAME);

        if ($ao->hasPermission('crm')) {
            $this->view->set('crm_access', true);
        }

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $category = DataObjectFactory::Factory('CompanyInCategories');
        $this->view->set('categories', implode(',', $category->getCategorynames($company->id)));

        if ($company instanceof Company) {
            $pl = new PreferencePageList('recently_viewed_leads' . EGS_COMPANY_ID);
            $pl->addPage(new Page([
                'module' => 'contacts',
                'controller' => 'leads',
                'action' => 'view',
                'id' => $company->id,
            ], 'company', $company->name));
            $pl->save();
        }
    }

    public function converttoaccount()
    {
        $company = $this->_uses['Lead'];

        if (isset($this->_data['Lead']) && isset($this->_data['Lead'][$company->idField])) {
            $id = $this->_data['Lead'][$company->idField];
            $data = $this->_data['Lead'];
        } elseif (isset($this->_data[$company->idField])) {
            $id = $this->_data[$company->idField];
        } else {
            $flash = Flash::Instance();
            $flash->addError('Select a Lead to convert');
            sendTo('leads', 'index', ['contacts']);
        }

        $company->load($id);

        if (! $company->isLoaded()) {
            $flash = Flash::instance();
            $flash->addError('You do not have permission to edit this lead.');
            sendTo($this->name, 'index', $this->_modules);
            return;
        }

        $pl = new PreferencePageList('recently_viewed_leads' . EGS_COMPANY_ID);
        $pl->removePage(new Page([
            'module' => 'contacts',
            'controller' => 'leads',
            'action' => 'view',
            'id' => $company->id,
        ], 'company', $company->name));
        $pl->save();

        $pl = new PreferencePageList('recently_viewed_companies' . EGS_COMPANY_ID);
        $pl->addPage(new Page([
            'module' => 'contacts',
            'controller' => 'companys',
            'action' => 'view',
            'id' => $company->id,
        ], 'company', $company->name));
        $pl->save();

        $system_prefs = SystemPreferences::instance();
        $autoGenerate = $system_prefs->getPreferenceValue('auto-account-numbering', 'contacts');

        if (! (empty($autoGenerate) || $autoGenerate === 'off')) {
            $company->update($id, ['is_lead', 'accountnumber'], ['false', $company->createAccountNumber()]);
            sendTo('companys', 'view', ['contacts'], [
                'id' => $company->id,
            ]);
        } else {
            if (isset($data['accountnumber'])) {
                $company->update($id, ['is_lead', 'accountnumber'], ['false', $data['accountnumber']]);
                sendTo('companys', 'view', ['contacts'], [
                    'id' => $company->id,
                ]);
            } else {
                parent::_new();
            }
        }
    }

    public function save($modelName = null, $dataIn = [], &$errors = []): void
    {
        $flash = Flash::Instance();

        $errors = [];

        $company = $this->_uses['Lead'];

        if (isset($this->_data['Lead'][$company->idField]) && ! empty($this->_data['Lead'][$company->idField])) {
            $company->load($this->_data['Lead'][$company->idField]);

            if ($company === false) {
                echo 'Could not load Company for id=' . $this->_data['Lead'][$person->idField] . ' - Abandoned<br>';
                sendBack();
            }
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

        if (count($errors) == 0 && parent::save('Lead')) {
            foreach ($this->saved_models as $model) {
                if (isset($model['Lead'])) {
                    $company = $model['Lead'];
                    break;
                }
            }

            $this->company_id = $company->id;

            if (isset($this->_data['Lead']['crm'])) {
                $crm_data = $this->_data['Lead']['crm'];

                $ao = &AccessObject::Instance(EGS_USERNAME);

                if ($ao->hasPermission('crm')) {
                    $crm_data['company_id'] = $company->{$company->idField};
                    parent::save('CompanyCrm', $crm_data);
                }
            }

            $category = DataObjectFactory::Factory('CompanyInCategories');
            $current_categories = $category->getCategoryID($company->{$company->idField});

            $check_categories = [];

            if (isset($this->_data['ContactCategories'])) {
                $delete_categories = array_diff($current_categories, $this->_data['ContactCategories']['category_id']);
                $insert_categories = array_diff($this->_data['ContactCategories']['category_id'], $current_categories);
            }

            $result = true;

            if (! empty($delete_categories)) {
                $result = $category->delete(array_keys($delete_categories), $errors);
            }

            if (! empty($insert_categories) && $result) {
                $result = $category->insert($insert_categories, $company->{$company->idField});
            }

            if ($result) {
                $db->CompleteTrans();
                sendTo($this->name, 'view', $this->_modules, [
                    $company->idField => $company->{$company->idField},
                ]);
            }
        }

        // Errors
        $flash->addErrors($errors);

        $db->FailTrans();

        $db->CompleteTrans();

        $this->refresh();
    }

    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'lead' : $base), $type);
    }
}

// End of LeadsController
