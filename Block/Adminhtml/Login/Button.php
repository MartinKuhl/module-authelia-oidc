<?php

namespace Martinkuhl\AutheliaOidc\Block\Adminhtml\Login;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Martinkuhl\AutheliaOidc\Helper\Data;

class Button extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if OIDC auth is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->helper->isEnabled();
    }

    /**
     * Get the redirect URL
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->getUrl('authelia/oidc/redirect');
    }
}