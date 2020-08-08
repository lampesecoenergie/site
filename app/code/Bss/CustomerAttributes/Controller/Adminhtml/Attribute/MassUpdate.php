<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Controller\Adminhtml\Attribute;

use Bss\CustomerAttributes\Model\ResourceModel\Attribute\Grid\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Attribute as Model;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassUpdate
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\Attribute
 */
class MassUpdate extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Model
     */
    protected $model;

    /**
     * MassUpdate constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Model $model
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Model $model
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * Update Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int) $this->getRequest()->getParam('status');
        $recordUpdate = 0;
        foreach ($collection->getItems() as $auctionProduct) {
            $this->updateAttribute($auctionProduct, $status);
            $recordUpdate++;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $recordUpdate)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Update Attribute
     *
     * @param Model $auctionProduct
     * @param string $status
     * @throws \Exception
     */
    protected function updateAttribute($auctionProduct, $status)
    {
        $this->model->load($auctionProduct->getId());
        $this->model->setIsVisible($status);
        $this->model->save();
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_edit');
    }
}
