<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Config;

/**
 * Class CatalogAttributes
 */
class CatalogAttributes
{
    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Add custom attributes to product
     *
     * @param Config $subject
     * @param array $result
     * @return array
     */
    public function afterGetProductAttributes(Config $subject, $result)
    {
        $attributes = ['length', 'width', 'height'];
        foreach ($attributes as $attribute) {
            $config = $this->scopeConfig->getValue(
                'carriers/mondialrelay/limitation/' . $attribute . '_attribute'
            );
            if ($config) {
                $result[] = $config;
            }
        }

        return $result;
    }
}
