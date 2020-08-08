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

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Framework\Module\Manager;

/**
 * Class ReturnLabelAction
 */
class ReturnLabelAction
{
    /**
     * @var Manager $moduleManager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(
        Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        if (!$context instanceof View) {
            return [$context, $buttonList];
        }

        if ($this->moduleManager->isEnabled('Magento_Rma')) {
            return [$context, $buttonList];
        }

        if (!$context->getAuthorization()->isAllowed('MondialRelay_Shipping::label')) {
            return [$context, $buttonList];
        }

        $order = $context->getOrder();

        if (!$order->hasShipments()) {
            return [$context, $buttonList];
        }

        $url = $context->getUrl(
            'mondialrelay_shipping/returnLabel',
            ['address_id' => $order->getBillingAddress()->getEntityId()]
        );

        $buttonList->add(
            'mondialrelay_return_label',
            [
                'label' => __('Mondial Relay Return'),
                'onclick' => 'setLocation("' . $url . '")',
                'class' => 'reset'
            ]
        );

        return [$context, $buttonList];
    }
}
