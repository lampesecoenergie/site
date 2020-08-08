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

class TagCapture extends \Mirasvit\Feed\Export\Liquid\Block
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $to;

    /**
     * @param string $markup
     * @param array  &$tokens
     * @throw \Exception
     */
    public function __construct($markup, &$tokens)
    {
        $syntaxRegexp = new \Mirasvit\Feed\Export\Liquid\Regexp('/(\w+)/');
        if ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens);
        } else {
            throw new \Exception("Syntax Error in 'capture' - Valid syntax: assign [var] = [source]"); // harry
        }
    }

    /**
     * Renders the block
     *
     * @param Context &$context
     * @return string
     */
    public function execute(&$context)
    {
        $context->push();
        $result = $this->executeAll($this->nodeList, $context);
        $context->pop();

        $context->set($this->to, $result);

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->resetNodes();
    }

    /**
     * {@inheritdoc}
     */
    protected function resetNodes()
    {
        foreach ($this->nodeList as $token) {
            $token->reset();
        }
    }
}