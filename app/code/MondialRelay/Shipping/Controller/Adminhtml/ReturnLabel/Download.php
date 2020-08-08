<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\ReturnLabel;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Exception;

/**
 * Class Download
 */
class Download extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var OrderRepositoryInterface $orderRepositoryInterface
     */
    protected $orderRepositoryInterface;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var FileFactory $fileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param ShippingHelper $shippingHelper
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepositoryInterface,
        ShippingHelper $shippingHelper,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);

        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->shippingHelper           = $shippingHelper;
        $this->fileFactory              = $fileFactory;
    }
    /**
     * Download label
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $order = $this->orderRepositoryInterface->get($orderId);

            if ($order) {
                $file = $this->shippingHelper->getReturnLabelPath($order, true);

                if ($file) {
                    $data = [
                        'type'  => 'filename',
                        'value' => $file
                    ];
                    return $this->fileFactory->create(basename($file), $data, DirectoryList::MEDIA);
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
