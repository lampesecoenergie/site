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

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class EmailingOptions implements \JsonSerializable, \Magento\Framework\Data\OptionSourceInterface
{

    const SEND_EMAIL_YES = 1;
    const SEND_EMAIL_NO = 0;
    const SEND_EMAIL_ASK = -1;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * EmailingOptions constructor.
     *
     * @param UrlInterface             $urlBuilder
     * @param ScopeConfigInterface     $scopeConfig
     * @param array                    $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->data = $data;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    public function toOptionArray()
    {
        $data = [
            [
                'value' => self::SEND_EMAIL_YES,
                'label' => __('Yes'),
            ],
            [
                'value' => self::SEND_EMAIL_NO,
                'label' => __('No'),
            ],
            [
                'value' => self::SEND_EMAIL_ASK,
                'label' => __('Ask'),
            ]
        ];
        return $data;
    }

    public function jsonSerialize()
    {
        if ($this->scopeConfig->getValue($this->data['configPath']) == self::SEND_EMAIL_ASK) {
            $actionConfig = [];

            $options = [
                ['label' => 'Send Email', 'value' => self::SEND_EMAIL_YES, '__disableTmpl' => true],
                ['label' => 'No Email', 'value' => self::SEND_EMAIL_NO, '__disableTmpl' => true],
            ];
            foreach ($options as $option) {
                $actionUrl = $this->urlBuilder->getUrl(
                    $this->data['urlPath'],
                    [$this->data['paramName'] => $option['value']]
                );
                $actionToAdd = [
                    'label' => $option['label'],
                    'type' => 'fooman_status' . $option['value'],
                    'url' => $actionUrl
                ];
                if (isset($this->data['callback'])) {
                    $actionToAdd['callback'] = $this->data['callback'];
                }

                $actionConfig[] = $actionToAdd;
            }
            return $actionConfig;
        }
    }
}
