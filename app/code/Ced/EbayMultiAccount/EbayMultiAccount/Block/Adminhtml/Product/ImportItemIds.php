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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Block\Adminhtml\Product;

use Magento\Backend\Block\Widget;

/**
 * Class Massupload
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Product
 */
class ImportItemIds extends \Magento\Backend\Block\Widget\Container
{
    /**
     * MassImport constructor.
     * @param Widget\Context $context
     * @param array $data
     */
    public function __construct(
        Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getAccountId()
    {
        return $this->_backendSession->getAccountId();
    }
}
