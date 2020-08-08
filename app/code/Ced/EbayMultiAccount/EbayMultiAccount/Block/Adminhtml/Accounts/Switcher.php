<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ced\EbayMultiAccount\Block\Adminhtml\Accounts;

/**
 * Store switcher block
 *
 * @api
 * @since 100.0.2
 */
class Switcher extends \Magento\Backend\Block\Template
{
    /**
     * URL for store switcher hint
     */
    const HINT_URL = 'http://docs.magento.com/m2/ce/user_guide/configuration/scope.html';

    /**
     * Name of website variable
     *
     * @var string
     */
    protected $_defaultWebsiteVarName = 'website';

    /**
     * Name of store group variable
     *
     * @var string
     */
    protected $_defaultStoreGroupVarName = 'group';

    /**
     * Name of store variable
     *
     * @var string
     */
    protected $_defaultStoreVarName = 'account_id';

    /**
     * @var array
     */
    protected $_storeIds;

    /**
     * Url for store switcher hint
     *
     * @var string
     */
    protected $_hintUrl;

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Ced_EbayMultiAccount::accounts/switcher.phtml';

    /**
     * Website factory
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Store Group Factory
     *
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_storeGroupFactory;

    /**
     * Store Factory
     *
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ced\EbayMultiAccount\Model\AccountsFactory $accountFactory,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_accountFactory = $accountFactory;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
    }


    public function getAccounts()
    {
        $accounts = $this->multiAccountHelper->getAllAccounts();
        return $accounts;
    }

    /**
     * @return int|null
     */
    public function getAccountId()
    {
        if (!$this->hasData('account_id')) {
            $this->setData('account_id', (int)$this->getRequest()->getParam($this->getStoreVarName()));
        }
        return $this->getData('account_id');
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function isAccountSelected(\Ced\EbayMultiAccount\Model\Accounts $account)
    {
        return $this->getAccountId() !== null && (int)$this->getAccountId() === (int)$account->getId();
    }

    /**
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl(
            '*/*/*',
            [
                '_current' => true
            ]
        );
    }

    /**
     * @return mixed|string
     */
    public function getStoreVarName()
    {
        if ($this->hasData('store_var_name')) {
            return (string)$this->getData('store_var_name');
        } else {
            return (string)$this->_defaultStoreVarName;
        }
    }


    /**
     * Get current selection name
     *
     * @return string
     */
    public function getCurrentSelectionName()
    {
        if (!($name = $this->getCurrentAccountName())) {
            $name = $this->getDefaultSelectionName();
        }
        return $name;
    }

    /**
     * Get current store view name
     *
     * @return string
     */
    public function getCurrentAccountName()
    {
        if ($this->getAccountId() !== null) {
            $account = $this->_accountFactory->create();
            $account->load($this->getAccountId());
            if ($account->getId()) {
                return $account->getAccountCode();
            }
        }
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isShow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isShow()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }




    /**
     * Get whether iframe is being used
     *
     * @return bool
     */
    public function isUsingIframe()
    {
        if ($this->hasData('is_using_iframe')) {
            return (bool)$this->getData('is_using_iframe');
        }
        return false;
    }
}
