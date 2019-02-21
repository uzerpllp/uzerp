<?php
use \League\OAuth2\Client\Token;

/**
 * Store and Manage Oauth2 Access Tokens
 */
class OauthStorage extends DataObject
{
    public function __construct($tablename='oauth') {
        parent::__construct($tablename);
        $this->idField='id';
    }

    /**
     * @param AccessToken $accesstoken  Access Token to be stored
     * @param String $target_key  A lookup key representing the related api
     * @return Bool true/false  Success/failure
     */
    public function storeToken(\League\OAuth2\Client\Token\AccessToken $accesstoken, string $target_key) {
        $this->id = 'NULL';
        $this->target_key = $target_key;
        $this->access_token = $accesstoken->getToken();
        $this->refresh_token = $accesstoken->getRefreshToken();
        $this->expires = $accesstoken->getExpires();
        if (!$this->save()) {
            return false;
        }
        return true;
    }

    /**
     * @param String $target_key  A lookup key representing the related api
     * @return mixed AccessToken or false
     */
    public function getToken(string $target_key) {
        $this->loadBy('target_key', $target_key);
        if (!$this->isLoaded()) {
            return false;
        }
        $accesstoken = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'expires' => $this->expires]);
        return $accesstoken;
    }

    /**
     * @param String $target_key  A lookup key representing the related api
     * @return mixed AccessToken or false
     */
    public function deleteToken($target_key) {
        $this->loadBy('target_key', $target_key);
        if (!$this->isLoaded()) {
            return false;
        }
        if (!$this->delete($this->id)) {
            return false;
        }
        return true;
    }
}
?>