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

class ImportByReport extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:order:ibr';

    private $sleep = 120;
    private $report;

    protected function configure()
    {
        // php bin/magento i:a:o:ib -a 2 -u 02-01-2019 -l 01-01-2019 -z 10 -r fba
        $this->setName(self::CLI_NAME);
        $this->setDescription('Import orders via report cli');
        $this->addOption(
            'account_ids',
            'a',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Amazon account ids',
            ''
        );

        $this->addOption(
            'report',
            'r',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Import Report Type (order|tracking|fba)',
            'order'
        );

        $this->addOption(
            'increment',
            'e',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Increment Value for Date (86400 - 24hrs, 1440 - 4 hrs)',
            86400
        );

        $this->addOption(
            'limit',
            'y',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Limit the orders',
            100
        );

        $this->addOption(
            'cli_limit',
            'z',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Limit the orders imported in a CLI command',
            null
        );

        $this->addOption(
            'create',
            'x',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Create Order in Magento (0|1)',
            1
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
            'path',
            'p',
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Report File Path',
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

        $tmp = "";
        $lower = $input->getOption('lower_date');
        $upper = $input->getOption('upper_date');
        $type = $input->getOption('report');
        $path = $input->getOption('path');
        $create = $input->getOption('create');
        $limit = $input->getOption('limit');
        $cli = $input->getOption('cli_limit');

        switch ($type) {
            case "tracking":
                // No customer. Hence create = false. Last 30 days data.
                // These reports return all orders, regardless of fulfillment channel or shipment status.
                $create = false;
                $this->report = \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_FLAT_ORDER_DATA;
                break;
            case "fba":
                // With customer. FBA shipments
                $this->report = \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_SHIPMENTS_DATA;
                break;
            default:
                $this->report = \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_ORDER_DATA;
                break;
        }

        $log = $input->getOption('log');
        $increment = $input->getOption('increment'); // 24 hrs 86400

        $accountIds = $input->getOption('account_ids');
        $accountIds = !empty($accountIds) ? explode(",", $accountIds) : [];

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
            /** @var \Ced\Amazon\Api\AccountRepositoryInterface $accountRepository */
            $accountRepository = $this->om->get(\Ced\Amazon\Api\AccountRepositoryInterface::class);
            /** @var \Ced\Amazon\Service\Order $service */
            $service = $this->om->create(\Ced\Amazon\Service\Order::class);

            foreach ($accountIds as $accountId) {
                $account = $accountRepository->getById($accountId);

                // Loop from the start date to end date and output all dates in between.
                $from += $increment;
                for ($i = $from; $i <= $to; $i += $increment) {
                    $endDate = date("Y-m-d H:i:s O", $i);
                    $startDate = date("Y-m-d H:i:s O", ($i - $increment));
                    $output->writeln("");
                    $output->writeln("\t Import Period : " . $startDate . "---" . $endDate);

                    $path = (!isset($path) || !file_exists($path)) ?
                        $this->getReportPath($output, $account, $startDate, $endDate) : $path;
                    /** @var \Ced\Amazon\Api\Data\Order\Import\ParamsInterface $params */
                    $params = $this->om->create(\Ced\Amazon\Api\Data\Order\Import\ParamsInterface::class);
                    $params->setPath($path)
                        ->setMode(\Ced\Amazon\Api\Data\Order\Import\ParamsInterface::IMPORT_MODE_REPORT)
                        ->setCreate($create)
                        ->setLimit($limit)
                        ->setAccountIds([$accountId]);
                    if (!empty($cli)) {
                        $params->setCliLimit($cli);
                    }
                    $service->setMedium("cli");
                    $result = $service->import($params);
                    $path = null;
                    $imported = $result->getOrderImportedTotal();
                    $output->writeln("");
                    $output->writeln("\t Orders imported: " . implode("|", $result->getIds()));
                    $progress->advance();
                    if ($log) {
                        /** @var \Ced\Amazon\Helper\File\Logger $logger */
                        $logger = $this->om->create(\Ced\Amazon\Helper\File\Logger::class);
                        $logger->info(
                            "Order import cron run by schedule.",
                            ["result" => $imported]
                        );
                    }
                }
            }
        }

        $progress->advance();
        usleep(300000);
        $progress->finish();
        $output->writeln('');
        $output->writeln($tmp);
    }

    /**
     * Get Report Path
     * @param $output
     * @param $account
     * @param $startDate
     * @param $endDate
     * @return string
     */
    private function getReportPath($output, $account, $startDate, $endDate)
    {
        $requestId = $this->request($output, $account, $startDate, $endDate);
        $output->writeln("");
        $output->writeln("\t Report Requested : Request Id " . $requestId);
        while (true && isset($requestId)) {
            sleep($this->sleep);

            $status = $this->getRepository()->get($requestId);
            if ($status == \Ced\Amazon\Model\Source\Feed\Status::CANCELLED) {
                $requestId = $this->request($output, $account, $startDate, $endDate);
                continue;
            }

            if (in_array(
                $status,
                [
                    \Ced\Amazon\Model\Source\Feed\Status::DONE,
                    \Ced\Amazon\Model\Source\Feed\Status::DONE_NO_DATA
                ]
            )) {
                /** @var \Ced\Amazon\Api\Data\ReportInterface $report */
                $request = $this->getRepository()->getByRequestId($requestId);
                $path = $request->getReportFile();
                $output->writeln("");
                $output->writeln("\t Report File : " . var_export($path, true));
                if (!empty($path)) {
                    return $path;
                }
            }

            $output->writeln("");
            $output->writeln("\t Waiting : Report Get. Status: " . $status);
        }
    }

    /**
     * Create Object
     * @return \Ced\Amazon\Api\ReportRepositoryInterface
     */
    private function getRepository()
    {
        if (!isset($this->reportRepository)) {
            /** @var \Ced\Amazon\Api\ReportRepositoryInterface $reportRepository */
            $this->reportRepository = $this->om->get(\Ced\Amazon\Api\ReportRepositoryInterface::class);
        }

        return $this->reportRepository;
    }

    /**
     * Request a report
     * @param $output
     * @param $account
     * @param $startDate
     * @param $endDate
     * @return bool|int|null
     */
    private function request($output, $account, $startDate, $endDate)
    {
        $specifics = [
            'type' => $this->report,
            'account_id' => $account->getId(),
            'ids' => ['*'],
            'marketplace' => '',
            'profile_id' => 0,
            'store_id' => $account->getStoreId(),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        $requestId = null;
        while (true) {
            $requestId = $this->getRepository()->request($specifics);
            if (!empty($requestId)) {
                break;
            }
            $output->writeln("");
            $output->writeln("\t Waiting : Report Request");
            sleep($this->sleep);
        }

        return $requestId;
    }
}
