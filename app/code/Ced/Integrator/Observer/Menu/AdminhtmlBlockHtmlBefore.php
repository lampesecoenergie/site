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
 * @package     Ced_Mlibre
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Observer\Menu;

use Magento\Framework\Event\ObserverInterface;

class AdminhtmlBlockHtmlBefore implements ObserverInterface
{
    const CEDCOMMERCE_VENDOR_PREFIX = 'Ced_';
    const CEDCOMMERCE_INTEGRATOR_MODULE_ID = 'Ced_Integrator::integrator';
    const CONFIG_PATH_MENU_MERGE = 'ced_integrator/settings/menu_merge';

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface  */
    public $scopeConfigManager;

    public $merge = 'na';

    public $modules = 2;

    public $index = null;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->scopeConfigManager = $config;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Backend\Block\Menu $block */
        $block = $observer->getBlock();

        if ($this->merge == 'na') {
            $this->merge = $this->scopeConfigManager->getValue(self::CONFIG_PATH_MENU_MERGE);
        }

        if ($block instanceof \Magento\Backend\Block\Menu && $this->merge) {
            /** @var \Magento\Backend\Model\Menu $menu */
            $menu = $block->getMenuModel();
            $originalMenu = clone $menu;

            /** @var \Magento\Backend\Model\Menu\Item $integrator */
            $integrator = $this->find($menu);

            if (isset($integrator) && !empty($integrator->getId())) {
                /** @var \Magento\Backend\Model\Menu $originals */
                $originals = $integrator->getChildren()->toArray();

                /** @var \Magento\Backend\Model\Menu\Item $item */
                foreach ($originalMenu as $item) {
                    if (isset($item) && strpos((string)$item->getId(), self::CEDCOMMERCE_VENDOR_PREFIX) !== false &&
                        $item->getId() != self::CEDCOMMERCE_INTEGRATOR_MODULE_ID &&
                        $this->modules < 7
                    ) {
                        // Remove common links, such as log, cron, etc
                        $this->clean($integrator, $item, $menu);

                        // Remove child-less parent without link
                        $this->clear($item, $menu);

                        // Move its menu to Integrator
                        $menu->move($item->getId(), $integrator->getId(), ($this->modules * 20));

                        $this->modules++;
                    }
                }

                $index = 10;
                /** @var \Magento\Backend\Model\Menu\Item $original */
                foreach ($originals as $original) {
                    $menu->move($original['id'], $integrator->getId(), $index);
                    $index += 10;
                }
            }
        }
    }

    /**
     * Remove duplicate childs
     * @param \Magento\Backend\Model\Menu\Item $integrator
     * @param \Magento\Backend\Model\Menu\Item $item
     * @param \Magento\Backend\Model\Menu $menu
     */
    private function clean($integrator, $item, $menu)
    {
        if ($integrator->hasChildren()) {
            /** @var \Magento\Backend\Model\Menu\Item $child */
            foreach ($integrator->getChildren() as $child) {
                if ($child->hasChildren()) {
                    foreach ($child->getChildren() as $subChild) {
                        $this->remove($subChild, $item, $menu);
                    }
                } else {
                    $this->remove($child, $item, $menu);
                }
            }
        }
    }

    /**
     * Remove child-less parent
     * @param \Magento\Backend\Model\Menu\Item $item
     * @param \Magento\Backend\Model\Menu $menu
     */
    private function clear($item, $menu)
    {
        /** @var \Magento\Backend\Model\Menu\Item $child */
        foreach ($item->getChildren() as $child) {
            if (!$child->hasChildren() && empty($child->getAction())) {
                $menu->remove($child->getId());
            }
        }
    }

    /**
     * Remove duplicate childs
     * @param \Magento\Backend\Model\Menu\Item $integrator
     * @param \Magento\Backend\Model\Menu\Item $item
     * @param \Magento\Backend\Model\Menu $menu
     */
    private function remove($integrator, $item, $menu)
    {
        /** @var \Magento\Backend\Model\Menu\Item $mergeChild */
        foreach ($item->getChildren() as $mergeChild) {
            if ($mergeChild->hasChildren()) {
                $this->remove($integrator, $mergeChild, $menu);
            } elseif (!empty($integrator->getAction()) && $integrator->getAction() == $mergeChild->getAction()) {
                $menu->remove($mergeChild->getId());
            }
        }
    }

    private function find($menu)
    {
        $integrator = null;
        /** @var \Magento\Backend\Model\Menu\Item $item */
        foreach ($menu as $item) {
            if ($item->getId() == self::CEDCOMMERCE_INTEGRATOR_MODULE_ID) {
                $integrator = $item;
                break;
            }
        }
        return $integrator;
    }
}
