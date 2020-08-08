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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Ced\Amazon\Api\Service\ConfigServiceInterface;

abstract class Base extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::profile';

    const PROFILE_ATTRIBUTES = "amazon_attributes";

    /** @var \Magento\Ui\Component\MassAction\Filter */
    public $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $catalog;

    /** @var \Ced\Amazon\Model\Profile\Product */
    public $product;

    /**
     * @var \Ced\Amazon\Model\Profile
     */
    public $profile;

    /**
     * @var \Ced\Amazon\Repository\Profile
     */
    public $repository;

    /** @var ConfigServiceInterface */
    public $config;

    /** @var \Magento\Framework\DataObject */
    public $data;

    /** @var \Magento\Framework\DataObject */
    public $validation;

    /**
     * TODO: move validate to repository
     * Base constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\DataObjectFactory $data
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection
     * @param \Ced\Amazon\Model\Profile\Product $product
     * @param \Ced\Amazon\Repository\Profile $repository
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @param ConfigServiceInterface $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\DataObjectFactory $data,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection,
        \Ced\Amazon\Model\Profile\Product $product,
        \Ced\Amazon\Repository\Profile $repository,
        \Ced\Amazon\Api\Data\ProfileInterface $profile,
        ConfigServiceInterface $config
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->catalog = $catalogCollection;
        $this->config = $config;

        $this->repository = $repository;
        $this->profile = $profile;

        $this->product = $product;
        $this->data = $data->create();
        $this->validation = $data->create();
    }

    /**
     * Validating post profile data.
     * @param bool $setErrors
     * @return bool
     */
    public function validate($setErrors = false)
    {
        $status = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STATUS, 0);
        $status = ($status === 'true' || $status == '1') ? 1 : 0;
        $this->data->setData(
            \Ced\Amazon\Model\Profile::COLUMN_STATUS,
            $status
        );

        $this->data->setData(
            \Ced\Amazon\Model\Profile::COLUMN_NAME,
            $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_NAME)
        );

        $category = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_CATEGORY);
        if (!empty($category)) {
            $category = explode('_', $category);
            if (isset($category[0]) && !empty($category[0])) {
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_CATEGORY, $category[0]);
            }
        }

        // Saving sub_category array
        $subCategory = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY);
        if (isset($subCategory)) {
            if (isset($subCategory[0]) && is_array($subCategory)) {
                $subCategory = explode('_', $subCategory[0]);
                // On changing category
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY, $subCategory[1]);
            } else {
                $subCategory = explode('_', $subCategory);
                // Saving already saved category
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY, $subCategory[1]);
            }
        }

        // Saving marketplace array
        $regions = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE);
        if (!empty($regions) && is_array($regions)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE, implode(',', $regions));
        }

        $appId = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID);
        if (isset($appId) && !empty($appId)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID, $appId);
        }

        $filter = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_FILTER);

        if (isset($filter) && !empty($filter)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_FILTER, $filter);
        }

        $id = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_ID);
        if (!empty($id)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_ID, $id);
        }

        $storeId = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID);
        if (isset($storeId)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID, $storeId);
        }

        $barcode = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXCEMPTION, 0);
        if (isset($barcode)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXCEMPTION, $barcode);
        }

        $messages = [];
        $valid = true;
        // Validating required values
        foreach (\Ced\Amazon\Model\Profile::COLUMN_REQUIRED as $column) {
            if (empty($this->data->getData($column))) {
                $valid = false;
                $error = "Invalid data provided: {$column}.";
                if ($setErrors == false) {
                    $this->messageManager->addErrorMessage($error);
                } else {
                    $messages[] = $error;
                }
            }
        }

        $this->validation->setData('messages', $messages);

        return $valid;
    }

    /**
     * Add attribute mapping
     */
    public function addAttributes()
    {
        // TODO: continue; FIX NAME SET AS ATTRIBUTE CODE
        $attributes = $this->getRequest()->getParam(self::PROFILE_ATTRIBUTES);
        if (!empty($attributes) && is_array($attributes)) {
            $attributes = $this->merge($attributes, 'value');
            $requiredAttributes = $optionalAttributes = [];
            foreach ($attributes as $attributeId => $attribute) {
                if (isset($attribute['minOccurs']) && $attribute['minOccurs'] == 1) {
                    $requiredAttributes[$attributeId] = $attribute;
                } else {
                    $optionalAttributes[$attributeId] = $attribute;
                    $optionalAttributes[$attributeId]['minOccurs'] = 0;
                }
            }

            $this->data->setData(
                \Ced\Amazon\Model\Profile::COLUMN_REQUIRED_ATTRIBUTES,
                $requiredAttributes
            );

            $this->data->setData(
                \Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES,
                $optionalAttributes
            );
        }
    }

    /**
     * Merging attribute mapping.
     * @param $attributes
     * @param $key
     * @return array
     */
    private function merge($attributes, $key)
    {
        $tempArray = [];
        $i = 0;
        $keyArray = [];

        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attribute) {
                if ((isset($val['delete']) && $attribute['delete'] == 1) || empty($attribute['value'])) {
                    continue;
                }

                if (!in_array($attribute[$key], $keyArray)) {
                    // decoding attribute options
                    if (isset($attribute['restriction']['optionValues']) &&
                        !empty($attribute['restriction']['optionValues'])) {
                        $data = htmlspecialchars_decode($attribute['restriction']['optionValues']);
                        $data = json_decode($data, true);
                        if (!empty($data) && is_array($data)) {
                            $options = $data;
                        } else {
                            $options = [];
                        }

                        $attribute['restriction']['optionValues'] = $options;
                    }

                    $keyArray[$attribute[$key]] = $attribute[$key];
                    $tempArray[$attribute[$key]] = $attribute;
                }
                $i++;
            }
        }

        return $tempArray;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Amazon::profile');
    }
}
