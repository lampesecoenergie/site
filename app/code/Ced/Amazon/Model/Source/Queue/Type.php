<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Queue;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 * @package Ced\Amazon\Model\Source
 */
class Type extends AbstractSource
{
    /** @var \Ced\Amazon\Model\Source\Feed\Type */
    public $feed;

    /** @var \Ced\Amazon\Model\Source\Report\Type */
    public $report;

    /**
     * Type constructor.
     * @param \Ced\Amazon\Model\Source\Report\Type $report
     * @param \Ced\Amazon\Model\Source\Feed\Type $feed
     */
    public function __construct(
        \Ced\Amazon\Model\Source\Report\Type $report,
        \Ced\Amazon\Model\Source\Feed\Type $feed
    ) {
        $this->report = $report;
        $this->feed = $feed;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $types = [];
        $options = $this->report->getAllOptions();
        $types = array_merge($types, $options);

        $options = $this->feed->getAllOptions();
        $types = array_merge($types, $options);

        return $types;
    }
}
