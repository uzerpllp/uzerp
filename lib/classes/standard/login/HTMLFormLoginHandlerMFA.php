<?php
/**
 * Authenticates the user using data supplied by the html login form
 * and validates second factor.
 * 
 * @author uzERP LLP
 * @license GPLv3 or later
 * @copyright (c) 2022 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

class HTMLFormLoginHandlerMFA implements MFALoginHandler
{
    private $gateway;
    //private $validator;

    public function __construct(AuthenticationGateway $gateway, MFAValidator $validator)
    {
        $this->gateway = $gateway;
        $this->validator = $validator;
    }

    public function doLogin()
    {
        $user_authenticated = false;

        $username = $_POST['username'];
        $password = $_POST['password'];
        $db = DB::Instance();

        // Authenticate the user
        $user_authenticated = $this->gateway->Authenticate(array(
            'username' => $username,
            'password' => $password,
            'db' => $db
        ));

        if ($user_authenticated === true) {
            return true;
        }

        return false;
    }

    public function interactive()
    {
        return true;
    }

    public function require_factor()
    {
        return true;
    }
}
