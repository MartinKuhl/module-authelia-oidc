<?php

namespace Martinkuhl\AutheliaOidc\Controller\Adminhtml\Oidc;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Martinkuhl\AutheliaOidc\Helper\LoggerHelper;

class TestLog extends Action
{
    const ADMIN_RESOURCE = 'Magento_Backend::admin';
    
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
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        try {
            // Test Log-Nachrichten schreiben
            $this->logger->debug('Debug-Test-Nachricht');
            $this->logger->info('Info-Test-Nachricht');
            $this->logger->warning('Warning-Test-Nachricht');
            $this->logger->error('Error-Test-Nachricht');
            
            $result->setData(['success' => true, 'message' => 'Testlog-Einträge wurden geschrieben']);
        } catch (\Exception $e) {
            $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
        
        return $result;
    }
}