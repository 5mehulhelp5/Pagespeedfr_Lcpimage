# Pagespeedfr_Lcpimage
LCP Module for magento 2 : Add preload image on page cms category and product and custom page with code / resize image / 2x / compatible Yireo_Webp2 &amp; Amasty image optimizer

Compatible with Hyvä

If enable,

Add preload link to the image you want, you choose it for each controller as you can see here :

![image](https://github.com/user-attachments/assets/acaf39aa-06fa-43d5-a849-d903c17e217d)


if on layout page catalog_category_view you have double picture, one for mobile other for desktop

![image](https://github.com/user-attachments/assets/a3c46414-5f5f-46f2-a743-e1b2331a8570)

put for exemple : catalog_category_view,//div[@class="top-container"]//picture/source2,srcset 


By default, automatic add <link rel="preload" as="image" fetchpriority="high" href="https://mysite.fr/media/catalog/product/cache/e71e4160766cc34e6ee58774081aa4a0/6/7/67cb2cdc00022.webp"> on product page (take the first main image) and category page (take the first product of the listing).
The module  :
- add, on Cms page and category page admin edit, a field "lcp mobile" and "lcp desktop" if it fill it's that url who is preload.
- look if they are a transformation in webp by amasty or yireo and put it in consequently.
- can resize image with helper $imageHelperLcp = $this->helper('Pagespeedfr\Lcpimage\Helper\Image');  $imageUrl2x = $imageHelperLcp->resize($urlimage,$width,$height);
- can transform image in webp with $imageHelperLcp->webpGoOn($imageUrl2x); using Yireo

You have 

INSTALLATION

In manual mode -> Download and unzip in app/code/Pagespeedfr/Lcpimage/ this code ; create folder if not exist

With composer : composer require pagespeedfr/lcpimage

Then 
php bin/magento s:up
php bin/magento setup:db-declaration:generate-whitelist --module-name=Pagespeedfr_Lcpimage

For update, ask me by issue

OSL-3.0 Licence
    
