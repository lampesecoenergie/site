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
class Text
{
    private $name;

    private $text;

    private $length = 1;

    private $index = 0;

    /**
     * Constructor
     *
     * @param string $markup
     */
    public function __construct($text)
    {
        $this->name = 'text';

        $this->text = $text;
    }

    /**
     * Gets the variable name
     *
     * @return string The name of the variable
     */
    public function getName()
    {
        return $this->name;
    }

    function execute($context)
    {
        if ($this->index == 0) {
            $this->index = 1;

            return $this->text;
        }
    }

    public function toArray()
    {
        return [
            'name'   => $this->name,
            'index'  => $this->index,
            'length' => $this->length
        ];
    }

    public function fromArray($array)
    {
        $this->index = $array['index'];
        $this->length = $array['length'];
    }

    public function reset()
    {
        $this->index = 0;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getLength()
    {
        return $this->length;
    }
}
