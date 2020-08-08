<?php

namespace Acyba\GLS\Cron;

use \Psr\Log\LoggerInterface;
use \Acyba\GLS\Helper\Tools;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Acyba\GLS\Model\Export;
use \Acyba\GLS\Model\Import;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class ImportExport
{

    protected $_logger;
    protected $_orderCollectionFactory;

    protected $scopeConfig;
    protected $import;
    protected $export;

    /**
     * ImportExport constructor.
     * @param \Acyba\GLS\Helper\Tools $glsLog
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Acyba\GLS\Model\Export $export
     * @param \Acyba\GLS\Model\Import $import
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Tools $glsLog,
        ScopeConfigInterface $scopeConfig,
        Export $export,
        Import $import,
        CollectionFactory $orderCollectionFactory
    ){
        $this->_logger = $glsLog;
        $this->scopeConfig = $scopeConfig;
        $this->export = $export;
        $this->import = $import;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function execute()
    {

        /*
         * Import des commandes
         */

        //Lancement de l'import des commandes
        $this->import->import();

        /*
         * Export des commandes
         */

        //Récupération du statut des commandes à exporter
        $export_status = $this->scopeConfig->getValue('gls_section/gls_import_export/gls_export_order_status');
        $this->_logger->glsLog('import_export : '.$export_status);

        //Récupération des commandes non encore exportée
        $order_collection = $this->_orderCollectionFactory->create()
            ->addFieldToFilter('status', ['eq' => $export_status])
            ->addAttributeToFilter('shipping_method', ['like' => 'gls_%'])
            ->addFieldToFilter('gls_exported', ['null' => true]);
        $this->_logger->glsLog('import_export : '.$order_collection->getSelect()->__toString());
        //Lancement de l'export des commandes en passant la collection des commandes
        $this->export->export($order_collection);

        return $this;
    }
}