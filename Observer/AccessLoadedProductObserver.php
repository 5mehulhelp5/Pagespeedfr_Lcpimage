<?php

namespace Pagespeedfr\Lcpimage\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
class AccessLoadedProductObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

     /**
     * @var \Magento\Framework\App\RequestInterface $request,
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {

        $lcpimageenable = $this->scopeConfig->getValue(
            'lcpimage/options/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$lcpimageenable || $this->request->isXmlHttpRequest()) {
            return;
        }
        $autolcp = $this->scopeConfig->getValue(
            'lcpimage/options/autolcp',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$autolcp) {
            return;
        }
        $lines = preg_split('/\r\n|\r|\n/', $autolcp); // explode par saut de ligne

        $result = [];
        $incontroller = false;
        $actualcontroller = $observer->getEvent()->getFullActionName();
        foreach ($lines as $line) {
            $parts = explode(',', $line); // explode par virgule
            if (count($parts) >= 2 && $parts[0] == $actualcontroller) {
                $result[] = trim($parts[1]);
                $result[] = isset($parts[2]) ? trim($parts[2]) : null;
                $incontroller = true;
                break;
            }
        }

        if (!$incontroller) {
            return;
        } 

        try {
            $layout = $observer->getLayout();
            $html = $layout->getOutput();
            $auto_lcp_url = $layout->getBlock('auto_lcp_url');
            if ($auto_lcp_url) {

                $dom = new \DOMDocument();
                libxml_use_internal_errors(true); // Ignore les erreurs HTML mal formé
                $dom->loadHTML($html);
                libxml_clear_errors();

                $xpath = new \DOMXPath($dom);

                // Sélectionne ce qui a été mis en bo en 2 et 3eme instance exemple-> //picture/source,srcset
                $src = null;
                $pathquery = null;
                $srcset = null;
                if (isset($result[0]) && isset($result[1])) {
                    $pathquery = $result[0];
                    $srcset = $result[1];
                } else {
                    return;
                }

                $pos = strpos($pathquery, '//picture/source2');
                if ($pos !== false) {
                    $pathquery = str_replace('//picture/source2', '//picture/source', $pathquery);
                    $src = [];
                    $source = $xpath->query($pathquery)->item(0);
                    if ($source) {

                        $src[0]['media'] = '(min-width: 768px)';
                        $src[0]['srcset'] = $source->getAttribute('srcset');
                    }
                    $source = $xpath->query($pathquery)->item(1);
                    if ($source) {
                        $src[1]['media'] = '(max-width: 767px)';
                        $src[1]['srcset'] = $source->getAttribute('srcset');
                    }
                } else {
                    $source = $xpath->query($pathquery)->item(0);
                    if ($source) {
                        $pos = strpos($pathquery, '//picture/source');
                        if ($pos !== false) {
                            $src = [];
                            $pathquery = str_replace('//picture/source', '//picture', $pathquery);
                            $picture = $xpath->query($pathquery)->item(0);
                            $sources = $picture->getElementsByTagName('source');
                            foreach ($sources as $source) {
                                if ($source->getAttribute('media')) {
                                    $src[]['media'] = $source->getAttribute('media');
                                }
                                if ($source->getAttribute('srcset')) {
                                    $src[]['srcset'] = $source->getAttribute('srcset');
                                }
                            }
                        } else {
                            $src = $source->getAttribute($srcset);
                        }
                    }
                }

                if ($src) {
                    $auto_lcp_url->setData('imagelcp', $src);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return;
    }
}
