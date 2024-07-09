<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PersonaddresssController extends Controller
{
    protected $version = '$Revision: 1.5 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Personaddress');

        $this->uses($this->_templateobject);

        $this->related['person'] = [
            'clickaction' => 'edit',
        ];
    }

    public function index($collection = null, $sh = '', &$c_query = null)
    {
        global $smarty;

        $this->view->set('clickaction', 'edit');

        parent::index(new PersonaddressCollection($this->_templateobject));
    }

    public function delete($modelName = null)
    {
        $flash = Flash::Instance();

        parent::delete('Personaddress');

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function save($modelName = null, $dataIn = [], &$errors = []): void
    {
        $flash = Flash::Instance();

        if (parent::save('Personaddress')) {
            sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
        } else {
            $this->refresh();
        }
    }
}

// End of PersonaddresssController
