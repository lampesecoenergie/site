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

class Process extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:queue:process';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Process queues via cli');
        $this->addOption(
            'processor',
            'p',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Processor Type (report|search|product)',
            'product'
        );
        $this->addOption(
            'type',
            't',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Job Type (_POST_PRODUCT_IMAGE_DATA_|_POST_ORDER_FULFILLMENT_DATA_|' .
            '_POST_PRODUCT_DATA_|_POST_PRODUCT_PRICING_DATA_|_POST_INVENTORY_AVAILABILITY_DATA_|' .
            '_POST_PRODUCT_RELATIONSHIP_DATA_)',
            'product'
        );
        $this->addOption(
            'log',
            'd',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Log in File',
            '0'
        );
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);
        $processorType = $input->getOption('processor');
        $jobType = $input->getOption('type');
        $rows = 2;
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->om->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            ['output' => $output,'rows' => $rows]
        );
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->start();
        usleep(300000);
        $progress->advance();
        usleep(300000);

        $result  = '';
        if (!empty($processorType) && $processorType == 'report') {
            /** @var \Ced\Amazon\Cron\Queue\Report\Processor $processor */
            $processor = $this->om->create(\Ced\Amazon\Cron\Queue\Report\Processor::class);
        } else {
            /** @var \Ced\Amazon\Cron\Queue\Processor $processor */
            $processor = $this->om->create(\Ced\Amazon\Cron\Queue\Processor::class);
        }

        if (!empty($jobType)) {
            $processor->setTypeOverride($jobType);
        }

        $processor->execute();
        $result = $processor->getResult();
        $progress->advance();
        usleep(300000);
        $progress->finish();
        $output->writeln('');
        $output->writeln("\t" . $result);

        $log = $input->getOption('log');
        if ($log) {
            /** @var \Ced\Amazon\Helper\File\Logger $logger */
            $logger = $this->om->create(\Ced\Amazon\Helper\File\Logger::class);
            $logger->info("Queue process cron run by schedule.", ["result" => $processor->getResult(false)]);
        }
    }
}
