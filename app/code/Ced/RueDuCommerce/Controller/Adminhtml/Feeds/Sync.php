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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Feeds;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Truncate
 *
 * @package Ced\RueDuCommerce\Controller\Adminhtml\Feeds
 */
class Sync extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Ced\RueDuCommerce\Model\Feeds
     */
    public $feeds;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $fileIo;
    public $feedrequest;
    public $config;
    public $rueducommerceProducthelper;
    public $configProduct;
    
    /**
     * Delete constructor.
     *
     * @param Action\Context                        $context
     * @param PageFactory                           $resultPageFactory
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param \Ced\RueDuCommerce\Model\Feeds               $rueducommerceFeeds
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Ced\RueDuCommerce\Model\Feeds $rueducommerceFeeds,
        \Ced\RueDuCommerce\Helper\Config $config,
        \Ced\RueDuCommerce\Helper\Product $product,
        \RueDuCommerceSdk\ProductFactory $feedrequest,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \Ced\RueDuCommerce\Helper\Profile $profile,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configfactory
    ) {
        parent::__construct($context);
        $this->prodCollection = $productFactory;
        $this->rueducommerceProducthelper = $product;
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->fileIo = $fileIo;
        $this->feeds = $rueducommerceFeeds;
        $this->feedrequest = $feedrequest;
        $this->_prodAction = $productAction;
        $this->profileHelper = $profile;
        $this->json = $json;
        $this->configProduct = $configfactory;
    }

    public function execute()
    {
        $feed = false;
        $feedStatus = false;
        $feed_id = $this->getRequest()->getParam('id');
        if (isset($feed_id) and !empty($feed_id)) {
            $feed = $this->feeds->load($feed_id);
        }

        if ($feed and $feed->getId()) {
            $feedStatus = true;
          
            if($feed->getType()=='inventory-update')
                $feed_data = $this->feedrequest->create(['config' => $this->config->getApiConfig()])->getFeeds($feed->getFeedId() ,\RueDuCommerceSdk\Core\Request::POST_OFFER_IMPORT.'/'.$feed->getFeedId());
            else
                $feed_data = $this->feedrequest->create(['config' => $this->config->getApiConfig()])->getFeeds($feed->getFeedId() ,\RueDuCommerceSdk\Core\Request::POST_ITEMS_SUB_URL.'/'.$feed->getFeedId());

            $feedError = $this->rueducommerceProducthelper->getFeeds($feed->getFeedId(), $feed_data);
            $productIds = json_decode($feed->getProductIds());
            if(is_array($productIds)) {
                $attrData = array( 'rueducommerce_feed_errors' => $feedError );
                $storeId = 0;
                $this->_prodAction->updateAttributes($productIds, $attrData, $storeId);
            }
            if(isset($feed_data['product_import_tracking'])) {
               $feed_data = $feed_data['product_import_tracking'];
               if(isset($feed_data['import_status']))
                $feed_data['status'] = $feed_data['import_status'];
                $feed->setData('transform_lines_in_error',$feed_data['transform_lines_in_error']);
                $feed->setData('transform_lines_in_success',$feed_data['transform_lines_in_success']);
                $feed->setData('transform_lines_read',$feed_data['transform_lines_read']);
                $feed->setData('transform_lines_with_warning',$feed_data['transform_lines_with_warning']);
                $feed->setData('status',$feed_data['import_status']);
                $feed->setData('feed_response', $feedError);
                $feed->save();
            } else if(isset($feed_data['import'])){
                $errorIds = $successIds = [];
                $feedError = $this->json->jsonDecode($feedError);
                if(isset($feedError['import']['offers']['offer']) && !isset($feedError['import']['offers']['offer'][0])) {
                    $feedError['import']['offers']['offer'] = array(
                        0 => $feedError['import']['offers']['offer'],
                    );
                }
                $skus = isset($feedError['import']['offers']['offer']) ? array_column($feedError['import']['offers']['offer'], 'sku') : array();
                if(is_array($productIds)) {
                    if(empty($skus)) {
                        $successIds = $productIds;
                    }
                    foreach ($productIds as $productId) {
                        $isChild = false;
                        $prod = $this->prodCollection->create()->load($productId);
                        $productParents = $this->configProduct->create()
                            ->getParentIdsByChild($productId);
                        if (!empty($productParents)) {
                            $isChild = true;
                        }
                        $profile = $this->profileHelper->getProfile($productId);
                        $requiredAttributes = $profile->getRequiredAttributes();
                        if($profile->getId()){
                            $mappedSku = isset($requiredAttributes['internal-sku']['magento_attribute_code']) ? $requiredAttributes['internal-sku']['magento_attribute_code'] : '';
                            if(array_search($prod->getData($mappedSku), $skus) !== false) {
                                $errorIds[] = $productId;
                                if($isChild && isset($productParents[0]) && !in_array($productParents[0], $errorIds)) {
                                    $errorIds[] = $productParents[0];
                                }
                            } else if($isChild) {
                                $successIds[] = $productId;
                            } else if(!$isChild && !array_search($prod->getData($mappedSku), $skus) !== false) {
                                $successIds[] = $productId;
                            }
                        }
                    }

                    $storeId = 0;
                    if(isset($successIds) && is_array($successIds) && count($successIds) > 0) {
                        $successData = array( 'rueducommerce_product_status' => 'LIVE' );
                        $this->_prodAction->updateAttributes($successIds, $successData, $storeId);
                    }
                    if(isset($errorIds) && is_array($errorIds) && count($errorIds) > 0) {
                        $errorData = array( 'rueducommerce_product_status' => 'INVALID' );
                        $this->_prodAction->updateAttributes($errorIds, $errorData, $storeId);
                    }
                }
                $feedError = $this->json->jsonEncode($feedError);
               $feed_data = $feed_data['import'];
               if(isset($feed_data['import_status']))
                $feed_data['status'] = $feed_data['status'];
                $feed->setData('transform_lines_in_error',isset($feed_data['lines_in_error']) ? $feed_data['lines_in_error'] : '');
                $feed->setData('transform_lines_in_success',isset($feed_data['lines_in_success']) ? $feed_data['lines_in_success'] : '');
                $feed->setData('transform_lines_read',isset($feed_data['lines_read']) ? $feed_data['lines_read'] : '');
                $feed->setData('transform_lines_with_warning',isset($feed_data['lines_in_pending']) ? $feed_data['lines_in_pending'] : '');
                $feed->setData('status',isset($feed_data['status']) ? $feed_data['status'] : '');
                $feed->setData('feed_response', $feedError);
                $feed->save();

            }
        }

        if ($feedStatus) {
            $this->messageManager->addSuccessMessage('Feed Updated successfully.');
        } else {
            $this->messageManager->addErrorMessage('Feed update failed.');
        }
        $this->_redirect('rueducommerce/feeds');
    }
}
