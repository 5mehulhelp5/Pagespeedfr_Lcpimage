<?php
namespace Pagespeedfr\Lcpimage\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    public $imageFactory;

    /**
     * @var Magento\Framework\Filesystem $filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Module\Manager $moduleManager
     */
    public $moduleManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
  

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory,
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->imageFactory = $imageFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * Resize the image to given dimensions.
     *
     * @param string $imageurl
     * @param int $width
     * @param int|null $height
     * @return string
     */
    public function resize(string $imageurl, ?int $width = null, ?int $height = null): string
    {
        if (!$imageurl) {
            return '';
        }
        if (!$width) {
            return $imageurl;
        }

        $relativePathImage = $this->getRelativeLinkImage($imageurl);
        $imageAbsolutePath = $this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('/') . $relativePathImage;
 
        if (!file_exists($imageAbsolutePath))
            return false;

        $pathToresize = 'media/resizedLcp/' . $width . '/' . $relativePathImage;
        $imageResized = $this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath($pathToresize) ;
 
        if (!file_exists($imageResized)) {

            $imageResize = $this->imageFactory->create();
            $imageResize->open($imageAbsolutePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(TRUE);
            $imageResize->quality(95); 
            $imageResize->resize($width,$height);

            $imageResize->save($imageResized);
        }
        $resizedURL = $this->storeManager->getStore()->getBaseUrl() .$pathToresize;
        return $resizedURL;
    }

    /**
     * Return just the image path after without yoursite.com/media/
     *
     * @param string $imagelink
     * @return string
     */
    protected function getRelativeLinkImage($imagelink): string
    {
        $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl();
        $relativePathImage = str_replace($baseMediaUrl, '', $imagelink);

        return $relativePathImage;
    }


    /**
     * Tranform the image in webp with yireo webp.
     *
     * @param string $image
     * @return string
     */
    public function webpGoOn(string $image): string
    {
        if (!$image) {
            return '';
        }

        if (!preg_match('/\.(jpg|jpeg|png)$/i', $image)) {
            return $image;
        }

        if ($this->moduleManager->isOutputEnabled('Yireo_NextGenImages')) {
            $yireoenable = $this->scopeConfig->getValue(
                'yireo_nextgenimages/settings/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($yireoenable) { 
                $image = $this->objectManager->get(\Yireo\NextGenImages\Image\ImageFactory::class)->createFromUrl($image);
                $convertor = $this->objectManager->get(\Yireo\Webp2\Convertor\Convertor::class);
                $image = $convertor->convertImage($image);
            }
        }

        return $image;
    }

    /**
     * Return the image in webp if exist.
     *
     * @param string $image
     * @return string
     */
    public function onWebp(string $image): string
    {
        if (!$image) {
            return '';
        }
        if (!preg_match('/\.(jpg|jpeg|png)$/i', $image)) {
            return $image;
        }
        if ($this->moduleManager->isOutputEnabled('Yireo_NextGenImages')) {
            $yireoenable = $this->scopeConfig->getValue(
                'yireo_nextgenimages/settings/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($yireoenable) {
                $webpimage = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image);
                $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                $relativePath = str_replace($baseMediaUrl, '', $webpimage);

                $baseMediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                $fullPath = $baseMediaPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
                $fullPath = str_replace('media//', 'media/', $fullPath);
                if (file_exists($fullPath)) {
                    $image = $webpimage;
                }
            }
        }
        if ($this->moduleManager->isOutputEnabled('Amasty_ImageOptimizer')) {
            $amastyimage = $this->scopeConfig->getValue(
                'amoptimizer/replace_images_general/replace_strategy',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($amastyimage && $amastyimage != 0) {
                $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $relativePath = str_replace($baseMediaUrl, '', $image);
                $relativePath = str_replace('catalog/product/', 'amasty/webp/catalog/product/', $relativePath);
                $relativePath = str_replace('.jpg', '_jpg.webp', $relativePath);
                $relativePath = str_replace('.jpeg', '_jpeg.webp', $relativePath);
                $relativePath = str_replace('.png', '_png.webp', $relativePath);

                $baseMediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                $fullPath = $baseMediaPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
                $fullPath = str_replace('media//', 'media/', $fullPath);

                if (file_exists($fullPath)) {
                    $image = $baseMediaUrl . $relativePath;
                }
            }
        }
        return $image;
    }
}
