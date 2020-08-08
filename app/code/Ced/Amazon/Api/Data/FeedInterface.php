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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Data;

/**
 * Interface FeedInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface FeedInterface extends \Ced\Integrator\Api\Data\FeedInterface
{
    /**
     * Get Specifics
     * @return string
     */
    public function getSpecifics();

    /**
     * Set Specifics
     * @param string $specifics
     * @return $this
     */
    public function setSpecifics($specifics);

    /**
     * Get Feed Response xml as Processed Array
     * @return mixed
     */
    public function getResponse();

    /**
     * Get Executed Date
     * @return string
     */
    public function getExecutedDate();

    /**
     * Get Created Date
     * @return string
     */
    public function getCreatedDate();
}
