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
use Mirasvit\Feed\Model\Feed\Deliverer;

class ValidateFtp extends Feed
{
    /**
     * @var Deliverer
     */
    protected $deliverer;

    /**
     * {@inheritdoc}
     * @param Deliverer   $deliverer
     * @param FeedFactory $feedFactory
     * @param Registry    $registry
     * @param Context     $context
     */
    public function __construct(
        Deliverer $deliverer,
        FeedFactory $feedFactory,
        Registry $registry,
        Context $context
    ) {
        $this->deliverer = $deliverer;

        parent::__construct($feedFactory, $registry, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $feed = $this->initModel();

        $params = $this->getRequest()->getParam('feed');
        if (is_array($params)) {
            $feed->addData($params);
        }

        try {
            $this->deliverer->validate($feed);
            $message = __('A connection was successfully established with "%1"', $feed->getFtpHost());
            $status = 'success';
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'error';
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode([
            'status'  => $status,
            'message' => $message
        ]));
    }
}
