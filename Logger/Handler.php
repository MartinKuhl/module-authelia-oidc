<?php

namespace Martinkuhl\AutheliaOidc\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/authelia_oidc.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}