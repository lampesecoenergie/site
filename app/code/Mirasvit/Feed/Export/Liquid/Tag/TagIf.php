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

use Mirasvit\Feed\Export\Liquid\Regexp;
use Mirasvit\Feed\Export\Liquid\Context;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class TagIf extends \Mirasvit\Feed\Export\Liquid\DecisionBlock
{

    /**
     * Array holding the nodes to render for each logical block
     *
     * @var array
     */
    private $_nodelistHolders = [];

    /**
     * Array holding the block type, block markup (conditions) and block nodelist
     *
     * @var array
     */
    private $_blocks = [];


    /**
     * Constructor
     *
     * @param string $markup
     * @param array  $tokens
     * @return $this
     */
    public function __construct($markup, &$tokens)
    {
        $this->nodeList = &$this->_nodelistHolders[count($this->_blocks)];

        array_push($this->_blocks, [
            'if', $markup, &$this->nodeList
        ]);

        parent::__construct($markup, $tokens);
    }


    /**
     * Handler for unknown tags, handle else tags
     *
     * @param string $tag
     * @param array  $params
     * @param array  $tokens
     */
    public function unknownTag($tag, $params, &$tokens)
    {
        if ($tag == 'else' || $tag == 'elsif') {
            /* Update reference to nodelistHolder for this block */
            $this->nodeList = &$this->_nodelistHolders[count($this->_blocks) + 1];
            $this->_nodelistHolders[count($this->_blocks) + 1] = [];

            array_push($this->_blocks, [
                $tag, $params, &$this->nodeList
            ]);

        } else {
            parent::unknownTag($tag, $params, $tokens);
        }
    }


    /**
     * Render the tag
     *
     * @param Context $context
     */
    public function execute(&$context)
    {
        $context->push();

        $logicalRegex = new Regexp('/\s+(and|or)\s+/');
        $conditionalRegex = new Regexp('/(' . LIQUID_QUOTED_FRAGMENT . ')\s*([=!<>a-z_]+)?\s*(' . LIQUID_QUOTED_FRAGMENT . ')?/');

        $result = '';

        foreach ($this->_blocks as $i => $block) {
            if ($block[0] == 'else') {
                $result = $this->executeAll($block[2], $context);

                break;
            }

            if ($block[0] == 'if' || $block[0] == 'elsif') {
                /* Extract logical operators */
                $logicalRegex->match($block[1]);

                $logicalOperators = $logicalRegex->matches;
                array_shift($logicalOperators);

                /* Extract individual conditions */
                $temp = $logicalRegex->split($block[1]);

                $conditions = [];

                foreach ($temp as $condition) {
                    if ($conditionalRegex->match($condition)) {
                        $left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
                        $operator = (isset($conditionalRegex->matches[2])) ? $conditionalRegex->matches[2] : null;
                        $right = (isset($conditionalRegex->matches[3])) ? $conditionalRegex->matches[3] : null;

                        array_push($conditions, [
                            'left'     => $left,
                            'operator' => $operator,
                            'right'    => $right
                        ]);
                    } else {
                        throw new \Exception("Syntax Error in tag 'if' - Valid syntax: if [condition]");
                    }
                }


                if (count($logicalOperators)) {
                    /* If statement contains and/or */
                    $display = true;

                    foreach ($logicalOperators as $k => $logicalOperator) {
                        if ($logicalOperator == 'and') {
                            $display = $this->_interpretCondition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context) && $this->_interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context);
                        } else {
                            $display = $this->_interpretCondition($conditions[$k]['left'], $conditions[$k]['right'], $conditions[$k]['operator'], $context) || $this->_interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context);
                        }
                    }

                } else {
                    /* If statement is a single condition */
                    $display = $this->_interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
                }

                if ($display) {
                    foreach ($block[2] as $token) {
                        $token->reset();
                    }
                    $result = $this->executeAll($block[2], $context);

                    break;
                }
            }
        }

        $context->pop();

        return $result;
    }

    public function reset()
    {
        $this->index = 0;
    }
}
