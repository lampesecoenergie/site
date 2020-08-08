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
use Mirasvit\Feed\Model\TemplateFactory;

class Save extends Feed
{
    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * {@inheritdoc}
     * @param FeedFactory     $feedFactory
     * @param TemplateFactory $templateFactory
     * @param Registry        $registry
     * @param Context         $context
     */
    public function __construct(
        FeedFactory $feedFactory,
        TemplateFactory $templateFactory,
        Registry $registry,
        Context $context
    ) {
        $this->templateFactory = $templateFactory;

        parent::__construct($feedFactory, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This feed no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $data = $this->filterPostData($data);

            if (isset($data['template_id'])) {
                $template = $this->templateFactory->create()->load($data['template_id']);
                $model->loadFromTemplate($template);
            }

            $model->addData($data);

            try {
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the feed.'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addError('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    public function filterPostData($data)
    {
        $feed = $data['feed'];
        unset($data['feed']);

        $feed['rule_ids'] = isset($feed['rule_ids']) ? $feed['rule_ids'] : [];
        $feed['rule_ids'] = array_keys($feed['rule_ids']);

        $data = array_merge($data, $feed);

        return $data;
    }
}
