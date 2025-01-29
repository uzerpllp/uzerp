<?php

use Ramsey\Uuid\Uuid;
use \Symfony\Component\Yaml\Yaml;

test('Validate MTD Fraud Prevention Headers with HMRC API', function () {

    // API documentation, see: https://developer.service.hmrc.gov.uk/api-documentation/docs/api/service/txm-fph-validator-api/1.0
    // Headers required, see: https://developer.service.hmrc.gov.uk/guides/fraud-prevention/connection-method/web-app-via-server/

    // Remove PHPUnit's error handler
    restore_error_handler();
    
    // Mock data from the client browser via JS
    $client_browser_data = '{"ip":"","iptime":"2023-02-28T13:08:38.342Z","pixelRatio":1,"screenWidth":1920,"screenHeight":1080,"colorDepth":24,"windowWidth":970,"windowHeight":1048,"plugins":[{"mime":"application/pdf","name":"PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"text/pdf","name":"PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"application/pdf","name":"Chrome PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"text/pdf","name":"Chrome PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"application/pdf","name":"Chromium PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"text/pdf","name":"Chromium PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"application/pdf","name":"Microsoft Edge PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"text/pdf","name":"Microsoft Edge PDF Viewer","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"application/pdf","name":"WebKit built-in PDF","description":"Portable Document Format","filename":"internal-pdf-viewer"},{"mime":"text/pdf","name":"WebKit built-in PDF","description":"Portable Document Format","filename":"internal-pdf-viewer"}],"userAgent":"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36","dnt":"false"}';

    // Set client and server IPs
    $_SERVER['REMOTE_PORT'] = '64778';
    $_SERVER['REMOTE_ADDR'] = '198.51.100.10';
    $_SERVER['SERVER_ADDR'] = '203.0.113.100';

    // Manually load all oauth configs
    $sandboxes = Yaml::parse(file_get_contents('conf/oauth.yml'));

    foreach ($sandboxes as $oauth_key => $config) {
        // Ignore any config that does not target the HMRC test API
        if ($config['baseurl'] !== 'https://test-api.service.hmrc.gov.uk') continue;
        
            $mtd = new MTD($client_browser_data, $oauth_key);

        $headers = $mtd->fraud_protection_headers;

        $uuid = Uuid::uuid5(Uuid::NAMESPACE_X500, EGS_USERNAME. '@' . $_SERVER['REMOTE_ADDR']);
        $headers['Gov-Client-Device-ID'] = $uuid->toString();

        $accessToken = $mtd->provider->getAccessToken('client_credentials');

        $url = "https://test-api.service.hmrc.gov.uk/test/fraud-prevention-headers/validate";
        $request = $mtd->provider->getAuthenticatedRequest(
            'GET',
            $url,
            $accessToken->getToken(),
            [
                'headers' => array_merge([
                'Accept' => 'application/vnd.hmrc.1.0+json',
                'Content-Type' => 'application/json'], $headers),
                'body' => '',
            ]
        );

        $responses = [];
        try
        {
            $response = $mtd->provider->getResponse($request);
            $responses[] = $response;
            if (!empty(json_decode($response->getBody(), true)['errors'])) {
                foreach (json_decode($response->getBody(), true)['errors'] as $error ) {
                    $hdr = implode('/', $error['headers']);
                    echo "{$error['code']}, {$hdr}: {$error['message']}\n";
                }
                echo "\n";
                foreach ($headers as $header => $value) {
                    echo "{$header}: {$value}\n";
                }
                echo "\n";
            }
        }
        catch (Exception $e)
        {
            $api_errors = json_decode($e->getResponse()->getBody()->getContents());
            if (is_countable($api_errors) && count($api_errors) > 1) {
                foreach ($api_errors->errors as $error) {
                    echo "{$error->code} {$error->message}\n";
                }
            } else {
                echo $e;
            }
        }
    }

    foreach ($responses as $r) {
        expect(json_decode($r->getBody(), true)['errors'])->toBeEmpty();
    }
});