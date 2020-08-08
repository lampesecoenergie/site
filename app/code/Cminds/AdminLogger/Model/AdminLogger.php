<?php

namespace Cminds\AdminLogger\Model;

use Cminds\AdminLogger\Api\Data\AdminLoggerInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AdminLogger
 *
 * @package Cminds\AdminLogger\Model
 */
class AdminLogger extends AbstractModel implements IdentityInterface, AdminLoggerInterface
{
    /**
     * Cache tag.
     *
     * @const string
     */
    const CACHE_TAG = 'cminds_adminlogger_action_history';

    /**
     * AdminLogger Model initialization.
     */
    protected function _construct()
    {
        $this->_cacheTag = 'cminds_adminlogger_action_history';
        $this->_eventPrefix = 'cminds_adminlogger_action_history';

        $this->_init(\Cminds\AdminLogger\Model\ResourceModel\AdminLogger::class);
    }

    /**
     * Return unique ID(s) for each object in system.
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Admin Id.
     *
     * @return int
     */
    public function getAdminId()
    {
        return $this->getData(self::ADMIN_ID);
    }

    /**
     * Set Admin Id.
     *
     * @param string $adminId
     *
     * @return $this
     */
    public function setAdminId($adminId)
    {
        return $this->setData(self::ADMIN_ID, $adminId);
    }

    /**
     * Get Reference Value.
     *
     * @return string
     */
    public function getReferenceValue()
    {
        return $this->getData(self::REFERENCE_VALUE);
    }

    /**
     * Set Reference Value.
     *
     * @param string $referenceValue
     *
     * @return $this
     */
    public function setReferenceValue($referenceValue)
    {
        return $this->setData(self::REFERENCE_VALUE, $referenceValue);
    }

    /**
     * Get Ip Address.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->getData(self::IP);
    }

    /**
     * Set Ip Address.
     *
     * @param string $ipAddress
     *
     * @return $this
     */
    public function setIp($ipAddress)
    {
        return $this->setData(self::IP, $ipAddress);
    }

    /**
     * Get Browser Agent.
     *
     * @return string
     */
    public function getBrowserAgent()
    {
        return $this->getData(self::BROWSER_AGENT);
    }

    /**
     * Set Browser Agent.
     *
     * @param string $browserAgent
     *
     * @return $this
     */
    public function setBrowserAgent($browserAgent)
    {
        return $this->setData(self::BROWSER_AGENT, $browserAgent);
    }

    /**
     * Get Old Value.
     *
     * @return string
     */
    public function getOldValue()
    {
        return $this->getData(self::OLD_VALUE);
    }

    /**
     * Set Old Value.
     *
     * @param string $oldValue
     *
     * @return $this
     */
    public function setOldValue($oldValue)
    {
        return $this->setData(self::OLD_VALUE, $oldValue);
    }

    /**
     * Get New Value.
     *
     * @return string
     */
    public function getNewValue()
    {
        return $this->getData(self::NEW_VALUE);
    }

    /**
     * Set New Value.
     *
     * @param string $newValue
     *
     * @return $this
     */
    public function setNewValue($newValue)
    {
        return $this->setData(self::NEW_VALUE, $newValue);
    }

    /**
     * Get Created At.
     *
     * @return int|null
     */
    public function getCratedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set Created At.
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
