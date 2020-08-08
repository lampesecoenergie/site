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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Ui\Component\Listing\Columns\Column;

class FailureReason extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item[$name]) && !empty($item[$name]) && $item[$name] != "[]") {
                    $reasons = $item[$name];
                    $item[$name] = [];
                    $item[$name]['error'] = [
                        'label' => __('View Reasons'),
                        'class' => 'cedcommerce actions error',
                        'popup' => [
                            'title' => __("Amazon Order #{$item['amazon_order_id']}"),
                            'message' => $reasons,
                            'type' => 'json',
                            'render' => 'html',
                        ],
                    ];
                } else {
                    $label = __('Successfully imported');
                    $css = 'tick';
                    if ($item[$name] == '') {
                        $label = __('Not available for import');
                        $css = "tick-grey";
                    }

                    $item[$name] = [];
                    $item[$name]['error'] = [
                        'label' => $label,
                        'class' => "cedcommerce actions {$css} nochange",
                        'disable' => true
                    ];
                }
            }
        }

        return $dataSource;
    }
}
