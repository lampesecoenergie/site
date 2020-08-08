<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Helper;

class Logger extends \Ced\Integrator\Helper\Logger
{
    public $mutelevel = 100;

    /**
     * DB logger, dependencies can be updated here, such as model.
     * Logger constructor.
     * @param \Ced\Amazon\Helper\Config $config
     * @param \Ced\Integrator\Model\LogFactory $log
     * @param string $name
     */
    public function __construct(
        \Ced\Amazon\Helper\Config $config,
        \Ced\Integrator\Model\LogFactory $log,
        $name = 'AMAZON'
    ) {
        parent::__construct($log, $name);
        $this->mutelevel = $config->getLoggingLevel();
    }
}
