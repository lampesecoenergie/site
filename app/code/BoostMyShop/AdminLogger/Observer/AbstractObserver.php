<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    protected $_logFactory;
    protected $_configFactory;


    public function __construct(
        \BoostMyShop\AdminLogger\Model\LogFactory $logFactory,
        \BoostMyShop\AdminLogger\Model\ConfigFactory $configFactory
    )
    {
        $this->_logFactory = $logFactory;
        $this->_configFactory = $configFactory;
    }

    protected function getConfig()
    {
        return $this->_configFactory->create();
    }

    public final function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->getConfig()->isEnabled())
            return $this;

        try
        {

            $this->_execute($observer);
        }
        catch(\Exception $ex)
        {

        }

        return $this;
    }

    protected abstract function _execute(\Magento\Framework\Event\Observer $observer);

}