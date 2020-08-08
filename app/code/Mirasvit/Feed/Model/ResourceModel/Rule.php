<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rule extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_feed_rule', 'rule_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadFeedIds($object);

        return parent::_afterLoad($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveFeedIds($object);

        return parent::_afterSave($object);
    }

    /**
     * Load feed IDS by rule
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    public function loadFeedIds(AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_feed_rule_feed'), ['feed_id'])
            ->where('rule_id = :rule_id');

        $feedIds = $this->getConnection()->fetchCol($select, [':rule_id' => $object->getId()]);
        $object->setData('feed_ids', $feedIds);

        return $object;
    }

    /**
     * Save feed ids for rule
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function saveFeedIds(AbstractModel $object)
    {
        /** @var \Mirasvit\Feed\Model\Rule $object */
        $connection = $this->getConnection();
        $table = $this->getTable('mst_feed_rule_feed');

        $connection->delete($table, ['rule_id = ?' => $object->getId()]);

        foreach ($object->getFeedIds() as $feedId) {
            $insertArray = [
                'rule_id' => $object->getId(),
                'feed_id' => $feedId,
            ];
            $connection->insert($table, $insertArray);
        }

        return $this;
    }

    /**
     * Remove product-rule relations
     *
     * @param int $ruleId
     * @return $this
     */
    public function clearProductIds($ruleId)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable('mst_feed_rule_product'), ['rule_id = ?' => $ruleId]);

        return $this;
    }

    /**
     * Save product-rule relations
     *
     * @param int   $ruleId
     * @param array $productIds
     * @return $this
     */
    public function saveProductIds($ruleId, $productIds)
    {
        if (!count($productIds)) {
            return $this;
        }

        $connection = $this->getConnection();

        $data = [];
        foreach ($productIds as $productId) {
            $data[] = [
                'rule_id'    => $ruleId,
                'product_id' => $productId,
            ];
        }

        try {
            $connection->insertOnDuplicate($this->getTable('mst_feed_rule_product'), $data);
        } catch (\Exception $e) {
            
        }

        return $this;
    }

    /**
     * List of relations product-rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getRuleProductIds($ruleId)
    {
        $read = $this->getConnection();
        $select = $read->select()
            ->from($this->getTable('mst_feed_rule_product'), 'product_id')
            ->where('rule_id = ?', $ruleId);

        return $read->fetchCol($select);
    }
}
