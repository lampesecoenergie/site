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



namespace Mirasvit\Feed\Export\Liquid\Tag;

use Mirasvit\Feed\Export\Liquid\Block;
use Mirasvit\Feed\Export\Liquid\Regexp;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class TagFor extends Block
{
    const DEFAULT_STEP = 50;

    /**
     * @var array The collection to loop over
     */
    private $collectionName;

    /**
     * @var string The variable name to assign collection elements to
     */
    private $variableName;

    /**
     * @var string The name of the loop, which is a compound of the collection and variable names
     */
    private $name;

    /**
     * @param string $markup
     * @param array &$tokens
     * @throws \Exception
     */
    public function __construct($markup, &$tokens)
    {
        parent::__construct($markup, $tokens);

        $syntaxRegexp = new Regexp('/(\w+)\s+in\s+(' . LIQUID_ALLOWED_VARIABLE_CHARS . '+)/');

        if ($syntaxRegexp->match($markup)) {
            $this->variableName = $syntaxRegexp->matches[1];
            $this->collectionName = $syntaxRegexp->matches[2];
            $this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
            $this->extractAttributes($markup);
        } else {
            throw new \Exception("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
        }
    }

    public function execute(&$context)
    {
        $result = '';

        if (!isset($context->registers['for'])) {
            $context->registers['for'] = [];
        }

        if ($this->length == 0) {
            $collection = $context->get($this->collectionName);
            if (is_object($collection)) {
                $this->length = $context->get($this->collectionName)->getSize();
            } elseif (is_array($collection)) {
                $this->length = count($collection);
            } else {
                return $collection;
            }
        }

        $length = $context->getProductExportStep() ?: self::DEFAULT_STEP;
        while ($this->index < $this->length) {
            $collection = $context->get($this->collectionName, [
                'index'  => $this->index,
                'length' => $length,
            ]);

            if (is_object($collection)) {
                $collection = $collection->getItems();
            }

            $context->push();

            if (!is_array($collection) || !count($collection)) {
                break;
            }

            foreach ($collection as $index => $item) {
                $context->set($this->variableName, $item);
                $context->set('forloop', [
                    'name'    => $this->name,
                    'length'  => $this->length,
                    'index'   => $this->index + 1,
                    'first'   => (int)($index == 0),
                    'last'    => (int)($index == $this->length - 1),
                    'notlast' => !($index == $this->length - 1),
                ]);

                if (isset($this->attributes['offset']) && $this->attributes['offset'] > $this->index) {
                    $this->index++;
                    continue;
                }

                $x = microtime(true);
                $result .= $this->executeAll($this->nodeList, $context);

                $this->index++;

                $this->resetNodes();

                if ($this->isMainCycle()) {
                    if ($context->isTimeout() || $this->index == $this->length) {
                        $context->isBreak = true;
                        break 2;
                    }
                }
            }

            $context->pop();
        }

        return $result;
    }

    public function reset()
    {
        $this->index = 0;
        $this->length = 0;
        $this->resetNodes();
    }

    protected function resetNodes()
    {
        foreach ($this->nodeList as $token) {
            $token->reset();
        }
    }

    public function getIndex()
    {
        $index = $this->index;

        return $index;
    }

    public function getLength()
    {
        $length = $this->length;

        return $length;
    }

    public function toArray()
    {
        $array = [
            'name'   => $this->name,
            'length' => $this->length,
            'index'  => $this->index,
        ];

        foreach ($this->nodeList as $token) {
            $array['nodeList'][] = $token->toArray();
        }

        return $array;
    }

    public function fromArray($array)
    {
        $this->length = $array['length'];
        $this->index = $array['index'];
    }

    /**
     * @return bool
     */
    protected function isMainCycle()
    {
        return strpos($this->name, 'context.products') !== false;
    }
}