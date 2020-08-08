<?php

namespace Cminds\AdminLogger\Api\Data;

/**
 * Interface AdminLoggerInterface
 *
 * @package Cminds\AdminLogger\Api\Data
 */
interface AdminLoggerInterface
{
    /**
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const ADMIN_ID = 'admin_id';
    const REFERENCE_VALUE = 'reference_value';
    const IP = 'ip';
    const BROWSER_AGENT = 'browser_agent';
    const OLD_VALUE = 'old_value';
    const NEW_VALUE = 'new_value';
    const CREATED_AT = 'created_at';

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get Admin Id.
     *
     * @return int
     */
    public function getAdminId();

    /**
     * Set Admin Id.
     *
     * @param int $adminId
     *
     * @return $this
     */
    public function setAdminId($adminId);

    /**
     * Get Reference Value.
     *
     * @return string
     */
    public function getReferenceValue();

    /**
     * Set Reference Value.
     *
     * @param string $referenceValue
     *
     * @return $this
     */
    public function setReferenceValue($referenceValue);

    /**
     * Get Ip Address.
     *
     * @return string
     */
    public function getIp();

    /**
     * Set Ip.
     *
     * @param string $ipAddress
     *
     * @return $this
     */
    public function setIp($ipAddress);

    /**
     * Get Browser Agent.
     *
     * @return string
     */
    public function getBrowserAgent();

    /**
     * Set Browser Agent.
     *
     * @param string $browserAgent
     *
     * @return $this
     */
    public function setBrowserAgent($browserAgent);

    /**
     * Get Old Value.
     *
     * @return string
     */
    public function getOldValue();

    /**
     * Set Old Value.
     *
     * @param string $oldValue
     *
     * @return $this
     */
    public function setOldValue($oldValue);

    /**
     * Get New Value.
     *
     * @return string
     */
    public function getNewValue();

    /**
     * Set New Value.
     *
     * @param string $newValue
     *
     * @return $this
     */
    public function setNewValue($newValue);

    /**
     * Get Created At Date.
     *
     * @return string
     */
    public function getCratedAt();

    /**
     * Set Created At.
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
