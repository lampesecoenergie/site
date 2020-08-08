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
 * Class Upload
 * @package Ced\Amazon\Controller\Adminhtml\Product
 */
class Upload extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Ced\Amazon\Helper\Product
     */
    public $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $products;

    /**
     * @var \Ced\Amazon\Helper\Config
     */
    public $config;

    /**
     * Upload constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Ced\Amazon\Helper\Product $product
     * @param \Ced\Amazon\Helper\Config $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Amazon\Helper\Product $product,
        \Ced\Amazon\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->products = $collectionFactory;
        $this->product = $product;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');
        $storeId = isset($filters['store_id']) ? $filters['store_id'] : 0;
        if (isset($filters)) {
            $collection = $this->filter->getCollection($this->products->create());
            $ids = $collection->getAllIds();
            $throttle = $this->config->getThrottleMode();
            $reasons = [];
            $message = 'Product(s) upload request processed successfully.';
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

            // TODO:  add loader and process in chunk
            $response = $this->product->update($ids, $throttle);

            // TODO: queue relation, image, price, inventory.
            /* Getting the relationship data and sending, if exists. */
            /*$relationEnvelopes = $this->amazon->getRelationships();
            if (isset($relationEnvelope) && !empty($relationEnvelope)) {
                $response = $this->amazon->send($relationEnvelope, \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP);
            }*/

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
                        'message' => 'Product Feed upload failed.',
                        'reasons' => [
                            'Profile must be disabled.',
                            'Account must be disabled.',
                            'Product must be invalid.'
                        ],
                        'support_url' => $this->getUrl('integrator/support/index')
                    ]
                );
            }
        } else {
            $this->messageManager->addComplexErrorMessage(
                'addAmazonError',
                [
                    'message' => 'Product Feed upload failed.',
                    'reasons' => ['No product(s) were selected.'],
                    'support_url' => $this->getUrl('integrator/support/index')
                ]
            );
        }

        return $this->_redirect('*/product/index', ['store' => $storeId]);
    }
}
