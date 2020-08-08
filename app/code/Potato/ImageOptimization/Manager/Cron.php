<?php

namespace Potato\ImageOptimization\Manager;

use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Logger\Logger;

class Cron
{
    const EXEC_OPTIMIZATION_COMMAND = 'po_image_optimization:optimize';
    const EXEC_SCAN_COMMAND = 'po_image_optimization:scan';
    const PATH_TO_MAGENTO_CLI = BP . '/bin/magento';

    /** @var Config  */
    protected $config;

    /** @var Logger  */
    protected $logger;

    /**
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        Logger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        $this->scan();
        $this->optimize();

        return $this;
    }

    private function scan()
    {
        exec(PHP_BINARY . ' -f ' . self::PATH_TO_MAGENTO_CLI . " " . self::EXEC_SCAN_COMMAND);
        return $this;
    }

    private function optimize()
    {
        exec(PHP_BINARY . ' -f ' . self::PATH_TO_MAGENTO_CLI . " " . self::EXEC_OPTIMIZATION_COMMAND);
        return $this;
    }
}
