Pagespeedfr_Lcpimage
LCP Module for Magento 2: Add preload image support on CMS pages, category pages, product pages, and custom pages with code-based selection, image resizing, 2x support, and compatibility with both Yireo_Webp2 and Amasty Image Optimizer.

✅ Compatible with Hyvä

Recommendation
For optimal Google PageSpeed results and full compatibility with this module, I recommend the free plugin:
[👉 Yireo_Webp2](https://github.com/yireo/Yireo_Webp2)

Features
When enabled, the module:

Adds <link rel="preload" as="image" fetchpriority="high"> tags for LCP images.

Default Behavior

By default, the module automatically adds a preload link on:

Product pages → preloads the first main product image.

Category pages → preloads the first image in the product listing.

<link rel="preload" as="image" fetchpriority="high" href="https://mysite.fr/media/catalog/product/cache/e71e4160766cc34e6ee58774081aa4a0/6/7/67cb2cdc00022.webp">



For Other page -> Allows you to choose which image to preload for each controller.
Example:

![image](https://github.com/user-attachments/assets/acaf39aa-06fa-43d5-a849-d903c17e217d)


Supports cases where you have separate images for desktop and mobile, for example on the catalog_category_view layout.

![image](https://github.com/user-attachments/assets/a3c46414-5f5f-46f2-a743-e1b2331a8570)

In such cases, you can define a selector like : catalog_category_view,//div[@class="top-container"]//picture/source2,srcset 


The module  :
- add, on Cms page and category page admin edit, a field "lcp mobile" and "lcp desktop" if it fill it's that url who is preload.
- look if they are a transformation in webp by amasty or yireo and put it in consequently.
- can resize image with helper $imageHelperLcp = $this->helper('Pagespeedfr\Lcpimage\Helper\Image');  $imageUrlResize = $imageHelperLcp->resize($urlimage,$width,$height);
- can transform image in webp with $imageHelperLcp->webpGoOn($imageUrl2x); using Yireo
 

🛠 INSTALLATION

Manual Installation

Download and unzip the module in:
app/code/Pagespeedfr/Lcpimage/
(Create folders if they don't exist)

With composer : composer require pagespeedfr/lcpimage

Then 
php bin/magento s:up
php bin/magento setup:db-declaration:generate-whitelist --module-name=Pagespeedfr_Lcpimage

After go to admin > stores > configuration > PAGESPEEDFR > lcpimage and enable it
you can remove the demo test for resize banner image webp on module directory : Pagespeedfr/Lcpimage/view/frontend/layout/cms_index_index.xml and comment the block with name test_lcp_image

 
🔄 Updates
For updates, please open an issue on the repository.

OSL-3.0 Licence
    
