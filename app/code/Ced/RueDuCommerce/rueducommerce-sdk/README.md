# rueducommerce-sdk
RueDuCommerce marketplace api integration sdk
+ Sand box seller panel
   https://rueducommerce-dev.mirakl.net
+ Sand box url
    https://rueducommerce-dev.mirakl.net
    
# User Guide
### Installation
+ ##### Manual Way 
    + Create "cedcoss" directory in vendor directory
    + run below command in cedcoss directory
                        
            git clone https://github.com/cedcoss/rueducommerce-sdk.git
    + now open composer.json present in root directory and add below lines in it
    
            "autoload": {
                 "psr-4": {
                "RueDuCommerceSdk\\": "vendor/cedcoss/rueducommerce-sdk/src/"
                }
            }
    + after that run below command
    
            composer update
    
+ ##### Install through composer 
    + Run Below commands in your root directory (Make sure ssh key setup is done fore this repo)
    
            composer config repositories.cedcoss/rueducommerce-sdk git git@github.com:cedcoss/rueducommerce-sdk.git
            
            composer require cedcoss/rueducommerce-sdk:dev-master
            
            
            
            
### RueDuCommerce Integration guide:
#### Product:
* All Required common product attributes:
    ```
    [
        "title",
        "short-desc",
        "item-class",
        "standard-price",
        "brand",
        "shipping-length",
        "shipping-width",
        "shipping-height",
        "shipping-weight",
        "offer-condition/condition",
        "item-id",
        "model-number",
        "image-url",
        "standard-price"
    ]
    
    ```
* Some optional common product attributes:
    ```
    [
        "upc", 
        "mature-content",
        "your-categorization",
        "long-desc",
        "manufacturer-name",
        "map-price-indicator",
        "no-warranty-available",
        "gift-message-eligible"
    ]
    
    ```
    
* **Note**:
    + Change in **"item-id"**, **"upc"** or **"model-number"** attributes will lead to creation of **new** product on rueducommerce.
    + UPC is required for certain categories. Kindly refer: https://www.rueducommercecommerceservices.com/question/in-which-categories-do-i-need-to-provide-a-upc/
            
    Refer to [lmp-item.xsd](https://github.com/cedcoss/rueducommerce-sdk/blob/dev/xsd/lmp-item.xsd "lmp-item.xsd")

    Kindly read: https://www.rueducommercecommerceservices.com/faq/
