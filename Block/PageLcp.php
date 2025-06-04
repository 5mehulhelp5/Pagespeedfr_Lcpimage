<?php
namespace Pagespeedfr\Lcpimage\Block;

class PageLcp extends \Magento\Cms\Block\Page
{
    /**
     * Prepare HTML content
     *
     * @return string
     */
    public function filterProvider($content)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($content);
        return $html;
    }
    /**
     * Prepare HTML content
     *
     * @return string
     */
    public function DataPage()
    {
        return $this->getPage();
    }

    /**
     * Display block
     * @return string
     */
    public function _toHtml()
    {
        $lcp_image_desktop_cms = $this->getPage()->getData('lcp_image_desktop_cms');
        $lcp_image_mobile_cms = $this->getPage()->getData('lcp_image_mobile_cms');
        if ($lcp_image_desktop_cms && $lcp_image_mobile_cms) {
            $html = '<link rel="preload" fetchpriority="high" as="image" imagesrcset="' . $lcp_image_mobile_cms . '" media="(max-width: 767px)" />' . "\n";
            $html .= '<link rel="preload" fetchpriority="high" as="image" imagesrcset="' . $lcp_image_desktop_cms . '" media="(min-width: 768px)" />' . "\n";
            return $html;
        }

        if ($lcp_image_desktop_cms) {
            $html = '<link rel="preload" fetchpriority="high" as="image" href="' . $lcp_image_desktop_cms . '" />' . "\n";
            return $html;
        }

        if ($lcp_image_mobile_cms) {
            $html = '<link rel="preload" fetchpriority="high" as="image" imagesrcset="' . $lcp_image_mobile_cms . '" media="(max-width: 767px)" />' . "\n";
            return $html;
        }
        return '';
    }
}