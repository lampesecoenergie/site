<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\OrderManager\Model\Source;

use Magento\Sales\Model\Config\Source\Order\Status;

class StatusOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Status
     */
    private $statusConfig;

    /**
     * @param Status $statusConfig
     */
    public function __construct(Status $statusConfig)
    {
        $this->statusConfig = $statusConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statusOptions = $this->statusConfig->toOptionArray();
        if (isset($statusOptions[0]['label']) && $statusOptions[0]['label']->getText() == __('-- Please Select --')) {
            $statusOptions[0]['label'] = __('Default');
        }
        return $statusOptions;
    }
}
