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


namespace Mirasvit\Feed\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mirasvit\Feed\Model\FeedFactory;
use Mirasvit\Feed\Model\Feed\Exporter;

abstract class Export extends Action
{
    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Exporter
     */
    protected $exporter;

    public function __construct(
        FeedFactory $feedFactory,
        Exporter $exporter,
        Context $context
    ) {
        $this->feedFactory = $feedFactory;
        $this->exporter = $exporter;

        parent::__construct($context);
    }

    /**
     * Current feed model
     *
     * @return \Mirasvit\Feed\Model\Feed
     */
    protected function getFeed()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $feed = $this->feedFactory->create()->load($id);

            return $feed;
        }

        return false;
    }
}
