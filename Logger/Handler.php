<?php

namespace Martinkuhl\AutheliaOidc\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\FileSystemException;

class Handler extends StreamHandler
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/authelia.log';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $file;

    /**
     * @param DirectoryList $directoryList
     * @param File $file
     * @param string $filePath
     */
    public function __construct(
        DirectoryList $directoryList,
        File $file,
        $filePath = null
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        
        // Absoluten Pfad zur Log-Datei festlegen
        try {
            $logfile = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/log/authelia.log';
            
            // Stelle sicher, dass die Log-Datei existiert
            $directory = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/log';
            if (!$this->file->isDirectory($directory)) {
                $this->file->createDirectory($directory, 0755);
            }
            
            if (!$this->file->isExists($logfile)) {
                $this->file->touch($logfile);
                $this->file->changePermissions($logfile, 0666);
            } else if (!$this->file->isWritable($logfile)) {
                $this->file->changePermissions($logfile, 0666);
            }
            
            // Debug-Ausgabe entfernt - nicht mehr notwendig
            
            // StreamHandler mit dem Level DEBUG und der Datei initialisieren
            parent::__construct($logfile, Logger::DEBUG);
        } catch (FileSystemException $e) {
            // Bei Fehler in die PHP-Fehlerprotokollierung schreiben
            error_log('Fehler beim Initialisieren des AutheliaOidc Logger-Handlers: ' . $e->getMessage());
            
            // Fallback auf ein Standard-Logfile
            parent::__construct(BP . '/var/log/authelia_fallback.log', Logger::DEBUG);
        }
    }
    
    /**
     * Fehlerbehandlung für den Logger
     *
     * @param string $message
     */
    private function handleError($message): void
    {
        // Bei Fehler in die PHP-Fehlerprotokollierung schreiben
        error_log('AutheliaOidc Logger-Fehler: ' . $message);
        
        try {
            // Versuche, direkt in die Datei zu schreiben
            $logfile = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/log/authelia.log';
            file_put_contents($logfile, date('[Y-m-d H:i:s]') . ' FEHLER: ' . $message . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            // Letzte Rettung: Schreibe in das PHP-Fehlerlog
            error_log('Kritischer AutheliaOidc Logger-Fehler: ' . $e->getMessage());
        }
    }
    
    /**
     * Überschreibe die isHandling-Methode, um Fehlerbehandlung hinzuzufügen
     * ohne die write-Methode zu überschreiben, die in neueren Monolog-Versionen
     * ein LogRecord-Objekt erwartet
     *
     * @param array|\Monolog\LogRecord $record
     * @return bool
     */
    public function isHandling($record): bool
    {
        try {
            return parent::isHandling($record);
        } catch (\Exception $e) {
            $this->handleError('Fehler beim Prüfen des Records: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Überschreibe handle, um Fehlerbehandlung hinzuzufügen
     * 
     * @param array|\Monolog\LogRecord $record
     * @return bool
     */
    public function handle($record): bool
    {
        try {
            // Debug-Zeilen entfernt - nicht mehr notwendig
            return parent::handle($record);
        } catch (\Exception $e) {
            $this->handleError('Fehler beim Verarbeiten des Records: ' . $e->getMessage());
            return false;
        }
    }
}
    


