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

namespace Ced\Amazon\Console\Order;

class Sync extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:order:sync';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Sync orders in Amazon Table via cli');
        $this->addOption(
            'account_ids',
            'a',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon account ids',
            ''
        );

        $this->addOption(
            'start_date',
            'l',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon order start date to create (-1 day of start date). Ex: 26-06-2019',
            ''
        );

        $this->addOption(
            'end_date',
            'u',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon order end date to create. Ex: 27-06-2019',
            ''
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

        $log = $input->getOption('log');
        $start = $input->getOption('start_date');
        $end = $input->getOption('end_date');

        $accountIds = $input->getOption('account_ids');
        $accountIds = !empty($accountIds) ? explode(",", $accountIds) : [];

        $rows = 2;
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->om->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            ['output' => $output, 'rows' => $rows]
        );
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->start();
        usleep(300000);
        /** @var \Ced\Amazon\Service\Order\Sync $processor */
        $processor = $this->om->create(\Ced\Amazon\Service\Order\Sync::class);
        $processor->setEndDate($end);
        $processor->setStartDate($start);
        $processor->setAccountIds($accountIds);
        $processor->execute();

        if ($log) {
            /** @var \Ced\Amazon\Helper\File\Logger $logger */
            $logger = $this->om->create(\Ced\Amazon\Helper\File\Logger::class);
            $logger->info("Order sync in Amazon Table cron run by schedule.");
        }

        $progress->advance();
        usleep(300000);
        $progress->finish();
        $output->writeln("");
    }
}
