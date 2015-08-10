<?php

/**
 * LDAPDBAuthenticator confirms that a user account exists with username
 *
 * @implements AuthenticationGateway
 * @package login
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class LDAPDBAuthenticator implements AuthenticationGateway
{

    /**
     * Test that a username exists in the database
     *
     * @param array $params
     * @return bool
     * @see AuthenticationGateway::Authenticate()
     */
    public function Authenticate(Array $params)
    {
        if (! isset($params['username']) || empty($params['db']))
            throw new Exception('LDAPDBAuthenticator expects a DB connection and a username');
        
        $db = $params['db'];
        
        $query = 'SELECT u.username FROM users u LEFT JOIN user_company_access uca
                    ON (u.username=uca.username) LEFT JOIN  system_companies sc
                    ON (uca.usercompanyid=sc.id)
                    WHERE sc.enabled AND uca.enabled
                    AND u.username=?';
        
        $query_params = array(
            $params['username']
        );
        
        $test = $db->GetOne($query, $query_params);
        
        if ($test !== false && ! is_null($test)) {
            return true;
        }
        
        return false;
    }
}
?>
