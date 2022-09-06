<?php

/**
 * MFALoginHandler authorises a user to access the system
 *
 * @author uzERP LLP
 * @license GPLv3 or later
 * @copyright (c) 2022 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
interface MFALoginHandler
{
    public function __construct(AuthenticationGateway $gateway, MFAValidator $validator);

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

    /**
     * Indicates that a second factor is required on login
     *
     * @return void
     */
    public function require_factor();
}