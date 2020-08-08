<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class Context
{
    /**
     * @var int
     */
    protected $productExportStep = 0;

    /**
     * Registers for non-variable state data
     *
     * @var array
     */
    public $registers;

    /**
     * The filterbank holds all the filters
     *
     * @var Filterbank
     */
    private $filterBank;

    /**
     * Global scopes
     *
     * @var array
     */
    public $environments = [];

    protected $timeoutCallback = null;

    protected $iterationCallback = null;

    /**
     * @var Template
     */
    protected $template;

    /**
     * @var bool
     */
    public $isBreak = false;

    /**
     * Constructor
     *
     * @param object $resolver
     * @param array  $registers
     */
    public function __construct($resolver, $assigns = [])
    {
        $this->assigns = [$assigns];
        $this->assigns[] = ['context' => $resolver];

        $this->resolver = $resolver;
        $this->filterBank = new Filterbank($this);
    }

    /**
     * @param int $step
     * @return $this
     */
    public function setProductExportStep($step)
    {
        $this->productExportStep = $step;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductExportStep()
    {
        return $this->productExportStep;
    }

    public function setTimeoutCallback($callback)
    {
        $this->timeoutCallback = $callback;

        return $this;
    }

    public function setIterationCallback($callback)
    {
        $this->iterationCallback = $callback;

        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Add a filter to the context
     *
     * @param mixed $filter
     */
    public function addFilters($filters)
    {
        foreach ($filters as $filter) {
            $this->filterBank->addFilter($filter);
        }
    }


    /**
     * Invoke the filter that matches given name
     *
     * @param string $name  The name of the filter
     * @param mixed  $value The value to filter
     * @param array  $args  Additional arguments for the filter
     * @return string
     */
    public function invoke($name, $value, $args = null)
    {
        return $this->filterBank->invoke($name, $value, $args);
    }


    /**
     * Merges the given assigns into the current assigns
     *
     * @param array $newAssigns
     */
    public function merge($newAssigns)
    {
        $this->assigns[0] = array_merge($this->assigns[0], $newAssigns);
    }


    /**
     * Push new local scope on the stack.
     *
     * @return bool
     */
    public function push()
    {
        array_unshift($this->assigns, []);
        return true;
    }


    /**
     * Pops the current scope from the stack.
     *
     * @return bool
     */
    public function pop()
    {
        if (count($this->assigns) == 1) {
            \Magento\Framework\Debug::backtrace();
            throw new \Exception('No elements to pop');
        }

        array_shift($this->assigns);
    }


    /**
     * Replaces []
     *
     * @param string
     * @return mixed
     */
    public function get($key, $args = [])
    {
        return $this->resolve($key, $args);
    }

    /**
     * Replaces []=
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $global
     */
    public function set($key, $value, $global = false)
    {
        if ($global) {
            for ($i = 0; $i < count($this->assigns); $i++) {
                $this->assigns[$i][$key] = $value;
            }
        } else {
            $this->assigns[0][$key] = $value;
        }
    }


    /**
     * Returns true if the given key will properly resolve
     *
     * @param string $key
     * @return bool
     */
    public function hasKey($key)
    {
        return (!is_null($this->resolve($key)));
    }


    /**
     * Resolve a key by either returning the appropriate literal or by looking up the appropriate variable
     *
     * Test for empty has been moved to interpret condition, in LiquidDecisionBlock
     *
     * @param string $key
     * @return mixed
     */
    public function resolve($key, $args = [])
    {
        // this shouldn't happen
        if (is_array($key)) {
            throw new \Exception("Cannot resolve arrays as key");
        }

        if (is_null($key) || $key == 'null') {
            return null;
        }

        if ($key == 'true') {
            return true;
        }

        if ($key == 'false') {
            return false;
        }

        if (preg_match('/^\'(.*)\'$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^"(.*)"$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(\d+)$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(\d[\d\.]+)$/', $key, $matches)) {
            return $matches[1];
        }

        return $this->variable($key, $args);
    }


    /**
     * Fetches the current key in all the scopes
     *
     * @param string $key
     * @return mixed
     */
    public function fetch($key)
    {
        foreach ($this->environments as $environment) {
            if (array_key_exists($key, $environment)) {
                return $environment[$key];
            }
        }

        foreach ($this->assigns as $scope) {
            if (array_key_exists($key, $scope)) {
                $obj = $scope[$key];

                return $obj;
            }
        }
    }


    /**
     * Resolved the namespaced queries gracefully.
     *
     * @param string $key
     * @return mixed
     */
    public function variable($key, $args = [])
    {
        /* Support [0] style array indices */
        if (preg_match("|\[[0-9]+\]|", $key)) {
            $key = preg_replace("|\[([0-9]+)\]|", ".$1", $key);
        }

        $parts = explode(LIQUID_VARIABLE_ATTRIBUTE_SEPARATOR, $key);

        $object = $this->fetch(array_shift($parts));

        if (!is_null($object)) {
            while (count($parts) > 0) {
                $nextPartName = array_shift($parts);

                if (is_array($object)) {
                    // if the last part of the context variable is .size we just return the count
                    if ($nextPartName == 'size' && count($parts) == 0 && !array_key_exists('size', $object)) {
                        return count($object);
                    }

                    if (array_key_exists($nextPartName, $object)) {
                        $object = $object[$nextPartName];
                    } else {
                        return null;
                    }
                } elseif (is_object($object)) {
                    $object = $this->resolver->resolve($object, $nextPartName, $args);
                }

                if (is_object($object) && method_exists($object, 'toLiquid')) {
                    $object = $object->toLiquid();
                }
            }

            return $object;
        } else {
            return null;
        }
    }

    public function isTimeout()
    {
        if ($this->iterationCallback) {
            $index = $this->template->getIndex();
            $length = $this->template->getLength();

            call_user_func($this->iterationCallback, ['index' => $index, 'length' => $length]);
        }

        if ($this->timeoutCallback) {
            return call_user_func($this->timeoutCallback);
        }

        return false;
    }
}
