<?php

namespace Martinkuhl\AutheliaOidc\Controller\Adminhtml\Oidc;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Martinkuhl\AutheliaOidc\Helper\LoggerHelper;

class TestLog extends Action
{
    // Spezifische Berechtigung für den TestLog-Controller
    const ADMIN_RESOURCE = 'Martinkuhl_AutheliaOidc::testlog';
    
    /**
     * @var LoggerHelper
     */
    private $logger;

    /**
     * @param Context $context
     * @param LoggerHelper $logger
     */
    public function __construct(
        Context $context,
        LoggerHelper $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Test Logging
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Format der Response je nach Accept-Header setzen
        $isAjax = $this->getRequest()->isAjax();
        
        if ($isAjax) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        } else {
            $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            // Layout für die HTML-Seite konfigurieren
            $result->getConfig()->getTitle()->prepend(__('Authelia OIDC Test Logging'));
            
            // Block zum Layout hinzufügen
            $block = $result->getLayout()->createBlock(
                \Martinkuhl\AutheliaOidc\Block\Adminhtml\TestLog::class,
                'authelia_testlog'
            );
            $block->setTemplate('Martinkuhl_AutheliaOidc::testlog.phtml');
            $result->getLayout()->getBlock('content')->append($block);
            
            // Direkt in die Log-Datei schreiben, dass der Controller aufgerufen wurde
            $logFile = BP . '/var/log/authelia_oidc.log';
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . ' TestLog Controller mit HTML-Ausgabe aufgerufen' . PHP_EOL, FILE_APPEND);
            
            return $result;
        }
        
        try {
            // Direktes Schreiben in die Log-Datei zur Fehlersuche
            $logFile = BP . '/var/log/authelia_oidc.log';
            
            // Debugging-Informationen hinzufügen
            $debugInfo = [
                'Time' => date('[Y-m-d H:i:s]'),
                'Controller' => 'TestLog-Controller wurde aufgerufen (Ajax)',
                'Request' => [
                    'URL' => $this->getRequest()->getUriString(),
                    'Method' => $this->getRequest()->getMethod(),
                    'Path' => $this->getRequest()->getPathInfo(),
                    'Module' => $this->getRequest()->getModuleName(),
                    'Controller' => $this->getRequest()->getControllerName(),
                    'Action' => $this->getRequest()->getActionName()
                ]
            ];
            
            // Debug-Info direkt in die Datei schreiben
            file_put_contents($logFile, print_r($debugInfo, true) . PHP_EOL, FILE_APPEND);
            
            // Test Log-Nachrichten über LoggerHelper schreiben
            $this->logger->debug('Debug-Test-Nachricht vom Admin-Panel (Ajax)');
            $this->logger->info('Info-Test-Nachricht vom Admin-Panel (Ajax)');
            $this->logger->warning('Warning-Test-Nachricht vom Admin-Panel (Ajax)');
            $this->logger->error('Error-Test-Nachricht vom Admin-Panel (Ajax)');
            
            $result->setData([
                'success' => true, 
                'message' => 'Testlog-Einträge wurden geschrieben',
                'debug_info' => $debugInfo
            ]);
        } catch (\Exception $e) {
            // Bei Ausnahmen auch direkt in die Datei schreiben
            $logFile = BP . '/var/log/authelia_oidc.log';
            file_put_contents($logFile, date('[Y-m-d H:i:s]') . ' FEHLER: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
            
            $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
        
        return $result;
    }
}