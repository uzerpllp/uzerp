<?php

/**
 * LDAPLoginHandler confirms that a user is authorised to access the system
 * using data passed from an Apache httpd ldap login
 *
 * @implements LoginHandler
 * @param AuthenticationGateway
 * @package login
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class LDAPLoginHandler implements LoginHandler
{

    private $gateway;

    public function __construct(AuthenticationGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Check that the authenticated user exists
     *
     * @return bool
     * @see LoginHandler::doLogin()
     */
    public function doLogin()
    {
        // get the authenticated username
        $username = $_SERVER['PHP_AUTH_USER'];
        
        $db = DB::Instance();
        $is_authorised = $this->gateway->Authenticate(array(
            'username' => $username,
            'db' => $db
        ));
        
        if ($is_authorised) {
            // populate the _POST globals that are used in IndexController::login()
            $_POST['username'] = $username;
            $_POST['password'] = 'xxx';
            
            return $is_authorised;
        }
        
        return false;
    }
    
    /**
     * Indicate that the html login form should not be shown
     *
     * @return bool
     * @see LoginHandler::interactive()
     */
    public function interactive()
    {
        return false;
    }
}
?>
