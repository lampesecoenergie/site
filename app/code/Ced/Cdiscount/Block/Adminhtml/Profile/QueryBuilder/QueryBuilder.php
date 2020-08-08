<?php

namespace Ced\Cdiscount\Block\Adminhtml\Profile\QueryBuilder;

class QueryBuilder extends \Magento\Backend\Block\Template
{
    public $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }


}