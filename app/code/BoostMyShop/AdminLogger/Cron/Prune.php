<?php

namespace BoostMyShop\AdminLogger\Cron;


class Prune
{

    protected $_config;
    protected $_logFactory;

    /**
     * @param \Magento\Indexer\Model\Processor $processor
     */
    public function __construct(
        \BoostMyShop\AdminLogger\Model\ConfigFactory $config,
        \BoostMyShop\AdminLogger\Model\ResourceModel\LogFactory $logFactory
    ) {
        $this->_config = $config;
        $this->_logFactory = $logFactory;
    }

    /**
     * Regenerate indexes for all invalid indexers
     *
     * @return void
     */
    public function execute()
    {
        $delay = $this->_config->create()->getPruneDays();
        $this->_logFactory->create()->prune($delay);
    }
}
