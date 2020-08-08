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
namespace Bss\CustomerAttributes\Model\ResourceModel\Entity;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * EAV attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Entity\Attribute
{
    /**
     * Update attribute default value
     *
     * @param EntityAttribute|AbstractModel $object
     * @param int|string $optionId
     * @param int $intOptionId
     * @param array $defaultValue
     * @return void
     */
    protected function _updateDefaultValue($object, $optionId, $intOptionId, &$defaultValue)
    {
        if (in_array($optionId, $object->getDefault())) {
            $frontendInput = $object->getFrontendInput();
            if ($frontendInput === 'multiselect' || $frontendInput === 'checkboxs') {
                $defaultValue[] = $intOptionId;
            } elseif ($frontendInput === 'select' || $frontendInput === 'radio') {
                $defaultValue = [$intOptionId];
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param int $entityTypeId
     * @return mixed
     */
    public function getAttributeCodes($setup, $entityTypeId)
    {
        $select = $setup->getConnection()->select()->from(
            ['ea' => $setup->getTable('eav_attribute')],
            ['attribute_code']
        )->where('ea.entity_type_id=? and ea.is_user_defined=1', $entityTypeId);
        return $setup->getConnection()->fetchCol($select);
    }
}
