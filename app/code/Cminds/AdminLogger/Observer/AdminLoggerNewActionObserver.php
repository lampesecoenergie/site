<?php

namespace Cminds\AdminLogger\Observer;

use Cminds\AdminLogger\Model\AdminLoggerFactory;
use Cminds\AdminLogger\Model\History\Factory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AdminLoggerNewActionObserver
 *
 * @package Cminds\AdminLogger\Observer
 */
class AdminLoggerNewActionObserver implements ObserverInterface
{
    /**
     * @var AdminLoggerFactory
     */
    private $adminLoggerFactory;

    /**
     * @var Factory
     */
    private $historyFactory;
    /**
     * AdminLoggerNewActionObserver constructor.
     *
     * @param AdminLoggerFactory $adminLoggerFactory
     * @param Factory            $historyFactory
     */
    public function __construct(
        AdminLoggerFactory $adminLoggerFactory,
        Factory $historyFactory
    ) {
        $this->adminLoggerFactory = $adminLoggerFactory;
        $this->historyFactory = $historyFactory;
    }

    /**
     * Main AdminLogger Observer initialized during new action dispatch.
     * For each Observer event.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $actionType = $event->getData('action_type');
        $action = $this->historyFactory->create($actionType);
        $actionData = $action->saveActionData($event);
        $actionHistory = $this->adminLoggerFactory->create();
        $actionHistory->setData($actionData);
        $actionHistory->save();
    }
}
