<?php
/**
 * Making Tax Digital for VAT
 * Oauth2 client and API Access
 */

use \League\OAuth2\Client\Provider\GenericProvider;
use \League\OAuth2\Client\Token;

class MTD {

    public $provider;
    private $accessToken;
    private $base_url;
    public $fraud_protection_headers;
    private $vrn;
    private $config_key;
    private $client_fp_info;
    private $logger;
    private $api_part;
    
    /**
     * MTD Class Constructor
     *
     * @param boolean $client_fp_info  String encoded JSON sent from the client browser
     * @param string $config_key  Key to lookup settings in Oauth config
     */
    function __construct($client_fp_info='', $config_key='mtd-vat') {
        $logger = uzLogger::Instance();
        // set log 'channel' for MTD log messages
        $this->logger = $logger->withName('uzerp_mtd');
        $this->config_key = $config_key;
        $this->client_fp_info = json_decode($client_fp_info);
        $company = DataObjectFactory::Factory('Systemcompany');
        $company->load(EGS_COMPANY_ID);
        $this->vrn = $company->getVRN();

        $oauth_config = OauthStorage::getconfig($this->config_key);

        $this->base_url = $oauth_config['baseurl'];
        $this->api_part = "/organisations/vat/{$this->vrn}";
        $this->provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $oauth_config['clientid'],
            'clientSecret'            => $oauth_config['clientsecret'],
            'scopes'                  => ['write:vat', 'read:vat'],
            'scopeSeparator'          => '+',
            'redirectUri'             => $oauth_config['redirecturl'],
            'urlAuthorize'            => "{$this->base_url}/oauth/authorize",
            'urlAccessToken'          => "{$this->base_url}/oauth/token",
            'urlResourceOwnerDetails' => "{$this->base_url}/organisations/vat" //required by the provider, but not implemented by the API
        ]);

        $config = Config::Instance();
        $current   = timezone_open('Europe/London');
        $utcTime  = new \DateTime('now', new \DateTimeZone('UTC'));
        $offsetInSecs =  $current->getOffset($utcTime);
        $hoursAndSec = gmdate('H:i', abs($offsetInSecs));
        $utc_offset = stripos($offsetInSecs, '-') === false ? "+{$hoursAndSec}" : "-{$hoursAndSec}";
        $uz_user = rawurlencode(constant('EGS_USERNAME'));
        $uz_version = rawurlencode((string) $config->get('SYSTEM_VERSION'));

        $this->fraud_protection_headers = [
            'Gov-Client-Connection-Method' => 'WEB_APP_VIA_SERVER',
            'Gov-Client-User-IDs' => "uzerp={$uz_user}",
            'Gov-Client-Timezone' => "UTC{$utc_offset}",
            'Gov-Vendor-Version' => "uzerp={$uz_version}"
        ];

        if (isset($oauth_config['productname']) && $oauth_config['productname'] !== '') {
            $this->fraud_protection_headers['Gov-Vendor-Product-Name'] = rawurlencode((string) $oauth_config['productname']);
        } else {
            $this->fraud_protection_headers['Gov-Vendor-Product-Name'] = 'uzERP';
        }

        // Gov-Client-Public-IP
        // Gov-Client-Public-Port
        // Only if uzERP host is on the internet
        // Not for clients connecting on private networks
        $client_public_ip = $_SERVER['REMOTE_ADDR'];
        if (!self::ip_is_private($client_public_ip)) {
            $this->fraud_protection_headers['Gov-Client-Public-IP'] = $client_public_ip;
            $this->fraud_protection_headers['Gov-Client-Public-Port'] = $_SERVER['REMOTE_PORT'];

            // Gov-Client-Public-IP-Timestamp
            $this->fraud_protection_headers['Gov-Client-Public-IP-Timestamp'] = $utcTime->format('Y-m-d\TH:i:s.v\Z');
        }

        // Gov-Client-Device-ID
        // Generate a uuid based on the username
        if (isset($_COOKIE["uzerpdevice"])) {
            $this->fraud_protection_headers['Gov-Client-Device-ID'] = $_COOKIE["uzerpdevice"];
        }

        // Gov-Client-Screens
        // Gov-Client-Window-Size
        $this->fraud_protection_headers['Gov-Client-Screens'] = "width={$this->client_fp_info->screenWidth}&height={$this->client_fp_info->screenHeight}&scaling-factor={$this->client_fp_info->pixelRatio}&colour-depth={$this->client_fp_info->colorDepth}";
        $this->fraud_protection_headers['Gov-Client-Window-Size'] = "width={$this->client_fp_info->windowWidth}&height={$this->client_fp_info->windowHeight}";

        // Gov-Client-Browser-Plugins
        // Modern browsers return an empty list
        $browser_plugins = $this->client_fp_info->plugins;
        if (count($browser_plugins) > 0) {
            $plugin_names = [];
            foreach ($browser_plugins as $plugin) {
                $plugin_names[] = rawurlencode($plugin->name);
            }
            $this->fraud_protection_headers['Gov-Client-Browser-Plugins'] = implode(',', $plugin_names);
        }

        // Gov-Client-Browser-JS-User-Agent
        $this->fraud_protection_headers['Gov-Client-Browser-JS-User-Agent'] = $this->client_fp_info->userAgent;

        // Gov-Client-Browser-Do-Not-Track
        $this->fraud_protection_headers['Gov-Client-Browser-Do-Not-Track'] = $this->client_fp_info->dnt;

        // Gov-Vendor-Public-IP
        // The public IP address of the servers the originating device sent their requests to.
        // Public networks only
        $server_ip = $_SERVER['SERVER_ADDR'];
        if (!self::ip_is_private($server_ip)) {
            $this->fraud_protection_headers['Gov-Vendor-Public-IP'] = rawurlencode((string) $server_ip);
        }

        // Gov-Vendor-Forwarded
        // A list that details hops over the internet between services that terminate Transport Layer Security (TLS).
        // Each key and value must be percent encoded. Do not percent encode separators (equal signs, ampersands and commas).
        // Public networks only
        if (!self::ip_is_private($_SERVER['SERVER_ADDR'])) {
            $by_ip = rawurlencode((string) $_SERVER['SERVER_ADDR']);
            $for_ip = rawurlencode((string) $client_public_ip);
            $this->fraud_protection_headers['Gov-Vendor-Forwarded'] = "by={$by_ip}&for={$for_ip}";
        }

        $this->logger->info('Fraud prevention headers set', [$this->fraud_protection_headers]);
        //foreach ($this->fraud_protection_headers as $name => $val) {
        //    $this->logger->info("$name: $val");
        //}
    }

    /**
     * Oauth2: Authorization Code Grant
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
            
            exit('Oauth Error: Invalid state on authorization grant');
        
        } else {
            try
            {
                // Try to get an access token using the authorization code grant.
                $this->accessToken = $this->provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                $storage = new OauthStorage();
                if (!$storage->storeToken($this->accessToken, $this->config_key)) {
                    exit('Oauth Error: failed to save access token after authorization grant');
                }
            }
            catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e)
            {
                // Failed to get the access token or user details.
                $response = $e->getResponseBody();
                exit("Oauth Error: {$response['error']}, {$response['error_description']}");
            }
        }
    }

    /**
     * Oauth2: Check and refresh Oauth2 Access Token
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/authorisation/user-restricted-endpoints
     */
    function refreshToken() {
        $flash = Flash::Instance();
        $storage = new OauthStorage();
        $existingAccessToken = $storage->getToken($this->config_key);

        if ($existingAccessToken !== false) {
            if ($existingAccessToken->hasExpired()) {
                try
                {
                    $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                        'refresh_token' => $existingAccessToken->getRefreshToken()
                    ]);

                    $storage->deleteToken($this->config_key);
                    $newStorage = new OauthStorage();
                    if (!$newStorage->storeToken($newAccessToken, $this->config_key)) {
                        $flash->addError("Oauth Error: Failed to store access token after refresh");
                        return false;
                    }
                }
                catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e)
                {
                    $response = $e->getResponseBody();
                    
                    // If authorization grant has expired, re-authorise application
                    if ($response['error'] == 'invalid_request' || $response['error'] == 'invalid_grant') {
                        $storage->deleteToken($this->config_key);
                        $this->authorizationGrant();
                    }
                    $flash->addWarning("Oauth Error: {$response['error']}, {$response['error_description']}");
                    return false;
                }
            }
            return true;
        } else {
            $this->authorizationGrant();
        }
    }

    /**
     * API: Get VAT Obligations
     * 
     * @param array $qparams  Assoc array of query string paramters
     * @return array
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/api/service/vat-api/1.0#_retrieve-vat-obligations_get_accordion
     */
    function getObligations($qparams) {
        $flash = Flash::Instance();

        $this->refreshToken();
        $storage = new OauthStorage();
        $accesstoken = $storage->getToken($this->config_key);
        if (!$accesstoken) {
            $this->authorizationGrant();
        }

        $query_string = '?';
        foreach ($qparams as $var => $qparam) {
            $query_string .= "{$var}=$qparam";
            if (next($qparams)) {
                $query_string .= '&' ;
            }
        }
        $endpoint = "{$this->base_url}{$this->api_part}/obligations";
        $url = $endpoint . $query_string;
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $url,
            $accesstoken->getToken(),
            [
                'headers' => array_merge([
                'Accept' => 'application/vnd.hmrc.1.0+json',
                'Content-Type' => 'application/json'], $this->fraud_protection_headers)
            ]
        );

        try
        {
            $response = $this->provider->getResponse($request);
            return json_decode((string) $response->getBody(), true);
        }
        catch (Exception $e)
        {
            $api_errors = json_decode((string) $e->getResponse()->getBody()->getContents());
            if (count($api_errors) > 1) {
                foreach ($api_errors->errors as $error) {
                    $this->logger->error("{$error->code} {$error->message}", [__METHOD__]);
                    $flash->addError("{$error->code} {$error->message}");
                }
            } else {
                $this->logger->error("{$api_errors->code} {$api_errors->message}", [__METHOD__]);
                $flash->addError("{$api_errors->code} {$api_errors->message}");
            }
            return false;
        }
    }

    /**
     * API: Submit VAT Return
     * 
     * @param string $year
     * @param string $tax_period
     * @return boolean
     */
    function postVat($year, $tax_period) {
        $flash = Flash::Instance();
        $this->refreshToken();
        $storage = new OauthStorage();
        $accesstoken = $storage->getToken($this->config_key);
        if (!$accesstoken) {
            $this->authorizationGrant();
        }

        try {
            $return = new VatReturn;
            $return->loadVatReturn($year, $tax_period);
        }
        catch (VatReturnStorageException $e)
        {
            $this->logger->error($e->getMessage(), ['class_method' => __METHOD__]);
            $flash->addError($e->getMessage());
            return false;
        }
        
        // Use the collection becuase it has required info, like the period end date
        $returnc = new VatReturnCollection;
        $sh = new SearchHandler($returnc, false);
        $cc = new ConstraintChain();
        $cc->add(new Constraint('year', '=', $year));
        $cc->add(new Constraint('tax_period', '=', $tax_period));
        $cc->add(new Constraint('finalised', 'is', 'false'));
        $sh->addConstraintChain($cc);
        $returnc->load($sh);
        if(iterator_count($returnc) == 0){
            $flash->addError('No un-submitted return found');
            return false;
        }
        $returnc->rewind();

        // Find the matching obligation and get the HMRC period key
        $obligations = $this->getObligations(['status' => 'O']);
        if (!$obligations) {
            return false;
        }
        foreach ($obligations['obligations'] as $obligation) {
            if ($obligation['end'] == $returnc->current()->enddate) {
                try {
                    $return->setVatReturnPeriodKey($year, $tax_period, $obligation['periodKey']);
                } catch (VatReturnStorageException  $e) {
                    $this->logger->error($e->getMessage(), ['class_method' => __METHOD__]);
                    $flash->addError($e->getMessage());
                    $flash->addError("Failed to submit return for {$year}/{$tax_period}");
                    return false;
                }
                
                $body = [
                    'periodKey' => $obligation['periodKey'],
                    'vatDueSales' => round($return->vat_due_sales,2),
                    'vatDueAcquisitions' => round($return->vat_due_acquisitions,2),
                    'totalVatDue' => round($return->total_vat_due,2),
                    'vatReclaimedCurrPeriod' => round($return->vat_reclaimed_curr_period,2),
                    'netVatDue' => abs(round($return->net_vat_due,2)),
                    'totalValueSalesExVAT' => round($return->total_value_sales_ex_vat),
                    'totalValuePurchasesExVAT' => round($return->total_value_purchase_ex_vat),
                    'totalValueGoodsSuppliedExVAT' => round($return->total_value_goods_supplied_ex_vat),
                    'totalAcquisitionsExVAT' => round($return->total_acquisitions_ex_vat),
                    'finalised' => true
                ];

                $url = "{$this->base_url}{$this->api_part}/returns";
                $request = $this->provider->getAuthenticatedRequest(
                    'POST',
                    $url,
                    $accesstoken->getToken(),
                    [
                        'headers' => array_merge([
                        'Accept' => 'application/vnd.hmrc.1.0+json',
                        'Content-Type' => 'application/json'], $this->fraud_protection_headers),
                        'body' => json_encode($body),
                    ]
                );

                try
                {
                    $this->logger->info('Submitting VAT return', [
                        'vat_return_data' => $body,
                        'class_method' =>__METHOD__]);
                    $response = $this->provider->getResponse($request);
                    $rbody = json_decode((string) $response->getBody(), true);
                    $rheader['Receipt-ID'] = $response->getHeader('Receipt-ID')[0];
                    $details = array_merge($rbody, $rheader);
                    $this->logger->info('VAT return submission response', [
                        'http_status' => $response->getStatusCode(),
                        'http_response_message' => $response->getReasonPhrase(),
                        'http_response_body' => $rbody,
                        'class_method' => __METHOD__]);
                    $return->saveSubmissionDetail($year, $tax_period, $details); //catch exception and log this info, it may fail to save
                    $flash->addMessage("VAT Return Submitted for {$year}/{$tax_period}");
                    return true;
                }
                catch (VatReturnStorageException $e)
                {
                    $this->logger->error('VAT return storage error', ['error_message' => $e->getMessage(), 'class_method' => __METHOD__]);
                    $flash->addError("VAT Return {$year}/{$tax_period} submitted, but not updated in uzERP");
                    return false;
                }
                catch (Exception $e)
                {
                    $api_errors = json_decode((string) $e->getResponse()->getBody()->getContents());
                    foreach ($api_errors->errors as $error) {
                        $this->logger->error("HMRC API ERROR: {$error->code} {$error->message}", [
                            'http_status' => $e->getResponse()->getStatusCode(),
                            'class_method' => __METHOD__]);
                        $flash->addError("HMRC API ERROR: {$error->code} {$error->message}");
                    }
                    return false;
                }
            }
        }

        $flash->addWarning("No obligation found for the {$year}/{$tax_period} VAT period");
        return false;
    }

    static function ip_is_private ($ip) {
        $pri_addrs = array (
                          '10.0.0.0|10.255.255.255', // single class A network
                          '172.16.0.0|172.31.255.255', // 16 contiguous class B network
                          '192.168.0.0|192.168.255.255', // 256 contiguous class C network
                          '169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
                          '127.0.0.0|127.255.255.255' // localhost
                         );
    
        $long_ip = ip2long ($ip);
        if ($long_ip != -1) {
    
            foreach ($pri_addrs AS $pri_addr) {
                list ($start, $end) = explode('|', $pri_addr);
    
                 // IF IS PRIVATE
                 if ($long_ip >= ip2long ($start) && $long_ip <= ip2long ($end)) {
                     return true;
                 }
            }
        }
    
        return false;
    }
}
?>
