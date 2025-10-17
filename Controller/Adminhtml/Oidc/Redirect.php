<?php

namespace Martinkuhl\AutheliaOidc\Controller\Adminhtml\Oidc;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;
use Martinkuhl\AutheliaOidc\Model\Oidc\Client;
use Martinkuhl\AutheliaOidc\Model\Oidc\State;
use Martinkuhl\AutheliaOidc\Helper\Data;

class Redirect extends Action
{
    const ADMIN_RESOURCE = 'Magento_Backend::admin';

    private Client $client;
    private State $state;
    protected $resultRedirectFactory;
    private UrlInterface $url;
    private Data $helper;

    public function __construct(
        Context $context,
        Client $client,
        State $state,
        Data $helper
    ) {
        parent::__construct($context);
        $this->client = $client;
        $this->state = $state;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->url = $context->getUrl();
        $this->helper = $helper;
    }

    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        if (!$this->helper->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Authelia OIDC ist deaktiviert'));
            $result->setPath('admin');
            return $result;
        }

        try {
            $baseUrl = $this->url->getBaseUrl();
            $pair = $this->state->generate();
            $authUrl = $this->client->getAuthorizeUrl($baseUrl, $pair['state'], $pair['nonce'], $pair['code_verifier']);
            $result->setUrl($authUrl);
            return $result;
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('OIDC Redirect fehlgeschlagen: %1', $e->getMessage()));
            $result->setPath('admin');
            return $result;
        }
    }
}
