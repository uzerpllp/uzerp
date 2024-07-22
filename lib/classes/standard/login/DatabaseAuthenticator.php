<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class DatabaseAuthenticator implements AuthenticationGateway
{
    protected $db;

    private function update_hash($password, $username)
    {
        try {
            $new_hash = password_hash((string) $password, PASSWORD_DEFAULT);
            $update_query = 'UPDATE users SET password=? WHERE username=?;';

            $update_query_params = array(
                $new_hash,
                $username
            );

            $result = $this->db->Execute($update_query, $update_query_params);
            if ($this->db->affected_rows() !== 1) {
                throw new Exception('DatabaseAuthenticator::update_hash, failed to update password hash for user');
            }
        } catch (Exception $e) {
            echo $e->getMessage(), "\n";
            exit;
        }
    }

    public function Authenticate(Array $params)
    {
        if (! isset($params['username']) || ! isset($params['password']) || empty($params['db'])) {
            throw new Exception('DatabaseAuthenticator expects a connection, a username and a password');
        }

        $this->db = $params['db'];
        $query = 'SELECT u.username, u.password FROM users u
                    LEFT JOIN user_company_access uca ON (u.username=uca.username)
                    LEFT JOIN system_companies sc ON (uca.usercompanyid=sc.id)
                    WHERE sc.enabled AND uca.enabled AND u.username=?';

        $query_params = array(
            $params['username']
        );

        $test = $this->db->GetAssoc($query, $query_params);

        if ($test !== false && ! is_null($test)) {
            // try against default hash
            if (password_verify((string) $params['password'], (string) $test[$params['username']])) {
                if (password_needs_rehash($test[$params['username']], PASSWORD_DEFAULT)) {
                    $this->update_hash($params['password'], $params['username']);
                }

                return TRUE;
            }

            // try against hashed md5
            if (password_verify(md5((string) $params['password']), (string) $test[$params['username']])) {
                // update hash
                $this->update_hash($params['password'], $params['username']);

                return TRUE;
            }
        }

        return FALSE;
    }
}
?>
