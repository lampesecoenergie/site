<?php

namespace Cminds\AdminLogger\Model\History;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 *
 * @package Cminds\AdminLogger\Model\History
 */
class Factory
{
    /**
     * Invokable Classes array.
     *
     * @var array
     */
    private $invokableClasses = [
        ModuleConfig::ACTION_PRODUCT_CREATE => \Cminds\AdminLogger\Model\History\Product\Create::class,
        ModuleConfig::ACTION_PRODUCT_UPDATE => \Cminds\AdminLogger\Model\History\Product\Update::class,
        ModuleConfig::ACTION_PRODUCT_DELETE => \Cminds\AdminLogger\Model\History\Product\Delete::class,
        ModuleConfig::ACTION_CATEGORY_DELETE => \Cminds\AdminLogger\Model\History\Category\Delete::class,
        ModuleConfig::ACTION_CATEGORY_CREATE => \Cminds\AdminLogger\Model\History\Category\Create::class,
        ModuleConfig::ACTION_CATEGORY_UPDATE => \Cminds\AdminLogger\Model\History\Category\Update::class,
        ModuleConfig::ACTION_CUSTOMER_CREATE => \Cminds\AdminLogger\Model\History\Customer\Create::class,
        ModuleConfig::ACTION_CUSTOMER_UPDATE => \Cminds\AdminLogger\Model\History\Customer\Update::class,
        ModuleConfig::ACTION_CUSTOMER_DELETE => \Cminds\AdminLogger\Model\History\Customer\Delete::class,
        ModuleConfig::ACTION_CONTENT_PAGE_CREATE => \Cminds\AdminLogger\Model\History\Content\Page\Create::class,
        ModuleConfig::ACTION_CONTENT_PAGE_UPDATE => \Cminds\AdminLogger\Model\History\Content\Page\Update::class,
        ModuleConfig::ACTION_CONTENT_PAGE_DELETE => \Cminds\AdminLogger\Model\History\Content\Page\Delete::class,
        ModuleConfig::ACTION_CONTENT_BLOCK_CREATE => \Cminds\AdminLogger\Model\History\Content\Block\Create::class,
        ModuleConfig::ACTION_CONTENT_BLOCK_UPDATE => \Cminds\AdminLogger\Model\History\Content\Block\Update::class,
        ModuleConfig::ACTION_CONTENT_BLOCK_DELETE => \Cminds\AdminLogger\Model\History\Content\Block\Delete::class,
        ModuleConfig::ACTION_CONTENT_WIDGET_CREATE => \Cminds\AdminLogger\Model\History\Content\Widget\Create::class,
        ModuleConfig::ACTION_CONTENT_WIDGET_UPDATE => \Cminds\AdminLogger\Model\History\Content\Widget\Update::class,
        ModuleConfig::ACTION_CONTENT_WIDGET_DELETE => \Cminds\AdminLogger\Model\History\Content\Widget\Delete::class,
        ModuleConfig::ACTION_ADMIN_LOGIN_FAILED => \Cminds\AdminLogger\Model\History\Admin\Login\Fail::class,
        ModuleConfig::ACTION_ADMIN_LOGIN_SUCCESS => \Cminds\AdminLogger\Model\History\Admin\Login\Success::class,
        ModuleConfig::ACTION_ADMIN_PASSWORD_CHANGE_REQUEST => \Cminds\AdminLogger\Model\History\Admin\PasswordReset\Request::class,
        ModuleConfig::ACTION_PAGE_VIEW => \Cminds\AdminLogger\Model\History\Page\View::class,
        ModuleConfig::ACTION_CONFIGURATION_UPDATE => \Cminds\AdminLogger\Model\History\Configuration\Update::class,
        ModuleConfig::ACTION_ORDER_INVOICE_CREATE => \Cminds\AdminLogger\Model\History\Order\Invoice\Create::class,
        ModuleConfig::ACTION_ORDER_CREDITMEMO_CREATE => \Cminds\AdminLogger\Model\History\Order\Creditmemo\Create::class,
        ModuleConfig::ACTION_ORDER_SHIPMENT_CREATE => \Cminds\AdminLogger\Model\History\Order\Shipment\Create::class,
        ModuleConfig::ACTION_ORDER_SHIPPING_ADDRESS_UPDATE => \Cminds\AdminLogger\Model\History\Order\Address\Update::class,
        ModuleConfig::ACTION_ORDER_BILLING_ADDRESS_UPDATE => \Cminds\AdminLogger\Model\History\Order\Address\Update::class,
        ModuleConfig::ACTION_ORDER_STATUS_UPDATE => \Cminds\AdminLogger\Model\History\Order\Status\Update::class,
        ModuleConfig::ACTION_ORDER_COMMENT_ADD => \Cminds\AdminLogger\Model\History\Order\Comment\Create::class
    ];

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Factory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Factory class create method.
     * Create class depends on requested class.
     *
     * @param $action
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function create($action)
    {
        $className = !empty($this->invokableClasses[$action])
            ? $this->invokableClasses[$action]
            : null;

        if ($className === null) {
            throw new LocalizedException(
                __('%1 action is not supported.'),
                $className
            );
        }

        $model = $this->objectManager->create($className);
        if (!$model instanceof HistoryInterface) {
            throw new LocalizedException(
                __(
                    '%1 doesn\'t implement \Cminds\AdminLogger\Model\History\HistoryInterface',
                    $className
                )
            );
        }

        return $model;
    }
}
