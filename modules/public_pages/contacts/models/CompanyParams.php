<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyParams
{
    protected $version = '$Revision: 1.4 $';

    protected $_params = [];

    public function __construct()
    {
        if (! defined('EGS_COMPANY_ID')) {
            return false;
        }

        $db = DB::Instance();

        $query = 'SELECT params FROM companyparams WHERE usercompanyid=' . EGS_COMPANY_ID . ' LIMIT 1 OFFSET 0';

        $result = $db->GetOne($query);

        if (! $result) {
            $this->save();
        } else {
            $temp = unserialize($result);
            $this->_params = $temp;
        }
    }

    public function getParam($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }

        return false;
    }

    public function editParam($name, $value)
    {
        if (isset($this->_params[$name])) {
            $this->addParam($name, $value);
            return true;
        }

        return false;
    }

    public function addParam($name, $value)
    {
        $this->_params[$name] = $value;

        return true;
    }

    public function exists($name)
    {
        if (isset($this->_params[$name])) {
            return true;
        } else {
            return false;
        }
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function save()
    {
        $db = DB::Instance();

        $serial = serialize($this->_params);

        $serial = $db->qstr($serial);

        $query = "UPDATE companyparams SET params=" . $serial . " WHERE usercompanyid=" . EGS_COMPANY_ID;

        if ($db->Execute($query)) {
            return true;
        } else {
            return false;
        }
    }
}

// End of CompanyParams
