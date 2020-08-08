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


namespace Mirasvit\Feed\Model\Feed;

use Mirasvit\Feed\Model\Feed;
use Mirasvit\Feed\Model\FeedFactory;

class Copier
{
    /**
     * Feed Factory
     *
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * Constructor
     *
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        FeedFactory $feedFactory
    ) {
        $this->feedFactory = $feedFactory;
    }

    /**
     * Create new copy of feed
     *
     * @param Feed $feed
     * @return Feed
     */
    public function copy(Feed $feed)
    {
        $copy = $this->feedFactory->create()
            ->setData($feed->getData())
            ->setId(null)
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setGeneratedAt(null)
            ->setGeneratedCnt(null)
            ->setGeneratedTime(null)
            ->setUploadedAt(null)
            ->setRuleIds(null)
            ->setName($feed->getName() . ' copy')
            ->setFilename($feed->getData('filename') . '_copy')
            ->save();

        $copy->setRuleIds($feed->getRuleIds())
            ->save();

        return $copy;
    }
}
