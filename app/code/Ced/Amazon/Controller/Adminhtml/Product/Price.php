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

namespace Ced\Amazon\Controller\Adminhtml\Product;

/**
 * Class Price
 * @package Ced\Amazon\Controller\Adminhtml\Product
 */
class Price extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $products;

    /** @var \Ced\Amazon\Helper\Product\Price  */
    public $price;

    /**
     * @var \Ced\Amazon\Helper\Config
     */
    public $config;

    /**
     * Price constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Ced\Amazon\Helper\Product\Price $price
     * @param \Ced\Amazon\Helper\Config $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Product\Price $price,
        \Ced\Amazon\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->products = $collectionFactory;
        $this->price = $price;
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
            $throttle = $this->config->getThrottleMode();
            $reasons = [];
            $message = 'Product(s) price request processed successfully.';
            if ($throttle) {
                $reasons[] = 'The feed is queued and will be processed later as availability of API.';
                $queueUrl = $this->getUrl('amazon/queue/index');
                $reasons[] =
                    "The queue records can be accessed from the <a href='{$queueUrl}' target='_blank'>grid</a>.";
            } else {
                $reasons[] = 'The feed is prepared and will be to sent Amazon.';
                $feedsUrl = $this->getUrl('amazon/feeds/index');
                $reasons[] =
                    "The feed records can be accessed from the <a href='{$feedsUrl}' target='_blank'>grid</a>.";
            }
            $reasons[] = 'The acceptance of feed depends on data criteria and policies of Amazon.';
            $response = $this->price->update($ids, $throttle);
        }

        if ($response) {
            $this->messageManager->addComplexSuccessMessage(
                'addAmazonSuccess',
                [
                    'message' => $message,
                    'reasons' => $reasons,
                    'support_url' => $this->getUrl('integrator/support/index')
                ]
            );
        } else {
            $this->messageManager->addComplexErrorMessage(
                'addAmazonError',
                [
                    'message' => 'Feed price upload failed.',
                    'reasons' => ['Profile must be disabled.', 'Account must be disabled.', 'Product must be invalid.'],
                    'support_url' => $this->getUrl('integrator/support/index')
                ]
            );
        }

        return $this->_redirect('*/product/index', ['store' => $storeId]);
    }
}