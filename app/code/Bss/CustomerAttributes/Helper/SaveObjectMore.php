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

use Magento\Framework\Controller\ResultFactory;

/**
 * Class SaveObject
 *
 * @package Bss\CustomerAttributes\Helper
 */
class SaveObjectMore
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $customerEntityFactory;
    /**
     * @var Data
     */
    protected $customerAttributeHelper;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $attributeSet;
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * SaveObjectMore constructor.
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param Data $customerAttHelper
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attributeSet
     * @param \Magento\Eav\Model\EntityFactory $EntityFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Bss\CustomerAttributes\Helper\Data $customerAttHelper,
        \Magento\Eav\Model\Entity\Attribute\Set $attributeSet,
        \Magento\Eav\Model\EntityFactory $EntityFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->customerAttributeHelper = $customerAttHelper;
        $this->validatorFactory = $validatorFactory;
        $this->attributeSet = $attributeSet;
        $this->customerEntityFactory = $EntityFactory;
        $this->customerFactory = $customerFactory;
        $this->fileSystem = $filesystem;
    }

    /**
     * @return string
     */
    public function returnDirectMedia()
    {
        return \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
    }

    /**
     * @return \Magento\Framework\Filesystem
     */
    public function returnFileSystem()
    {
        return $this->fileSystem;
    }

    /**
     * @return \Magento\Customer\Model\CustomerFactory
     */
    public function returnCustomerFactory()
    {
        return $this->customerFactory;
    }

    /**
     * @return \Magento\Eav\Model\EntityFactory
     */
    public function returnEntityFactory()
    {
        return $this->customerEntityFactory;
    }
    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->customerAttributeHelper;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     */
    public function returnAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * @return \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    public function returnValidationFactory()
    {
        return $this->validatorFactory;
    }
}
