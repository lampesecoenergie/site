<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Overstock
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Helper;

class Logger implements \Psr\Log\LoggerInterface
{
    /**
     * Detailed debug information
     */
    const DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 200;

    /**
     * Uncommon events
     */
    const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;

    /**
     * Runtime errors
     */
    const ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 550;

    /**
     * Urgent alert.
     */
    const EMERGENCY = 600;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    public static $levels = [
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    ];

    /**
     * @var \DateTimeZone
     */
    public static $timezone;

    /**
     * @var string
     */
    public $name;

    /**
     * The handler Model
     * @var \Ced\Integrator\Model\LogFactory
     */
    public $handler;

    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
    public $processors;

    public $mutelevel = 100;

    public function __construct(
        \Ced\Integrator\Model\LogFactory $log,
        $name = 'INTEGRATOR'
    ) {
        $this->name = $name;
        $this->handler = $log;
    }

    public function setMuteLevel($level)
    {
        $this->mutelevel = $level;
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels()
    {
        return array_flip(static::$levels);
    }

    /**
     * Set the timezone to be used for the timestamp of log records.
     *
     * This is stored globally for all Logger instances
     *
     * @param \DateTimeZone $tz Timezone object
     */
    public static function setTimezone(\DateTimeZone $tz)
    {
        self::$timezone = $tz;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set handler, replacing all existing ones.
     *
     * @param $handler
     * @return $this
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addDebug($message, array $context = [])
    {
        return $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record.
     *
     * @param integer $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [])
    {
        try {
            if ($level > $this->mutelevel) {
                $levelName = static::getLevelName($level);

                if (!static::$timezone) {
                    static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
                }
                $record = [
                    'message' => (string)$message,
                    'context' => json_encode($context),
                    'level' => $level,
                    'level_name' => $levelName,
                    'channel' => $this->name,
                    'datetime' => \DateTime::createFromFormat(
                        'U.u',
                        sprintf(
                            '%.6F',
                            microtime(true)
                        ),
                        static::$timezone
                    )->setTimezone(static::$timezone),
                    'extra' => [],
                ];

                $this->handler->create()->addData($record)->save();
            }
        } catch (\Exception $e) {
            //Silence
        }

        return true;
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  integer $level
     * @return string
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$levels[$level])) {
            throw new \Psr\Log\InvalidArgumentException(
                'Level "' . $level . '" is not defined, use one of: ' .
                implode(', ', array_keys(static::$levels))
            );
        }

        return static::$levels[$level];
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addInfo($message, array $context = [])
    {
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addNotice($message, array $context = [])
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addWarning($message, array $context = [])
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addError($message, array $context = [])
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addCritical($message, array $context = [])
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addAlert($message, array $context = [])
    {
        return $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addEmergency($message, array $context = [])
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed $level The log level
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function log($level, $message, array $context = [])
    {
        $level = static::toMonologLevel($level);

        return $this->addRecord($level, $message, $context);
    }

    /**
     * Converts PSR-3 levels to Monolog ones if necessary
     *
     * @param string|int Level number (monolog) or name (PSR-3)
     * @return int
     */
    public static function toMonologLevel($level)
    {
        if (is_string($level) && defined(__CLASS__ . '::' . strtoupper($level))) {
            return constant(__CLASS__ . '::' . strtoupper($level));
        }

        return $level;
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function debug($message, array $context = [])
    {
        return $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function info($message, array $context = [])
    {
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function notice($message, array $context = [])
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function warn($message, array $context = [])
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function warning($message, array $context = [])
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function err($message, array $context = [])
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function error($message, array $context = [])
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function crit($message, array $context = [])
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function critical($message, array $context = [])
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function alert($message, array $context = [])
    {
        return $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function emerg($message, array $context = [])
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function emergency($message, array $context = [])
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }
}
