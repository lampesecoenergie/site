<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Console\Feed;

class ShowList extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:feed:list';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Show feeds via cli');
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);

        /** @var \Ced\Amazon\Repository\Feed $feed */
        $feed = $this->om->create(\Ced\Amazon\Api\FeedRepositoryInterface::class);

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $search */
        $search = $this->om->create(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);

        if (false) {
            /** @var \Magento\Framework\Api\Filter $search */
            $filter = $this->om->create(\Magento\Framework\Api\Filter::class);
            /** @var \Magento\Framework\Api\Filter $filter */
            $filter->setField(\Ced\Amazon\Model\Feed::COLUMN_STATUS)
                ->setConditionType('eq')
                ->setValue(\Ced\Amazon\Model\Source\Feed\Status::SUBMITTED);
            $search->addFilter($filter);
        }

        $list = $feed->getList($search->create());
        $rows = [];
        $header = ['id', 'feed_id', 'account_id', 'status', 'type'];
        /** @var \Ced\Amazon\Api\Data\FeedInterface $item */
        foreach ($list->getItems() as $item) {
              $rows[] = [
                $item->getId(),
                $item->getFeedId(),
                $item->getAccountId(),
                $item->getStatus(),
                $item->getType()
            ];
        }

        /** @var \Symfony\Component\Console\Helper\Table $result */
        $result = $this->om->create(
            \Symfony\Component\Console\Helper\Table::class,
            ['output' => $output]
        );

        $result->setHeaders($header)
            ->setRows($rows);
        $result->render();
    }
}
