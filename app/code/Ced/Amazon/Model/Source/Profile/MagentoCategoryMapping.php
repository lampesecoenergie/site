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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Profile;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * @deprecated
 * Class MagentoCategoryMapping
 * @package Ced\Amazon\Model\Source\Profile
 */
class MagentoCategoryMapping implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    public $category;

    /**
     * MagentoCategoryMapping constructor.
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->category = $collectionFactory;
    }

    public function toOptionArray()
    {
        $categoryFactory = $this->category;
        $categories = $categoryFactory->create()->addAttributeToSelect('entity_id')->addAttributeToSelect('name')
            ->setStore($this->_storeManager->getStore());
        $options = [
            [
                'label' => 'Please select a magento category.',
                'value' => '',
            ]
        ];
        foreach ($categories as $category) {
            if ($category->getLevel() == 2) {
                $option['label'] = $category->getName() . " [{$category->getEntityId()}]";
                $option['value'] = $category->getEntityId();
                $options[] = $option;
            }
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }
}
