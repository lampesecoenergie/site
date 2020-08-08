<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Profile;

/**
 * Class Delete
 * @package Ced\Amazon\Controller\Adminhtml\Profile
 */
class Delete extends \Magento\Backend\App\Action
{
    /** @var \Ced\Amazon\Model\ResourceModel\Profile */
    public $resource;

    /** @var \Ced\Amazon\Model\Profile */
    public $profile;

    /** @var \Ced\Amazon\Model\Profile\Product */
    public $product;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Amazon\Model\ResourceModel\Profile $resource,
        \Ced\Amazon\Model\Profile $profile,
        \Ced\Amazon\Model\Profile\Product $product
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->profile = $profile;
        $this->product = $product;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!empty($id)) {
            try {
                $this->resource->load($this->profile, $id);
                $storeId = $this->profile->getData(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID);
                $this->resource->delete($this->profile);
                if ($this->profile->isDeleted()) {
                    $this->product->remove($id, $storeId);
                }
            } catch (\Exception $e) {
                //TODO : log
            }
        }

        return $this->_redirect('*/profile/index');
    }
}
