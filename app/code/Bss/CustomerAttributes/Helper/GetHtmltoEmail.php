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

/**
 * Class Customerattribute
 *
 * @package Bss\CustomerAttributes\Helper
 */
class GetHtmltoEmail extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * GetHtmltoEmail constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param SaveObject $saveObject
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ConfigFactory $eavAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerAttributes\Helper\SaveObject $saveObject,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->eavAttribute = $eavAttributeFactory;
        $this->storeManager = $storeManager;
        $this->metadata = $metadata;
        $this->customerRepository = $customerRepository;
        $this->urlEncoder = $context->getUrlEncoder();
        $this->saveObject = $saveObject;
        $this->customerFactory = $customerFactory;
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
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->getConfig('bss_customer_attribute/general/title');
    }

    /**
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
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Attribute $attributes
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasDataCustomerAttributesEmail($customer, $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                continue;
            }
            if ($this->isAttribureAddtoEmail($attribute->getAttributeCode())) {
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
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Attribute $attributes
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasDataCustomerAttributesEmailNewAccount($customer, $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                continue;
            }
            if ($this->isAttribureAddtoEmailNewAccount($attribute->getAttributeCode())) {
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
     * @param int $idCustomer
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariableEmailHtml($idCustomer)
    {
        $html = '';
        if ($this->getConfig('bss_customer_attribute/general/enable') && $idCustomer) {
            $entityTypeId = 'customer';
            $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
            $customer = $this->customerFactory->create()->load($idCustomer);
            $customerData = $customer->getDataModel();
            if ($this->hasDataCustomerAttributesEmail($customerData, $attributes)) {
                $html = '<h3>' . $this->getTitle() . '</h3>';
                foreach ($attributes as $attribute) {
                    if ($attribute->isSystem() || !$attribute->isUserDefined() || !$attribute->isVisible()) {
                        continue;
                    }

                    if ($this->isAttribureAddtoEmail($attribute->getAttributeCode())) {
                        if ($customerData->getCustomAttribute($attribute->getAttributeCode())) {
                            $html .= $this->getValueAttributetoEmail($attribute, $customerData);
                        }
                    }
                }
            }
        }
        return $html;
    }

    /**
     * @param int $idCustomer
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariableEmailNewAccountHtml($idCustomer)
    {
        $html = '';
        if ($this->getConfig('bss_customer_attribute/general/enable') && $idCustomer) {
            $entityTypeId = 'customer';
            $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
            $customer = $this->customerRepository->getById($idCustomer);
            if ($this->hasDataCustomerAttributesEmailNewAccount($customer, $attributes)) {
                $html = '<h3>' . $this->getTitle() . '</h3>';
                foreach ($attributes as $attribute) {
                    if ($attribute->isSystem() || !$attribute->isUserDefined() || !$attribute->isVisible()) {
                        continue;
                    }

                    if ($this->isAttribureAddtoEmailNewAccount($attribute->getAttributeCode())) {
                        if ($customer->getCustomAttribute($attribute->getAttributeCode())) {
                            $html .= $this->getValueAttributetoEmail($attribute, $customer);
                        }
                    }
                }
            }
        }
        return $html;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param \Magento\Customer\Model\Customer $customer
     * @return string
     */
    private function getValueAttributetoEmail($attribute, $customer)
    {
        $html = '';
        if ($customer->getCustomAttribute($attribute->getAttributeCode())->getValue() != '') {
            if ($attribute->getOptions()) {
                $valueOption = $customer->getCustomAttribute($attribute->getAttributeCode())->getValue();
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
                    $attribute->getFrontendLabel() . ': ' . "</span></div>" . "<div class=\"value_attribute\"><span>" .
                    rtrim($label, ",") .
                    "</span></div></div><br/>";
            } else {
                $valueAttribute = $customer->getCustomAttribute($attribute->getAttributeCode())->getValue();
                $html .= $this->getAttributeFileinEmail($attribute, $valueAttribute);
            }
        }
        return $html;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param string $valueAttribute
     * @return string
     */
    private function getAttributeFileinEmail($attribute, $valueAttribute)
    {
        $html = "";
        if ($attribute->getFrontendInput() == 'file') {
            if (!$this->getConfig("bss_customer_attribute/general/allow_download_file")) {
                $noDownloadFile = "class=\"disabled\"";
            } else {
                $noDownloadFile = " ";
            }

            if (preg_match("/\.(gif|png|jpg)$/", $valueAttribute)) {
                $html .= $this->getFileImageFrontend($attribute, $valueAttribute);
            } elseif (preg_match("/\.(mp4|3gb|mov|mpeg)$/", $valueAttribute)) {
                $html .= $this->getFileVideoAudiotoEmail($attribute, $valueAttribute);
            } elseif (preg_match("/\.(mp3|ogg|wav)$/", $valueAttribute)) {
                $html .= $this->getFileVideoAudiotoEmail($attribute, $valueAttribute);
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
     * @param \Magento\Customer\Model\Attribute $attribute
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
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param string $valueAttribute
     * @return string
     */
    private function getFileVideoAudiotoEmail($attribute, $valueAttribute)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
            $attribute->getFrontendLabel() . ': ' .
            "</span></div>" . "<div class=\"value_attribute\"><a href=\"" . $this->getViewFile($valueAttribute) .
            "\">" . $this->getFileName($valueAttribute) . "</a>
            </div>" .
            "</div><br/>";

        return $html;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param string $valueAttribute
     * @param string $noDownloadFile
     * @return string
     */
    private function getFileOtherFrontend($attribute, $valueAttribute, $noDownloadFile)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\">
            <span>" . $attribute->getFrontendLabel() . ': '."</span></div>"."<div class=\"value_attribute\"><span>" .
            "<a href=\"" . $this->getViewFile($valueAttribute) . "\"" . " " . $noDownloadFile . " " .
            "target=\"_blank\">" . $this->getFileName($valueAttribute) . "</a>
            </span></div></div><br/>";

        return $html;
    }

    /**
     * Return escaped value
     *
     * @param string $fieldValue
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
