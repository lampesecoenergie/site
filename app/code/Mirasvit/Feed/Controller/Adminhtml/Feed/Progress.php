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
use Mirasvit\Feed\Model\Feed\Exporter;

class Progress extends Feed
{
    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * {@inheritdoc}
     * @param Exporter       $exporter
     * @param FeedFactory    $feedFactory
     * @param Registry       $registry
     * @param Context        $context
     */
    public function __construct(
        Exporter $exporter,
        FeedFactory $feedFactory,
        Registry $registry,
        Context $context
    ) {
        $this->exporter = $exporter;

        parent::__construct($feedFactory, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $feed = $this->initModel();

        $progress = $this->exporter->getHandler($feed)->toJson();

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode($progress));
    }
}
