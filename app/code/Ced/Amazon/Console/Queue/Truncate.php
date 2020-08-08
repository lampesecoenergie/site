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

class Truncate extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:queue:truncate';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Truncate queues via cli');
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);

        /** @var \Ced\Amazon\Model\ResourceModel\Queue\Collection $queues */
        $queues = $this->om->create(\Ced\Amazon\Model\ResourceModel\Queue\Collection::class);
        $rows = $queues->getSize();
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->om->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            ['output' => $output,'rows' => $rows]
        );
        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->start();
        usleep(300000);

        foreach ($queues as $item) {
            $item->delete();
            $progress->advance();
            usleep(300000);
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln($rows . " records deleted.");
    }
}
