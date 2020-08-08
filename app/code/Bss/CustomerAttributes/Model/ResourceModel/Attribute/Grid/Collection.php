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
namespace Bss\CustomerAttributes\Model\ResourceModel\Attribute\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Customer attribute grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends SearchResult
{
    /**
     * @return SearchResult|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        //Join eav attribute table
        $this->getSelect()->joinLeft(
            ['eav_attribute' => $this->getTable('eav_attribute')],
            'eav_attribute.attribute_id = main_table.attribute_id'
        );

        $this->getSelect()
            ->where('eav_attribute.is_user_defined=1')
            ->where('eav_attribute.attribute_code != "b2b_activasion_status"');
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return 'main_table.attribute_id';
    }

    /**
     * @return $this|SearchResult
     */
    protected function _afterLoad()
    {
        parent::_beforeLoad();
        foreach ($this as $row) {
            $row->setData("main_table.attribute_id", $row->getAttributeId());
        }
        return $this;
    }
}
