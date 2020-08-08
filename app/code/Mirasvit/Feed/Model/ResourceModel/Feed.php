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
use Magento\Framework\Stdlib\DateTime;

class Feed extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_feed_feed', 'feed_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Mirasvit\Feed\Model\Feed $object */

        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        if (!$object->getIsMassStatus()) {
            if (is_array($object->getCronDay())) {
                $object->setCronDay(implode(',', $object->getCronDay()));
            }
            if (is_array($object->getCronTime())) {
                $object->setCronTime(implode(',', $object->getCronTime()));
            }
            if (is_array($object->getNotificationEvents())) {
                $object->setNotificationEvents(implode(',', $object->getNotificationEvents()));
            }
        }

        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        $this->saveRules($object);

        return parent::_afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadRuleIds($object);

        return parent::_afterLoad($object);
    }

    /**
     * Load rule ids by feed
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    public function loadRuleIds(AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_feed_rule_feed'))
            ->where('feed_id = ?', $object->getId());

        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['rule_id'];
            }
            $object->setData('rule_ids', $array);
        }

        return $object;
    }

    /**
     * Save rule ids
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    protected function saveRules(AbstractModel $object)
    {
        $table = $this->getTable('mst_feed_rule_feed');
        $condition = $this->getConnection()->quoteInto('feed_id = ?', $object->getId());

        $this->getConnection()->delete($table, $condition);

        foreach ((array)$object->getData('rule_ids') as $ruleId) {
            $insertArray = [
                'feed_id' => $object->getId(),
                'rule_id' => $ruleId,
            ];
            $this->getConnection()->insert($table, $insertArray);
        }

        return $object;
    }

    /**
     * Save product ids for feed
     *
     * @param AbstractModel $object
     * @param array         $productIds
     * @return $this
     * @todo We need this?
     */
    public function saveProductIds($object, $productIds)
    {
        $feedId = intval($object->getId());
        $this->getConnection()->delete($this->getTable('mst_feed_feed_product'), 'feed_id = ' . $feedId);

        $queryStart = 'INSERT INTO ' . $this->getTable('mst_feed_feed_product') . ' (
                feed_id, product_id) values ';
        $queryEnd = ' ON DUPLICATE KEY UPDATE product_id = VALUES(product_id)';

        $rows = [];
        foreach ($productIds as $productId) {
            $rows[] = "('" . implode("','", [$feedId, $productId]) . "')";

            if (sizeof($rows) == 1000) {
                $sql = $queryStart . implode(',', $rows) . $queryEnd;
                $this->getConnection()->query($sql);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            $sql = $queryStart . implode(',', $rows) . $queryEnd;
            $this->getConnection()->query($sql);
        }

        return $this;
    }
}
