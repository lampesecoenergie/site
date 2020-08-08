<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\OrderManager\Model;

use Magento\Shipping\Model\CarrierFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CarrierTitleLookup
{
    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CarrierFactory       $carrierFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CarrierFactory $carrierFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->carrierFactory = $carrierFactory;
    }

    public function getTitleFromCarrierCode($code)
    {
        if ($code === 'custom') {
            return $this->scopeConfig->getValue('ordermanager/settings/customtitle');
        }

        $carrier = $this->carrierFactory->create($code);
        if ($carrier) {
            return $carrier->getConfigData('title');
        }

        return '';
    }
}
