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

namespace Ced\Amazon\Ui\Component\Listing\Columns\Queue;

use Magento\Ui\Component\Listing\Columns\Column;

class Specifics extends Column
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
                if (isset($item[$name])) {
                    $data = $item[$name];
                    $item[$name] = [];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'popup' => [
                            'title' => __("Specifics"),
                            'type' => 'json',
                            'render' => 'html',
                            'message' => $data
                        ],
                        'class' => 'cedcommerce actions view'
                    ];
                }
            }
        }

        return $dataSource;
    }
}
