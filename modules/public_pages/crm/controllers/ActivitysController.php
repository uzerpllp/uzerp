<?php

/**
 *  CRM Activities Controller
 *
 *  @package crm
 *  @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class ActivitysController extends printController
{

    protected $version = '$Revision: 1.6 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Activity');

        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $this->setSearch('ActivitySearch', 'useDefault');
        $this->view->set('clickaction', 'view');
        $this->view->set('page_title', 'CRM Activities');
        parent::index($a = new ActivityCollection($this->_templateobject));
        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', array(
            'new' => array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new'
                ),
                'tag' => 'New Activity'
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function delete()
    {
        $flash = Flash::Instance();

        parent::delete($this->modeltype);

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function _new($followup = FALSE)
    {
        if (array_key_exists('followup', $this->_data)) {
            $followup = $this->_data['followup'];
        }

        $this->view->set('page_title', 'New CRM Activity');

        // New follow-up activity
        if ($followup) {
            $this->view->set('page_title', 'New CRM Follow-up Activity');
            unset($this->_data['id']);
            $activity = new Activity;
            $activity->load($followup);
            $this->_data['person_id'] = $activity->person_id;
            $this->_data['company_id'] = $activity->company_id;
            $this->_data['name'] = $activity->name;
            $this->_data['description'] = $activity->description;
            $this->_data['completed'] = '';
        }

        // Edit existing activity
        if (isset($this->_data['id'])) {
            $this->view->set('page_title', 'Edit CRM Activity');
        }

        // New activity from opportunity
        if (isset($this->_data['opportunity_id'])) {
            $this->view->set('page_title', 'New CRM Activity');
            $opportunity = DataObjectFactory::Factory('Opportunity');
            $opportunity->load($this->_data['opportunity_id']);
            $this->_data['person_id'] = $opportunity->person_id;
            $this->_data['company_id'] = $opportunity->company_id;
        }

        parent::_new();
    }

    /**
     * Don't allow edit if Activity is completed
     * @see Controller::edit()
     */
    public function edit() {
        $flash = Flash::Instance();
        if (isset($this->_data['id'])) {
            $activity = $this->_uses['Activity'];
            $activity->load($this->_data['id']);
            if ($activity->isLoaded() && !is_null($activity->completed)) {
                $flash->addWarning('Activity Marked as Completed, editing not allowed');
                sendBack();
            }
            parent::edit();
        }
    }

    /**
     * Protect as POST only!
     */
    public function complete()
    {
        $flash = Flash::Instance();
        if (isset($this->_data['id'])) {
            $activity = $this->_uses['Activity'];
            $activity->load($this->_data['id']);
            if ($activity->isLoaded() && is_null($activity->completed)) {
                $activity->completed = date('Y-m-d');
                $result = $activity->save();
            }
        }

        if ($result) {
            $flash->addMessage('Activity Marked as Completed');
        }

        // Show the new activity form with default data from this activity, to create a follow-up task
        sendTo($this->name,'new',$this->_modules, ['followup' => $this->_data['id']]);

        // ...or if auto follow-up not confgured (need settings for the module)
        //sendBack();
    }

    public function save()
    {
        $flash = Flash::Instance();

        if (parent::save('Activity')) {
            // Ensures return to viewing the new activity after saving a follow-up
            if($_SESSION['refererPage']['controller'] == $this->name && $_SESSION['refererPage']['action'] == 'view'){
                sendTo($this->name,'view',$this->_modules, ['id' => $this->saved_model->id]);
            }

            // Return to the refering page, e.g. when adding an activity from the contacts module
            sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
        } else {
            $this->refresh();
        }
    }

    public function view()
    {
        if (! $this->loadData()) {
            sendBack();
        }

        $activity = $this->_uses['Activity'];
        $this->view->set('activity', $activity);
        $this->view->set('page_title', 'View CRM Activity - ' . $activity->name );
        $sidebar = new SidebarController($this->view);

        $actions = [];

        $actions['index'] = array(
            'tag' => 'View All Activities',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );
        $sidebar->addList('actions', $actions);

        $actions = [];

        $actions[$activity->name] = array(
            'tag' => $activity->name,
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $activity->id
            )
        );
        if (is_null($activity->completed)) {
            $actions['edit'] = array(
                'tag' => 'Edit',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'edit',
                    'id' => $activity->id
                )
            );
            $actions['delete'] = array(
                'tag' => 'Delete',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'delete',
                    'id' => $activity->id
                )
            );
            $actions['mark_as_completed'] = array(
                'tag' => 'Mark as completed',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'complete',
                    'id' => $activity->id
                )
            );
        }
        $sidebar->addList('currently_viewing', $actions);

        $sidebar->addList('related_items', array(
            'notes' => array(
                'tag' => 'Notes',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'activitynotes',
                    'action' => 'viewactivity',
                    'activity_id' => $activity->id
                ),
                'new' => array(
                    'modules' => $this->_modules,
                    'controller' => 'activitynotes',
                    'action' => 'new',
                    'activity_id' => $activity->id
                )
            ),
            'spacer',
            'attachments' => array(
                'tag' => 'Attachments',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'activityattachments',
                    'action' => 'index',
                    'activity_id' => $activity->id
                ),
                'new' => array(
                    'modules' => $this->_modules,
                    'controller' => 'activityattachments',
                    'action' => 'new',
                    'data_module' => 'activity',
                    'entity_id' => $activity->id
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }
}
?>

