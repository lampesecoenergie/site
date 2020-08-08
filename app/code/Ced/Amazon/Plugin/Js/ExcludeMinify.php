<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Plugin\Js;

use Magento\Framework\View\Asset\Minification;

class ExcludeMinify
{
    /**
     * For < 2.2
     * @param \Magento\Framework\View\Asset\Minification $subject
     * @param callable $proceed
     * @param $contentType
     * @return array
     */
    /*public function aroundGetExcludes(
        \Magento\Framework\View\Asset\Minification $subject,
        callable $proceed,
        $contentType
    ) {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }

        $result[] = 'Ced_Amazon/js/vkbeautify.0.99.00.beta';
        return $result;
    }*/

    /**
     * For > 2.2
     * @param Minification $subject
     * @param array $result
     * @param $contentType
     * @return array
     */
    public function afterGetExcludes(Minification $subject, array $result, $contentType)
    {
        if ($contentType == 'js') {
            $result[] = 'Ced_Amazon/js/vkbeautify.0.99.00.beta';
        }

        return $result;
    }
}
