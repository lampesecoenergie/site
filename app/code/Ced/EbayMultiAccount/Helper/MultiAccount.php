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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Helper;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
class MultiAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Ced\EbayMultiAccount\Model\Accounts
     */
    protected $accountModel;
    /**
     * @var \Ced\EbayMultiAccount\Model\Profile
     */
    protected $profileModel;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public $eavAttribute;
    /** @var EavSetup $eavSetup */
    public $eavSetup;
    /**
     * @var \Ced\EbayMultiAccount\Model\ResourceModel\Accounts\Colection
     */
    protected $accountsCollectionFactory;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\EbayMultiAccount\Model\AccountsFactory $accounts,
        \Ced\EbayMultiAccount\Model\ProfileFactory $profile,
        \Ced\EbayMultiAccount\Model\ResourceModel\Accounts\CollectionFactory $accountsCollectionFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
    )
    {
        parent::__construct($context);
        $this->accountModel = $accounts;
        $this->accountsCollectionFactory = $accountsCollectionFactory;
        $this->profileModel = $profile;
        $this->_coreRegistry = $coreRegistry;
        $this->eavAttribute = $eavAttribute;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
    }

    public function createProfileAttribute($accId = null, $accName = null) {
        $attributeCode = 'ebaymultiaccount_profile_'.$accId;
        $attributeLabel = 'Ebay ' . $accName . ' Profile';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'eBay',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'ebaymultiaccount_item_'.$accId;
        $attributeLabel = 'Ebay ' . $accName . ' Item ID';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'eBay',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'ebaymultiaccount_prod_status_'.$accId;
        $attributeLabel = 'Ebay ' . $accName . ' Product Status';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'eBay',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
        $attributeCode = 'ebayma_listing_error_'.$accId;
        $attributeLabel = 'Ebay ' . $accName . ' Product Validation';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'eBay',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
    }

    public function unsAccountRegistry() {
        if($this->_coreRegistry->registry('ebay_account'))
            $this->_coreRegistry->unregister('ebay_account');
    }

    public function getAccountRegistry($accId = null) {
        /** @var \Ced\EbayMultiAccount\Model\Accounts $account */
        $account = $this->accountModel->create();
        if (isset($accId) and $accId > 0) {
            $account = $account->load($accId);
        }
        if(!$this->_coreRegistry->registry('ebay_account'))
            $this->_coreRegistry->register('ebay_account', $account);
        return $this->_coreRegistry->registry('ebay_account');
    }

    public function getProfileAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'ebaymultiaccount_profile_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getItemIdAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'ebaymultiaccount_item_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdStatusAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'ebaymultiaccount_prod_status_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdListingErrorAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'ebayma_listing_error_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getAccountRegistryByPId($profileId = null, $profileCode = null) {
        if($profileId != null)
            $profile = $this->profileModel->create()->load($profileId);
        else
            $profile = $this->profileModel->create()->load($profileCode, 'profile_code');
        $accId = $profile->getAccountId();
        $account = $this->accountModel->create();
        if (isset($accId) and $accId > 0) {
            $account = $account->load($accId);
        }
        if(!$this->_coreRegistry->registry('ebay_account'))
            $this->_coreRegistry->register('ebay_account', $account);
        return $this->_coreRegistry->registry('ebay_account');
    }

    public function getAllAccounts($onlyActive = false) {
        if($onlyActive)
            $accountCollection = $this->accountsCollectionFactory->create()->addFieldToFilter('account_status', 1);
        else
            $accountCollection = $this->accountsCollectionFactory->create();
        return $accountCollection;
    }

    public function getAllProfileAttr() {
        $attributeCodes = array();
        $accounts = $this->accountsCollectionFactory->create();
        foreach ($accounts as $account) {
            $accId = $account->getId();
            if($accId > 0) {
                $attributeCodes[] = 'ebaymultiaccount_profile_' . $accId;
            }
        }
        return $attributeCodes;
    }

    public function getAccountFromLocation($location) {
        $account = $this->accountsCollectionFactory->create()
            ->addFieldToFilter('account_location', array('eq' => $location))
            ->getFirstItem();
        return $account->getId();
    }
}