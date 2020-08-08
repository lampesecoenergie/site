<?php

namespace Acyba\GLS\Model;

use \Acyba\GLS\Helper\Tools;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Framework\Filesystem\Io\File;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Sales\Api\ShipmentRepositoryInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Sales\Model\Convert\Order;
use \Magento\Sales\Model\Order\Shipment\Track;
use \Magento\Sales\Model\Order\Shipment\TrackFactory;
use \Magento\Shipping\Model\ShipmentNotifier;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Sales\Api\Data\OrderInterface;
use \Magento\Framework\Api\FilterBuilder;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class Import extends AbstractModel
{
    const PREFIX_GLS_IMPORT_FILE = 'GlsWinExpe6_';

    protected $logger;
    protected $io;
    protected $directoryList;
    protected $scopeConfig;
    protected $orderRepository;
    protected $shipmentRepository;
    protected $messageManager;
    protected $convertOrder;
    protected $orderShipmentTrack;
    protected $trackFactory;
    protected $shipmentNotifier;
    protected $errorsMessages;
    protected $orderInterface;
    protected $filterBuilder;
    protected $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        Registry $registry,

        ScopeConfigInterface $scopeConfig,
        Tools $glsLogger,
        File $io,
        DirectoryList $directory_list,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        ManagerInterface $messageManager,
        Order $convertOrder,
        Track $orderShipmentTrack,
        TrackFactory $trackFactory,
        ShipmentNotifier $shipmentNotifier,
        OrderInterface $orderInterface,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,

        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->logger = $glsLogger;
        $this->io = $io;
        $this->directoryList = $directory_list;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->messageManager = $messageManager;
        $this->convertOrder = $convertOrder;
        $this->orderShipmentTrack = $orderShipmentTrack;
        $this->trackFactory = $trackFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->orderInterface = $orderInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->errorsMessages = [];
    }

    /**
     * The file to import has :
     * 5th column <=> $data[4] -> order reference
     * 18th column <=> $data[17] -> gls tracking number
     *
     * @return int number of rows imported
     */
    public function import()
    {
        $importFolder = $this->scopeConfig->getValue('gls_section/gls_import_export/gls_import_folder');

        $folder = $this->directoryList->getRoot() . '/' . $importFolder;

        if (!file_exists($folder) and !is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $dir = opendir($folder);
        $count = 0;
        $countOrdersWithWrongId = 0;

        // Parcours du dossier
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && !is_dir($folder . $file) &&
                strpos($file, self::PREFIX_GLS_IMPORT_FILE) !== false
            ) {
                $aOrdersUpdated = [];
                // Parcours du fichier
                if (($handle = fopen($folder . DIRECTORY_SEPARATOR . $file, "r")) !== false) {
                    $row = 0;
                    while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                        $row++;
                        if ($row > 1 && isset($data[4]) && $data[4]) {

                            // On récupère le champ 5 qui contient le numéro de la commande
                            $incrementId = $data[4];

                            $filters = [
                                $this->filterBuilder->setField(OrderInterface::INCREMENT_ID)
                                    ->setValue($incrementId)
                                    ->create()
                            ];

                            $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();

                            $orderMatch = $this->orderRepository->getList($searchCriteria)->getItems();

                            if (!empty($orderMatch)) {
                                $order = array_pop($orderMatch);
                                // On met à jour le trackid avec le champ 18
                                if ($order && !isset($aOrdersUpdated[$incrementId])) {
                                    $order->setGlsTrackid($data[17]);
                                    //On flag la commande comme importée
                                    $order->setGlsImported(1);
                                    $order->save();
                                    $aOrdersUpdated[$incrementId] = 1;
                                    $count++;
                                    continue;
                                }

                                if ($order && $aOrdersUpdated[$incrementId]) {
                                    $order->setGlsTrackid($order->getGlsTrackid() . ',' . $data[17]);
                                    //On flag la commande comme importée
                                    $order->setGlsImported(1);
                                    $order->save();
                                }
                            } else {
                                //Le numéro de commande à importer n'existe pas
                                $countOrdersWithWrongId++;
                                $this->logger->glsLog(__('Import : the order doesn\'t exist : ') . $data[4], 'error');
                            }
                        }
                    }
                }
                fclose($handle);

                // Creation des expedition
                foreach ($aOrdersUpdated as $key => $orderToShip) {
                    try {
                        $orderShipped = $this->orderInterface->loadByIncrementId($key);
                        if ($this->_createShipment($orderShipped, $orderShipped->getGlsTrackid()) == 0) {
                            $count--;
                        }
                    } catch (\Exception $e) {
                        $this->logger->glsLog('import : ' . __(
                                'Shipment creation error for Order %s : %s',
                                $key,
                                $e->getMessage()
                            )
                        );
                    }
                }

                try {
                    unlink($folder . $file);
                } catch (\Exception $e) {
                    $this->logger->glsLog(__("Import : unable to delete file : ") . $folder . $file);
                }
            }
        }

        foreach ($this->errorsMessages as $errorMessage) {
            if (isset($errorMessage)) {
                $this->messageManager->addErrorMessage($errorMessage);
            }
        }

        if ($countOrdersWithWrongId) {
            $this->messageManager->addErrorMessage($countOrdersWithWrongId . __(' orders doesn\'t exists. Check logs for more details'));
        }

        closedir($dir);
        return $count;
    }

    private function _createShipment($order, $trackcode)
    {

        if (!$order->canShip()) {
            $this->errorsMessages[] = __("Order %s can not be shipped or has already been shipped",
                $order->getRealOrderId());

            return 0;
        }

        // Create the shipment:
        $shipment = $this->convertOrder->toShipment($order);

        // Add items to shipment:
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }

        // Register the shipment:
        $shipment->register();

        $arrTracking = [
            'carrier_code' => 'gls',
            'title' => 'GLS',
            'number' => $trackcode
        ];

        $track = $this->trackFactory->create()->addData($arrTracking);
        $shipment->addTrack($track);

        $this->shipmentRepository->save($shipment);

        if (!$shipment->getData('email_sent')) {
            $this->shipmentNotifier->notify($shipment);
        }

        $shipment->getOrder()->setIsInProcess(true);

        $this->orderRepository->save($shipment->getOrder());

        return 1;
    }
}