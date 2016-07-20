Quafzi AddressValidation Extension
=====================
Magento module to validate customer addresses

Facts
-----
- composer package name: quafzi/magento-address-validation

Description
-----------
Apply some configurable validation rules for customer addresses to be applied
during the checkout.

Requirements
------------
- PHP >= 5.6.0
- Mage_Checkout
- Mage_Customer
- Mage_Sales

Compatibility
-------------
- Magento >= 1.9 (may work in earlier versions as well)

Installation Instructions
-------------------------
1. Install the extension via Magento Connect with the key shown above or copy all the files into your document root.
2. Clear the cache, logout from the admin panel and then login again.
3. Configure and activate the extension under System - Configuration - Customer
4. Optionally run shell script to prevalidate and fix existing addresses (`shell/fix-customer-data.php`)

Uninstallation
--------------
1. Remove all extension files from your Magento installation
2. Clear the cache.

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/quafzi/magento-address-validation/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------

Thomas Birke [@quafzi](https://twitter.com/quafzi)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2016 Thomas Birke
