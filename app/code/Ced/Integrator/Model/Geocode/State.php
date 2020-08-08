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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Model\Geocode;

use Magento\Framework\DataObject;
use Ced\Integrator\Api\Data\Geocode\StateInterface;

class State extends DataObject implements StateInterface
{
    /**
     * Get Long Name
     * @return string
     */
    public function getLongName()
    {
        return $this->getData(self::LONG_NAME);
    }

    /**
     * Get Short Name
     * @return string
     */
    public function getShortName()
    {
        return $this->getData(self::SHORT_NAME);
    }

    /**
     * Get Type List as Array
     * @return mixed
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }
}
