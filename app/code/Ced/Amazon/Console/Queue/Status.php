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

class Status extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:queue:status';

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('Get queue status via cli');

        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        parent::execute($input, $output);
        /** @var \Ced\Amazon\Model\Cache $cache */
        $cache = $this->om->create(\Ced\Amazon\Model\Cache::class);
        $status = $cache->getValue("processor_cron_status");
        if (is_array($status)) {
            foreach ($status as $key => $value) {
                $output->writeln($key . " : " . var_export($value, true));
            }
        }
    }
}
