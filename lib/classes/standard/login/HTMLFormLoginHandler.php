<?php

/**
 * HTMLFormLoginHandler confirms that a user is authorised to access the system
 * using data supplied by the html login form
 *
 * @implements LoginHandler
 * @param AuthenticationGateway
 * @package login
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class HTMLFormLoginHandler implements LoginHandler
{

    protected $version = '$Revision: 1.3 $';

    private $gateway;

    public function __construct(AuthenticationGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function doLogin()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $db = DB::Instance();
        return $this->gateway->Authenticate(array(
            'username' => $username,
            'password' => $password,
            'db' => $db
        ));
    }

    public function interactive()
    {
        return true;
    }
}

?>
