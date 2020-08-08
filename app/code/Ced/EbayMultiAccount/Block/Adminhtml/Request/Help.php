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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Request;

/**
 * Class Help
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Request
 */
class Help extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    public $_template = "request/help.phtml";

    /**
     * Help constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
    }
}