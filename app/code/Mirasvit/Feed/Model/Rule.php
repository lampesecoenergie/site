<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model;

use Magento\Rule\Model\AbstractModel;
use Mirasvit\Core\Service\YamlService;

/**
 * @method ResourceModel\Rule getResource()
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 * @method string getName()
 * @method bool getIsActive()
 */
class Rule extends AbstractModel
{
    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var \Mirasvit\Feed\Model\Rule\Condition\CombineFactory
     */
    protected $ruleConditionCombineFactory;

    /**
     * @var \Mirasvit\Feed\Model\Rule\Action\CollectionFactory
     */
    protected $ruleActionCollectionFactory;

    /**
     * @var \Mirasvit\Feed\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Mirasvit\Feed\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

    /**
     * @since 1.0.56
     */
    protected $serializer;

    public function __construct(
        \Mirasvit\Feed\Model\Rule\Condition\CombineFactory $conditionCombineFactory,
        \Mirasvit\Feed\Model\Rule\Action\CollectionFactory $ruleActionCollectionFactory,
        \Mirasvit\Feed\Model\RuleFactory $ruleFactory,
        \Mirasvit\Feed\Model\Config $config,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        $this->ruleConditionCombineFactory = $conditionCombineFactory;
        $this->ruleActionCollectionFactory = $ruleActionCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->config = $config;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection);

        $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Mirasvit\Feed\Service\Serialize::class
        );
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mirasvit\Feed\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Assigned feed ids
     *
     * @return array
     */
    public function getFeedIds()
    {
        return is_array($this->getData('feed_ids')) ? $this->getData('feed_ids') : [];
    }

    /**
     * {@inheritdoc}
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->ruleConditionCombineFactory->create();
    }

    /**
     * {@inheritdoc}
     * @return Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->ruleActionCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->getResource()->getRuleProductIds($this->getId());
    }

    /**
     * @return $this
     */
    public function clearProductIds()
    {
        $this->getResource()->clearProductIds($this->getId());

        return $this;
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function saveProductIds($productIds)
    {
        $this->getResource()->saveProductIds($this->getId(), $productIds);

        return $this;
    }


    /**
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toString($format = '')
    {
        $this->load($this->getId());
        $string = $this->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }

    /**
     * @return string
     */
    public function export()
    {
        $path = $this->config->getRulePath() . '/' . $this->getName() . '.yaml';

        $yaml = YamlService::dump($this->toArray([
            'name',
            'conditions_serialized',
            'actions_serialized',
        ]), 10);

        file_put_contents($path, $yaml);

        return $path;
    }

    /**
     * @todo need create typical interface
     */
    public function import($filePath)
    {
        $content = file_get_contents($filePath);

        $data = YamlService::parse($content);

        $model = $this->getCollection()
            ->addFieldToFilter('name', $data['name'])
            ->getFirstItem();


        $model->addData($data)
            ->setIsImport(true)
            ->setIsActive(1)
            ->save();

        return $model;
    }

    public function beforeSave()
    {
        if ($this->getIsImport()) {
            return $this;
        }

        return parent::beforeSave();
    }

    public function duplicate()
    {
        $this->ruleFactory->create()
            ->addData($this->getData())
            ->setRuleId(null)
            ->setName($this->getName() . ' (copy)')
            ->setIsActive(1)
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setFeedIds(null)
            ->save();

        return $this;
    }

    /**
     * @return array
     */
    public function getRowsToExport()
    {
        $array = [
            'name',
            'conditions_serialized',
            'actions_serialized',
        ];

        return $array;
    }
}
