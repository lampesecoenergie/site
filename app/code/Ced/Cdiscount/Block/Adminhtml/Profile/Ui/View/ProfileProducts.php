<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 7/2/18
 * Time: 5:33 PM
 */

namespace Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View;



class ProfileProducts extends \Magento\Backend\Block\Template
{
    public $_template = 'Ced_Cdiscount::profile/profile_products.phtml';

    public $blockGrid;

    public $jsonEncoder;

    public $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View\Grid\Product::class,
                'cdiscount.profile.products.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $products = $this->getProfile()->getProductsPosition();
        if (!empty($products)) {
            return $this->jsonEncoder->encode($products);
        }
        return '{}';
    }

    /**
     * @return string
     */
    public function getFilters()
    {
        $filters = '';
        $profileProductFilter = $this->getProfile()->getProfileProductsFilters();
        if (isset($profileProductFilter) and !empty($profileProductFilter)) {
            $filters = $profileProductFilter;
        }
        return $filters;
    }

    /**
     * @return $profile
     */
    public function getProfile()
    {
        return $this->registry->registry('cdiscount_profile');
    }
}