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

class Remove extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX.'amazon:cron:remove';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('install cron jobs via cli');
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);

        /** @var \Ced\Amazon\Model\CrontabManager $crontabManager */
        $crontabManager = $this->om->create(\Ced\Amazon\Model\CrontabManager::class);
        try {
            $crontabManager->removeTasks();
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Amazon cron tasks have been removed</info>');

        return Cli::RETURN_SUCCESS;
    }
}
