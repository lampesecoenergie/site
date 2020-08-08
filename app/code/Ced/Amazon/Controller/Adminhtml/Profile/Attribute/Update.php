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

namespace Ced\Amazon\Controller\Adminhtml\Profile\Attribute;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Ced\Amazon\Api\ProfileRepositoryInterface;
use Ced\Amazon\Helper\Category;
use Ced\Amazon\Helper\Logger;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Profile
 */
class Update extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::profile';

    /** @var PageFactory */
    public $resultPageFactory;

    /** @var ProfileRepositoryInterface */
    public $profile;

    /** @var Category */
    public $category;

    /** @var Logger */
    public $logger;

    /** @var array */
    public $initial = [];

    /**
     * Update constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileRepositoryInterface $profile
     * @param Category $category
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileRepositoryInterface $profile,
        Category $category,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->profile = $profile;
        $this->category = $category;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id'); // Not needed.

        // If profile is already saved then load the attributes and setting the same mapping in current mappings.
        $profileId = $this->getRequest()->getParam('profile_id');
        try {
            /** @var \Ced\Amazon\Model\Profile $profile */
            $profile = $this->profile->getById($profileId);
            $this->initial = $profile->getProfileAttributes();
        } catch (\Exception $e) {
            // ignore
        }

        $categoryId = $this->getRequest()->getParam('category_id');
        $subCategoryId = $this->getRequest()->getParam('sub_category_id');
        $marketplaceIds = $this->getRequest()->getParam('marketplace_ids');
        $barcode = $this->getRequest()->getParam('barcode_exemption', false);
        $requiredAttributes = [];
        $optionalAttributes = [];

        try {
            if (isset($marketplaceIds, $categoryId, $subCategoryId, $accountId) &&
                !empty($marketplaceIds) && !empty($accountId) && is_array($marketplaceIds)) {
                foreach ($marketplaceIds as $marketplaceId) {
                    $params = [
                        'marketplaceId' => $marketplaceId,
                        'minOccurs' => '1'
                    ];
                    /** @var array $requiredAttributes */
                    $requiredAttributes = array_merge(
                        $requiredAttributes,
                        $this->category->getAttributes($categoryId, $subCategoryId, $params, $barcode)
                    );
                    $requiredAttributes = $this->remap($requiredAttributes);

                    $params = [
                        'marketplaceId' => $marketplaceId,
                        'minOccurs' => '0'
                    ];
                    /** @var array $optionalAttributes */
                    $optionalAttributes = array_merge(
                        $optionalAttributes,
                        $this->category->getAttributes($categoryId, $subCategoryId, $params, $barcode)
                    );

                    // Adding ASIN alternate field
                    $optionalAttributes["StandardProductID_Value_ASIN"] = [
                        'sequence' => '20\20',
                        'name' => 'ASIN',
                        'dataType' => "Barcode",
                        'minOccurs' => '0',
                        "length" => "8:16"
                    ];

                    $optionalAttributes = $this->remap($optionalAttributes);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }

        $attributes[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttributes
        ];

        $attributes[] = [
            'label' => __('Optional Attributes'),
            'value' => $optionalAttributes
        ];

        /** @var  $result */
        $result = $this->resultPageFactory->create(true);
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $result->getLayout();
        /** @var \Magento\Framework\View\Element\BlockInterface $block */
        $block = $layout->createBlock(
            \Ced\Amazon\Block\Adminhtml\Profile\Ui\Form\AttributeMapping::class,
            'amazon_attributes'
        );

        $html = $block->setAttributes($attributes)->toHtml();

        return $this->getResponse()->setBody($html);
    }

    /**
     * Remapping the previous mapped values.
     * @param array $attributes
     * @return array
     */
    private function remap(array $attributes)
    {
        foreach ($attributes as $id => &$attribute) {
            if (isset(
                $this->initial[$id],
                $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE]
            ) || isset(
                $this->initial[$id],
                $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE]
            )) {
                $attribute[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE] =
                    $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_DEFAULT_VALUE];
                $attribute[\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE] =
                    $this->initial[$id][\Ced\Amazon\Api\Data\AttributeInterface::ATTRIBUTE_MAGENTO_ATTRIBUTE_CODE];
            }
        }

        return $attributes;
    }
}
