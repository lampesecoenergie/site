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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Product\Status;

/**
 * Class Sync
 * @package Ced\Amazon\Controller\Adminhtml\Product\Status
 */
class Sync extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $products;

    /** @var \Ced\Amazon\Helper\Product\Sync */
    public $status;

    /**
     * @var \Ced\Amazon\Helper\Config
     */
    public $config;

    /**
     * Product constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Ced\Amazon\Helper\Product\Status $status
     * @param \Ced\Amazon\Helper\Config $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Product\Status $status,
        \Ced\Amazon\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->products = $collectionFactory;
        $this->status = $status;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $response = false;
        $storeId = 0;
        $filters = $this->getRequest()->getParam('filters');
        if (isset($filters)) {
            $storeId = isset($filters['store_id']) ? $filters['store_id'] : 0;
            $collection = $this->filter->getCollection($this->products->create());
            $ids = $collection->getAllIds();
            $message = 'Product(s) queued for status syncing successfully.';
            $throttle = $this->config->getThrottleMode();
            $response = $this->status->sync($ids, $throttle);
        }

        if ($response) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addComplexErrorMessage(
                'addAmazonError',
                [
                    'message' => 'Feed product status sync failed.',
                    'reasons' => [
                        'Profile(s) must be disabled.',
                        'Account(s) must be disabled.',
                        'Product(s) must be invalid.',
                        'Product(s) is not available or uploaded on the marketplace.'
                    ],
                    'support_url' => $this->getUrl('integrator/support/index')
                ]
            );
        }

        return $this->_redirect('*/product/index', ['store' => $storeId]);
    }
}
