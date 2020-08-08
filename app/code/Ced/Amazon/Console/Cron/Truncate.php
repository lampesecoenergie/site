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

class Truncate extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX.'amazon:cron:truncate';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('truncate cron jobs via cli');
        $this->addArgument(
            'job_code',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'job code to truncate'
        );
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);

        $job = $input->getArgument('job_code');
        $job = empty($job) ? "ced_amazon_": $job;

        /** @var \Magento\Cron\Model\ResourceModel\Schedule\Collection $collection */
        $collection = $this->om->create(\Magento\Cron\Model\ResourceModel\Schedule\Collection::class);
        $collection->addFieldToFilter('job_code', ['like' => $job."%"]);
        $rows = $collection->getSize();
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->om->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            ['output' => $output,'rows' => $rows]
        );
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->start();
        usleep(300000);
        foreach ($collection->getItems() as $item) {
            $item->delete();
            $progress->advance();
            usleep(300000);
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln($rows.' jobs deleted successfully.');
    }
}
