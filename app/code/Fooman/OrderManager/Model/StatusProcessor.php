<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\OrderManager\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DB\TransactionFactory;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use Fooman\OrderManager\Model\Source\EmailingOptions;

class StatusProcessor
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StateLookup
     */
    private $stateLookup;

    /**
     * @var OrderCommentSender
     */
    private $orderUpdateSender;

    /**
     * @var State
     */
    private $orderState;

    /**
     * @param ScopeConfigInterface     $scopeConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionFactory       $transactionFactory
     * @param LoggerInterface          $logger
     * @param StateLookup              $stateLookup
     * @param OrderCommentSender       $orderUpdateSender
     * @param State                    $state
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        TransactionFactory $transactionFactory,
        LoggerInterface $logger,
        StateLookup $stateLookup,
        OrderCommentSender $orderUpdateSender,
        State $state
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->stateLookup = $stateLookup;
        $this->orderUpdateSender = $orderUpdateSender;
        $this->orderState = $state;
    }

    /**
     * @param      $orderId
     * @param null $assignStatus
     * @param bool $sendEmails
     *
     * @throws LocalizedException
     */
    public function setStatus($orderId, $assignStatus = null, $sendEmails = false)
    {
        $order = $this->orderRepository->get($orderId);

        if (!$assignStatus) {
            $assignStatus = $this->scopeConfig->getValue('ordermanager/status/new_status');
        }

        if ($assignStatus) {
            $order->setStatus($assignStatus);
            $order->setState($this->stateLookup->getStateForStatus($assignStatus));

            $this->orderState->check($order);
            if ($order->getStatus() !== $assignStatus) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This order\'s state can\'t be changed.')
                );
            }

            $transactionSave = $this->transactionFactory->create()
                                                        ->addObject($order);
            $transactionSave->save();
        }

        $this->processEmails($order, $sendEmails);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool                                   $sendEmails
     */
    public function processEmails(\Magento\Sales\Api\Data\OrderInterface $order, $sendEmails = false)
    {
        if ($sendEmails ||
            $this->scopeConfig->getValue('ordermanager/status/email') == EmailingOptions::SEND_EMAIL_YES) {
            $this->sendOrderUpdateEmail($order);
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function sendOrderUpdateEmail(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        try {
            $this->orderUpdateSender->send($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

}