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
class DecisionBlock extends Block
{

    /**
     * The current left variable to compare
     *
     * @var string
     */
    public $left;

    /**
     * The current right variable to compare
     *
     * @var string
     */
    public $right;


    /**
     * Returns a string value of an array for comparisons
     *
     * @param mixed $value
     * @return string
     * @throws \Exception
     */
    private function _stringValue($value)
    {
        // objects should have a to_string a value to compare to
        if (is_object($value)) {
            if (method_exists($value, 'to_string')) {
                $value = $value->to_string();
            }
            if (method_exists($value, 'getSize')) {
                $value = $value->getSize();
            } else {
                throw new \Exception("Cannot convert $value to string");// harry
            }

        }

        // arrays simply return true
        if (is_array($value)) {
            return $value;
        }

        return $value;
    }


    /**
     * Check to see if to variables are equal in a given context
     *
     * @param string                               $left
     * @param string                               $right
     * @param \Mirasvit\Feed\Export\Liquid\Context $context
     * @return bool
     */
    protected function _equalVariables($left, $right, &$context)
    {
        $left = $this->_stringValue($context->get($left));
        $right = $this->_stringValue($context->get($right));

        return ($left == $right);

    }


    /**
     * Interpret a comparison
     *
     * @param string  $left
     * @param string  $right
     * @param string  $op
     * @param Context $context
     * @return bool
     */
    protected function _interpretCondition($left, $right, $op = null, &$context)
    {
        if (is_null($op)) {
            $value = $this->_stringValue($context->get($left));

            return $value;
        }

        // values of 'empty' have a special meaning in array comparisons
        if ($right == 'empty' && is_array($context->get($left))) {
            $left = count($context->get($left));
            $right = 0;

        } elseif ($left == 'empty' && is_array($context->get($right))) {
            $right = count($context->get($right));
            $left = 0;
        } else {
            $left = $context->get($left);
            $right = $context->get($right);

            $left = $this->_stringValue($left);
            $right = $this->_stringValue($right);
        }

        // special rules for null values
        if (is_null($left) || is_null($right)) {
            // null == null returns true
            if ($op == '==' && is_null($left) && is_null($right)) {
                return true;
            }

            // null != anything other than null return true
            if ($op == '!=' && (!is_null($left) || !is_null($right))) {
                return true;
            }

            // everything else, return false;
            return false;
        }

        // regular rules
        switch ($op) {
            case '==':
                return ($left == $right);

            case '!=':
                return ($left != $right);

            case '>':
                return ($left > $right);

            case '<':
                return ($left < $right);

            case '>=':
                return ($left >= $right);

            case '<=':
                return ($left <= $right);

            case 'contains':
                return is_array($left) ? in_array($right, $left) : ($left == $right || strpos($left, $right));

            default:
                throw new \Exception("Error in tag '" . $this->name() . "' - Unknown operator $op");
        }
    }
}
