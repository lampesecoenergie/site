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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Profile;

use Magento\Framework\DataObject;

/**
 * Class Save
 *
 * @package Ced\Cdiscount\Controller\Adminhtml\Profile
 */
class Save extends \Magento\Backend\App\Action
{
    public $config;
    /**
     * @var \Magento\Framework\Registry
     */
    public $registory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $catalogCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryCollection;

    /**
     * @var \Ced\Cdiscount\Model\ProfileProductFactory
     */
    public $profileProduct;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    public $moduleDataSetup;

    /**
     * @var \Ced\Cdiscount\Model\ProfileFactory
     */
    public $profileFactory;

    /**
     * @var \Ced\Cdiscount\Helper\Profile
     */
    public $profileHelper;

    /**
     * @var DataObject
     */
    public $data;

    public $cache;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Helper\Context $configContext,
        \Magento\Framework\Registry $registory,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Ced\Cdiscount\Helper\Config $cdiscountConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurable,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\DataObject $data,
        \Ced\Cdiscount\Model\ProfileProductFactory $profileProduct,
        \Ced\Cdiscount\Model\ProfileFactory $profileFactory,
        \Ced\Cdiscount\Helper\Profile $profileHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->configStructure = $configStructure;
        $this->registory = $registory;
        $this->config = $cdiscountConfig;
        $this->productConfigFactory = $configurable;
        $this->catalogCollection = $catalogCollection;
        $this->categoryCollection = $categoryCollection;
        $this->profileHelper = $profileHelper;
        $this->profileFactory = $profileFactory;
        $this->profileProduct = $profileProduct;
        $this->data = $data;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $profileId = null;
        $returnToEdit = true;

        if ($this->validate()) {
            $storeId = $this->config->getStore();
            $profile = $this->profileFactory->create()->load($this->data->getProfileId());
            $profile->addData($this->data->getData());
            $profile->save();
            $profile->removeProducts($storeId);
            $profile->addProducts($storeId);
//            die('test');
            $profileId = $profile->getId();
            $this->profileHelper->setProfile($profileId);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($profileId) {
                $resultRedirect->setPath(
                    'cdiscount/profile/edit',
                    ['id' => $profileId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'cdiscount/profile/edit',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('cdiscount/profile/index');
        }
        return $resultRedirect;
    }

    private function validate()
    {
        $generalInformation = $this->getRequest()->getParam('general_information');
        $profileMappings = $this->getRequest()->getParam('profile_mappings');
        $profileProductsFilters = $this->getRequest()->getParam('profile_products_filters');
        $cdiscountAttributes = $this->getRequest()->getParam('cdiscount_attributes');
        $cdiscountModelname = $this->getRequest()->getParam('modelName');
        $producCondition = isset($generalInformation['product_state']) ? $generalInformation['product_state'] : false;
        if (!empty($cdiscountAttributes)) {
            $cdiscountAttributes = $this->mergeAttributes($cdiscountAttributes, 'name');
            $requiredAttributes = $optionalAttributes = [];
            foreach ($cdiscountAttributes as $cdiscountAttribute_key => $cdiscountAttribute_value) {
                if (isset($cdiscountAttribute_value['isMandatory']) and $cdiscountAttribute_value['isMandatory'] == 1) {
                    $requiredAttributes[$cdiscountAttribute_key] = $cdiscountAttribute_value;
                } elseif (isset($cdiscountAttribute_value['model_attributes']) and $cdiscountAttribute_value['model_attributes'] == 1) {
                    $requiredAttributes[$cdiscountAttribute_key] = $cdiscountAttribute_value;
                } else {
                    $optionalAttributes[$cdiscountAttribute_key] = $cdiscountAttribute_value;
                    $optionalAttributes[$cdiscountAttribute_key]['isMandatory'] = 0;
                    $optionalAttributes[$cdiscountAttribute_key]['model_attributes'] = 0;
                }
            }

            foreach ($requiredAttributes as $requiredAttribute) {
                $requiredAttribute['options'] = json_decode($requiredAttribute['options'],true);
                $requiredAttribute['option_mapping'] = json_decode($requiredAttribute['option_mapping'],true);
            }
            $this->data->setData('profile_required_attributes', json_encode($requiredAttributes));
            $this->data->setData('profile_optional_attributes', json_encode($optionalAttributes));
            $this->data->setData('model_name', $cdiscountModelname);
        }

        if (isset($generalInformation)) {
            $this->data->addData($generalInformation);
        }
        if (isset($profileMappings['profile_category'])) {
            if (isset($profileMappings['profile_category'][0]) and is_array($profileMappings['profile_category'])) {
                // On changing category
                $this->data->setData('profile_category', $profileMappings['profile_category'][0]);
            } else {
                // Saving already saved category
                $this->data->setData('profile_category', $profileMappings['profile_category']);
            }
        }

        if (isset($profileProductsFilters)) {
            $this->data->setData('profile_products_filters', $profileProductsFilters);
        }

        if (isset($generalInformation['profile_name'])) {
            $this->data->addData($generalInformation);
        }
        $this->data->setData('product_state', $producCondition);

        if (!$this->data->getProfileCode() or !$this->data->getProfileName() or !$this->data->getProfileCategory()) {
            return false;
        }

        return true;
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    private function mergeAttributes($attributes, $key)
    {

        $tempArray = [];
        $i = 0;
        $keyArray = [];

        if (!empty($attributes) and is_array($attributes)) {
            foreach ($attributes as $val) {
                if (isset($val['delete']) and $val['delete']  == 1) {
                    continue;
                }
                if (!in_array($val[$key], $keyArray)) {
                    $keyArray[$val[$key]] = $val[$key];
                    $tempArray[$val[$key]] = $val;
                }
                $i++;
            }
        }

        return $tempArray;
    }

}
