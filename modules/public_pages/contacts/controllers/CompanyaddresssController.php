<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyaddresssController extends Controller
{
    protected $version = '$Revision: 1.5 $';
    protected $_templateobject;
    protected $related = null;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Companyaddress');

        $this->uses($this->_templateobject);

        $this->related['company'] = [
            'clickaction' => 'edit',
        ];
    }

    #[\Override]
    public function index($collection = null, $sh = '', &$c_query = null)
    {
        global $smarty;

        $this->view->set('clickaction', 'edit');

        parent::index(new CompanyaddressCollection($this->_templateobject));
    }

    #[\Override]
    public function delete($modelName = null)
    {
        $flash = Flash::Instance();

        parent::delete('Companyaddress');

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], $_SESSION['refererPage']['other'] ?? null);
    }

    #[\Override]
    public function save($modelName = null, $dataIn = [], &$errors = []): void
    {
        $flash = Flash::Instance();

        if (parent::save('Companyaddress')) {
            sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], $_SESSION['refererPage']['other'] ?? null);
        } else {
            $this->refresh();
        }
    }
}

// End of CompanyaddresssController
