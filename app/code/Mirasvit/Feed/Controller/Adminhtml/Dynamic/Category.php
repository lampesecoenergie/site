<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
abstract class Category extends \Magento\Backend\App\Action
{
    /**
     * @var \Mirasvit\Feed\Model\Dynamic\CategoryFactory
     */
    protected $dynamicCategoryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $_resultFactory;

    /**
     * {@inheritdoc}
     * @param \Mirasvit\Feed\Model\Dynamic\CategoryFactory $variableFactory
     * @param \Magento\Framework\Registry                  $registry
     * @param \Magento\Backend\App\Action\Context          $context
     */
    public function __construct(
        \Mirasvit\Feed\Model\Dynamic\CategoryFactory $variableFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->dynamicCategoryFactory = $variableFactory;
        $this->registry = $registry;
        $this->context = $context;
        $this->resultForwardFactory = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Catalog::catalog');

        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Product Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Category Mapping'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     * @return |Mirasvit\Feed\Model\Dynamic\Category
     */
    public function initModel()
    {
        $model = $this->dynamicCategoryFactory->create();

        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Feed::feed_dynamic_category');
    }
}
