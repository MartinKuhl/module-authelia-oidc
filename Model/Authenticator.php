
<?php
namespace martinkuhl\AutheliaOidc\Model;

use Magento\User\Model\UserFactory;
use Magento\Backend\Model\Auth;
use Magento\Framework\Exception\LocalizedException;
use martinkuhl\AutheliaOidc\Helper\Data;

class Authenticator
{
    private UserFactory $userFactory;
    private Auth $auth;
    private Data $helper;

    public function __construct(UserFactory $userFactory, Auth $auth, Data $helper)
    {
        $this->userFactory = $userFactory;
        $this->auth = $auth;
        $this->helper = $helper;
    }

    public function loginFromClaims(array $claims): void
    {
        $claimName = $this->helper->getUsernameClaim();
        $identifier = $claims[$claimName] ?? null;
        if (!$identifier) {
            throw new LocalizedException(__('Claim %1 fehlt im ID Token', $claimName));
        }

        $user = $this->userFactory->create();
        if ($claimName === 'email') {
            $user->loadByEmail($identifier);
        } else {
            $user->loadByUsername($identifier);
        }

        if (!$user->getId()) {
            throw new LocalizedException(__('Admin-Benutzer nicht gefunden'));
        }
        if (!$user->getIsActive()) {
            throw new LocalizedException(__('Admin-Benutzer ist deaktiviert'));
        }

        $this->auth->setUser($user);
        $this->auth->getAuthStorage()->setIsLoggedIn(true);
    }
}
