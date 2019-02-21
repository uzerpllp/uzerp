<?php
use \League\OAuth2\Client\Provider\GenericProvider;
use \League\OAuth2\Client\Token;

/**
 * Making Tax Digital Oauth2 client and API Access
 */
class MTD {

    private $provider;
    private $accessToken;
    private $base_url;
    
    function __construct($clientId, $clientSecret, $scopes=['write:vat', 'read:vat']) {
        $this->base_url = "https://test-api.service.hmrc.gov.uk";
        $this->provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $clientId,
            'clientSecret'            => $clientSecret,
            'scopes'                  => $scopes,
            'scopeSeparator'          => '+',
            'redirectUri'             => 'http://localhost:8080/?module=vat&controller=vat&action=vatauth',
            'urlAuthorize'            => "{$this->base_url}/oauth/authorize",
            'urlAccessToken'          => "{$this->base_url}/oauth/token",
            'urlResourceOwnerDetails' => "{$this->base_url}/organisations/vat" //?
        ]);
    }

    /**
     * Authorization Code Grant
     * 
     * Enables the user to authorise uzERP to access the Making Tax Digital VAT api
     * on behalf of the organisation
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/authorisation/user-restricted-endpoints
     */
    function authorizationGrant() {
        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {
        
            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $this->provider->getAuthorizationUrl();
        
            // Get the state generated for you and store it to the session.
            $_SESSION['oauth2state'] = $this->provider->getState();
        
            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            exit;
        
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
        
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            
            exit('Invalid state');
        
        } else {
        
            try {
        
                // Try to get an access token using the authorization code grant.
                $this->accessToken = $this->provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                $storage = new OauthStorage();
                if (!$storage->storeToken($this->accessToken, 'vat-mtd')) {
                    echo 'failed to save access token';
                    exit;
                }
                
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        
                // Failed to get the access token or user details.
                exit($e->getMessage());
        
            }
        }
    }

    /**
     * Check and refresh Oauth2 Access Token
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/authorisation/user-restricted-endpoints
     */
    function refreshToken() {
        $storage = new OauthStorage();
        $existingAccessToken = $storage->getToken('vat-mtd');

        if ($existingAccessToken) {
            if ($existingAccessToken->hasExpired()) {
                try {
                    $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                        'refresh_token' => $existingAccessToken->getRefreshToken()
                    ]);

                    $storage->update($storage->id, ['access_token', 'expires'], [$newAccessToken->getToken(), $newAccessToken->getExpires()]);
                    return;
                } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                    // Assume the the refresh token is no longer valid, re-authorise the application
                    $storage->deleteToken('vat-mtd');
                    $this->authorizationGrant();
                }
            }
        } else {
            $this->authorizationGrant();
        }
    }

    /**
     * Get VAT Obligations
     * 
     * @param string $vrn  VAT Registration Number
     * @param array $qparams  Assoc array of query string paramters
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/api/service/vat-api/1.0#_retrieve-vat-obligations_get_accordion
     */
    function getObligations(String $vrn, $qparams) {
        $this->refreshToken();
        $storage = new OauthStorage();
        $accesstoken = $storage->getToken('vat-mtd');
        if ($accesstoken) {
            $query_string = '?';
            foreach ($qparams as $var => $qparam) {
                $query_string .= "{$var}=$qparam";
                if (next($qparams)) {
                    $query_string .= '&' ;
                }
            }
            $endpoint = "{$this->base_url}/organisations/vat/{$vrn}/obligations";
            $url = $endpoint . $query_string;
            
            $request = $this->provider->getAuthenticatedRequest(
                'GET',
                $url,
                $accesstoken->getToken(),
                [
                    'headers' => [
                    'Accept' => 'application/vnd.hmrc.1.0+json',
                    'Content-Type' => 'application/json']
                ]
            );
            
            $response = $this->provider->getResponse($request);
            
            echo var_dump(json_decode($response->getBody(), true));
            exit;
        } else {
            // empty token - re-auth
            $this->authorizationGrant();
        }
    }
}

?>