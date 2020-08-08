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
namespace Ced\Cdiscount\Block\Adminhtml\Attribute;

class Index extends \Magento\Backend\Block\Template
{
    public $category;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\Cdiscount\Helper\Category $category,
        $data = []
    ) {
        $this->category = $category;
        parent::__construct($context, $data);
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

    public function getAttributes($categories)
    {
        return $this->category->getAttributes($categories);
    }
}
