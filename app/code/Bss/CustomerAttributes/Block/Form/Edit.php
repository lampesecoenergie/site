<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Block\Form;

use Magento\Framework\View\Element\Template;

class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Edit constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->productMetadata =$productMetadata;
        parent::__construct($context, $data);
    }

    /**
     * Reset Password
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getResetPassWordHtml()
    {
        $data = '';
        if (version_compare($this->productMetadata->getVersion(), '2.2.6', '>=') &&
            $this->moduleManager->isEnabled('Amazon_Payment')) {
            $amazonReset = $this->getLayout()->createBlock(\Amazon\Payment\Block\Widget\ResetPassword::class);
            if ($amazonReset->displayAmazonInfo()) {
                $data = $amazonReset->toHtml();
            }
        }
        return $data;
    }
}
