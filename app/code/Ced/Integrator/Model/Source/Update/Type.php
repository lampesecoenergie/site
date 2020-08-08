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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Model\Source\Update;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    const TYPE_PROMO = 'PROMO';
    const TYPE_NEW_RELEASE = 'NEW_RELEASE';
    const TYPE_UPDATE_RELEASE = 'UPDATE_RELEASE';
    const TYPE_INFO = 'INFO';
    const TYPE_INSTALLED_UPDATE = 'INSTALLED_UPDATE';

    public $objectManager;
    public $model;
    public $payment_data;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_INSTALLED_UPDATE, 'label' => __('Only Installed Extension(s) Updates')],
            ['value' => self::TYPE_UPDATE_RELEASE, 'label' => __('All Extensions Updates')],
            ['value' => self::TYPE_NEW_RELEASE, 'label' => __('New Releases')],
            ['value' => self::TYPE_PROMO, 'label' => __('Special Offers')],
            ['value' => self::TYPE_INFO, 'label' => __('Other Information')]
        ];
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = [];
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
