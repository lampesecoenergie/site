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



namespace Mirasvit\Feed\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\FeedFactory;

abstract class Feed extends Action
{
    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var Context
     */
    protected $context;

    /**
     * {@inheritdoc}
     * @param FeedFactory    $feedFactory
     * @param Registry       $registry
     * @param Context        $context
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $registry,
        Context $context
    ) {
        $this->feedFactory = $feedFactory;
        $this->registry = $registry;
        $this->context = $context;
        $this->backendSession = $context->getSession();

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @param \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage
     * @return \Magento\Backend\Model\View\Result\Page\Interceptor
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Catalog::catalog');
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Product Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Feeds'));

        return $resultPage;
    }

    /**
     * Current feed model
     * @return \Mirasvit\Feed\Model\Feed
     */
    protected function initModel()
    {
        $model = $this->feedFactory->create();

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
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Feed::feed_feed');
    }
}
