<?php

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Processor\WebProcessor;

/**
 * Singleton wrapper around Monolog
 * 
 * Logs to a file defined in the uzERP config
 * or to the PHP error_log if not.
 *
 * @author uzERP LLP, Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 * 
 * uzERP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 */
class uzLogger extends Logger
{
    function __construct()
    {
        // Set the default log channel name 'uzerp'
        parent::__construct('uzerp');
        $config = Config::Instance();
        $logfile = $config->get('UZERP_LOG_PATH');
        
        $handler = new ErrorLogHandler();
        if($logfile !== '') {
            $handler = new RotatingFileHandler($logfile, Logger::DEBUG);
        }
        $this->pushHandler($handler);
        $this->pushProcessor(new WebProcessor());
    }

    /**
     * Return a logger instance
     *
     * @return instance
     */
    public static function &Instance()
    {
        static $logger;
        if (empty($logger)) {
            $logger = new uzLogger();
        }
        return $logger;
    }
}