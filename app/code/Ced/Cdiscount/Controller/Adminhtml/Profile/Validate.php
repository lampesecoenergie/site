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
 * @category    Ced
 * @package     Ced_Cdiscount
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Profile;

use Magento\Framework\DataObject;

/**
 * Class Save
 * @package Ced\Rueducommerce\Controller\Adminhtml\Profile
 */
class Validate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $registory;

    /**
     * @var CollectionFactory
     */
    public $catalogCollection;

    public $categoryCollection;

    /**
     * @var \Ced\Cdiscount\Model\ProfileProductFactory
     */
    public $profileProduct;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    public $moduleDataSetup;

    /**
     * @var \Ced\Cdiscount\Model\ProfileFactory
     */
    public $profileFactory;

    /**
     * @var \Ced\Cdiscount\Model\ResourceModel\Profile\CollectionFacrtory
     */
    public $profileCollectionFactory;

    /**
     * @var \Ced\Cdiscount\Helper\Profile
     */
    public $profileHelper;

    public $dataObject;

    public $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registory,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurable,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Cdiscount\Model\ProfileProductFactory $profileProduct,
        \Ced\Cdiscount\Model\ProfileFactory $profileFactory,
        \Ced\Cdiscount\Helper\Profile $profileHelper,
        \Ced\Cdiscount\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->configStructure = $configStructure;
        $this->registory = $registory;
        $this->dataObject = $dataObject;
        $this->configFactory = $configFactory;
        $this->productConfigFactory = $configurable;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->catalogCollection = $catalogCollection;
        $this->categoryCollection = $categoryCollection;
        $this->profileHelper = $profileHelper;
        $this->profileFactory = $profileFactory;
        $this->profileProduct = $profileProduct;
        $this->resultJsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $id = !empty($params['general_information']['id']) ? $params['general_information']['id'] : 0;
        $pcode = '';
        $this->dataObject->setData($params);
        $this->dataObject->setError(0);
        $messages = [];
        if (isset($params['general_information']) && isset($params['general_information']['profile_code'])) {
            $pcode = $params['general_information']['profile_code'];
        }

        $codeMatching = $this->profileFactory->create()
            ->load($id)
            ->getData('profile_code');

        if (empty($codeMatching)) {
            $collection = $this->profileCollectionFactory->create()
                ->addFieldToFilter('profile_code', ['eq' => $pcode])
                ->addFieldToSelect('profile_code')
                ->setPageSize(1)
                ->getFirstItem()
                ->getData('profile_code');
            if (!empty($collection)) {
                $this->dataObject->setError(1);
                $messages[] = 'Profile with code "'.$pcode.'" already exist';
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        if ($this->dataObject->getError()) {
            $this->dataObject->setError(true);
            $this->dataObject->setMessages($messages);
        }

        $resultJson->setData($this->dataObject);
        return $resultJson;
    }
}
