<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\Test;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Soap;
use Magento\Backend\App\Action;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Backend::system';

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Soap $soap
     */
    protected $soap;

    /**
     * @param Action\Context $context
     * @param ShippingHelper $shippingHelper
     * @param Soap $soap
     */
    public function __construct(
        Action\Context $context,
        ShippingHelper $shippingHelper,
        Soap $soap
    ) {
        parent::__construct($context);

        $this->shippingHelper = $shippingHelper;
        $this->soap           = $soap;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');

        $data = [
            'Pays' => 'FR',
            'CP'   => '75008',
        ];

        $response = $this->soap
            ->setStoreId($store)
            ->setWebsiteId($website)
            ->execute('WSI3_PointRelais_Recherche', $data);

        if ($response['error']) {
            $this->messageManager->addErrorMessage($response['error']);
        } else {
            $this->messageManager->addSuccessMessage(__('The connection is working fine'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
