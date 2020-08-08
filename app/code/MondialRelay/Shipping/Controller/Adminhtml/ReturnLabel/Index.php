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
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderAddressRepositoryInterface $orderAddressRepository
     */
    protected $orderAddressRepository;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param ShippingHelper $shippingHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        OrderAddressRepositoryInterface $orderAddressRepository,
        ShippingHelper $shippingHelper
    ) {
        parent::__construct($context);

        $this->resultPageFactory      = $resultPageFactory;
        $this->coreRegistry           = $registry;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->shippingHelper         = $shippingHelper;
    }

    /**
     * Edit Price
     *
     * @return Page|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/');

        $addressId = $this->getRequest()->getParam('address_id');

        if (!$addressId) {
            return $resultRedirect;
        }

        try {
            /** @var Address $address */
            $address = $this->orderAddressRepository->get($addressId);
        } catch (NoSuchEntityException $exception) {
            return $resultRedirect;
        }

        $order = $address->getOrder();

        if (!$order->hasShipments()) {
            return $resultRedirect;
        }

        $this->coreRegistry->register('address', $address);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Mondial Relay Return')));

        return $resultPage;
    }
}
