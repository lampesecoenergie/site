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
 * @category  Ced
 * @package   Ced_Integrator
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Ui\Component\Listing\Columns\Log;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Context
 */
class Context extends Column
{
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $response = $item[$name];
                $item[$name] = [];
                if (isset($item['message'])) {
                    $message = $item['id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("#{$item['id']} Log Context {$message}"),
                            'message' => $response,
                            'type' => 'json',
                            'render' => 'html',
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
