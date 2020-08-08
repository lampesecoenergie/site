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
class Document extends Block
{

    /**
     * Constructor
     *
     * @param array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->parse($tokens);

        return $this;
    }


    /**
     * Check for cached includes
     *
     * @return string
     */
    public function checkIncludes()
    {
        $return = false;
        foreach ($this->nodeList as $token) {
            if (is_object($token)) {
                if (get_class($token) == 'LiquidTagInclude' || get_class($token) == 'LiquidTagExtends') {
                    if ($token->checkIncludes() == true) {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }


    /**
     * There isn't a real delimiter
     *
     * @return string
     */
    public function blockDelimiter()
    {
        return '';
    }


    /**
     * Document blocks don't need to be terminated since they are not actually opened
     *
     * @return void
     */
    public function assertMissingDelimitation()
    {
    }
}
