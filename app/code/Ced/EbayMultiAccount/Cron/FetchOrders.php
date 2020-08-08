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

namespace Ced\EbayMultiAccount\Cron;

class FetchOrders
{
    public $logger;

    public $orderHelper;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\EbayMultiAccount\Helper\Order $orderHelper
     */
    public function __construct(
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Ced\EbayMultiAccount\Helper\Order $orderHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
        $this->objectManager = $objectManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Ced\EbayMultiAccount\Helper\Order
     */
    public function execute()
    {
        $order = true;
        $scopeConfigManager = $this->objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $autoFetch = $scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_cron/order_cron');
        if ($autoFetch) {
            $acccounts = $this->multiAccountHelper->getAllAccounts(true);
            $acccountIds = $acccounts->getColumnValues('id');
            foreach ($acccountIds as $acccountId) {
                $order = $this->orderHelper->getNewOrders([$acccountId]);
                $this->logger->addError('In FetchOrder Cron: success', ['path' => __METHOD__, 'account_id' => $acccountId, 'Response' => $order]);
            }
        }
        $this->logger->addError('In FetchOrder Cron: Disable', ['path' => __METHOD__]);
        return $order;
    }
}
