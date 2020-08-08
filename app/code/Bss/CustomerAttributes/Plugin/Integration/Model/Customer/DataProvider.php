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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Integration\Model\Customer;

use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * @inheritDoc
 */
class DataProvider extends \Magento\Customer\Model\Customer\DataProvider
{
    /**
     * @var \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     */
    private $b2BRegistrationIntegration;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EavValidationRules $eavValidationRules
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param FilterPool $filterPool
     * @param \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     * @param FileProcessorFactory|null $fileProcessorFactory
     * @param array $meta
     * @param array $data
     * @param ContextInterface|null $context
     * @param bool $allowToShowHiddenAttributes
     * @param null $fileUploaderDataResolver
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        FilterPool $filterPool,
        \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration,
        FileProcessorFactory $fileProcessorFactory = null,
        array $meta = [],
        array $data = [],
        ContextInterface $context = null,
        $allowToShowHiddenAttributes = true,
        $fileUploaderDataResolver = null
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $eavValidationRules,
            $customerCollectionFactory,
            $eavConfig,
            $filterPool,
            $fileProcessorFactory,
            $meta,
            $data,
            $context,
            $allowToShowHiddenAttributes,
            $fileUploaderDataResolver
        );
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(Type $entityType)
    {
        $meta = parent::getAttributesMeta($entityType);
        if (!$this->b2BRegistrationIntegration->isB2BRegistrationModuleEnabled()) {
            return $meta;
        }
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\App\Request\Http');
        $params = $request->getParams();
        $attributes = $entityType->getAttributeCollection();
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == "b2b_activasion_status") {
                continue;
            }
            $usedInForms = $attribute->getUsedInForms();

            if (in_array('is_customer_attribute', $usedInForms)) {
                $customerId = $params['id'];
                if (isset($this->getData()[$customerId]['customer']['b2b_activasion_status']) && $this->getData()[$customerId]['customer']['b2b_activasion_status']) {
                    /* B2b Customer */
                    if (!in_array('b2b_account_edit', $usedInForms)) {
                        unset($meta[$attributeCode]);
                    }
                } else {
                    /* Normal Account */
                    if (!in_array('customer_account_edit_frontend', $usedInForms)) {
                        unset($meta[$attributeCode]);
                    }
                }
            }
        }

        return $meta;
    }
}
