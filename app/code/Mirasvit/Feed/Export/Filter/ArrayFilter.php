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


namespace Mirasvit\Feed\Export\Filter;

use Mirasvit\Feed\Export\Resolver\GeneralResolver;

class ArrayFilter
{
    /**
     * Constructor
     *
     * @param GeneralResolver $resolver
     */
    public function __construct(
        GeneralResolver $resolver
    ) {
        $this->resolver = $resolver;
    }

    /**
     * First
     *
     * Return first element in array.
     *
     * @param [] $input
     * @return string
     */
    public function first($input)
    {
        return is_array($input) ? reset($input) : $input;
    }

    /**
     * Last
     *
     * Return last element in array.
     *
     * @param [] $input
     * @return string
     */
    public function last($input)
    {
        return is_array($input) ? end($input) : $input;
    }

    /**
     * Join
     *
     * Join array to string using glue.
     *
     * @param []     $input
     * @param string $glue
     * @return string
     */
    public function join($input, $glue = ', ')
    {
        $input = $this->toPlain($input);

        return is_array($input) ? implode($glue, $input) : $input;
    }

    /**
     * Count
     *
     * @param [] $input
     * @return integer
     */
    public function count($input)
    {
        return is_array($input) ? count($input) : 0;
    }

    /**
     * Select
     *
     * Select values for key from array
     *
     * @param []     $input
     * @param string $key
     *
     * @return \Traversable
     */
    public function select($input, $key = '')
    {
        if (is_array($input) || $input instanceof \Traversable) {
            $result = [];
            foreach ($input as $item) {
                $result[] = $this->resolver->resolve($item, $key);
            }

            return $result;
        }

        return $input;
    }

    /**
     * Convert array of object to array of scalars
     *
     * @param array $input
     * @return array
     */
    protected function toPlain($input)
    {
        $result = [];
        if (!is_array($input)) {
            $input = [];
        }

        $input = array_values($input);

        if (count($input)) {
            $first = $input[0];

            if (is_object($first)) {
                foreach ($input as $item) {
                    $result[] = $this->resolver->resolve($item, false);
                }
            } else {
                $result = $input;
            }
        }

        return $result;
    }
}
