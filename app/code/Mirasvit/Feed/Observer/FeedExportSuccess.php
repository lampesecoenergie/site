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


namespace Mirasvit\Feed\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Feed\Model\Feed\Notifier;

class FeedExportSuccess implements ObserverInterface
{
    /**
     * @var Notifier
     */
    protected $notifier;

    /**
     * Constructor
     *
     * @param Notifier $notifier
     */
    public function __construct(
        Notifier $notifier
    ) {
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $feed = $observer->getData('feed');

        $this->notifier->exportSuccess($feed);
    }
}
