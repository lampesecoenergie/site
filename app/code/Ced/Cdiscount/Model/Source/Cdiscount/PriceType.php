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
 * @package   Ced_m2.2.EE
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Model\Source\Cdiscount;

use Magento\Framework\Data\OptionSourceInterface;

class PriceType implements OptionSourceInterface
{
    public $currencymodel;

    public function __construct(
        \Magento\Directory\Model\Currency $currencyModel
    ) {
        $this->currencymodel = $currencyModel;
    }
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $data = [];
        foreach ($this->currencymodel->getConfigAllowCurrencies() as $allowCurrency) {
            $data[] = [
                'value' => $allowCurrency,
                'label' => $allowCurrency
            ];
        }
        return $data;
    }
}
