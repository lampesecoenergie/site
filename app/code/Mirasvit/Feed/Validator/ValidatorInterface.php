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



namespace Mirasvit\Feed\Validator;

interface ValidatorInterface
{
    /**
     * Get validator code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Get validator name.
     *
     * @return string
     */
    public function getName();

    /**
     * Validate given value according to concrete validator logic.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value);

    /**
     * Retrieve validator error message.
     *
     * @param bool $isHtml
     *
     * @return string
     */
    public function getMessage($isHtml = false);

    /**
     * Get validator hint to fix error.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function getHint($attribute = '');
}
