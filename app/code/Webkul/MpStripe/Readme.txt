#Installation

Magento2 Marketplace Stripe Payment module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and MpStripe(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MpStripe/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
composer require stripe/stripe-php

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

For Magento2 MpStripe module's working process follow user guide - http://webkul.com/blog/magento2-marketplace-stripe-payment-2/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/

-------------------------------------------------------------------------------------
Note - This license file is strictly need to use when you have purchased the software from
webkul store i.e https://store.webkul.com . If you purchase the module from magento marketplace
connect this license file will not work.
