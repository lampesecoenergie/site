<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Helper;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class SaveObject
 *
 * @package Bss\CustomerAttributes\Helper
 */
class SaveObject
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Data
     */
    protected $customerAttributeHelper;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Customer\Model\Attribute
     */
    protected $model;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $attributeSet;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * SaveObject constructor.
     * @param \Magento\Customer\Model\Attribute $model
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexer
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param SaveObjectMore $saveObjectMore
     */
    public function __construct(
        \Magento\Customer\Model\Attribute $model,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Indexer\IndexerRegistry $indexer,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Bss\CustomerAttributes\Helper\SaveObjectMore $saveObjectMore
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->model = $model;
        $this->eavConfig = $eavConfig;
        $this->indexer = $indexer;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->saveObjectMore = $saveObjectMore;
    }

    /**
     * @return SaveObjectMore
     */
    public function returnSaveObjectMore()
    {
        return $this->saveObjectMore;
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->saveObjectMore->getHelperData();
    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function returnLayoutFactory()
    {
        return $this->layoutFactory;
    }

    /**
     * @return \Magento\Eav\Model\Config
     */
    public function returnEavConfig()
    {
        return$this->eavConfig;
    }

    /**
     * @return \Magento\Framework\DataObjectFactory
     */
    public function returnDataObjectFactory()
    {
        return $this->dataObjectFactory;
    }

    /**
     * @return \Magento\Framework\Indexer\IndexerRegistry
     */
    public function returnIndexer()
    {
        return $this->indexer;
    }

    /**
     * @return \Magento\Customer\Model\Attribute
     */
    public function returnModelAttribute()
    {
        return $this->model;
    }

    /**
     * @return int
     */
    public function returnDefaultSortOrder()
    {
        return \Bss\CustomerAttributes\Helper\Data::DEFAULT_SORT_ORDER;
    }

    /**
     * @return string
     */
    public function returnConstEntity()
    {
        return \Magento\Customer\Model\Customer::ENTITY;
    }

    /**
     * @return string
     */
    public function returnScopeStore()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return string
     */
    public function returnTypeJson()
    {
        return ResultFactory::TYPE_JSON;
    }

    /**
     * @return string
     */
    public function returnTypeRedirect()
    {
        return ResultFactory::TYPE_REDIRECT;
    }
}
