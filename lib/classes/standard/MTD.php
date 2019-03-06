<?php
/**
 * Making Tax Digital for VAT
 * Oauth2 client and API Access
 * 
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 * uzERP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 */

use \League\OAuth2\Client\Provider\GenericProvider;
use \League\OAuth2\Client\Token;

class MTD {

    private $provider;
    private $accessToken;
    private $base_url;
    private $fraud_protection_headers;
    private $vrn;
    
    function __construct($clientId, $clientSecret, $scopes=['write:vat', 'read:vat']) {
        $company = DataObjectFactory::Factory('Systemcompany');
        $company->load(EGS_COMPANY_ID);
        $this->vrn = $company->getVRN();

        $this->base_url = "https://test-api.service.hmrc.gov.uk";
        $this->api_part = "/organisations/vat/{$this->vrn}";
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

        $current   = timezone_open('Europe/London');
        $utcTime  = new \DateTime('now', new \DateTimeZone('UTC'));
        $offsetInSecs =  $current->getOffset($utcTime);
        $hoursAndSec = gmdate('H:i', abs($offsetInSecs));
        $utc_offset = stripos($offsetInSecs, '-') === false ? "+{$hoursAndSec}" : "-{$hoursAndSec}";
        $os_info = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' '. php_uname('m');
        $uz_user = constant('EGS_USERNAME');
        $config = Config::Instance();
        
        $this->fraud_protection_headers = [
            'Gov-Client-Connection-Method' => 'OTHER_DIRECT',
            'Gov-Client-User-IDs' => "uzerp={$uz_user}",
            'Gov-Client-Timezone' => "UTC{$utc_offset}",
            'Gov-Client-User-Agent' => $os_info,
            'Gov-Vendor-Version' => "uzerp={$config->get('SYSTEM_VERSION')}"
        ];
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
     * Oauth2: Check and refresh Oauth2 Access Token
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/authorisation/user-restricted-endpoints
     */
    function refreshToken() {
        $storage = new OauthStorage();
        $existingAccessToken = $storage->getToken('vat-mtd');

        if ($existingAccessToken !== false) {
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
     * API: Get VAT Obligations
     * 
     * @param array $qparams  Assoc array of query string paramters
     * @return void
     * 
     * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/api/service/vat-api/1.0#_retrieve-vat-obligations_get_accordion
     */
    function getObligations($qparams) {
        $this->refreshToken();
        $storage = new OauthStorage();
        $accesstoken = $storage->getToken('vat-mtd');
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
        
        $response = $this->provider->getResponse($request);
        
        return json_decode($response->getBody(), true);
    }

    /**
     * API: Submit VAT Return
     * 
     * @param string $year
     * @param string $tax_period
     * @return void
     */
    function postVat($year, $tax_period) {
        $flash = Flash::Instance();

        $this->refreshToken();
        $storage = new OauthStorage();
        $accesstoken = $storage->getToken('vat-mtd');
        if (!$accesstoken) {
            $this->authorizationGrant();
        }

        try {
            $return = new VatReturn;
            $return->loadVatReturn($year, $tax_period);
        }
        catch (VatReturnStorageException $e)
        {
            $flash->addError($e->getMessage());
            sendBack();
        }
        
        // Use the collection becuase it has required info, like the period end date
        $returnc = new VatReturnCollection;
        $sh = new SearchHandler($returnc, false);
        $cc = new ConstraintChain();
        $cc->add(new Constraint('year', '=', $year));
        $cc->add(new Constraint('tax_period', '=', $tax_period));
        $sh->addConstraintChain($cc);
        $returnc->load($sh);
        $returnx = $returnc->current(); // first in the collection, should only be one record

        // Find the matching obligation and get the HMRC period key
        $obligations = $this->getObligations(['status' => 'O']);
        foreach ($obligations as $obligation) {
            if ($obligation[0]['end'] == $returnx->enddate) {
                try {
                    $return->setVatReturnPeriodKey($year, $tax_period, $obligation[0]['periodKey']);
                } catch (VatReturnStorageException  $e) {
                    $flash->addError($e->getMessage());
                    $flash->addError("Failed to submit return for {$year}/{$tax_period}");
                    sendBack();
                }
                
                //var_dump($returnx->enddate);
                $body = [
                    'periodKey' => $obligation[0]['periodKey'],
                    'vatDueSales' => round($return->vat_due_sales,2),
                    'vatDueAcquisitions' => round($return->vat_due_aquisitions,2),
                    'totalVatDue' => round($return->total_vat_due,2),
                    'vatReclaimedCurrPeriod' => round($return->vat_reclaimed_curr_period,2),
                    'netVatDue' => round($return->net_vat_due,2),
                    'totalValueSalesExVAT' => round($return->total_value_sales_ex_vat),
                    'totalValuePurchasesExVAT' => round($return->total_value_purchase_ex_vat),
                    'totalValueGoodsSuppliedExVAT' => round($return->total_value_goods_supplied_ex_vat),
                    'totalAcquisitionsExVAT' => round($return->total_aquisitions_ex_vat),
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
                    $response = $this->provider->getResponse($request);
                    $rbody = json_decode($response->getBody(), true);
                    $rheader['Receipt-ID'] = $response->getHeader('Receipt-ID')[0];
                    $details = array_merge($rbody, $rheader);
                    $return->saveSubmissionDetail($year, $tax_period, $details); //catch exception and log this info, it may fail to save
                    $flash->addMessage("VAT Return Submitted for {$year}/{$tax_period}");
                    sendBack();
                    //echo var_dump(json_decode($rbody, true));
                    //echo var_dump($rheader);
                }
                catch (VatReturnStorageException $e)
                {
                    $flash->addError("VAT Return {$year}/{$tax_period} submitted, but not updated in uzERP");
                    sendBack();
                }
                catch (Exception $e)
                {
                    $api_errors = json_decode($e->getResponse()->getBody()->getContents());
                    foreach ($api_errors->errors as $error) {
                        $flash->addError("{$error->code} {$error->message}");
                        echo "{$error->code} {$error->message}";
                        sendBack();
                    }
                }
            } else {
                $flash->addWarning('No obligation found for the VAT period');
           }
        }
    }
}
?>