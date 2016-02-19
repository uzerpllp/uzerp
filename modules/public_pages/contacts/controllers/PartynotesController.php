<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class PartynotesController extends Controller
{

    protected $version = '$Revision: 1.6 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('PartyNote');

        $this->uses($this->_templateobject);

        $this->related['company'] = array(
            'clickaction' => 'edit'
        );

        $this->related['person'] = array(
            'clickaction' => 'edit'
        );
    }

    public function index()
    {
        $this->view->set('allow_delete',true);

        // Search
        $errors = array();

        $s_data = array();

        // Set context
        if (isset($this->_data['party_id'])) {
            $s_data['party_id'] = $this->_data['party_id'];
        } elseif (isset($this->_data['Search'])) {
            $s_data['party_id'] = $this->_data['Search']['party_id'];
        }

        $this->setSearch('PartynotesSearch', 'useDefault', $s_data);

        $this->view->set('clickaction', 'edit');

        parent::index(new PartyNoteCollection($this->_templateobject));
    }

    public function _new()
    {
        // Set title when showing form after customer account stopped
        if ($this->_data['title'] == 'Account stopped') {
            $this->view->set('page_title', 'Enter reason for stopping account');
        }
        parent::_new();
    }

    public function delete()
    {
        $flash = Flash::Instance();

        parent::delete('PartyNote');

        sendBack();
    }

    public function save()
    {
        $flash = Flash::Instance();

        if (parent::save('PartyNote')) {
            sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
        } else {
            $this->refresh();
        }
    }

    public function viewRelated($name)
    {
        $this->index();

        $this->setTemplateName('index');
    }

    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'note' : $base), $type);
    }
}

// End of PartynotesController
