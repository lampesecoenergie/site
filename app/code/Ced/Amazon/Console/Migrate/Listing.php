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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Console\Migrate;

class Listing extends \Ced\Integrator\Console\Base
{
    const CLI_NAME = self::CLI_PREFIX . 'amazon:migrate:listing';
    const MODULE_M2EPRO = 'Ess_M2ePro';
    const MODULE_SUPPORTED = [
        self::MODULE_M2EPRO
    ];

    protected function configure()
    {
        $this->setName(self::CLI_NAME);
        $this->setDescription('migrate listing form 3rd party module to cedcommerce');
        $this->addArgument(
            'module',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'module name to migrate listing'
        );

        $this->addArgument(
            'profile_id',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'profile id to migrate listing'
        );
        parent::configure();
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    )
    {
        parent::execute($input, $output);

        $profileId = $input->getArgument('profile_id');
        $module = $input->getArgument('module');
        $module = empty($job) ? "Ess_M2ePro" : $module;
        if (in_array($module, self::MODULE_SUPPORTED)) {
            switch ($module) {
                case 'Ess_M2ePro':
                    $records = $this->m2epro($profileId, $output);
            }

            $output->writeln('');
            $output->writeln($records . ' listing successfully imported.');
        } else {
            $output->writeln('');
            $output->writeln($module . ' is not supported. Listing cannot be imported.');
        }
    }

    public function m2epro($profileId, $output)
    {
        $records = 0;
        return $records;
    }
}
