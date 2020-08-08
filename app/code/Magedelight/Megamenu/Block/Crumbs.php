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

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Crumbs extends \Magento\Framework\View\Element\Template
{
    /**
     * Catalog data
     * @var Data
     */
    private $catalogData = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Crumbs constructor.
     * @param Context $context
     * @param Data $catalogData
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $catalogData,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->catalogData = $catalogData;
        $this->registry = $registry;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getCrumbs()
    {
        $categoriesIds = $this->request->getParam('category');
        $evercrumbs = [];
        $evercrumbs[] = [
            'label' => 'Home',
            'title' => 'Go to Home Page',
            'link' => $this->_storeManager->getStore()->getBaseUrl()
        ];
        $product = $this->registry->registry('current_product');
        
        $userCategoryPathInUrl = $this->scopeConfig->getValue(
            'catalog/seo/product_use_categories',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (($userCategoryPathInUrl) && (!is_null($categoriesIds))) {
            $path = $this->catalogData->getBreadcrumbPath();
            foreach ($path as $k => $p) {
                $evercrumbs[] = [
                    'label' => $p['label'],
                    'title' => $p['label'],
                    'link' => isset($p['link']) ? $p['link'] : ''
                ];
            }
        } else {
             $evercrumbs[] = [
                'label' => $product->getName(),
                'title' => $product->getName(),
                'link' => ''
             ];
        }
        return $evercrumbs;
    }
}
