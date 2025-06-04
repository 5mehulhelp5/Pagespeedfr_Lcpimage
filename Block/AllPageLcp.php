<?php
namespace Pagespeedfr\Lcpimage\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AllPageLcp extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check if LCP image feature is enabled in config
     *
     * @return bool
     */
    public function isLcpImageEnabled()
    {
        return $this->scopeConfig->getValue(
            'lcpimage/options/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the static URL from config
     *
     * @return string|null
     */
    public function getStaticUrl()
    {
        return $this->scopeConfig->getValue(
            'lcpimage/options/staticurl',
            ScopeInterface::SCOPE_STORE
        );
    }
}
