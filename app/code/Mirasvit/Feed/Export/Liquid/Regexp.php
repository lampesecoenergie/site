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
class Regexp
{

    /**
     * The regexp pattern
     *
     * @var string
     */
    public $pattern;

    /**
     * The matches from the last method called
     *
     * @var array;
     */
    public $matches;


    /**
     * Constructor
     *
     * @param string $pattern
     * @return Regexp
     */
    public function __construct($pattern)
    {
        $this->pattern = (substr($pattern, '0', 1) != '/') ? '/' . $this->quote($pattern) . '/' : $pattern;
    }


    /**
     * Quotes regular expression characters
     *
     * @param string $string
     * @return string
     */
    function quote($string)
    {
        return preg_quote($string, '/');
    }


    /**
     * Returns an array of matches for the string in the same way as Ruby's scan method
     *
     * @param string $string
     * @return array
     */
    function scan($string)
    {
        $result = preg_match_all($this->pattern, $string, $matches);

        if (count($matches) == 1)
        {
            return $matches[0];
        }

        array_shift($matches);

        $result = array();

        foreach($matches as $matchKey => $subMatches)
        {
            foreach($subMatches as $subMatchKey => $subMatch)
            {
                $result[$subMatchKey][$matchKey] = $subMatch;
            }
        }

        return $result;
    }


    /**
     * Matches the given string. Only matches once.
     *
     * @param string $string
     * @return int 1 if there was a match, 0 if there wasn't
     */
    public function match($string)
    {
        return preg_match($this->pattern, $string, $this->matches);
    }


    /**
     * Matches the given string. Matches all.
     *
     * @param string $string
     * @return int The number of matches
     */
    function match_all($string)
    {
        return preg_match_all($this->pattern, $string, $this->matches);
    }


    /**
     * Splits the given string
     *
     * @param string $string
     * @param int $limit Limits the amount of results returned
     * @return array
     */
    function split($string, $limit = null)
    {
        return preg_split($this->pattern, $string, $limit);
    }
}
