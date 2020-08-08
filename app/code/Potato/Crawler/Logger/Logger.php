<?php
namespace Potato\Crawler\Logger;

use Potato\Crawler\Model\Config;

class Logger extends \Monolog\Logger
{
    /** @var Config  */
    protected $config;

    /**
     * Logger constructor.
     * @param Config $config
     * @param string             $name       The logging channel
     * @param array $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct(Config $config, $name, array $handlers = array(), array $processors = array())
    {
        parent::__construct($name, $handlers, $processors);
        $this->config = $config;
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function info($message, array $context = array())
    {
        if ($this->config->canDebug()) {
            return parent::info(vsprintf($message, $context));
        }
        return false;
    }

    /**
     * @param \Exception $e
     * @param array $context
     * @return mixed
     */
    public function customError($e, array $context = array())
    {
        parent::error($e->getMessage(), $context);
        return parent::error($e->getTraceAsString(), $context);
    }
}