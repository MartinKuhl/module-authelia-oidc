
<?php
namespace martinkuhl\AutheliaOidc\Controller\Adminhtml\Oidc;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use martinkuhl\AutheliaOidc\Model\Oidc\Client;
use martinkuhl\AutheliaOidc\Model\Oidc\State;
use martinkuhl\AutheliaOidc\Model\Oidc\TokenValidator;
use martinkuhl\AutheliaOidc\Model\Authenticator;
use martinkuhl\AutheliaOidc\Helper\Data;

class Callback extends Action
{
    const ADMIN_RESOURCE = 'Magento_Backend::admin';

    private Client $client;
    private State $state;
    private TokenValidator $validator;
    private Authenticator $authenticator;
    private RedirectFactory $resultRedirectFactory;
    private UrlInterface $url;
    private Data $helper;
    private RedirectInterface $redirect;

    public function __construct(
        Context $context,
        Client $client,
        State $state,
        TokenValidator $validator,
        Authenticator $authenticator,
        Data $helper,
        RedirectInterface $redirect
    ) {
        parent::__construct($context);
        $this->client = $client;
        $this->state = $state;
        $this->validator = $validator;
        $this->authenticator = $authenticator;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->url = $context->getUrl();
        $this->helper = $helper;
        $this->redirect = $redirect;
    }

    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        if (!$this->helper->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Authelia OIDC ist deaktiviert'));
            $result->setPath('admin');
            return $result;
        }

        $stateParam = (string)$this->getRequest()->getParam('state');
        $code = (string)$this->getRequest()->getParam('code');
        $error = (string)$this->getRequest()->getParam('error');

        if ($error) {
            $this->messageManager->addErrorMessage(__('OIDC Fehler: %1', $error));
            $result->setPath('admin');
            return $result;
        }

        if (!$stateParam || !$this->state->validate($stateParam)) {
            $this->messageManager->addErrorMessage(__('Ungültiger OIDC state'));
            $result->setPath('admin');
            return $result;
        }

        try {
            $baseUrl = $this->url->getBaseUrl();
            $tokens = $this->client->exchangeCodeForTokens($code, $baseUrl, $this->state->getCodeVerifier());
            $idToken = $tokens['id_token'];
            $claims = $this->validator->validateIdToken($idToken, $this->state->getNonce());
            $this->state->clear();

            $this->authenticator->loginFromClaims($claims);
            $result->setPath('admin/dashboard');
            return $result;
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Login fehlgeschlagen: %1', $e->getMessage()));
            $result->setPath('admin');
            return $result;
        }
    }
}
