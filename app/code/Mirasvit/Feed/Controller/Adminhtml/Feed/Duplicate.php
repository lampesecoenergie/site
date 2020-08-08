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



namespace Mirasvit\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Feed\Controller\Adminhtml\Feed;
use Mirasvit\Feed\Model\FeedFactory;
use Mirasvit\Feed\Model\Feed\Copier;

class Duplicate extends Feed
{
    /**
     * @var Copier
     */
    protected $copier;

    /**
     * {@inheritdoc}
     * @param Copier      $copier
     * @param FeedFactory $feedFactory
     * @param Registry    $registry
     * @param Context     $context
     */
    public function __construct(
        Copier $copier,
        FeedFactory $feedFactory,
        Registry $registry,
        Context $context
    ) {
        $this->copier = $copier;

        parent::__construct($feedFactory, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $feed = $this->initModel();
            $this->copier->copy($feed);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }

        $this->messageManager->addSuccess(__('Feed was successfully duplicated.'));
        return $resultRedirect->setPath('*/*/');
    }
}
