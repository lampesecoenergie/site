<?php
/**
 * Systempay V2-Payment Module version 2.3.2 for Magento 2.x. Support contact : supportvad@lyra-network.com.
 *
 * NOTICE OF LICENSE
 *
 * This source file is licensed under the Open Software License version 3.0
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Payment
 * @package   Systempay
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2018 Lyra Network and contributors
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Lyranetwork\Systempay\Model\Method;

class Standard extends Systempay
{

    protected $_code = \Lyranetwork\Systempay\Helper\Data::METHOD_STANDARD;

    protected $_formBlockType = \Lyranetwork\Systempay\Block\Payment\Form\Standard::class;

    protected $_canSaveCc = true;

    /**
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Lyranetwork\Systempay\Model\Api\SystempayRequest $systempayRequest
     * @param \Lyranetwork\Systempay\Helper\Data $dataHelper
     * @param \Lyranetwork\Systempay\Helper\Payment $paymentHelper
     * @param \Lyranetwork\Systempay\Helper\Checkout $checkoutHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Module\Dir\Reader $dirReader
     * @param \Magento\Framework\DataObject\Factory $dataObjectFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Lyranetwork\Systempay\Model\Api\SystempayRequestFactory $systempayRequestFactory,
        \Lyranetwork\Systempay\Helper\Data $dataHelper,
        \Lyranetwork\Systempay\Helper\Payment $paymentHelper,
        \Lyranetwork\Systempay\Helper\Checkout $checkoutHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Module\Dir\Reader $dirReader,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $localeResolver,
            $systempayRequestFactory,
            $dataHelper,
            $paymentHelper,
            $checkoutHelper,
            $productMetadata,
            $messageManager,
            $dirReader,
            $dataObjectFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function setExtraFields($order)
    {
        $info = $this->getInfoInstance();

        if ($this->isLocalCcType()) {
            // set payment_cards
            $this->systempayRequest->set('payment_cards', $info->getCcType());
        } else {
            // payment_cards is given as csv by magento
            $paymentCards = explode(',', $this->getConfigData('payment_cards'));
            $paymentCards = in_array('', $paymentCards) ? '' : implode(';', $paymentCards);

            $this->systempayRequest->set('payment_cards', $paymentCards);
        }

        // set payment_src to MOTO for backend payments
        if ($this->dataHelper->isBackend()) {
            $this->systempayRequest->set('payment_src', 'MOTO');
            return;
        }

        if ($this->isIframeMode()) {
            // iframe enabled
            $this->systempayRequest->set('action_mode', 'IFRAME');

            // enable automatic redirection
            $this->systempayRequest->set('redirect_enabled', '1');
            $this->systempayRequest->set('redirect_success_timeout', '0');
            $this->systempayRequest->set('redirect_error_timeout', '0');

            $returnUrl = $this->systempayRequest->get('url_return');
            $this->systempayRequest->set('url_return', $returnUrl . '?iframe=true');
        }

        if ($this->getConfigData('oneclick_active') && $order->getCustomerId()) {
            // 1-Click enabled and customer logged-in
            $customer = $this->customerRepository->getById($order->getCustomerId());

            if ($customer->getData('systempay_identifier') && $info->getAdditionalInformation(\Lyranetwork\Systempay\Helper\Payment::IDENTIFIER)) {
                // customer has an identifier and wants to use it
                $this->dataHelper->log('Customer ' . $customer->getEmail() . ' has an identifier and chose to use it for payment.');
                $this->systempayRequest->set('identifier', $customer->getData('systempay_identifier'));
            } else {
                // bank data acquisition on payment page, let's ask customer for data registration
                $this->dataHelper->log('Customer ' . $customer->getEmail() . ' will be asked for card data registration on payment page.');
                $this->systempayRequest->set('page_action', 'ASK_REGISTER_PAY');
            }
        }
    }

    protected function sendOneyFields()
    {
        $oneyContract = $this->dataHelper->getCommonConfigData('oney_contract');
        if (! $oneyContract) {
            return false;
        }

        $cards = explode(',', $this->getConfigData('payment_cards'));
        return in_array('', $cards) /* All cards */ || in_array('ONEY', $cards) || in_array('ONEY_SANDBOX', $cards);
    }

    /**
     * Return available card types.
     *
     * @return array[string][array]
     */
    public function getAvailableCcTypes()
    {
        // all cards
        $allCards = \Lyranetwork\Systempay\Model\Api\SystempayApi::getSupportedCardTypes();

        // selected cards from module configuration
        $cards = $this->getConfigData('payment_cards');

        if (! empty($cards)) {
            $cards = explode(',', $cards);
        } else {
            $cards = array_keys($allCards);
        }

        if (! $this->sendOneyFields()) {
            $cards = array_diff($cards, [
                'ONEY',
                'ONEY_SANDBOX'
            ]);
        }

        $availCards = [];
        foreach ($allCards as $code => $label) {
            if (in_array($code, $cards)) {
                $availCards[$code] = $label;
            }
        }

        return $availCards;
    }

    public function isOneclickAvailable()
    {
        if (! $this->isAvailable()) {
            return false;
        }

        // no 1-Click
        if (! $this->getConfigData('oneclick_active')) {
            return false;
        }

        if ($this->dataHelper->isBackend()) {
            return false;
        }

        // customer not logged in
        if (! $this->customerSession->isLoggedIn()) {
            return false;
        }

        // customer has not Systempay identifier
        $customer = $this->customerSession->getCustomer();
        if (! $customer || ! $customer->getData('systempay_identifier')) {
            return false;
        }

        return true;
    }

    /**
     * Assign data to info model instance.
     *
     * @param array|\Magento\Framework\DataObject $data
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        // reset payment method specific data
        $this->resetData();

        parent::assignData($data);

        $info = $this->getInfoInstance();

        $systempayData = $this->extractSystempayData($data);

        $info->setCcType($systempayData->getData('systempay_standard_cc_type'));

        // wether to do a payment by identifier
        $info->setAdditionalInformation(
            \Lyranetwork\Systempay\Helper\Payment::IDENTIFIER,
            $systempayData->getData('systempay_standard_use_identifier')
        );

        return $this;
    }

    /**
     * Return true if iframe mode is enabled.
     *
     * @return bool
     */
    public function isIframeMode()
    {
        if ($this->dataHelper->isBackend()) {
            return false;
        }

        return $this->getConfigData('card_info_mode') == 3;
    }

    /**
     * Check if the local card type selection option is choosen.
     *
     * @return bool
     */
    public function isLocalCcType()
    {
        if ($this->dataHelper->isBackend()) {
            return false;
        }

        return $this->getConfigData('card_info_mode') == 2;
    }
}
