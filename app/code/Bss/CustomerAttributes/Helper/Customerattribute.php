<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Helper;

use Bss\CustomerAttributes\Model\ResourceModel\Attribute\Grid\Collection;

/**
 * Class Customerattribute
 *
 * @package Bss\CustomerAttributes\Helper
 */
class Customerattribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Attribute factory
     *
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * Eav attribute factory
     * @var \Magento\Eav\Model\Config
     */
    protected $eavAttribute;

    /**
     * Store factory
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SaveObject
     */
    protected $saveObject;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Customerattribute constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param SaveObject $saveObject
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ConfigFactory $eavAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerAttributes\Helper\SaveObject $saveObject,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->eavAttribute = $eavAttributeFactory;
        $this->storeManager = $storeManager;
        $this->metadata = $metadata;
        $this->customerRepository = $customerRepository;
        $this->urlEncoder = $context->getUrlEncoder();
        $this->saveObject = $saveObject;
        $this->productMetadata = $productMetadata;
        $this->json = $json;
    }

    /**
     * Get Config
     *
     * @param string $path
     * @param int $store
     * @param string $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = $this->saveObject->returnScopeStore();
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Get Tittle
     *
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->getConfig('bss_customer_attribute/general/title');
    }

    /**
     * Return user defined attributes attributes
     *
     * @return mixed
     */
    public function getUserDefinedAttributes()
    {
        $entityTypeId = $this->saveObject->returnSaveObjectMore()->returnEntityFactory()->create()
            ->setType(\Magento\Customer\Model\Customer::ENTITY)
            ->getTypeId();
        $attribute = $this->attributeFactory->create()
            ->setEntityTypeId($entityTypeId);
        $collection = $attribute->getCollection()
            ->addVisibleFilter()
            ->addFieldToFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        return $collection;
    }

    /**
     * Check Attribute use in account create
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForCustomerAccountCreate($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('customer_account_create_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in account Edit
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForCustomerAccountEdit($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('customer_account_edit_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Order Detail
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForOrderDetail($attributeCode)
    {
        $attribute  = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('order_detail', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Email
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoEmail($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('show_in_email', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in New Account Email
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoEmailNewAccount($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('show_in_email_new_account', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Order Detail Frontend
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoOrderFrontend($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('show_order_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Checkout
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoCheckout($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('show_checkout_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Hide Field If Fill Before
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isHideIfFill($attributeCode)
    {
        $attribute   = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('hide_if_fill_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Get Store Id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Get Attribute Options
     *
     * @param string $attributeCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeOptions($attributeCode)
    {
        $customerEntity = \Magento\Customer\Model\Customer::ENTITY;
        $options = $this->eavAttribute->create()->getAttribute($customerEntity, $attributeCode)
            ->getSource()->getAllOptions();
        return $options;
    }

    /**
     * Get loged in customer data
     *
     * @param int $customerId
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer($customerId)
    {
        $customer = $this->saveObject->returnSaveObjectMore()->returnCustomerFactory()->create()->load($customerId);
        return $customer;
    }

    /**
     * Check Attribute Has Data
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Attribute $attributes
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasDataCustomerAttributesOrderFrontend($customer, $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                continue;
            }
            if ($this->isAttribureAddtoOrderFrontend($attribute->getAttributeCode())) {
                if ($customer->getCustomAttribute($attribute->getAttributeCode())) {
                    if ($customer->getCustomAttribute($attribute->getAttributeCode())->getValue() != '') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get Attribute Html
     *
     * @param string $idCustomer
     * @param $order
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributesHtml($idCustomer, $order)
    {
        $html = '';
        if ($this->getConfig('bss_customer_attribute/general/enable') && $idCustomer) {
            $entityTypeId = 'customer';
            $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
            $customer = $this->customerRepository->getById($idCustomer);
            if ($this->hasDataCustomerAttributesOrderFrontend($customer, $attributes)) {
                $html = '<h3>' . $this->getTitle() . '</h3>';
                foreach ($attributes as $attribute) {
                    $displayOrderDetail = (bool)$this->isAttribureForOrderDetail($attribute->getAttributeCode());
                    if ($attribute->isSystem()
                        || !$attribute->isUserDefined()
                        || !$attribute->isVisible()
                        || !$displayOrderDetail
                    ) {
                        continue;
                    }

                    if ($this->isAttribureAddtoOrderFrontend($attribute->getAttributeCode())) {
                        $orderKey = sprintf('customer_%s', $attribute->getAttributeCode());
                        if ($order->getData($orderKey) != '') {
                            $html .= $this->getValueAttribute($attribute, $order->getData($orderKey));
                        }
                    }
                }
            }
        }
        return $html;
    }

    /**
     * @param $attribute
     * @param $attributeValue
     * @return string
     */
    private function getValueAttribute($attribute, $attributeValue)
    {
        $html = '';
        if ($attribute->getOptions()) {
            $valueOption = $attributeValue;
            $valueOption = explode(",", $valueOption);
            $label = "";
            foreach ($valueOption as $value) {
                foreach ($attribute->getOptions() as $option) {
                    if ($value == $option->getValue()) {
                        $label .= $option->getLabel() . ",";
                    }
                }
            }
            $html .= "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
                $attribute->getFrontendLabel() . ' : ' . "</span></div>" . "<div class=\"value_attribute\"><span>" .
                rtrim($label, ",") .
                "</span></div></div><br/>";
        } else {
            $valueAttribute = $attributeValue;
            $html .= $this->getAttributeFileinOrderFront($attribute, $valueAttribute);
        }
        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @return string
     */
    private function getAttributeFileinOrderFront($attribute, $valueAttribute)
    {
        $html = "";
        if ($attribute->getFrontendInput() == 'file') {
            if (!$this->getConfig("bss_customer_attribute/general/allow_download_file")) {
                $noDownload = "controlsList =\"nodownload\" ";
            } else {
                $noDownload = " ";
            }

            if (!$this->getConfig("bss_customer_attribute/general/allow_download_file")) {
                $noDownloadFile = "class=\"disabled\"";
            } else {
                $noDownloadFile = " ";
            }

            if (preg_match("/\.(gif|png|jpg)$/", $valueAttribute)) {
                $html .= $this->getFileImageFrontend($attribute, $valueAttribute);
            } elseif (preg_match("/\.(mp4|3gb|mov|mpeg)$/", $valueAttribute)) {
                $html .= $this->getFileVideoFrontend($attribute, $valueAttribute, $noDownload);
            } elseif (preg_match("/\.(mp3|ogg|wav)$/", $valueAttribute)) {
                $html .= $this->getFileAudioFrontend($attribute, $valueAttribute, $noDownload);
            } else {
                $html .= $this->getFileOtherFrontend($attribute, $valueAttribute, $noDownloadFile);
            }
        } else {
            $html .= "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
                $attribute->getFrontendLabel() . ': ' . "</span></div>" . "<div class=\"value_attribute\"><span>" .
                $valueAttribute . "</span></div></div><br/>";
        }

        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @return string
     */
    private function getFileImageFrontend($attribute, $valueAttribute)
    {
        $tagA = "";
        $endTagA = "";
        if ($this->getConfig("bss_customer_attribute/general/allow_download_file")) {
            $tagA = "<a href=\"" . $this->getViewFile($valueAttribute) . "\"" . " target=\"_blank\" >";
            $endTagA = "</a>";
        }
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
            $attribute->getFrontendLabel() . ': ' . "</span></div>" .
            $tagA . "<div class=\"value_attribute\"><img src=\"" .
            $this->getViewFile($valueAttribute) . "\" alt=\""
            . $this->getFileName($valueAttribute) . "\" width=\"200\" /></div>" .
            "</div>" . $endTagA . "<br/>";
        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @param string $noDownload
     * @return string
     */
    private function getFileVideoFrontend($attribute, $valueAttribute, $noDownload)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
            $attribute->getFrontendLabel() . ': ' .
            "</span></div>" . "<div class=\"value_attribute\">
            <video width=\"400\" height=\"100\" " . $noDownload . " controls>" .
            "<source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"video/mp4\">
            <source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"video/ogg\">
            Your browser does not support HTML5 video.
            </video></div>" .
            "</div><br/>";

        return $html;
    }

    /**
     * @param Attribute$attribute
     * @param string $valueAttribute
     * @param string $noDownload
     * @return string
     */
    private function getFileAudioFrontend($attribute, $valueAttribute, $noDownload)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\">
            <span>" . $attribute->getFrontendLabel() . ': ' .
            "</span></div>" . "<div class=\"value_attribute\"><audio controls " . $noDownload . " >" .
            "<source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"audio/mpeg\">
            <source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"audio/ogg\">
            Your browser does not support the audio element.
            </audio>
            </div>" . "</div><br/>";

        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @param string $noDownloadFile
     * @return string
     */
    private function getFileOtherFrontend($attribute, $valueAttribute, $noDownloadFile)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\">
            <span>" . $attribute->getFrontendLabel() . ': ' . "</span></div>" .
            "<div class=\"value_attribute\"><span>" .
            "<a href=\"" . $this->getViewFile($valueAttribute) . "\"" . " " . $noDownloadFile . " " .
            "target=\"_blank\">" . $this->getFileName($valueAttribute) . "</a>
            </span></div></div><br/>";

        return $html;
    }

    /**
     * Return escaped value
     *
     * @return string
     */
    public function getViewFile($fieldValue)
    {
        if ($fieldValue) {
            return $this->_getUrl(
                'customerattribute/index/viewfile',
                [
                    'file' => $this->urlEncoder->encode($fieldValue)
                ]
            );
        }
        return $fieldValue;
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     */
    public function getValueValidateFile($attribute)
    {
        $version = $this->productMetadata->getVersion();
        $value = $attribute->getData('validate_rules');
        // @codingStandardsIgnoreStart
        if (version_compare($version, '2.2.0', '<')) {
            $data = unserialize($value);
        } else {
            $data = $this->json->unserialize($value);
        }
        return $data;
    }

    /**
     * Get Default Value Required
     *
     * @param \Magento\Customer\Model\Attribute $attributeObject
     * @return mixed|string
     */
    public function getDefaultValueRequired($attributeObject)
    {
        $frontendInput = $attributeObject->getFrontendInput();
        $defaultRequired = "";
        if ($frontendInput == 'text'
            || $frontendInput == "textarea"
            || $frontendInput == "date"
            || $frontendInput == "file"
        ) {
            $validateRules = $attributeObject->getValidateRules();
            if ($validateRules) {
                if (!is_array($validateRules)) {
                    $validateRules = json_decode($validateRules, true);
                }
                if (isset($validateRules['default_value_required'])) {
                    $defaultRequired = $validateRules['default_value_required'];
                }
            }
        } else {
            $defaultRequired = $attributeObject->getDefaultValue();
        }

        return $defaultRequired;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFileName($filename)
    {
        if (strpos($filename, "/") !== false) {
            $nameArr = explode("/", $filename);
            return end($nameArr);
        }
        return $filename;
    }
}
