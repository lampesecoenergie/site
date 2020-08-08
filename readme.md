[lampesecoenergie.com(https://lampesecoenergie.com) (Magento 2).

## How to deploy the static content
```posh
bin/magento cache:clean
rm -rf pub/static/*
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US fr_FR
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme Smartwave/porto \
	-f en_US fr_FR
```