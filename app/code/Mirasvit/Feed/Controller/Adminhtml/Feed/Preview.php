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
use Mirasvit\Feed\Model\FeedFactory;
use Mirasvit\Feed\Model\Feed\Exporter;
use Mirasvit\Feed\Model\TemplateFactory;

class Preview extends Save
{
    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * {@inheritdoc}
     * @param Exporter        $exporter
     * @param FeedFactory     $feedFactory
     * @param TemplateFactory $templateFactory
     * @param Registry        $registry
     * @param Context         $context
     */
    public function __construct(
        Exporter $exporter,
        FeedFactory $feedFactory,
        TemplateFactory $templateFactory,
        Registry $registry,
        Context $context
    ) {
        $this->exporter = $exporter;

        $this->layout = $context->getView()->getLayout();

        parent::__construct($feedFactory, $templateFactory, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $feed = $this->initModel();

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        if ($post = $request->getPostValue('data')) {
            //we receive form values as query string
            parse_str($post, $data);

            $data = $this->filterPostData($data);

            $feed->addData($data);
        }

        $contentType = 'text/html';

        try {
            $this->exporter->exportPreview($feed);
            $content = file_get_contents($feed->getPreviewFilePath());

            if ($request->getPostValue()) {
                $content = $this->layout->createBlock('Magento\Backend\Block\Template')
                    ->setTemplate('Mirasvit_Feed::feed/preview.phtml')
                    ->setContent($content)
                    ->toHtml();
            } else {
                if ($feed->isXml()) {
                    $contentType = 'application/xml';
                } else {
                    $contentType = 'text/plain';
                }
            }
        } catch (\Exception $e) {
            $content = $e;
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response
            ->setHeader('Content-Type', $contentType)
            ->setBody($content);
    }

    public function _processUrlKeys()
    {
        return true;
    }
}
