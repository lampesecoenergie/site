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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Processor;

interface ProcessorInterface
{
    /**
     * Run the task
     * @return boolean
     */
    public function execute();

    /**
     * Process queue items
     * @param \Ced\Amazon\Api\Data\QueueInterface[] $items
     * @throws \Exception
     */
    public function process(array $items);

    /**
     * Stage Next Action to execute in status.
     * @return bool
     * @throws \Exception
     */
    public function stageNext();

    /**
     * Get Results
     * @param boolean $json
     * @return string|array
     */
    public function getResult($json = true);

    /**
     * Set Results
     * @param array $result
     * @return void
     */
    public function setResult($result);

    /**
     * Add Value to Result
     * @param string $key
     * @param string|array|null $value
     * @return void
     */
    public function addResult($key, $value = null);

    /**
     * Set Job Type
     * @param $type
     * @return void
     */
    public function setType($type);

    /**
     * Get Job Type
     * @return string
     */
    public function getType();

    /**
     * Set Job Type Override
     * @param $type
     * @return void
     */
    public function setTypeOverride($type);

    /**
     * Get Job Type Override
     * @return string
     */
    public function getTypeOverride();
}
