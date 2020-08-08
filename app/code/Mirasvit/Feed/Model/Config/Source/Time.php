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



namespace Mirasvit\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Time implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];

        for ($i = 0; $i < 24; ++$i) {
            $hour = $i;
            $suffix = ' AM';
            if ($hour > 12) {
                $hour -= 12;
                $suffix = ' PM';
            }

            if ($hour < 10) {
                $hour = '0'.$hour;
            }

            $result[] = [
                'label' => $hour.':00'.$suffix,
                'value' => $i * 60,
            ];
            $result[] = [
                'label' => $hour.':30'.$suffix,
                'value' => $i * 60 + 30,
            ];
        }

        return $result;
    }
}
