<?php

namespace Cminds\AdminLogger\Model\ResourceModel\AdminLogger;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Cminds\AdminLogger\Model\ResourceModel\AdminLogger
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * AdminLogger collection initialization.
     *
     */
    protected function _construct()
    {
        $this->_init(
            \Cminds\AdminLogger\Model\AdminLogger::class,
            \Cminds\AdminLogger\Model\ResourceModel\AdminLogger::class
        );
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $collection = $this
            ->getSelect()
            ->joinLeft(
                ['admin_user' => $this->getTable('admin_user')],
                'main_table.admin_id = admin_user.user_id', // common column which available in both table
                ['username']  // select columns to fetch
            );

        return $collection;
    }

    /**
     * Set validation filter.
     *
     * @param null $now
     *
     * @return Collection
     */
    public function setValidationFilter($now = null)
    {
        if (!$this->getFlag('validation_filter')) {
            $this->addDateFilter($now);
            $this->setOrder('sort_order', self::SORT_ORDER_DESC);
            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * From date or to date filter.
     *
     * @param $now
     *
     * @return Collection
     */
    public function addDateFilter($now)
    {
        $this
            ->getSelect()
            ->where('from_date is null or from_date <= ?', $now)
            ->where('to_date is null or to_date >= ?', $now);

        return $this;
    }

    /**
     * Filter collection to only page view logs.
     *
     * @return Collection
     */
    public function addPageViewFilter()
    {
        $this
            ->getSelect()
            ->where('action_type = ?', ModuleConfig::ACTION_PAGE_VIEW);

        return $this;
    }

    /**
     * Filter collection to only login logs.
     *
     * @return Collection
     */
    public function addLoginLogsFilter()
    {
        $this
            ->getSelect()
            ->where('action_type = ?', ModuleConfig::ACTION_ADMIN_LOGIN_SUCCESS)
            ->orWhere('action_type = ?', ModuleConfig::ACTION_ADMIN_PASSWORD_CHANGE_REQUEST)
            ->orWhere('action_type = ?', ModuleConfig::ACTION_ADMIN_LOGIN_FAILED);

        return $this;
    }

    /**
     * Filter collection to only action logs (everything exclusive following types).
     *
     * @return Collection
     */
    public function addActionLogsFilter()
    {
        $this
            ->addFieldToFilter('action_type', ['neq' => ModuleConfig::ACTION_ADMIN_LOGIN_SUCCESS])
            ->addFieldToFilter('action_type', ['neq' => ModuleConfig::ACTION_ADMIN_PASSWORD_CHANGE_REQUEST])
            ->addFieldToFilter('action_type', ['neq' => ModuleConfig::ACTION_ADMIN_LOGIN_FAILED])
            ->addFieldToFilter('action_type', ['neq' => ModuleConfig::ACTION_PAGE_VIEW]);

        return $this;
    }

    /**
     * Group items by action_type field.
     *
     * @return Collection
     */
    public function groupByActionType()
    {
        $this->getSelect()->group('action_type');

        return $this;
    }
}
