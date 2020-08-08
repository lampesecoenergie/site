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
 * @package     Ced_RueDuCommerce
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Profile;

/**
 * Class Save
 * @package Ced\RueDuCommerce\Controller\Adminhtml\Profile
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
     * @var \Ced\RueDuCommerce\Model\ProfileProductFactory
     */
    public $profileProduct;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    public $moduleDataSetup;

    /**
     * @var \Ced\RueDuCommerce\Model\ProfileFactory
     */
    public $profileFactory;

    /**
     * @var \Ced\RueDuCommerce\Helper\Profile
     */
    public $profileHelper;

    public $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registory,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurable,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\RueDuCommerce\Model\ProfileProductFactory $profileProduct,
        \Ced\RueDuCommerce\Model\ProfileFactory $profileFactory,
        \Ced\RueDuCommerce\Helper\Profile $profileHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->configStructure = $configStructure;
        $this->registory = $registory;
        $this->configFactory = $configFactory;
        $this->productConfigFactory = $configurable;
        $this->catalogCollection = $catalogCollection;
        $this->categoryCollection = $categoryCollection;
        $this->profileHelper = $profileHelper;
        $this->profileFactory = $profileFactory;
        $this->profileProduct = $profileProduct;
        $this->resultJsonFactory = $jsonFactory;
    }

    public function execute()
    {

        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }

}
