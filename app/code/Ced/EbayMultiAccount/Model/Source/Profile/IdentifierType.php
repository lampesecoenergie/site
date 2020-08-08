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
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Model\Source\Profile;

use Magento\Framework\Data\OptionSourceInterface;

class IdentifierType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'ASIN', 'label' => __('ASIN')],
            ['value' => 'UPC', 'label' => __('UPC')],
            ['value' => 'EAN', 'label' => __('EAN')],
            ['value' => 'ISBN-10', 'label' => __('ISBN-10')],
            ['value' => 'ISBN-13', 'label' => __('ISBN-13')],
            ['value' => 'GTIN-14', 'label' => __('GTIN-14')]
        ];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

}