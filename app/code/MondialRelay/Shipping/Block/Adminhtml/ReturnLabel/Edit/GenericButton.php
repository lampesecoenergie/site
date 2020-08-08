<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\ReturnLabel\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * @var Context $context
     */
    protected $context;

    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context      = $context;
        $this->coreRegistry = $registry;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * Retrieve Order Id
     *
     * @return int
     */
    public function getOrderId()
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $this->coreRegistry->registry('address');

        return $address->getParentId();
    }

    /**
     * Retrieve Address Id
     *
     * @return int
     */
    public function getAddressId()
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $this->coreRegistry->registry('address');

        return $address->getId();
    }
}
