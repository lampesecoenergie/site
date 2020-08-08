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
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Profile\Ui\Form\Product\Renderer;

class Category extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $categoryFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->categoryFactory = $categoryFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $categoryIds = $row->getCategoryIds();
        $categories = $this->categoryFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        $html = [];
        foreach ($categories as $category) {
            $html[] = $category->getName();
        }

        $html = implode(',<br/>', $html);
        return $html;
    }
}
