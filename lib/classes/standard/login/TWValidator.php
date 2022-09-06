<?php
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Endroid\QrCode\QrCode;

/**
 * Handle Twilio Verify TOTP Enrollment and Validation
 * 
 * @author uzERP LLP
 * @license GPLv3 or later
 * @copyright (c) 2022 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class TWValidator implements MFAValidator
{
    public function __construct()
    {
        $this->twillio_errors = [404, 60310, 60311];
        $dotenv = Dotenv\Dotenv::createImmutable(FILE_ROOT . 'conf/');
        $dotenv->safeLoad();
        try {
            // Check we have all the configs
            $dotenv->required(['TWILIO_ACCOUNT_SID',
                               'TWILIO_AUTH_TOKEN',
                               'TWILIO_SERVICE_SID'])->notEmpty();
            $sid = $_ENV["TWILIO_ACCOUNT_SID"];
            $token = $_ENV["TWILIO_AUTH_TOKEN"];
        }
        catch (Exception $e)
        {
            throw new Exception('Cannot start application, 
                some system configuration values are missing. 
                Please see the log for more information.');
            exit();
        }

        // Create the Twilio API client
        $this->twilio = new Client($sid, $token);
    }


    /**
     * Enroll a user with Twilio Verify
     *
     * @param User $user
     * @return array
     */
    public function Enroll(User $user, &$errors)
    {
        try {
            $new_factor = $this->twilio->verify->v2->services($_ENV["TWILIO_SERVICE_SID"])
                ->entities($user->uuid)
                ->newFactors
                ->create("{$user->username}", "totp");
        } catch (Exception $e) {
            $errors[$e->getCode()] = $e->getMessage();
        }

        $pack = [];
        $qrcode = new QrCode($new_factor->binding["uri"]);
        $pack['qrCode'] = $qrcode->writeString();
        $pack['uri'] = $new_factor->binding["uri"];
        $pack['secret'] = $new_factor->binding["secret"];
        $pack['sid'] = $new_factor->sid;
        $pack['status'] = $new_factor->status;

        return $pack;
    }


    /**
     * Verify Twilio Verify Enrollment
     *
     * @param User $user
     * @param array $params
     * @param string $payload
     * @return void
     */
    public function VerifyEnroll(User $user, array $params, string $payload, &$errors)
    {
        try {
            $factor = $this->twilio->verify->v2->services($_ENV["TWILIO_SERVICE_SID"])
                ->entities($user->uuid)
                ->factors($params['sid'])
                ->update(["authPayload" => $payload]);
        } catch (Exception $e) {
            $errors[$e->getCode()] = $e->getMessage();
            if ( in_array($e->getCode(), $this->verify_errors)) {
                $errors['verification-failed'] = $e->getMessage();
            }
        }

        if ($factor->status === 'verified') {
            return true;
        }

        return false;
    }


    /**
     * Validate Twilio Verify Token
     *
     * @param User $user
     * @param String $payload
     * @return void
     */
    public function ValidateToken(User $user, string $payload, &$errors)
    {
        try {
            $challenge = $this->twilio->verify->v2->services($_ENV["TWILIO_SERVICE_SID"])
                ->entities($user->uuid)
                ->challenges
                ->create($user->mfa_sid, ["authPayload" => $payload]);
        } catch (TwilioException $e) {
            $errors[$e->getCode()] = $e->getMessage();
        }

        if ($challenge->status === 'approved') {
            return true;
        }
        return false;
    }

    /**
     * Remove entity from Twilio Verify
     *
     * @param User $user
     * @param [type] $errors
     * @return void
     */
    public function ResetEnrollment(User $user, &$errors)
    {
        try {
            $factors = $this->twilio->verify->v2->services($_ENV["TWILIO_SERVICE_SID"])
                              ->entities($user->uuid)
                              ->delete();
        } catch (TwilioException $e) {
            $errors[$e->getCode()] = $e->getMessage();
        }
    }
}
