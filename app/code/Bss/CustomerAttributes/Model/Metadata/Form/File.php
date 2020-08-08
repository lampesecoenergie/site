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
namespace Bss\CustomerAttributes\Model\Metadata\Form;

/**
 * Class File
 *
 * @package Bss\CustomerAttributes\Model\Metadata\Form
 */
class File
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    protected $fileValidator;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * File constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $fileValidator
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerAttribute
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $fileValidator,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerAttribute,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->request = $request;
        $this->fileValidator = $fileValidator;
        $this->customerAttribute = $customerAttribute;
        $this->file = $file;
    }

    /**
     * @param \Magento\Eav\Model\Attribute\Data\File $subject
     * @param callable $proceed
     * @param array|string $value
     * @return array|bool|string[]
     */
    public function aroundValidateValue(
        $subject,
        callable $proceed,
        $value
    ) {
        $attribute = $subject->getAttribute();
        $attributeCollection = $this->customerAttribute->getUserDefinedAttributes();
        $customAttributeArr = [];
        foreach ($attributeCollection as $customAttribute) {
            $customAttributeArr[] = $customAttribute->getAttributeCode();
        }

        $attributeCode = $attribute->getAttributeCode();
        $type = $attribute->getFrontendInput();

        if ($type == 'file' && in_array($attributeCode, $customAttributeArr)) {
            $files = $this->request->getFiles();
            $fileData = $files[$attribute->getAttributeCode()];

            $errors = $this->validateAttribute($subject, $attribute, $fileData);
            if (count($errors) == 0) {
                return true;
            }
            return $errors;
        } else {
            return $proceed($value);
        }
    }

    /**
     * @param Attribute $attribute
     * @param array|string $fileData
     * @return array|string[]
     */
    public function validateAttribute($subject, $attribute, $fileData)
    {
        $page = $this->request->getFullActionName();
        $usedInForms = $attribute->getUsedInForms();
        $errors = [];
        $toDelete = $this->returnUpload($fileData);
        $toUpload = $this->returnUpload($fileData);
        if (in_array('is_customer_attribute', $usedInForms)) {
            if ($this->returnEmptyError($usedInForms, $page, $attribute)) {
                return $errors;
            }
            $checkError = $this->returnEmptyArray(
                $errors,
                $usedInForms,
                $page,
                $toUpload,
                $toDelete,
                $subject,
                $attribute
            );
            if (isset($checkError)) {
                return $checkError;
            }

            if ($attribute->getIsRequired() && !$toUpload) {
                $label = __($attribute->getStoreLabel());
                $errors[] = __('"%1" is a required value.', $label);
            }

            if ($toUpload) {
                $errors = array_merge($errors, $this->validateByRules($attribute, $fileData));
            }
        }

        return $errors;
    }

    /**
     * @param array $usedInForms
     * @param string $page
     * @param Attribute $attribute
     * @return bool
     */
    protected function returnEmptyError($usedInForms, $page, $attribute)
    {
        if (!$this->customerAttribute->getConfig('bss_customer_attribute/general/enable')) {
            return true;
        }
        if (!in_array('customer_account_create_frontend', $usedInForms)
            && $page == 'customer_account_createpost'
        ) {
            return true;
        }
        if ($attribute->getIsRequired() && $page = 'customerattribute_attribute_save') {
            return true;
        }
        return false;
    }

    /**
     * @param array $errors
     * @param array $usedInForms
     * @param string $page
     * @param bool $toUpload
     * @param bool $toDelete
     * @param \Magento\Eav\Model\Attribute\Data\File $subject
     * @param Attribute $attribute
     * @return array|null
     */
    protected function returnEmptyArray($errors, $usedInForms, $page, $toUpload, $toDelete, $subject, $attribute)
    {
        if (!in_array('customer_account_edit_frontend', $usedInForms)
            && $page == 'customer_account_editPost'
        ) {
            return $errors;
        }

        if (!$toUpload && !$toDelete && $subject->getEntity()->getData($attribute->getAttributeCode())) {
            return $errors;
        }

        if (!$attribute->getIsRequired() && !$toUpload) {
            return $errors;
        }
        return null;
    }

    /**
     * @param array $fileData
     * @return bool
     */
    protected function returnDelete($fileData)
    {
        if (!empty($fileData['delete'])) {
            return true;
        }
        return false;
    }

    /**
     * @param array $fileData
     * @return bool
     */
    protected function returnUpload($fileData)
    {
        if (!empty($fileData['tmp_name'])) {
            return true;
        }
        return false;
    }

    /**
     * Validate file by attribute validate rules
     * Return array of errors
     *
     * @param Attribute $attribute
     * @param array $fileData
     * @return array
     */
    protected function validateByRules($attribute, $fileData)
    {
        $label = $attribute->getStoreLabel();
        $rules = $attribute->getValidateRules();
        $pathInfo = $this->file->getPathInfo($fileData['name']);
        $extension = $pathInfo["extension"];

        if (!empty($rules['file_extensions'])) {
            $extensions = explode(',', $rules['file_extensions']);
            $extensions = array_map('trim', $extensions);
            if (!in_array($extension, $extensions)) {
                return [__('"%1" is not a valid file extension.', $label)];
            }
        }

        /**
         * Check protected file extension
         */
        if (!$this->fileValidator->isValid($extension)) {
            return $this->fileValidator->getMessages();
        }

        if (!empty($rules['max_file_size'])) {
            $size = $fileData['size'];
            if ($rules['max_file_size'] * 1000 < $size) {
                return [__('"%1" exceeds the allowed file size.', $label)];
            }
        }

        return [];
    }
}
