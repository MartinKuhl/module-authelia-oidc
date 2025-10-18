<?php

namespace Martinkuhl\AutheliaOidc\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Magento\Framework\App\Filesystem\DirectoryList;

class Handler extends StreamHandler
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/authelia_oidc.log';

    /**
     * @param DirectoryList $directoryList
     * @param string $filePath
     */
    public function __construct(
        DirectoryList $directoryList,
        $filePath = null
    ) {
        // Absoluten Pfad zur Log-Datei festlegen
        $logfile = $directoryList->getPath(DirectoryList::VAR_DIR) . '/log/authelia_oidc.log';
        
        // Stelle sicher, dass die Datei existiert und beschreibbar ist
        if (!file_exists($logfile)) {
            touch($logfile);
            chmod($logfile, 0666);
        }
        
        // StreamHandler mit dem Level DEBUG und der Datei initialisieren
        parent::__construct($logfile, Logger::DEBUG);
    }
}
    


