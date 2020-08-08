<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Block;

use Magedelight\Megamenu\Block\Topmenu;

/**
 * Class Topmenu
 *
 * @package Magedelight\Megamenu\Block
 */
class ShortcodeMenu extends Topmenu
{
    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function isStickyEnable()
    {
        $this->primaryMenuId = $this->getMenuid();
        $this->primaryMenu = $this->menuFactory->create()->load($this->primaryMenuId);
        return $this->primaryMenu->getIsSticky();
    }

    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $html = '';

        $this->primaryMenuId = $this->getMenuid();
        $this->primaryMenu = $this->menuFactory->create()->load($this->primaryMenuId);

        if ($this->getConfigMenuStatus() == 1 && $this->primaryMenu->getIsActive() == 1) {
            $menuItems = $this->menuItemsFactory->create()->getCollection()
                    ->addFieldToFilter('menu_id', $this->primaryMenuId)
                    ->addFieldToFilter('item_parent_id', 0)
                    ->setOrder('sort_order', 'ASC');
            foreach ($menuItems as $item) {
                $childrenWrapClass = "level0 nav-1 first parent main-parent";
                $html .= $this->setMegamenu($item, $childrenWrapClass);
            }
        }

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'shortcode_block_html_topmenu_gethtml_after',
            ['menu' => $this->primaryMenuId, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    public function getCacheLifetime()
    {
        return null;
    }
}
