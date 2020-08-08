# Systempay for Magento

Systempay for Magento is an open source plugin that links e-commerce websites based on Magento to Systempay secured payment gateway developped by [Lyra Network](https://www.lyra-network.com/).

Namely, it enables the following payment methods :
* Systempay - Standard credit card payment
* Systempay - Credit card payment in installments

## Installation & upgrade

- Create app/code/Lyranetwork/Systempay folder if not exists.
- Unzip module in your Magento 2 app/code/Lyranetwork/Systempay folder.
- Open command line and change to Magento installation root directory.
- Enable module: php bin/magento module:enable --clear-static-content Lyranetwork_Systempay
- Upgrade database: php bin/magento setup:upgrade
- Re-run compile command: php bin/magento setup:di:compile
- Update static files by: php bin/magento setup:static-content:deploy [locale]

In order to deactivate the module: php bin/magento module:disable --clear-static-content Lyranetwork_Systempay

## Configuration

- In Magento 2 administration interface, browse to "STORES > Configuration" menu
- Click on "Payment Methods" link under "SALES" section
- Expand Systempay payment method to enter your gateway credentials
- Refresh invalidated Magento cache afeter config saved. 

## License

Each Systempay payment module source file included in this distribution is licensed under Open Software License (OSL 3.0).

Please see LICENSE.txt for the full text of the OSL 3.0 license. It is also available through the world-wide-web at this URL: https://opensource.org/licenses/osl-3.0.php.
