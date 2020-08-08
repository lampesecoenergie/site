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

class Import extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:order:import';

    const IMPORT_MODE_REPORT = "report";
    const IMPORT_MODE_API = "api";

    private $sleep = 120;

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Import orders via cli');
        $this->addOption(
            'account_ids',
            'a',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon account ids',
            ''
        );

        $this->addOption(
            'increment',
            'e',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Increment Value for Date (86400 - 24hrs, 1440 - 4 hrs)',
            14400
        );

        $this->addOption(
            'order_id',
            'i',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon order id to import',
            null
        );

        $this->addOption(
            'status',
            's',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon order status to import',
            ''
        );

        $this->addOption(
            'lower_date',
            'l',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon order start date to import. Ex: 26-06-2019',
            ''
        );

        $this->addOption(
            'upper_date',
            'u',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon order end date to import. Ex: 27-06-2019',
            ''
        );

        $this->addOption(
            'modified',
            'm',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Fetch Modified Orders',
            '1'
        );

        $this->addOption(
            'created',
            'c',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Fetch Created Orders',
            '1'
        );

        $this->addOption(
            'log',
            'd',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Log in File',
            '0'
        );

        $this->addOption(
            'medium',
            'y',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Medium',
            'cli'
        );

        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);

        $tmp = "";
        $lower = $input->getOption('lower_date');
        $upper = $input->getOption('upper_date');

        $modified = $input->getOption('modified');
        $created = $input->getOption('created');
        $log = $input->getOption('log');
        $medium = $input->getOption('medium');
        $increment = $input->getOption('increment'); // 4 hrs 14400

        $accountIds = $input->getOption('account_ids');
        $accountIds = !empty($accountIds) ? explode(",", $accountIds) : [];

        $status = $input->getOption('status');
        $status = !empty($status) ? explode(",", $status) : [
            \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
            \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
        ];

        $orderId = $input->getOption('order_id');
        $orderId = !empty($orderId) ? $orderId : null;

        $from = strtotime($lower); // Convert date to a UNIX timestamp
        $to = strtotime($upper); // Convert date to a UNIX timestamp

        $rows = 2;
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->om->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            ['output' => $output, 'rows' => $rows]
        );
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->start();
        usleep(300000);

        if (!empty($from) && empty($to)) {
            $to = strtotime("now");
        }

        if (!empty($from) && !empty($to)) {
            /** @var \Ced\Amazon\Cron\Order\Import $processor */
            $processor = $this->om->create(\Ced\Amazon\Cron\Order\Import::class);

            if ($modified == '0') {
                $processor->fetchModified = false;
            }

            if ($created == '0') {
                $processor->fetchCreated = false;
            }

            // Loop from the start date to end date and output all dates in between.
            for ($i = $from; $i <= $to; $i += $increment) {
                $progress->advance();

                $processor->accountIds = $accountIds;
                $processor->orderId = $orderId;
                $processor->status = $status;
                $processor->upper = date("Y-m-d H:i:s O", $i);
                $output->writeln($processor->upper);
                $output->writeln(" ");
                $processor->lower = date("Y-m-d H:i:s O", ($i - $increment));
                $processor->setMedium($medium);
                $processor->execute();
                if ($log) {
                    /** @var \Ced\Amazon\Helper\File\Logger $logger */
                    $logger = $this->om->create(\Ced\Amazon\Helper\File\Logger::class);
                    $logger->info("Order import cron run by schedule.", ["result" => $processor->result]);
                }
            }
        } else {
            /** @var \Ced\Amazon\Cron\Order\Import $processor */
            $processor = $this->om->create(\Ced\Amazon\Cron\Order\Import::class);

            if ($modified == '0') {
                $processor->fetchModified = false;
            }

            if ($created == '0') {
                $processor->fetchCreated = false;
            }
            $processor->accountIds = $accountIds;
            $processor->orderId = $orderId;
            $processor->status = $status;
            $processor->setMedium($medium);
            $processor->execute();
            $tmp = $processor->result;
            if ($log) {
                /** @var \Ced\Amazon\Helper\File\Logger $logger */
                $logger = $this->om->create(\Ced\Amazon\Helper\File\Logger::class);
                $logger->info("Order import cron run by schedule.", ["result" => $tmp]);
            }
        }

        $progress->advance();
        usleep(300000);
        $progress->finish();
        $output->writeln('');
        $output->writeln($tmp);
    }
}
