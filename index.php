<?php

/**
 *	uzERP Start-up Entry Point
 *
 *  @version $Revision: 1.12 $
 *  @package uzerp
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	This file is part of uzERP.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 *
 *	uzERP is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with uzERP.  If not, see <http://www.gnu.org/licenses/>.
 **/
session_start();

// define time at start of request
define('START_TIME', microtime(TRUE));

require 'system.php';

$system = system::Instance();

// *******
// CONFIG

// we have to load parts of system to load the config
$system->check_system();
$system->load_essential();

// **************************
// ERROR REPORTING & LOGGING
if (defined('SENTRY_DSN')) {
    // custom exception handler, sends to sentry
    set_exception_handler('sentry_exception_handler');

    // send fatals to sentry
    try {
        $client = new Raven_Client(SENTRY_DSN, unserialize(SENTRY_CONFIG));
        $client->tags_context(array(
            'source' => 'fatal'
        ));
        $error_handler = new Raven_ErrorHandler($client);
        $error_handler->registerShutdownFunction();
    } catch (Exception $e) {
        // If something went wrong, just continue.
    }
} else {
    // custom exception handler, show error to user
    set_exception_handler('uzerp_exception_handler');
}

function sentry_exception_handler($exception)
{
    error_log($exception);
    try {
        $client = new Raven_Client(SENTRY_DSN, unserialize(SENTRY_CONFIG));
        $client->tags_context(array(
            'source' => 'exception'
        ));
        $config = Config::Instance();
        $event_id = $client->getIdent($client->captureException($exception, array(
            'extra' => array(
                'uzerp_version' => $config->get('SYSTEM_VERSION')
            )
        )));

        $smarty = new Smarty();
        $smarty->assign('config', $config->get_all());
        $smarty->compile_dir = DATA_ROOT . 'templates_c';
        $smarty->assign('event_id', $event_id);
        $smarty->assign('support_email', SUPPORT_EMAIL);
        $email_body = "uzERP Exception logged to sentry with ID: " . $event_id;
        $smarty->assign('email_body', rawurlencode($email_body));
        $smarty->assign('xhr', false);
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $smarty->assign('xhr', true);
        }
        $smarty->display(STANDARD_TPL_ROOT . 'error.tpl');
    } catch (Exception $e) {
        // If something went wrong, just continue.
    }
}

function uzerp_exception_handler($exception)
{
    $config = Config::Instance();

    // send the error to print dialogs via a json response
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_REQUEST['ajax_print'])) {
        // log the error
        error_log($exception);
        header('Content-type: application/json');
        $options = [];
        $options['status'] = false;
        if ($config->get('ENVIRONMENT') == 'development') {
            $options['message'] = "Error: {$exception->getMessage()}. {$exception->getFile()}, {$exception->getLine()}";
        } else {
            $options['message'] = 'Failed to print document, further information may be found in the logs';
        }
        // return the html response
        echo json_encode($options);
        exit();
    }

    $smarty = new Smarty();
    $smarty->assign('config', $config->get_all());
    $smarty->compile_dir = DATA_ROOT . 'templates_c';
    $smarty->assign('exception_message', $exception->getMessage());
    $smarty->assign('support_email', $config->get('ADMIN_EMAIL'));
    $email_body = "Request: " . $_SERVER['REQUEST_URI'] . "\n";
    $email_body .= "uzERP Version: " . $config->get('SYSTEM_VERSION') . "\n\n" . $exception->getMessage();
    $smarty->assign('email_body', rawurlencode($email_body));
    $smarty->assign('xhr', false);
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $smarty->assign('xhr', true);
    }
    $smarty->display(STANDARD_TPL_ROOT . 'error.tpl');
}

// set the error reporting based on the environment
switch (strtolower(get_config('ENVIRONMENT'))) {

    case 'development':
        // All errors except notices will be sent to the log
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set("display_errors", false);
        $config = Config::Instance();
        $logfile = $config->get('UZERP_LOG_PATH');
        ini_set("error_log", $logfile);
        break;

    case 'production':
    default:
        error_reporting(E_ERROR);
        break;
}

// *******************
// LOAD THE FRAMEWORK

$system->display();

if (AUDIT || get_config('AUDIT_LOGIN')) {

    if (is_array($system->controller->_data) && isset($system->controller->_data['password'])) {
        $system->controller->_data['password'] = '********************';
    }

    $audit = Audit::Instance();
    $audit->write(print_r($system->controller->_data, TRUE) . print_r($system->flash, TRUE), TRUE, (microtime(TRUE) - START_TIME));
    $audit->update();
}
?>
