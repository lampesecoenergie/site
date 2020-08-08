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

namespace Bss\CustomerAttributes\Controller\Adminhtml\Attribute;

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Save
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\Attribute
 */
class Save extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\AbstractAction
{
    /**
     * @var \Bss\CustomerAttributes\Helper\SaveObject
     */
    protected $saveObject;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helperCustomerAttribute;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\CustomerAttributes\Helper\SaveObject $saveObject
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\CustomerAttributes\Helper\SaveObject $saveObject,
        CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute
    ) {
        parent::__construct($context, $coreRegistry, $productUrl, $eavEntity, $resultPageFactory);
        $this->saveObject = $saveObject;
        $this->helperCustomerAttribute = $helperCustomerAttribute;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Save Attribute Execute
     *
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $attributeId = $this->getRequest()->getParam('attribute_id');
            $attributeCode = $this->setAttributeCode();
            if ($this->validatorAttrCode($attributeCode, $attributeId)) {
                return $this->validatorAttrCode($attributeCode, $attributeId);
            }

            if (!$attributeId) {
                $data['attribute_code'] = 'ca_' . $attributeCode;
            }
            //validate frontend_input
            if ($this->validateFrontEndInput($data, $attributeId)) {
                return $this->validateFrontEndInput($data, $attributeId);
            }
            $previousRequiredDefault = "";
            if ($attributeId) {
                $attribute = $this->saveObject->returnModelAttribute()->load($attributeId);
                if ($this->checkIdandEntityTypeId($data)) {
                    return $this->checkIdandEntityTypeId($data);
                }
                $data['attribute_code'] = $attribute->getAttributeCode();
                $data['is_user_defined'] = $attribute->getIsUserDefined();
                $data['frontend_input'] = $attribute->getData('frontend_input');
                $previousRequiredDefault = $this->helperCustomerAttribute->getDefaultValueRequired($attribute);
            } else {
                $data['source_model'] = $this->saveObject->getHelperData()->getAttributeSourceModelByInputType(
                    $data['frontend_input']
                );
                $data['backend_model'] = $this->saveObject->getHelperData()->getAttributeBackendModelByInputType(
                    $data['frontend_input']
                );
                $data['data_model'] = $this->saveObject->getHelperData()->getAttributeDataModelByInputType(
                    $data['frontend_input']
                );
            }
            if ($this->saveObject
                    ->returnModelAttribute()
                    ->getIsUserDefined() === null || $this->saveObject->returnModelAttribute()
                    ->getIsUserDefined() != 0) {
                $data['backend_type'] = $this->saveObject
                    ->getHelperData()
                    ->getBackendTypeByInput($data['frontend_input']);
            }
            $mediaPath = $this->saveObject->returnSaveObjectMore()->returnFileSystem()->getDirectoryRead(
                $this->saveObject->returnSaveObjectMore()->returnDirectMedia()
            )->getAbsolutePath();
            $media = $mediaPath . 'customer/';
            $file = $this->getRequest()->getFiles('default_value_file_required');
            $defaultValueField = $this->saveObject->getHelperData()->getDefaultValueByInput($data['frontend_input']);
            $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            $defaultValueRequired = $this->setDefaultValueRequired($data);
            $data['default_value_required'] = $this->setParamDefaultValueRequired($defaultValueRequired, $data, $file);
            $data['file_default_attribute'] = $this->setParamFileDefaultAttribute($data, $file, $media);
            $usedInForms = $this->getUsedInForm($data);
            $this->_eventManager->dispatch(
                'bss_attribute_form',
                ['usedInForms' => $usedInForms, 'dataPost' => $data]
            );
            $data['used_in_forms'] = $usedInForms->getData();

            //Get default attribute set id
            $defaultAttrSetId = $this->saveObject->returnEavConfig()
                ->getEntityType($this->saveObject->returnConstEntity())
                ->getDefaultAttributeSetId();
            $data['attribute_set_id'] = $defaultAttrSetId;

            //Get default attribute group id
            $defaultAttrGroupId = $this->saveObject->returnSaveObjectMore()->returnAttributeSet()
                ->getDefaultGroupId($defaultAttrSetId);
            $data['attribute_group_id'] = $defaultAttrGroupId;
            $data['sort_order'] = (int)$data['sort_order'] + $this->saveObject->returnDefaultSortOrder();
            $data['validate_rules'] = $this->saveObject
                ->getHelperData()
                ->getAttributeValidateRules($data['frontend_input'], $data);
            $this->saveObject->returnModelAttribute()->addData($data);
            $this->checkUseInCustomerGrid($attributeId, $data);
            try {
                $this->saveObject->returnModelAttribute()->save();
                $this->messageManager->addSuccessMessage(__('You saved the customer attribute.'));
                $this->checkDefaultRequired($attributeId, $data, $previousRequiredDefault);
                $this->_session->setAttributeData(false);
                $indexer = $this->saveObject->returnIndexer()->get("customer_grid");
                $indexer->reindexAll();

                return $this->returnResultInTry();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setAttributeData($data);
                return $this->returnResult(
                    'customerattribute/*/edit',
                    ['attribute_id' => $attributeId, '_current' => true],
                    ['error' => true]
                );
            }
        }
        return $this->returnResult('customerattribute/*/', [], ['error' => true]);
    }

    /**
     * @param string $defaultValueRequired
     * @param array $data
     * @param array $file
     * @return string|null
     */
    protected function setParamDefaultValueRequired($defaultValueRequired, $data, $file)
    {
        $data['default_value_required'] = $this->getRequest()->getParam($defaultValueRequired);
        if ($data['frontend_input'] == 'file' && $file['name']) {
            $data['default_value_required'] = $file['name'];
        }
        return $data['default_value_required'];
    }

    /**
     * @param array $data
     * @param array $file
     * @param string $media
     * @return string|null
     */
    protected function setParamFileDefaultAttribute($data, $file, $media)
    {
        $data['file_default_attribute'] = '';
        if ($data['frontend_input'] == 'file' && $file['name']) {
            // @codingStandardsIgnoreStart
            move_uploaded_file($file['tmp_name'], $media.$file['name']);
            // @codingStandardsIgnoreEnd
            $data['file_default_attribute'] = $file['name'];
        }
        return $data['file_default_attribute'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    protected function setDefaultValueRequired($data)
    {
        $defaultValueRequired = $this->saveObject->getHelperData()->getDefaultValueByInput(
            $data['frontend_input'],
            '_required'
        );
        if ($data['frontend_input'] == 'file') {
            $defaultValueRequired = $this->saveObject->getHelperData()->getDefaultValueByInput(
                'text',
                '_required'
            );
        }
        return $defaultValueRequired;
    }

    /**
     * Check Attribute
     *
     * @param array $data
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    private function checkIdandEntityTypeId($data)
    {
        if (!$this->saveObject->returnModelAttribute()->getId()) {
            $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
            return $this->returnResult('customerattribute/*/', [], ['error' => true]);
        }

        // entity type check
        if ($this->saveObject->returnModelAttribute()->getEntityTypeId() != $this->_entityTypeId) {
            $this->messageManager->addErrorMessage(__('We can\'t update the attribute.'));
            $this->_session->setAttributeData($data);
            return $this->returnResult('customerattribute/*/', [], ['error' => true]);
        }

        return false;
    }

    /**
     * Validate attribute code
     *
     * @param string $attributeCode
     * @param int $attributeId
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    private function validatorAttrCode($attributeCode, $attributeId)
    {
        $strlen = strlen($attributeCode);
        if ($strlen > 0) {
            if (!preg_match("/^[a-z][a-z_0-9]{0,30}$/", $attributeCode)) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                        'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                        $attributeCode
                    )
                );
                return $this->returnResult(
                    'customerattribute/*/edit',
                    ['attribute_id' => $attributeId, '_current' => true],
                    ['error' => true]
                );
            }
        }
        return false;
    }

    /**
     * Validate Frontend Input
     *
     * @param array $data
     * @param int $attributeId
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    private function validateFrontEndInput($data, $attributeId)
    {
        if (isset($data['frontend_input'])) {
            $inputType = $this->saveObject->returnSaveObjectMore()->returnValidationFactory()->create();
            if (!$inputType->isValid($data['frontend_input'])) {
                foreach ($inputType->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                return $this->returnResult(
                    'customerattribute/*/edit',
                    ['attribute_id' => $attributeId, '_current' => true],
                    ['error' => true]
                );
            }
        }
        return false;
    }

    /**
     * Set Attribute code
     *
     * @return mixed|string
     */
    private function setAttributeCode()
    {
        if ($this->getRequest()->getParam('attribute_code')) {
            return $this->getRequest()->getParam('attribute_code');
        } else {
            return $this->generateCode($this->getRequest()->getParam('frontend_label')[0]);
        }
    }

    /**
     * Return Result Intry
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    private function returnResultInTry()
    {
        if ($this->getRequest()->getParam('back', false)) {
            return $this->returnResult(
                'customerattribute/*/edit',
                ['attribute_id' => $this->saveObject->returnModelAttribute()->getId(), '_current' => true],
                ['error' => false]
            );
        }
        return $this->returnResult('customerattribute/*/', [], ['error' => false]);
    }

    /**
     * Set Use In Form
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getUsedInForm($data)
    {
        $usedInForms = $this->saveObject->returnDataObjectFactory()->create();

        $usedInForms[0] = 'adminhtml_customer';
        $usedInForms[1] = 'customer_account_create';
        $usedInForms[2] = 'customer_account_edit';
        $usedInForms[3] = 'is_customer_attribute';

        $num = 4;
        if ($this->setUserInFormAccountCreate($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUserInFormAccountCreate($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUserInFormAccountEdit($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUserInFormAccountEdit($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUserInFormShowInOrderEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUserInFormShowInOrderEmail($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUserInFormNewAccountEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUserInFormNewAccountEmail($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUserInFormShowOrderFrontend($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUserInFormShowOrderFrontend($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInFormOrDetail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormOrDetail($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInCheckout($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInCheckout($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setHideInCheckoutIfFill($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setHideInCheckoutIfFill($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setShowAttrSection($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setShowAttrSection($data, $usedInForms, $num);
        }
        return $usedInForms;
    }

    /**
     * Set Attribute use in Customer Create Account page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUserInFormAccountCreate($data, $usedInForms, $num)
    {
        if (isset($data['customer_account_create_frontend']) && $data['customer_account_create_frontend'] == 1) {
            $usedInForms[$num] = 'customer_account_create_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Customer Edit Account page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUserInFormAccountEdit($data, $usedInForms, $num)
    {
        if (isset($data['customer_account_edit_frontend']) && $data['customer_account_edit_frontend'] == 1) {
            $usedInForms[$num] = 'customer_account_edit_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Order email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUserInFormShowInOrderEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_email']) && $data['show_in_email'] == 1) {
            $usedInForms[$num] = 'show_in_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Order Frontend
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUserInFormShowOrderFrontend($data, $usedInForms, $num)
    {
        if (isset($data['show_order_frontend']) && $data['show_order_frontend'] == 1) {
            $usedInForms[$num] = 'show_order_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Order Details
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormOrDetail($data, $usedInForms, $num)
    {
        if (isset($data['order_detail']) && $data['order_detail'] == 1) {
            $usedInForms[$num] = 'order_detail';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Create Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUserInFormNewAccountEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_email_new_account']) && $data['show_in_email_new_account'] == 1) {
            $usedInForms[$num] = 'show_in_email_new_account';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Use In Checkout Page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInCheckout($data, $usedInForms, $num)
    {
        if (isset($data['show_checkout_frontend']) && $data['show_checkout_frontend'] == 1) {
            $usedInForms[$num] = 'show_checkout_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Hide In Checkout If Fill Before
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setHideInCheckoutIfFill($data, $usedInForms, $num)
    {
        if (isset($data['hide_if_fill_frontend']) && $data['hide_if_fill_frontend'] == 1) {
            $usedInForms[$num] = 'hide_if_fill_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set position of attribute in registration form
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setShowAttrSection($data, $usedInForms, $num)
    {
        if (isset($data['show_customer_attr_in'])) {
            $usedInForms[$num] = $data['show_customer_attr_in'];
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Check show in grid
     *
     * @param int $attributeId
     * @param array $data
     */
    private function checkUseInCustomerGrid($attributeId, $data)
    {
        if (!$attributeId) {
            $this->saveObject->returnModelAttribute()->setEntityTypeId($this->_entityTypeId);
            $this->saveObject->returnModelAttribute()->setIsUserDefined(1);
        }

        if (isset($data['is_used_in_grid']) && $data['is_used_in_grid'] == 1) {
            $this->saveObject->returnModelAttribute()->setIsVisibleInGrid(1);
            $this->saveObject->returnModelAttribute()->setIsFilterableInGrid(1);
            $this->saveObject->returnModelAttribute()->setIsSearchableInGrid(0);
            $this->saveObject->returnModelAttribute()->setIsUserInGrid(1);
        } else {
            $this->saveObject->returnModelAttribute()->setIsVisibleInGrid(0);
            $this->saveObject->returnModelAttribute()->setIsFilterableInGrid(0);
            $this->saveObject->returnModelAttribute()->setIsSearchableInGrid(0);
            $this->saveObject->returnModelAttribute()->setIsUserInGrid(0);
        }
    }

    /**
     * Return Result
     *
     * @param string $path
     * @param array $params
     * @param array $response
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->saveObject->returnLayoutFactory()->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create($this->saveObject->returnTypeJson())->setData($response);
        }
        return $this->resultFactory->create($this->saveObject->returnTypeRedirect())->setPath($path, $params);
    }

    /**
     * Define whether request is Ajax
     *
     * @return boolean
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }

    /**
     * Check Set Default Required For Existing Customer
     *
     * @param int $attributeId
     * @param array $data
     * @param string $previousRequiredDefault
     */
    private function checkDefaultRequired($attributeId, $data, $previousRequiredDefault)
    {
        $default = [];
        if (isset($data['default'])) {
            $default = $data['default'];
        }
        $defaultRequiredValue = null;
        /* If attribute is created and attribute has default_value_required */
        $isAllowSetDefaultConfig = $this->saveObject->getHelperData()->isAllowSetDefaultConfig();
        $frontendInput = $data['frontend_input'];
        if ($frontendInput == 'text' || $frontendInput == "textarea" ||
            $frontendInput == "date") {
            $defaultRequiredValue = $data['default_value_required'];
        } else {
            if ($frontendInput == "file" && $data['file_default_attribute']) {
                $defaultRequiredValue = $data['file_default_attribute'];
            } else {
                $defaultRequiredValue = implode(",", $default);
            }
        }
        $this->setAttributeForCustomer(
            $attributeId,
            $defaultRequiredValue,
            $previousRequiredDefault,
            $isAllowSetDefaultConfig,
            $data
        );
    }

    /**
     * @param int $attributeId
     * @param string|null $defaultRequiredValue
     * @param string|null $previousRequiredDefault
     * @param string $isAllowSetDefaultConfig
     * @param array $data
     */
    protected function setAttributeForCustomer(
        $attributeId,
        $defaultRequiredValue,
        $previousRequiredDefault,
        $isAllowSetDefaultConfig,
        $data
    ) {
        if ((!$attributeId
                || ($defaultRequiredValue !== null && ($defaultRequiredValue != $previousRequiredDefault)))
            && $isAllowSetDefaultConfig) {
            if ($data['is_required'] == 1) {
                $this->setDefaultRequired($data['attribute_code'], $defaultRequiredValue);
            }
        }
    }

    /**
     * Set Default Required For Existing Customer
     *
     * @param string $code
     * @param string $value
     */
    private function setDefaultRequired($code, $value)
    {
        $customerCollection = $this->saveObject->getHelperData()->returnCustomerFactory()->create();
        foreach ($customerCollection as $customer) {
            $customerDataModel = $this->customerRepository->get($customer->getEmail());
            $customerDataModel->setCustomAttribute($code, $value);
            $this->setCustomer($customerDataModel);
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return mixed
     */
    private function setCustomer($customer)
    {
        /** ignore validate customer attribute */
        $customer->setData('ignore_validation_flag', true);
        return $this->customerRepository->save($customer);
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::save');
    }
}
