# Installation

## With composer

1. Update Magento **composer.json** as follows:

```json
{
    ...
    "require": {
        ...
        "mondialrelay/module-shipping": "@stable"
    },
    ...
    "config": {
        ...
        "github-oauth": {
            "github.com": "123456789123456789123456789123456789"
        }
    },
    ...
    "repositories": {
        ...
        "mondialrelay/module-shipping": {
            "type": "vcs",
            "url": "https://github.com/magentix/mondialRelay-shipping.git"
        }
    },
    ...
}
```

_Generate Github **Personal access token** from your account (Settings > Developer settings > Personal access tokens)._

2. Add package:

```shell
composer require mondialrelay/module-shipping
```

## By download

1. Download the latest release from module repository

2. Create **app/code/MondialRelay/Shipping** directory in Magento

3. Unzip module archive content in **app/code/MondialRelay/Shipping** directory

## Enable Module

Enable and install module in Magento:

```shell
php bin/magento module:enable MondialRelay_Shipping
php bin/magento setup:db:status
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
```

# Contact

support@magentix.fr