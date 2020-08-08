<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Model\Source\AccountConfig;

use Magento\Framework\Data\OptionSourceInterface;

class Configuration implements OptionSourceInterface
{
    /** @var \Ced\EbayMultiAccount\Model\ResourceModel\AccountConfig\CollectionFactory $accountConfigFactory */
    public $accountConfigFactory;

    public function __construct(
        \Ced\EbayMultiAccount\Model\ResourceModel\AccountConfig\CollectionFactory $accountConfigFactory
    )
    {
        $this->accountConfigFactory = $accountConfigFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray($locationID = null)
    {
        $accountConfigurations = $this->accountConfigFactory->create()->addFieldToFilter('account_location', array('eq' => $locationID));
        $options = [];
        foreach ($accountConfigurations->getData() as $config) {
            $options[$config['id']] = (string)$config['config_name'];
        }
        return $options;
    }

}