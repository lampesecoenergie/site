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
namespace Bss\CustomerAttributes\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity as Entity;

/**
 * Class UpgradeData
 *
 * @package Bss\CustomerAttributes\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * Entity
     *
     * @var Entity
     */
    private $entityModel;

    /**
     * @var \Bss\CustomerAttributes\Model\ResourceModel\Entity\Attribute
     */
    private $attributeModel;

    /**
     * UpgradeData constructor.
     * @param Config $eavConfig
     * @param \Bss\CustomerAttributes\Model\ResourceModel\Entity\Attribute $attributeModel
     * @param Entity $entityModel
     */
    public function __construct(
        Config $eavConfig,
        \Bss\CustomerAttributes\Model\ResourceModel\Entity\Attribute $attributeModel,
        Entity $entityModel
    ) {
        $this->attributeModel = $attributeModel;
        $this->eavConfig = $eavConfig;
        $this->entityModel = $entityModel;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $entityTypeId = $this->entityModel->setType(
            \Magento\Customer\Model\Customer::ENTITY
        )->getTypeId();

        foreach ($this->attributeModel->getAttributeCodes($setup, $entityTypeId) as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute('customer', $attributeCode);
            $usedInForms = $attribute->getUsedInForms();
            if (!in_array('is_customer_attribute', $usedInForms)) {
                array_push($usedInForms, 'is_customer_attribute');
                $attribute->setData('used_in_forms', $usedInForms);
                $this->saveAttribute($attribute);
            }
        }

        $setup->endSetup();
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     */
    private function saveAttribute($attribute)
    {
        return $attribute->save();
    }
}
