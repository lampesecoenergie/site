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

namespace Ced\Amazon\Console\Queue;

class ShowList extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:queue:list';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Show queues via cli');
        $this->addOption(
            'status',
            's',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Status Type (_SUBMITTED_|_IN_PROGRESS_|_PROCESSED_|_DONE_|_ERROR_)',
            null
        );

        $this->addOption(
            'show-specifics',
            'd',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Show Specifics (0|1)',
            0
        );
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);
        $status = $input->getOption('status');
        $show = $input->getOption('show-specifics');

        /** @var \Ced\Amazon\Repository\Queue $queue */
        $queue = $this->om->create(\Ced\Amazon\Api\QueueRepositoryInterface::class);

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $search */
        $search = $this->om->create(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);

        if (isset($status) && in_array($status, \Ced\Amazon\Model\Source\Queue\Status::STATUS_LIST)) {
            /** @var \Magento\Framework\Api\Filter $search */
            $filter = $this->om->create(\Magento\Framework\Api\Filter::class);
            /** @var \Magento\Framework\Api\Filter $statusFilter */
            $filter->setField(\Ced\Amazon\Model\Queue::COLUMN_STATUS)
                ->setConditionType('eq')
                ->setValue(\Ced\Amazon\Model\Source\Queue\Status::SUBMITTED);
            $search->addFilter($filter);
        }

        $list = $queue->getList($search->create());
        $rows = [];
        $header = ['id', 'account_id', 'marketplace', 'status', 'type', 'operation_type'];
        if ($show) {
            $header[] = 'specifics';
        }
        /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
        foreach ($list->getItems() as $item) {
            $data = "";
            if ($show) {
                $specifics = $item->getSpecifics();
                foreach ($specifics as $key => $value) {
                    if ($key == 'ids') {
                        $data .= "total_ids: " . count($value) . "\n";
                    } elseif (!in_array($key, $header)) {
                        $value = is_array($value) ? json_encode($value) : $value;
                        $data .= "$key: {$value} \n";
                    }
                }
            }

            $row = [
                $item->getId(),
                $item->getAccountId(),
                $item->getMarketplace(),
                $item->getStatus(),
                $item->getType(),
                $item->getOperationType(),
                $data
            ];
            if ($show) {
                $row[] = $data;
            }
            $rows[] = $row;
        }

        /** @var \Symfony\Component\Console\Helper\Table $result */
        $result = $this->om->create(
            \Symfony\Component\Console\Helper\Table::class,
            ['output' => $output]
        );

        $result->setHeaders($header)
            ->setRows($rows);
        $result->render();
        $output->writeln(count($rows) . " records available.");
    }
}
