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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Product;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Feed
 * @package Ced\Amazon\Ui\Component\Listing\Columns\Product
 */
class Feed extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item[$fieldName])) {
                    $message = $item[$name];
                    $item[$name] = [];
                    $item[$name]['view'] = [
                        'popup' => [
                            'title' => __("Feed Result"),
                            'type' => 'json',
                            'render' => 'html',
                            'message' => $message
                        ],
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view'
                    ];
                }
            }
        }
        return $dataSource;
    }
}
