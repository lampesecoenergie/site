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

namespace Ced\Amazon\Controller\Adminhtml\Account;

use Magento\Framework\DataObject;

/**
 * Class Save
 *
 * @package Ced\Amazon\Controller\Adminhtml\Account
 */
abstract class Base extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::account';

    public static $fields = [
        \Ced\Amazon\Model\Account::COLUMN_NAME => 'Account Name',
        \Ced\Amazon\Model\Account::COLUMN_SELLER_ID => 'Seller Id/Merchant Token',
        \Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID => 'Aws Access Key Id',
        \Ced\Amazon\Model\Account::COLUMN_SECRET_KEY => 'Secret Key/MWS Credentials',
        \Ced\Amazon\Model\Account::COLUMN_MARKETPLACE => 'Marketplaces',
        \Ced\Amazon\Model\Account::COLUMN_STORE_ID => 'Default Store',
    ];

    /**
     * @var \Magento\Framework\Registry
     */
    public $registory;

    /** @var \Magento\Framework\DataObject  */
    public $error;

    /** @var array */
    public $invalid = [];

    /**
     * @var DataObject
     */
    public $data;

    /** @var \Magento\Framework\DataObject\Factory  */
    public $dataFactory;

    /** @var \Ced\Amazon\Helper\Logger  */
    public $logger;

    /** @var \Ced\Amazon\Model\AccountFactory  */
    public $account;

    /** @var \Ced\Amazon\Repository\Account  */
    public $repository;

    /** @var \Ced\Amazon\Helper\Config  */
    public $config;

    /** @var \Amazon\Sdk\Api\Order\OrderListFactory */
    public $orderList;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registory,
        \Magento\Framework\DataObject\Factory $data,
        \Ced\Amazon\Repository\Account $repository,
        \Ced\Amazon\Model\AccountFactory $account,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Config $config,
        \Amazon\Sdk\Api\Order\OrderListFactory $orderList
    ) {
        parent::__construct($context);
        $this->registory = $registory;
        $this->dataFactory = $data;
        $this->repository = $repository;
        $this->account = $account;
        $this->config = $config;
        $this->logger = $logger;

        $this->orderList = $orderList;
    }

    /**
     * Validate submit params
     * @return bool
     */
    public function validate()
    {
        $id = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_ID);
        $name = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_NAME);
        $sellerId = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_SELLER_ID);
        $awsAccessKeyId = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID, '');
        $secretKey = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_SECRET_KEY, '');
        $awsAuthId = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_AWS_AUTH_ID);
        $marketplace = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_MARKETPLACE, []);
        $cedcommerce = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE, 0);
        $multistore = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE, 0);
        $storeValues = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES, []);

        if ($multistore) {
            $storeValues = $this->cleanStores($storeValues);

            unset(self::$fields[\Ced\Amazon\Model\Account::COLUMN_STORE_ID]);

            self::$fields[\Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES] = 'Marketplace - Store Mapping';
        }

        $mode = $this->getRequest()->getParam(
            \Ced\Amazon\Model\Account::COLUMN_MODE,
            \Ced\Amazon\Model\Account::MODE_MOCK
        );
        $active = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_ACTIVE, 0);
        $status = $this->getRequest()->getParam(
            \Ced\Amazon\Model\Account::COLUMN_STATUS,
            \Ced\Amazon\Model\Source\Account\Status::ADDED
        );
        $notes = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_NOTES, '');
        $storeId = $this->getRequest()->getParam(\Ced\Amazon\Model\Account::COLUMN_STORE_ID, 0);
        $channel = $this->getRequest()->getParam(
            \Ced\Amazon\Model\Account::COLUMN_CHANNEL,
            \Ced\Amazon\Model\Source\Order\Channel::TYPE_MFN
        );

        $shipping = $this->getRequest()->getParam(
            \Ced\Amazon\Model\Account::COLUMN_SHIPPING_METHOD,
            \Ced\Amazon\Model\Carrier\Shipbyamazon::METHOD_NAME_CODE
        );
        $payment = $this->getRequest()->getParam(
            \Ced\Amazon\Model\Account::COLUMN_PAYMENT_METHOD,
            \Ced\Amazon\Model\Payment\Paybyamazon::METHOD_CODE
        );

        if ($cedcommerce) {
            unset(self::$fields[\Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID]);
            unset(self::$fields[\Ced\Amazon\Model\Account::COLUMN_SECRET_KEY]);

            self::$fields[\Ced\Amazon\Model\Account::COLUMN_AWS_AUTH_ID] = 'Aws Auth Id/MWS Auth Token';
        }

        // Validating required.
        foreach (self::$fields as $field => $fieldName) {
            if (empty($this->getRequest()->getParam($field))) {
                $this->invalid[] = $fieldName;
            }
        }

        if (empty($this->invalid)) {
            $this->data->addData([
                \Ced\Amazon\Model\Account::COLUMN_ACTIVE => $active,
                \Ced\Amazon\Model\Account::COLUMN_NAME => $name,

                \Ced\Amazon\Model\Account::COLUMN_STATUS => $status,
                \Ced\Amazon\Model\Account::COLUMN_SELLER_ID => $sellerId,
                \Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID => $awsAccessKeyId,
                \Ced\Amazon\Model\Account::COLUMN_AWS_AUTH_ID => $awsAuthId,
                \Ced\Amazon\Model\Account::COLUMN_SECRET_KEY => $secretKey,
                \Ced\Amazon\Model\Account::COLUMN_MARKETPLACE => implode(',', $marketplace),

                \Ced\Amazon\Model\Account::COLUMN_STORE_ID => $storeId,
                \Ced\Amazon\Model\Account::COLUMN_CHANNEL => $channel,
                \Ced\Amazon\Model\Account::COLUMN_SHIPPING_METHOD => $shipping,
                \Ced\Amazon\Model\Account::COLUMN_PAYMENT_METHOD => $payment,

                \Ced\Amazon\Model\Account::COLUMN_MODE => $mode,
                \Ced\Amazon\Model\Account::COLUMN_NOTES => $notes,
                \Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE => $cedcommerce,
                \Ced\Amazon\Model\Account::COLUMN_MULTI_STORE => $multistore,
                \Ced\Amazon\Model\Account::COLUMN_MULTI_STORE_VALUES => json_encode($storeValues),
            ]);

            if (!empty($id)) {
                $this->data->setData('id', $id);
            }

            return true;
        }

        return false;
    }

    private function cleanStores($storeValues)
    {
        $marketplaceIds = [];
        foreach ($storeValues as $id => $storeValue) {
            if (isset($storeValue['marketplace']) && !in_array($storeValue['marketplace'], $marketplaceIds)) {
                $marketplaceIds[$storeValue['marketplace']] = $storeValue['marketplace'];
            } else {
                unset($storeValues[$id]);
            }
        }

        return $storeValues;
    }

    /**
     * Validate Api Credentials by calling get orders list
     * @return bool
     */
    public function api()
    {
        $valid = false;
        try {
            $data = $this->data->getData();
            if ($this->data->getData(\Ced\Amazon\Model\Account::COLUMN_MODE) == \Ced\Amazon\Model\Account::MODE_MOCK) {
                $this->data->setData('status', \Ced\Amazon\Model\Source\Account\Status::VALID);
                $valid = true;
            } elseif (!empty($data)) {
                $config = $this->config->prepare($this->data);
                /** @var \Amazon\Sdk\Api\Order\OrderList $orderList */
                $orderList = $this->orderList->create([
                    'config' => $config,
                    'logger' => $this->logger,
                    'mockMode' => false,
                ]);

                $orderList->setOrderStatusFilter(\Amazon\Sdk\Api\Order\Core::ORDER_STATUS);

                $orderDate = date('Y/m/d', strtotime('-3 months'));
                $orderList->setLimits('Created', $orderDate);
                $orderList->setMaxResultsPerPage(1);
                $response = $orderList->fetchOrders();

                if ($response == true) {
                    $this->data->setData('status', \Ced\Amazon\Model\Source\Account\Status::VALID);
                    $valid = true;
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('Account validation failed.', ['path' => __METHOD__]);
        }

        return $valid;
    }
}
