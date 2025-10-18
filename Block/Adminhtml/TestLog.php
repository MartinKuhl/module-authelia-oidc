<?php

namespace Martinkuhl\AutheliaOidc\Block\Adminhtml;

use Magento\Backend\Block\Template;

class TestLog extends Template
{
    /**
     * @var \Martinkuhl\AutheliaOidc\Helper\LoggerHelper
     */
    protected $logger;
    
    /**
     * @param Template\Context $context
     * @param \Martinkuhl\AutheliaOidc\Helper\LoggerHelper $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Martinkuhl\AutheliaOidc\Helper\LoggerHelper $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($context, $data);
    }
    
    /**
     * Schreibt Test-Logs und gibt eine Bestätigung zurück
     *
     * @return string
     */
    public function writeTestLogs()
    {
        try {
            // Direktes Schreiben in die Log-Datei zur Fehlersuche
            $logFile = BP . '/var/log/authelia_oidc.log';
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . ' Block-TestLog aufgerufen' . PHP_EOL, FILE_APPEND);
            
            // Test-Logs schreiben
            $this->logger->debug('Debug-Test-Nachricht vom Block');
            $this->logger->info('Info-Test-Nachricht vom Block');
            $this->logger->warning('Warning-Test-Nachricht vom Block');
            $this->logger->error('Error-Test-Nachricht vom Block');
            
            return 'Log-Einträge wurden erfolgreich geschrieben!';
        } catch (\Exception $e) {
            return 'Fehler beim Schreiben der Log-Einträge: ' . $e->getMessage();
        }
    }
    
    /**
     * Holt den Inhalt der Log-Datei
     *
     * @return string
     */
    public function getLogFileContents()
    {
        $logFile = BP . '/var/log/authelia_oidc.log';
        if (file_exists($logFile)) {
            // Nur die letzten 20 Zeilen anzeigen
            $logs = file($logFile);
            if (count($logs) > 20) {
                $logs = array_slice($logs, -20);
            }
            return implode('', $logs);
        } else {
            return 'Log-Datei nicht gefunden.';
        }
    }
}