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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Test\Unit\Helper;

/**
 * @usage: /opt/lampp/bin/php vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Ced/Integrator/Test/Unit/Helper/LoggerTest.php
 * @usage: sudo /opt/lampp/bin/php bin/magento dev:tests:run unit
 * Class LoggerTest
 * @package Ced\Integrator\Test\Unit\Helper
 */
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Ced\Integrator\Helper\Logger */
    public $logger;

    /** @var \Magento\Framework\App\Helper\Context */
    public $context;

    /** @var \Ced\Integrator\Model\LogFactory */
    public $modalFactory;

    public function setUp()
    {
       /* $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->modalFactory = $this->getMockBuilder(\Ced\Integrator\Model\LogFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'create', 'save'])
            ->getMockForAbstractClass();

        $arguments = [
            'context' => $this->context,
            'log' => $this->modalFactory,
            'name' => 'INTEGRATOR_TEST'
        ];

        $this->logger = $objectManager->getObject(\Ced\Integrator\Helper\Logger::class, $arguments);*/
    }

    public function testAddInfo()
    {
       /* $response = $this->logger->addInfo(
            'Test case run. Message added for Info.',
            ['path' => __METHOD__]
        );
        $this->assertEquals($response, true);*/
    }
}
