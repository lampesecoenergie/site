<?php
namespace Potato\Crawler\Logger;

use Monolog\Logger as PotatoLogger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = PotatoLogger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/po_crawler.log';
}