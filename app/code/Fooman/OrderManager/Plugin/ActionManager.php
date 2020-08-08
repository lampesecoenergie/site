<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\OrderManager\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Helper\Data as BackendHelper;

class ActionManager
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var BackendHelper
     */
    private $backendHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        BackendHelper $backendHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->backendHelper = $backendHelper;
    }

    /**
     * @param \Magento\Ui\Component\Action $subject
     * @param \Closure                     $proceed
     */
    public function aroundPrepare(
        \Magento\Ui\Component\Action $subject,
        \Closure $proceed
    ) {
        $proceed();
        $config = $subject->getData('config');

        if ($config['type'] === 'fooman_invoice'
            && $this->scopeConfig->isSetFlag('ordermanager/invoice/pdf')) {
            $config['label'] = __('Invoice + Print');
            $config['url'] = $this->backendHelper->getUrl('ordermanager/order/invoiceAndPrint');
        }

        if ($config['type'] === 'fooman_ship'
            && $this->scopeConfig->isSetFlag('ordermanager/ship/pdf')) {
            $config['label'] = __('Ship + Print');
            $config['url'] = $this->backendHelper->getUrl('ordermanager/order/shipAndPrint');
        }

        if ($config['type'] === 'fooman_invoice_ship'
            && $this->scopeConfig->isSetFlag('ordermanager/invoiceAndShip/pdf')
        ) {
            $config['label'] = __('Invoice + Ship + Print');
            $config['url'] = $this->backendHelper->getUrl('ordermanager/order/invoiceAndShipAndPrint');
        }

        $subject->setData('config', $config);
    }
}
