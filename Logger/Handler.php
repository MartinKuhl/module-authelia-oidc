<?php

namespace Martinkuhl\AutheliaOidc\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;
use Magento\Framework\App\Filesystem\DirectoryList;

class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/authelia_oidc.log';

    
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG; // Alle Nachrichten mit Level DEBUG und höher werden protokolliert
    
    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     * @param DirectoryList $directoryList
     * @param string $filePath
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        DirectoryList $directoryList,
        $filePath = null
    ) {
        $this->fileName = $directoryList->getPath(DirectoryList::VAR_DIR) . '/log/authelia_oidc.log';
        parent::__construct($filesystem, $filePath);
    }
}