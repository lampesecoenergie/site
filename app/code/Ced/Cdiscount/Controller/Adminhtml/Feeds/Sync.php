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
 * @package   Ced_m2.2.EE
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Feeds;

class Sync extends \Magento\Backend\App\Action
{
    const PRODUCTSTATUS = 'cdiscount_product_status';

    public $product;
    public $pageFactory;
    public $filter;
    public $actionFactory;
    public $feeds;
    public $redirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Ced\Cdiscount\Helper\Product $productFactory,
        \Ced\Cdiscount\Model\FeedsFactory $feedsFactory
    ) {
        $this->product = $productFactory;
        $this->actionFactory = $actionFactory;
        $this->pageFactory = $pageFactory;
        $this->filter = $filter;
        $this->feeds = $feedsFactory;
        $this->redirectFactory = $redirectFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $resync = $this->getRequest()->getParam('resync');
            $redirect = $this->redirectFactory->create();
            if (!empty($id) && is_numeric($id)) {
                $feedIds[] = $this->feeds->create()->getCollection()
                    ->addFieldToSelect('feed_id')
                    ->addFieldToFilter('id', ['eq' => $id])
                    ->setPageSize(1)
                    ->getFirstItem()
                    ->getFeedId();
                if (!empty($feedIds)) {
                    if (empty($resync)) {
                        $this->product->syncFeeds($feedIds);
                    } elseif ($resync == true) {
                        $url = $this->feeds->create()->load($id);
                        $name = $url->getUniqueName();
                        $resp = $this->product->sendProductPackage($name);
                        if ($resp == true) {
                            $productIds = @json_decode($url->getProductIds(), true);
                            if (isset($productIds) && !empty($productIds)) {
                                $this->actionFactory->create()
                                    ->updateAttributes(
                                        $productIds,
                                        [self::PRODUCTSTATUS => \Ced\Cdiscount\Model\Source\Product\Status::SUBMITTED],
                                        $this->product->config->getStore()
                                    );
                            }
                        }
                    }
                }
            } else {
                $parseFilters = $this->filter->getCollection($this->feeds->create()->getCollection());
                $feedIds = $parseFilters->getColumnValues('feed_id');
                $this->product->syncFeeds($feedIds);
            }
            return  $redirect->setPath('cdiscount/feeds/index');
        } catch (\Exception $exception) {
            return  $redirect->setPath('cdiscount/feeds/index');
        }
    }
}
