<?php

namespace Acyba\GLS\Setup;

use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $salesSetupFactory;

    public function __construct(SalesSetupFactory $salesSetupFactory)
    {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $salesSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'gls_relay_id',
            [
                'type' => 'varchar',
                'label' => 'Id du point relay GLS',
                'input' => 'text',
                'visible' => true,
                'required' => false,
            ]
        );

        $salesSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'gls_trackid',
            [
                'type' => 'varchar',
                'label' => 'Trackid',
                'input' => 'text',
                'visible' => true,
                'required' => false,
            ]
        );

        $salesSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'gls_exported',
            [
                'type' => 'int',
                'label' => 'Exported',
                'visible' => true,
                'required' => false,
            ]
        );

        $salesSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'gls_imported',
            [
                'type' => 'int',
                'label' => 'Imported',
                'visible' => true,
                'required' => false,
            ]
        );
    }
}