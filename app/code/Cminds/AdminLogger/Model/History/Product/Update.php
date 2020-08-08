<?php

namespace Cminds\AdminLogger\Model\History\Product;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Product
 */
class Update extends AbstractObject implements HistoryInterface
{
    /**
     * Save data attached to event.
     *
     * @param $event
     *
     * @return array
     */
    public function saveActionData($event)
    {
        $actionType = $event->getData('action_type');
        $product = $event->getData('product');
        $attributes = $product->getAttributes();

        $productNewData = [];
        $productOldData = [];

        foreach ($attributes as $attribute) {
            if ($product->dataHasChangedFor($attribute->getAttributeCode())) {
                if (is_array($product->getData($attribute->getAttributeCode())) === false
                ) {
                    $productNewData[$attribute->getAttributeCode()] = $product->getData($attribute->getAttributeCode());
                } else {
                    foreach ($product->getData($attribute->getAttributeCode()) as $key => $value) {
                        $productNewData[$key] = $value;
                    }
                }

                if ($product->getOrigData($attribute->getAttributeCode())
                    && is_array($product->getOrigData($attribute->getAttributeCode())) === false
                ) {
                    $productOldData[$attribute->getAttributeCode()] = $product->getOrigData(
                        $attribute->getAttributeCode()
                    );
                }
            }
        }

        $productChangedValues = $this->dataChecker
            ->getProductAttributesDataChanges($product, $productOldData, $productNewData);

        // prepare to check stock item product object
        $oldStockItem = $this->registry->registry('cminds_campaignmanager_product_old_stock_item');
        $newStockItem = $product->getData()['extension_attributes']->getStockItem()->getData();

        // check media gallery for add/delete
        $oldMediaGallery = $this->registry
            ->registry('cminds_campaignmanager_product_old_media_gallery')['images'];
        $newMediaGallery = $product->getData('media_gallery')['images'];

        // add/delete gallery item detection
        $galleryChangedValues = [];
        foreach ($newMediaGallery as $key => $value) {
            if (isset($oldMediaGallery[$key]) === false) {
                $galleryChangedValues['old_value']['new_' . $newMediaGallery[$key]['media_type']] = '';
                $galleryChangedValues['new_value']['new_' . $newMediaGallery[$key]['media_type']]
                    = $newMediaGallery[$key]['file'];
            } elseif (isset($newMediaGallery[$key]['removed'])) {
                if ($newMediaGallery[$key]['removed'] == 1) {
                    $galleryChangedValues['old_value']['deleted_' . $newMediaGallery[$key]['media_type']]
                        = $newMediaGallery[$key]['file'];
                    $galleryChangedValues['new_value']['deleted_' . $newMediaGallery[$key]['media_type']] = '';
                }
            }
        }

        // product stock item
        foreach ($newStockItem as $key => $value) {
            if (is_array($newStockItem[$key]) === false) {
                $productNewData[$key] = $value;
                if (isset($oldStockItem[$key])) {
                    if (is_array($oldStockItem[$key]) === false) {
                        $productOldData[$key] = $oldStockItem[$key];
                    }
                }
            } else {
                unset($newStockItem[$key]);
            }
        }

        $stockItemChangedValues = $this->dataChecker->getProductChanges($oldStockItem, $newStockItem);
        $changedValues = array_merge_recursive($productChangedValues, $stockItemChangedValues , $galleryChangedValues);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
