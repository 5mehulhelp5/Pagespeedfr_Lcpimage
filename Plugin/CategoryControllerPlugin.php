<?php

namespace Pagespeedfr\Lcpimage\Plugin;

use Magento\Framework\Controller\ResultInterface;
use Pagespeedfr\Lcpimage\Helper\Image;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class CategoryControllerPlugin
{
    /**
     * @var \Pagespeedfr\Lcpimage\Helper\Image $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface $request
     */
    protected $request;

    /**
     * @var PageFactory $pageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;

    /**
     * Constructeur du plugin
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        PageFactory $pageFactory,
        Image $helper,
        Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->pageFactory = $pageFactory;
        $this->request = $request;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Plugin après exécution du controller Category View
     *
     * @param \Magento\Catalog\Controller\Category\View $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterExecute(
        $subject,
        ResultInterface $result
    ) {

        $lcpimageenable = $this->scopeConfig->getValue(
            'lcpimage/options/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$lcpimageenable) {
            return $result;
        }

        if ($subject->getRequest()->isXmlHttpRequest()) {
            return $result;
        } else {

            try {
                /** @var Category|null $category */
                $category = $this->registry->registry('current_category');

                if ($category && $category->getId()) {
                    $lcp_image_desktop = $category->getData('lcp_image_desktop');
                    if ($lcp_image_desktop) {
                        return $result;
                    }
                }

                $page = $this->pageFactory->create();

                // Force le layout à être généré
                $layout = $page->getLayout();
                $auto_lcp_url = $layout->getBlock('auto_lcp_url');
                $categoryproductslist = $layout->getBlock('category.products.list');
                if (!$auto_lcp_url || !$categoryproductslist) {
                    return $result;
                }
                //option 1 on prend la premiere image du html
                $html = $categoryproductslist->toHtml();

                $dom = new \DOMDocument();
                libxml_use_internal_errors(true); // Ignore les erreurs HTML mal formé
                $dom->loadHTML($html);
                libxml_clear_errors();

                $xpath = new \DOMXPath($dom);

                // Sélectionne la première image dans la balise main avec id "maincontent"
                $src = null;
                $source = $xpath->query('//picture/source')->item(0);

                if ($source) {
                    $src = $source->getAttribute('srcset');
                } else {
                    $img = $xpath->query('//img')->item(0);
                    if ($img) {
                        $src = $img->getAttribute('src');
                    }
                }

                if ($src) {
                    $src = $this->helper->onWebp($src);
                    $auto_lcp_url->setData('imagelcpplugin', $src);
                }

                return $result;

            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $result;
    }
}
