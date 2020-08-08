<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Controller\Adminhtml\Export;

use LaPoste\ExpeditorInet\Helper\Config as ConfigHelper;
use LaPoste\ExpeditorInet\Model\Config\Source\FileCharset;
use LaPoste\ExpeditorInet\Model\Config\Source\FileExtension;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Order export controller.
 *
 * @author Smile (http://www.smile.fr)
 */
class Export extends Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var CollectionFactory
     */
    protected $ordersFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ConfigHelper $configHelper
     * @param FileFactory $fileFactory
     * @param CollectionFactory $ordersFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ConfigHelper $configHelper,
        FileFactory $fileFactory,
        CollectionFactory $ordersFactory,
        DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->fileFactory = $fileFactory;
        $this->ordersFactory = $ordersFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('selected');
        $excluded = $this->getRequest()->getPost('excluded');

        if ($excluded !== 'false' && empty($orderIds)) {
            return $this->_redirect('*/*');
        }

        if ($excluded === 'false') {
            // All orders were selected
            $orderIds = [];
        }

        try {
            // Prepare the export file name
            $fileExtension = $this->configHelper->getExportFileExtension();
            $fileName = 'orders_export_' . $this->dateTime->date('Ymd_His') . $fileExtension;

            // Send a download link to the CSV file
            return $this->fileFactory->create(
                $fileName,
                $this->exportOrdersToCsv($orderIds),
                DirectoryList::VAR_DIR,
                $this->getContentType($fileExtension)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An error occurred while creating the export file.'));
            return $this->_redirect('*/*');
        }
    }

    /**
     * Export orders to a CSV string.
     *
     * @param array $orderIds
     * @return string
     */
    protected function exportOrdersToCsv(array $orderIds)
    {
        $content = '';
        $fieldDelimiter = $this->configHelper->getExportFieldDelimiter();
        $fieldEnclosure = $this->configHelper->getExportFieldEnclosure();
        $lineBreak = $this->configHelper->getExportEolCharacter();

        // Prepare the order collection
        $orders = $this->ordersFactory->create();
        if (!empty($orderIds)) {
            $orders->addFieldToFilter('entity_id', ['in' => $orderIds]);
        }

        $counter = 0;
        $ordersCount = $orders->count();

        // Generate the CSV file
        foreach ($orders as $order) {
            $counter++;
            $fields = $this->getOrderData($order);
            $content .= $this->arrayToCsv($fields, $fieldDelimiter, $fieldEnclosure);

            if ($counter < $ordersCount) {
                $content .= $lineBreak;
            }
        }

        if ($this->configHelper->getExportFileCharset() === FileCharset::ISO88591) {
            $content = utf8_decode($content);
        }

        return $content;
    }

    /**
     * Get the order data to add to the CSV file.
     *
     * @param Order $order
     * @return array
     */
    protected function getOrderData(Order $order)
    {
        $productCode = $this->getColissimoValue($order, 'DELIVERYMODE');

        // Signature
        if ($productCode === 'DOM' && $this->configHelper->getExportSignatureRequired()) {
            $productCode = 'DOS';
        }

        // Use the billing address for pickup delivery, shipping address otherwise
        $address = in_array($productCode, $this->configHelper->getPickupPointCodes())
            ? $order->getBillingAddress()
            : $order->getShippingAddress();

        // Get the telephone from Colissimo data, or the address otherwise
        $telephone = $this->getColissimoValue($order, 'CEPHONENUMBER');
        if (!$telephone) {
            $telephone = $address->getTelephone();
        }

        return [
            $order->getRealOrderId(),
            $address->getFirstname(),
            $address->getLastname(),
            $address->getCompany(),
            $address->getStreetLine(1),
            $address->getStreetLine(2),
            $address->getStreetLine(3),
            $address->getStreetLine(4),
            $address->getPostcode(),
            $address->getCity(),
            $address->getCountry(),
            $telephone,
            $productCode,
            $this->getColissimoValue($order, 'CEDELIVERYINFORMATION'),
            $this->getPrefixCode($order),
            $this->getColissimoValue($order, 'CEDOORCODE1'),
            $this->getColissimoValue($order, 'CEDOORCODE2'),
            $this->getColissimoValue($order, 'CEENTRYPHONE'),
            $this->getColissimoValue($order, 'PRID'),
            $this->getColissimoValue($order, 'CODERESEAU'),
            $this->getColissimoValue($order, 'CEEMAIL'),
            (float) $order->getWeight(),
            $this->configHelper->getExportCompanyName(),
        ];
    }

    /**
     * Get the Colissimo data stored in the order.
     *
     * @param Order $order
     * @return array
     */
    protected function getColissimoData(Order $order)
    {
        if ($order->hasData('decoded_colissimosimplicite_data')) {
            return $order->getData('decoded_colissimosimplicite_data');
        }

        $colissimoData = $order->getData('colissimosimplicite_data');
        $decodedColissimoData = $colissimoData ? json_decode($colissimoData, true) : [];
        $order->setData('decoded_colissimosimplicite_data', $decodedColissimoData);

        return $decodedColissimoData;
    }

    /**
     * Get the value of a field stored in the order Colissimo data.
     *
     * @param array $colissimoData
     * @param string $field
     * @return mixed
     */
    protected function getColissimoValue(Order $order, $field)
    {
        $colissimoData = $this->getColissimoData($order);

        return isset($colissimoData[$field]) ? $colissimoData[$field] : null;
    }

    /**
     * Get the code of the civility stored in the Colissimo data.
     *
     * @param Order $order
     * @return int
     */
    protected function getPrefixCode(Order $order)
    {
        $prefix = $this->getColissimoValue($order, 'CECIVILITY');

        if ($prefix === 'MR') {
            return 2;
        } elseif ($prefix === 'MME') {
            return 3;
        } elseif ($prefix === 'MLE') {
            return 4;
        } else {
            return 1;
        }
    }

    /**
     * Get content type header by file extension.
     *
     * @param string $extension
     * @return string
     */
    protected function getContentType($extension)
    {
        return $extension === FileExtension::CSV ? 'application/csv' : 'plain/text';
    }

    /**
     * Convert an array to a CSV record.
     *
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    protected function arrayToCsv(array $fields, $delimiter, $enclosure)
    {
        foreach ($fields as $index => $field) {
            $fields[$index] = $enclosure . $field . $enclosure;
        }

        return implode($delimiter, $fields);
    }
}
