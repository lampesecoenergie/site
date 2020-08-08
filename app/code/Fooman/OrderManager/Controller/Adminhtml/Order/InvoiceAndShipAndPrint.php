<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\OrderManager\Controller\Adminhtml\Order;

class InvoiceAndShipAndPrint extends InvoiceAndShip
{
    protected function getComponentRefererUrl()
    {
        return parent::getComponentRefererUrl()
            . '/printAction/printDocs/printIds/'
            . implode(',', $this->processedOrderIds);
    }

    /**
     * Invoice and ship and print selected orders
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $result = parent::execute();
        $this->messageManager->addNoticeMessage(__('Pdf will be created momentarily.'));
        return $result;
    }
}
