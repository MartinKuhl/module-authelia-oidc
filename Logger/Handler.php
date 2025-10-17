<?php

namespace Martinkuhl\AutheliaOidc\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = BP . '/var/log/authelia_oidc.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO; // Alle Nachrichten mit Level INFO und höher werden protokolliert
}