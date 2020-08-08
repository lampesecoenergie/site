<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Block\Adminhtml\Category;

class Index extends \Magento\Backend\Block\Template
{
    public $category;
    public $totalCategories;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\Cdiscount\Helper\Category $category,
        \Ced\Cdiscount\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        $data = []
    ) {
        $this->category = $category;
        $this->totalCategories = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('cdiscount/category/fetch');
    }
    /**
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        return $this->category->getCategoriesTree();
    }

    public function categoryExist()
    {
        $size = $this->totalCategories->create()->getSize();
        return $size;
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('cdiscount/category/truncate');
    }
}
