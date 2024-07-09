<?php

/**
 * Party Notes Controller
 *
 * @package contacts
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class PartynotesController extends Controller
{
    protected $version = '$Revision: 1.6 $';
    protected $_templateobject;
    protected $related = null;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('PartyNote');

        $this->uses($this->_templateobject);

        $this->related['company'] = [
            'clickaction' => 'edit',
        ];

        $this->related['person'] = [
            'clickaction' => 'edit',
        ];
    }

    #[\Override]
    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $this->view->set('allow_delete', true);

        // Search
        $errors = [];

        $s_data = [];


        if (isset($this->_data['party_id'])) {
            $s_data['party_id'] = $this->_data['party_id'];
        } elseif (isset($this->_data['Search'])) {
            $s_data['party_id'] = $this->_data['Search']['party_id'];
        }

        $this->setSearch('PartynotesSearch', 'useDefault', $s_data);

        $this->view->set('clickaction', 'edit');

        parent::index(new PartyNoteCollection($this->_templateobject));
    }

    #[\Override]
    public function _new()
    {
        // Set title when showing form after customer account stopped
        if ($this->_data['title'] == 'Account stopped') {
            $this->view->set('page_title', 'Enter reason for stopping account');
        }
        parent::_new();
    }

    #[\Override]
    public function delete($modelName = null)
    {
        $flash = Flash::Instance();

        parent::delete('PartyNote');

        sendBack();
    }

    #[\Override]
    public function save($modelName = null, $dataIn = [], &$errors = []): void
    {
        $flash = Flash::Instance();

        if (parent::save('PartyNote')) {
            sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], $_SESSION['refererPage']['other'] ?? null);
        } else {
            $this->refresh();
        }
    }

    #[\Override]
    public function viewRelated($name)
    {
        $this->index();

        $this->setTemplateName('index');
    }

    #[\Override]
    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'note' : $base), $type);
    }
}

// End of PartynotesController
