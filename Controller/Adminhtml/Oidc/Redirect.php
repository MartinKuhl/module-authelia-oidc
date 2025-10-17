<?php

namespace Martinkuhl\AutheliaOidc\Controller\Adminhtml\Oidc;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;
use Martinkuhl\AutheliaOidc\Model\Oidc\Client;
use Martinkuhl\AutheliaOidc\Model\Oidc\State;
use Martinkuhl\AutheliaOidc\Helper\Data;
use Martinkuhl\AutheliaOidc\Helper\LoggerHelper;

class Redirect extends Action
{
    const ADMIN_RESOURCE = 'Magento_Backend::admin';

    private Client $client;
    private State $state;
    protected $resultRedirectFactory;
    private UrlInterface $url;
    private Data $helper;
    private LoggerHelper $logger;

    public function __construct(
        Context $context,
        Client $client,
        State $state,
        Data $helper,
        LoggerHelper $logger
    ) {
        parent::__construct($context);
        $this->client = $client;
        $this->state = $state;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->url = $context->getUrl();
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        if (!$this->helper->isEnabled()) {
            $message = 'Authelia OIDC ist deaktiviert';
            $this->messageManager->addErrorMessage(__($message));
            $this->logger->error($message);
            $result->setPath('admin');
            return $result;
        }

        try {
            $baseUrl = $this->url->getBaseUrl();
            $pair = $this->state->generate();
            
            // Debug-Informationen
            $issuer = $this->helper->getIssuer();
            $clientId = $this->helper->getClientId();
            $redirect = $this->helper->getRedirectUri($baseUrl);
            
            $debugInfo = [
                'issuer' => $issuer,
                'client_id' => $clientId,
                'redirect_uri' => $redirect
            ];
            
            $this->logger->info('OIDC Redirect Anfrage', $debugInfo);
            $this->messageManager->addNoticeMessage(__('Debug: Issuer=%1, Client ID=%2, Redirect=%3', $issuer, $clientId, $redirect));
            
            $authUrl = $this->client->getAuthorizeUrl($baseUrl, $pair['state'], $pair['nonce'], $pair['code_verifier']);
            $this->logger->info('OIDC Auth URL generiert', ['auth_url' => $authUrl]);
            $this->messageManager->addNoticeMessage(__('Debug: Auth URL=%1', $authUrl));
            
            $result->setUrl($authUrl);
            return $result;
        } catch (\Throwable $e) {
            $errorMsg = 'OIDC Redirect fehlgeschlagen: ' . $e->getMessage();
            $this->logger->error($errorMsg, ['exception' => $e->getTraceAsString()]);
            $this->messageManager->addErrorMessage(__($errorMsg));
            $result->setPath('admin');
            return $result;
        }
    }
}
