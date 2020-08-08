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
 * @package     Ced_EbayMultiAccount
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\EbayMultiAccount\Model;

class Profile extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Profile constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Catalog\Model\Product\ActionFactory $productActionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\Product\ActionFactory $productActionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productCollection = $productCollection;
        $this->productActionFactory = $productActionFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\EbayMultiAccount\Model\ResourceModel\Profile');
    }



    /**
     * Load entity by attribute
     *
     * @param string|array field
     * @param null|string|array $value
     * @param string $additionalAttributes
     * @return mixed
     */
    public function loadByField($field, $value, $additionalAttributes = '*')
    {
        $collection = $this->getResourceCollection()->addFieldToSelect($additionalAttributes);
        if(is_array($field) && is_array($value)){
            foreach($field as $key=>$f) {
                if(isset($value[$key])) {
                    //$f = $helper->getTableKey($f);
                    $collection->addFieldToFilter($f, $value[$key]);
                }
            }
        } else {
            $collection->addFieldToFilter($field, $value);
        }

        $collection->setCurPage(1)
            ->setPageSize(1);
        foreach ($collection as $object) {
            $this->load($object->getId());
            return $this;
        }
        return $this;
    }

    /**
     * @param $profileProducts
     */
    public function updateProducts($profileProducts, $profileAttr = null)
    {
        $profileAttr = ($profileAttr != null) ? $profileAttr : 'ebaymultiaccount_profile_id';
        if ($id = $this->getId()) {
            $oldIds = $this->productCollection->create()
                    ->addAttributeToFilter($profileAttr, ['eq' => $id])
                    ->getAllIds();
            $newIds = array_diff($profileProducts, $oldIds);
            $toBeRemoveIds = array_diff($oldIds, $profileProducts);
            if (!empty($newIds)) {
                $this->productActionFactory->create()
                    ->updateAttributes($newIds, [$profileAttr => $id], 0);
            }

            if (!empty($toBeRemoveIds)) {
                $this->productActionFactory->create()
                    ->updateAttributes($toBeRemoveIds, [$profileAttr => 0], 0);
            }
        }
    }
}