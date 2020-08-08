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

namespace Ced\Amazon\Console\Cron;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;

class Install extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . "amazon:cron:install";

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription("install cron jobs via cli");
        $this->addOption(
            "log",
            "d",
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            "Log in File",
            "0"
        );

        $this->addOption(
            "optional",
            "p",
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            "Add optional crons",
            "0"
        );

        $this->addOption(
            "view",
            "s",
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            "View Commands",
            "0"
        );
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);
        $log = (int)$input->getOption("log");
        $optional = (int)$input->getOption("optional");
        $view = (int)$input->getOption("view");

        /** @var \Ced\Amazon\Model\CrontabManager $crontabManager */
        $crontabManager = $this->om->create(\Ced\Amazon\Model\CrontabManager::class);

        if ($crontabManager->getTasks()) {
            $output->writeln("<error>Crontab has already been generated and saved</error>");
            return Cli::RETURN_FAILURE;
        }

        $tasks = [
            "order_import" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:order:import --log {$log} --medium cron",
                "expression" => "*/15 * * * *", // Every 15 mins
            ],
            "shipment_sync" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:order:shipment:sync --log {$log}",
                "expression" => "*/15 * * * *", // Every 15 mins
            ],
            "queue_process" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:queue:process --log {$log}",
                "expression" => "*/5 * * * *", // Every 5 mins
            ],
            "queue_sync" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:queue:sync --log {$log}",
                "expression" => "*/5 * * * *", // Every 5 mins
            ],
            "queue_flush" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:queue:flush --log {$log}",
                "expression" => "0 0,12 * * *", // twice a day
            ],
            "product_inventory" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:product:inventory --log {$log}",
                "expression" => "0 * * * *", // Every 1 hour
            ],
            "product_price" => [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:product:price --log {$log}",
                "expression" => "0 * * * *", // Every 1 hour
            ],
        ];

        if ($optional) {
            $tasks["order_create"] = [
                "command" => "{phpPath} {magentoRoot}bin/magento integrator:amazon:order:create --log {$log}",
                "expression" => "0 * * * *", // Every 1 hour
            ];
        }

        try {
            $crontabManager->saveTasks($tasks);
            if ($view) {
                $output->writeln($crontabManager->getContent());
            }
        } catch (LocalizedException $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            return Cli::RETURN_FAILURE;
        }

        $output->writeln("<info>Amazon crons has been generated and saved.</info>");

        return Cli::RETURN_SUCCESS;
    }
}
