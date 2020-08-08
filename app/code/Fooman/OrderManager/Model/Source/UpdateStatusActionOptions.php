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
use Magento\Framework\UrlInterface;

class UpdateStatusActionOptions implements \JsonSerializable, \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Status
     */
    private $statusConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var array
     */
    private $data;

    /**
     * UpdateStatusActionOptions constructor.
     *
     * @param Status       $statusConfig
     * @param UrlInterface $urlBuilder
     * @param array        $data
     */
    public function __construct(
        Status $statusConfig,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->statusConfig = $statusConfig;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->statusConfig->toOptionArray();
    }

    public function jsonSerialize()
    {
        $actionConfig = [];
        $statusOptions = $this->statusConfig->toOptionArray();

        //remove --Please Select -- entry
        array_shift($statusOptions);
        foreach ($statusOptions as $option) {
            $actionUrl = $this->urlBuilder->getUrl(
                $this->data['urlPath'],
                [$this->data['paramName'] => $option['value']]
            );
            $actionConfig[] = [
                'label' => $option['label'],
                'type' => 'fooman_status'.$option['value'],
                'url' => $actionUrl]
            ;
        }

        return $actionConfig;
    }
}
