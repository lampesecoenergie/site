<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @package     RueDuCommerce-Sdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace RueDuCommerceSdk;

class Product extends \RueDuCommerceSdk\Core\Request
{
    /**
     * Retrieve list of categories
     * @param bool $forceFetch
     * @return array
     */
    public function getCategories($params, $forceFetch = false)
    {
        $categories = array();

        try {
            $itemsFile = $this->baseDirectory . DS . 'categories' . DS . 'categories-library'.$params['max_level'].$params['hierarchy'].'.xml';
            if (file_exists($itemsFile) && !$forceFetch) {
                $categories = file_get_contents($itemsFile);
                $categories = $this->xmlToArray($categories);
                $categories = $categories['body']['hierarchies']['hierarchy'];
            } else {
                $hierarchy =$params['hierarchy'];
                if(isset($params['hierarchy']) && ($params['hierarchy']==''))
                    unset($params['hierarchy']);

                $response = $this->getRequest(self::GET_CATEGORIES_SUB_URL, $params);
                $responseParsed = $this->xmlToArray($response);
                if (isset($responseParsed['body']['hierarchies']['hierarchy'])) {
                    file_put_contents(
                        $this->getFile($this->baseDirectory . DS . 'categories', 'categories-library'.$params['max_level'].$hierarchy.'.xml'),
                        $response
                    );
                    $categories = $responseParsed['body']['hierarchies']['hierarchy'];
                }
            }
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Product\\getCategories() : Errors: " . var_export($e->getMessage(), true)
                );
            }
        }

        return $categories;
    }

    /**
     * @todo handle invalid xml response
     * Retrieve list of current attribute and attribute values
     * @param bool $forceFetch
     * @return array
     */
    public function getAttributes($params, $forceFetch = false)
    {
        $attributes = array();
        try {
            $itemsFile = $this->baseDirectory . DS . 'attributes' . DS . 'attribute-library'.$params['hierarchy'].'.xml';
            if (file_exists($itemsFile) && !$forceFetch) {
                $attributes = file_get_contents($itemsFile);
                $attributes = $this->xmlToArray($attributes);
                foreach ($attributes['body']['attributes']['attribute'] as &$attribute) {
                    if($attribute['type'] == "LIST" || $attribute['type'] == "LIST_MULTIPLE_VALUES") {
                        $valueLists = $this->getValueLists($attribute['values_list']);
                        $attribute['values']['_value'] = $valueLists;
                    }
                }
                $attributes = $attributes['body']['attributes']['attribute'];
            } else {
                $response = [];
                if(isset($params['hierarchy']) && $params['hierarchy'])
                $response = $this->getRequest(self::GET_ATTRIBUTES_SUB_URL, $params);
                $responseParsed = $this->xmlToArray($response);
                if (isset($responseParsed['body']['attributes']['attribute'])) {
                    foreach ($responseParsed['body']['attributes']['attribute'] as &$attribute) {
                        if($attribute['type'] == "LIST" || $attribute['type'] == "LIST_MULTIPLE_VALUES") {
                            $valueLists = $this->getValueLists($attribute['values_list'], true);
                            $attribute['values']['_value'] = $valueLists;
                        }
                    }
                    file_put_contents(
                        $this->getFile(
                            $this->baseDirectory . DS . 'attributes',
                            'attribute-library'.$params['hierarchy'].'.xml'
                        ),
                        $response
                    );
                    $attributes = $responseParsed['body']['attributes']['attribute'];
                }
            }
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Product\\getAttributes() : Errors: " . var_export($e->getMessage(), true)
                );
            }
        }
        return $attributes;
    }

    /**
     * Create/Update Product on RueDuCommerce
     * @param $data
     * @return bool|array
     */
    public function createProduct($data)
    {

        if (isset($data[0]['product']) or isset($data[0]['variation-group'])) {
            $product = array(
                'catalog' =>
                    array(
                        '_attribute' => array(),
                        '_value' => array(
                            'products' => array(
                                '_attribute' => array(),
                                '_value' => $data
                            )
                        )
                    )
            );

            $path = $this->getFile($this->baseDirectory, 'lmp-item.xml');
            $product = $this->xml->arrayToXml($product);
            $product->save($path);

            //check Xml via Xsd validator
            //$validate = $this->validateXml($path, $this->xsdPath . 'lmp-item.xsd', self::FEED_CODE_ITEM_UPDATE);
            //echo "<pre>";print_r($validate);die('df');
            //if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {

            $response = $this->postRequest(self::POST_ITEMS_SUB_URL, array('file' => $path));
            //$response = $this->putRequest(self::PUT_ITEMS_SUB_URL, ['data' => $product->__toString()]);
            if ($this->debugMode) {
                $cpPath = $this->getFile(
                    $this->baseDirectory,
                    self::FEED_CODE_ITEM_UPDATE . '-' . time() . '.xml'
                );
                $product->save($cpPath);
                return $this->responseParse($response, self::FEED_CODE_ITEM_UPDATE, $cpPath);
            }
            return $this->responseParse($response, self::FEED_CODE_ITEM_UPDATE);
            //}
            $response = $this->xmlToArray($response);
            return $response;
        }

        return false;
    }


	/**
     * Create/Update Offer on RueDuCommerce
     * @param $data
     * @return bool|array
     */
    public function createOffer($data)
    {
        if (isset($data[0]['offer']) or isset($data[0]['variation-group'])) {
            $product = array(
                'import' =>
                    array(
                        '_attribute' => array(
                        ),
                        '_value' => array(
                            'offers' => array(
                                '_attribute' => array(),
                                '_value' => $data
                            )
                        )
                    )
            );
            $path = $this->getFile($this->baseDirectory, 'item-offer.xml');
            $product = $this->xml->arrayToXml($product);
            $product->save($path);

            //check Xml via Xsd validator
            //$validate = $this->validateXml($path, $this->xsdPath . 'lmp-item.xsd', self::FEED_CODE_ITEM_UPDATE);
            //echo "<pre>";print_r($validate);die('df');
            //if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {
            $response = $this->postRequest(self::POST_OFFER_IMPORT, array('file' => $path, 'with_products' => 'false'), "offer");
            
            //$response = $this->putRequest(self::PUT_ITEMS_SUB_URL, ['data' => $product->__toString()]);
            if ($this->debugMode) {
                $cpPath = $this->getFile(
                    $this->baseDirectory,
                    self::FEED_CODE_INVENTORY_UPDATE . '-' . time() . '.xml'
                );
                $product->save($cpPath);
                return $this->responseParse($response, self::FEED_CODE_INVENTORY_UPDATE, $cpPath);
            }
            return $this->responseParse($response, self::FEED_CODE_INVENTORY_UPDATE);
            //}
            return $response;
        }

        return false;
    }

    /**
     * Create/Update Offer on RueDuCommerce
     * @param $data
     * @return bool|array
     */
    public function createOfferWithProduct($data, $productData)
    {
        if (isset($data[0]['offer'])) {
            $product = array(
                'import' =>
                    array(
                        '_attribute' => array(
                        ),
                        '_value' => array(
                        )
                    )
            );
            if (isset($productData[0]['product']) ) {
                $product['import']['_value']['products'] = array(
                    '_attribute' => array(),
                    '_value' => $productData
                );
            }
            if (isset($data[0]['offer']) ) {
                $product['import']['_value']['offers'] = array(
                    '_attribute' => array(),
                    '_value' => $data
                );
            }

            $path = $this->getFile($this->baseDirectory, 'item-offer.xml');
            $product = $this->xml->arrayToXml($product);
            $product->save($path);

            //check Xml via Xsd validator
            //$validate = $this->validateXml($path, $this->xsdPath . 'lmp-item.xsd', self::FEED_CODE_ITEM_UPDATE);
            //echo "<pre>";print_r($validate);die('df');
            //if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {
            $response = $this->postRequest(self::POST_OFFER_IMPORT, array('file' => $path, 'with_products' => 'true'), "offer");

            //$response = $this->putRequest(self::PUT_ITEMS_SUB_URL, ['data' => $product->__toString()]);
            if ($this->debugMode) {
                $cpPath = $this->getFile(
                    $this->baseDirectory,
                    self::FEED_CODE_INVENTORY_UPDATE . '-' . time() . '.xml'
                );
                $product->save($cpPath);
                return $this->responseParse($response, self::FEED_CODE_INVENTORY_UPDATE, $cpPath);
            }
            return $this->responseParse($response, self::FEED_CODE_INVENTORY_UPDATE);
            //}
            return $response;
        }

        return false;
    }

    /**
     * Deactivate Product on RueDuCommerce
     * @param $data
     * @return bool | array
     */
    public function deactivateProduct($data)
    {
        if (isset($data[0]['item-to-inactivate'])) {
            $product = [
                'catalog-feed' =>
                    [
                        '_attribute' => [
                            'xmlns' => 'http://seller.marketplace.rueducommerce.com/catalog/v22',
                            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                            'xsi:schemaLocation' =>
                                'http://seller.marketplace.rueducommerce.com/catalog/v22 ../../../../../rest/catalog/import/v22/lmp-item.xsd',
                        ],

                        '_value' => [
                            'fbm-catalog' => [
                                '_attribute' => [

                                ],
                                '_value' => [
                                    'items-to-inactivate' => [
                                        '_attribute' => [

                                        ],
                                        '_value' => $data
                                    ]
                                ]
                            ]
                        ]
                    ]
            ];
            $path = $this->getFile($this->baseDirectory, 'lmp-item-deactivate.xml');
            $product = $this->xml->arrayToXml($product);
            $product->save($path);

            //check Xml via Xsd validator
            $validate = $this->validateXml($path, $this->xsdPath . 'lmp-item.xsd', self::FEED_CODE_ITEM_DEACTIVATE);

            if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {
                $response = $this->putRequest(self::PUT_ITEMS_SUB_URL, ['data' => $product->__toString()]);
                if ($this->debugMode) {
                    $cpPath = $this->getFile(
                        $this->baseDirectory,
                        self::FEED_CODE_ITEM_DEACTIVATE . '-' . time() . '.xml'
                    );
                    $product->save($cpPath);
                    return $this->responseParse($response, self::FEED_CODE_ITEM_DEACTIVATE, $cpPath);
                }
                return $this->responseParse($response, self::FEED_CODE_ITEM_DEACTIVATE);
            }
            return $validate;
        }
        return false;
    }

    /**
     * Delete Product on RueDuCommerce
     * @param $data
     * @return bool | array
     */
    public function deleteProduct($data)
    {

        if (isset($data[0]['item-to-delete'])) {
            $product = [
                'catalog-feed' =>
                    [
                        '_attribute' => [
                            'xmlns' => 'http://seller.marketplace.catch.com/catalog/v22',
                            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                            'xsi:schemaLocation' =>
                                'http://seller.marketplace.catch.com/catalog/v22 ../../../../../rest/catalog/import/v22/lmp-item.xsd',
                        ],

                        '_value' => [
                            'fbm-catalog' => [
                                '_attribute' => [

                                ],
                                '_value' => [
                                    'items-to-delete' => [
                                        '_attribute' => [

                                        ],
                                        '_value' => $data
                                    ]
                                ]
                            ]
                        ]
                    ]
            ];

            $path = $this->getFile($this->baseDirectory, 'lmp-item-delete.xml');
            $product = $this->xml->arrayToXml($product);
            $product->save($path);

            //check Xml via Xsd validator
            $validate = $this->validateXml($path, $this->xsdPath . 'lmp-item.xsd', self::FEED_CODE_ITEM_DELETE);

            if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {
                $response = $this->putRequest(self::PUT_ITEMS_SUB_URL, ['data' => $product->__toString()]);
                if ($this->debugMode) {
                    $cpPath = $this->getFile(
                        $this->baseDirectory,
                        self::FEED_CODE_ITEM_DELETE . '-' . time() . '.xml'
                    );
                    $product->save($cpPath);
                    return $this->responseParse($response, self::FEED_CODE_ITEM_DELETE, $cpPath);
                }
                return $this->responseParse($response, self::FEED_CODE_ITEM_DELETE);
            }
            return $validate;
        }
        return false;
    }

    /**
     * Update Inventory
     * @param $data
     * @return array|bool
     */
    public function updateInventory($data)
    {
        if (isset($data[0]['item'])) {
            $inventory = [
                'store-inventory' => [
                    '_attribute' => [
                        'xmlns' => 'http://seller.marketplace.catch.com/catalog/v7',
                        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                        'xsi:schemaLocation' =>
                            'http://seller.marketplace.catch.com/catalog/v7 ../../../../../rest/inventory/import/v7/store-inventory.xsd'],
                    '_value' => $data
                ]
            ];

            $path = $this->getFile($this->baseDirectory, 'store-inventory.xml');
            $xml = new \RueDuCommerceSdk\Generator();
            $inventory = $xml->arrayToXml($inventory);
            $inventory->save($path);

            //check Xml via Xsd validator
            $validate = $this->validateXml(
                $path,
                $this->xsdPath . 'store-inventory.xsd',
                self::FEED_CODE_INVENTORY_UPDATE
            );

            if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {
                $response = $this->putRequest(self::PUT_INVENTORY_SUB_URL, ['data' => $inventory->__toString()]);
                if ($this->debugMode) {
                    $cpPath = $this->getFile(
                        $this->baseDirectory,
                        self::FEED_CODE_INVENTORY_UPDATE . '-' . time() . '.xml'
                    );
                    $inventory->save($cpPath);
                    return $this->responseParse($response, self::FEED_CODE_INVENTORY_UPDATE, $cpPath);
                }
                return $this->responseParse($response, self::FEED_CODE_INVENTORY_UPDATE);
            }
            return $validate;
        }
        return false;
    }

    /**
     * Update Price
     * @param $data
     * @return array|bool
     */
    public function updatePrice($data)
    {
        if (isset($data[0]['item'])) {
            $pricing = [
                'pricing-feed' => [
                    '_attribute' => [
                        'xmlns' => 'http://seller.marketplace.catch.com/pricing/v5',
                        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                        'xsi:schemaLocation' =>
                            'http://seller.marketplace.catch.com/pricing/v5 ../../../../../rest/pricing/import/v5/pricing.xsd',
                    ],
                    '_value' => [
                        'fbm-pricing' => [
                            '_attribute' => [],
                            '_value' => $data
                        ],

                    ]
                ]
            ];

            $path = $this->getFile($this->baseDirectory, 'pricing.xml');
            $xml = new \RueDuCommerceSdk\Generator();
            $pricing = $xml->arrayToXml($pricing);
            $pricing->save($path);

            //check Xml via Xsd validator
            $validate = $this->validateXml($path, $this->xsdPath . 'pricing.xsd', self::FEED_CODE_PRICE_UPDATE);

            if (isset($validate['feed_errors']) && empty($validate['feed_errors'])) {
                $response = $this->putRequest(self::PUT_PRICE_SUB_URL, ['data' => $pricing->__toString()]);
                if ($this->debugMode) {
                    $cpPath = $this->getFile(
                        $this->baseDirectory,
                        self::FEED_CODE_PRICE_UPDATE . '-' . time() . '.xml'
                    );
                    $pricing->save($cpPath);
                    return $this->responseParse($response, self::FEED_CODE_PRICE_UPDATE, $cpPath);
                }
                return $this->responseParse($response, self::FEED_CODE_PRICE_UPDATE);
            }
            return $validate;
        }
        return false;
    }

    // Get inventory for all products uploaded on rueducommerce for status change
    public function getInventory($skus = [])
    {
        if (!empty($skus) and is_array($skus)) {
            $response = $this->getRequest(
                sprintf(self::GET_INVENTORY_SUB_URL, implode(",", $skus), $this->apiSellerId)
            );
            try {
                $response = $this->xmlToArray($response);
                if (isset($response['inventory']['_value']['item'])) {
                    if (isset($response['inventory']['_value']['item']['_attribute'])) {
                        $response['inventory']['_value']['item'][] = $response['inventory']['_value']['item'];
                    }
                    return $response;
                }
            } catch (\Exception $e) {
                if ($this->debugMode) {
                    $this->logger->addDebug('\RueDuCommerceSdk\Product\getInventory(): Error: ' . $e->getMessage());
                }
                return false;
            }
        }
        return false;
    }

    /**
     * Retrieve list of current attribute and attribute values: FBM/FBS
     * @link  https://www.rueducommercecommerceservices.com/question/current-xml-puts-and-gets/
     * @param $itemClassId
     * @return array
     */
    public function getValueLists($attributeCode, $forceFetch = false, $url = self::GET_ATTRIBUTES_VALUE_LIST)
    {
        $valueLists = array();
        try {
            $url .= $attributeCode;
            $valueListFile = $this->baseDirectory . DS . 'attributes' . DS . 'value-list-' . $attributeCode . '.xml';
            if (file_exists($valueListFile) && !$forceFetch) {
                $valueLists = file_get_contents($valueListFile);
            } else {
                $valueLists = $this->getRequest($url);
                //@ToDo check api response data for attributes then save
                file_put_contents(
                    $this->getFile(
                        $this->baseDirectory . DS . 'attributes', 'value-list-' . $attributeCode . '.xml'),
                    $valueLists
                );
            }
            $valueLists = $this->xmlToArray($valueLists);
            return isset($valueLists['body']['values_lists']) ?
                $valueLists['body']['values_lists'] : array();
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Product\\getValueLists() : Errors: " . var_export($e->getMessage(), true)
                );
            }
            return $valueLists;
        }
    }

    /**
     * @todo handle invalid xml response
     * Retrieve list of current offers
     * @param bool $forceFetch
     * @return array
     */
    public function getOffers($chunkSize = 50, $offset = 0, $url = self::GET_OFFERS)
    {
        $offers = array();
        try {
            $url = $url . '?max=' . $chunkSize . '&offset=' . $offset;
            $offers = $this->getRequest($url);
            $offers = $this->xmlToArray($offers);
            return isset($offers['body']['offers']['offer']) ?
                $offers['body']['offers']['offer'] : array();
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Product\\getOffers() : Errors: " . var_export($e->getMessage(), true)
                );
            }
            return $offers;
        }
    }

    /**
     * @todo handle invalid xml response
     * Retrieve list of current attribute and attribute values
     * @param bool $forceFetch
     * @return array
     */
    public function getAllAttributes($forceFetch = false)
    {
        $attributes = array();
        try {
            $itemsFile = $this->baseDirectory . DS . 'attributes' . DS . 'attribute-library.xml';
            if (file_exists($itemsFile) && !$forceFetch) {
                $attributes = file_get_contents($itemsFile);
                $attributes = $this->xmlToArray($attributes);
                $attributes = $attributes['body']['attributes']['attribute'];
            } else {
                $response = [];
                $response = $this->getRequest(self::GET_ATTRIBUTES_SUB_URL, array());
                $responseParsed = $this->xmlToArray($response);
                if (isset($responseParsed['body']['attributes']['attribute'])) {
                    file_put_contents(
                        $this->getFile(
                            $this->baseDirectory . DS . 'attributes',
                            'attribute-library.xml'
                        ),
                        $response
                    );
                    $attributes = $responseParsed['body']['attributes']['attribute'];
                }
            }
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug(
                    "RueDuCommerceSdk\\Product\\getAttributes() : Errors: " . var_export($e->getMessage(), true)
                );
            }
        }
        return $attributes;
    }

}
