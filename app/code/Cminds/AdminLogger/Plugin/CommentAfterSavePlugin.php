<?php

namespace Cminds\AdminLogger\Plugin;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;

/**
 * Class CommentAfterSavePlugin
 *
 * @package Cminds\AdminLogger\Plugin
 */
class CommentAfterSavePlugin
{
    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * CommentAfterSavePlugin constructor.
     *
     * @param Manager      $eventManager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $eventManager,
        ModuleConfig $moduleConfig
    ) {
        $this->eventManager = $eventManager;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Catch the order comment.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param $result
     *
     * @return mixed
     */
    public function afterAddStatusHistoryComment(\Magento\Sales\Model\Order $order, $result)
    {
        if ($this->moduleConfig->isActive() === false) {
            return $result;
        }

        $this->eventManager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'order' => $order,
                'entity_type' => 'order',
                'action_type' => ModuleConfig::ACTION_ORDER_COMMENT_ADD
            ]
        );

        return $result;
    }
}
