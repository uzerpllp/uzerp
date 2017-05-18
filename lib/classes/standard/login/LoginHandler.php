<?php

/**
 * LoginHandler authorises a user to access the system
 *
 * @param AuthenticationGateway
 * @version $Revision: 2.0 $
 * @package login
 * @author uzERP LLP
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
interface LoginHandler
{

    public function __construct(AuthenticationGateway $gateway);

    /**
     * Check username and password using AuthenticationGateway
     *
     * @return bool
     */
    public function doLogin();

    /**
     * Indicate if this is a interactive login handler
     *
     * For a login that requires the html form to be shown, return TRUE.
     * Otherwise, return false.
     *
     * @return bool
     */
    public function interactive();
}
?>
