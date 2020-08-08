<?php

namespace Acyba\GLS\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Framework\Filesystem\Io\File;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Acyba\GLS\Helper\Tools;

class Export extends AbstractModel
{
    protected $scopeConfig;
    protected $filename;
    protected $io;
    protected $directoryList;
    protected $tools;

    private $_aProductnoCorrespondance = [
        'ls_tohome' => 02,
        'ls_tohome_international' => 01,
        'ls_fds' => 18,
        'ls_fds_international' => 19,
        'ls_relay' => 17,
        'ls_express' => 16,
    ];

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param File $io
     * @param DirectoryList $directory_list
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        File $io,
        DirectoryList $directory_list,
        Tools $tools,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_init('Acyba\GLS\Model\ResourceModel\Export');
        $this->io = $io;
        $this->directoryList = $directory_list;
        $this->scopeConfig = $scopeConfig;
        $this->tools = $tools;
    }

    public function export($collection, $download = false)
    {
        if ($collection->getSize() > 0) {
            /*
             * Csv export configuration
             */
            $delimiter = ';';
            $encloser = '"';
            $this->filename = 'GlsCmd_'.$this->udate('YmdHisu').'.csv';

            /*
             * Get the export Folder
             */
            $exportFolder = $this->scopeConfig->getValue('gls_section/gls_import_export/gls_export_folder');


            /*
             * Populate orders array
             */
            $aOrdersToExport = [];

            // HEADERS of the file
            $aheaders = [
                'ORDERID',
                'ORDERNAME',
                'PRODUCTNO',
                'ORDERWEIGHTTOT',
                'CONSID',
                'CONTACT',
                'CONTACTMAIL',
                'CONTACTMOBILE',
                'CONTACTPHONE',
                'STREET1',
                'STREET2',
                'STREET3',
                'COUNTRYCODE',
                'CITY',
                'ZIPCODE',
                'REFPR',
            ];
            $aOrdersToExport[] = $aheaders;

            // Parsing of the orders
            foreach ($collection as $order) {
                $aRow = [];

                // Getting the addresses of the order
                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();

                // ORDERID
                $aRow[] = $order->getIncrementId();

                // ORDERNAME
                if ($shippingAddress->getCompany()) {
                    $aRow[] = mb_strtoupper($shippingAddress->getCompany());
                }else {
                    $aRow[] = mb_strtoupper(
                        $shippingAddress->getFirstname().' '.$shippingAddress->getLastname(),
                        'UTF-8'
                    );
                }

                // PRODUCTNO
                $shippingMethod = $order->getShippingMethod();
                //On regarde si la livraison est en France
                $country_code = mb_strtoupper($shippingAddress->getCountryId(), 'UTF-8');
                if ($country_code != 'FR') {
                    $international = true;
                }else {
                    $international = false;
                }

                if (strpos($shippingMethod, 'ls_tohome') !== false && strpos($shippingMethod, 'ls_tohome') !== 0) {
                    if ($international) {
                        $aRow[] = $this->_aProductnoCorrespondance['ls_tohome_international'];
                    }else {
                        $aRow[] = $this->_aProductnoCorrespondance['ls_tohome'];
                    }
                }
                if (strpos($shippingMethod, 'ls_fds') !== false && strpos($shippingMethod, 'ls_fds') !== 0) {
                    if ($international) {
                        $aRow[] = $this->_aProductnoCorrespondance['ls_fds_international'];
                    }else {
                        $aRow[] = $this->_aProductnoCorrespondance['ls_fds'];
                    }
                }
                if (strpos($shippingMethod, 'ls_relay') !== false && strpos($shippingMethod, 'ls_relay') !== 0) {
                    $aRow[] = $this->_aProductnoCorrespondance['ls_relay'];
                }
                if (strpos($shippingMethod, 'ls_express') !== false && strpos($shippingMethod, 'ls_express') !== 0) {
                    $aRow[] = $this->_aProductnoCorrespondance['ls_express'];
                }
                // ORDERWEIGHTTOT
                $totalWeight = $order->getWeight();
                $aRow[] = str_pad(number_format($totalWeight, 2, '.', ''), 5, "0", STR_PAD_LEFT);

                // CONSID
                $aRow[] = $order->getCustomerId();

                // CONTACT
                if ($shippingAddress->getCompany()) {
                    $aRow[] = mb_strtoupper(
                        $shippingAddress->getFirstname().' '.$shippingAddress->getLastname(),
                        'UTF-8'
                    );
                }else {
                    if (strpos($shippingMethod, 'ls_express') !== false && strpos($shippingMethod, 'ls_express') !== 0
                    ) {
                        /* If Shipping method is express, CONTACT field is always FIRSTNAME - LASTNAME */
                        $aRow[] = mb_strtoupper($shippingAddress->getFirstname().' '.$shippingAddress->getLastname(),
                            'UTF-8');
                    }else {
                        $aRow[] = '';
                    }
                }

                // CONTACTMAIL
                $aRow[] = $shippingAddress->getEmail();

                // CONTACTMOBILE
                $aRow[] = $shippingAddress->getTelephone();

                // CONTACTPHONE
                $aRow[] = $shippingAddress->getTelephone();

                // Repartition de l'adresse en fonction des tailles.
                $street = $shippingAddress->getStreet();
                if (empty($street[1])) {
                    $street[1] = '';
                }
                if (empty($street[2])) {
                    $street[2] = '';
                }

                if (strlen($street[0]) > 35 || strlen($street[1]) > 35 || strlen($street[2]) > 35) {
                    $street = $street[0].' '.$street[1].' '.$street[2];
                    $street = wordwrap($street, 35, ';', true);
                    $aStreet = explode(';', $street);

                    foreach ($aStreet as $onePart){
                        $aRow[] = mb_strtoupper($onePart, 'UTF-8');
                    }

                }else {
                    // STREET1
                    $aRow[] = mb_strtoupper($street[0], 'UTF-8');

                    // STREET2
                    $aRow[] = mb_strtoupper($street[1], 'UTF-8');

                    // STREET3
                    $aRow[] = mb_strtoupper($street[2], 'UTF-8');
                }

                // COUNTRYCODE
                $aRow[] = mb_strtoupper($shippingAddress->getCountryId(), 'UTF-8');

                // CITY
                $aRow[] = mb_strtoupper($shippingAddress->getCity(), 'UTF-8');

                // ZIPCODE
                $aRow[] = mb_strtoupper($shippingAddress->getPostcode(), 'UTF-8');

                // REFPR (identifiant du point relais)
                $aRow[] = $order->getGlsRelayId();

                // Adding the order to the export array
                $aOrdersToExport[] = $aRow;

                //On flag la commande comme exportée
                $order->setGlsExported(1);
                $order->save();
            }

            if (!$download) {
                /*
                 * Export the file
                 */
                $this->array2csv($aOrdersToExport, $this->filename, $delimiter, $encloser, $exportFolder);
            }else {
                return $this->printCsv($aOrdersToExport, $this->filename, $delimiter, $encloser);
            }
        }
    }

    private function udate($format = 'u', $utimestamp = null)
    {
        if (is_null($utimestamp)) {
            $utimestamp = microtime(true);
        }

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);
        $milliseconds = substr($milliseconds, 0, 2);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

    private function array2csv(
        array &$array,
        $filename,
        $delimiter = ';',
        $encloser = '"',
        $folder = 'var/export/gls/'
    ){
        if (count($array) == 0) {
            return null;
        }

        $folder = $this->directoryList->getRoot().'/'.$folder;

        if (!file_exists($folder) and !is_dir($folder)) {
            $this->io->mkdir($folder, 0755, true);
        }

        ob_start();
        $df = fopen($folder.$filename, 'w+');
        foreach ($array as $row) {
            // WINEXPE attends de l'ISO-8859-1
            foreach (array_keys($row) as $key) {
                $row[$key] = iconv('UTF-8', 'ISO-8859-9', $row[$key]);
            }

            fputcsv($df, $row, $delimiter, $encloser);
        }
        fclose($df);

        return ob_get_clean();
    }

    public function printCsv(
        array &$array,
        $filename,
        $delimiter = ';',
        $encloser = '"'
    ){
        if (count($array) == 0) {
            return null;
        }

        $csvData = '';
        foreach ($array as $row) {
            // WINEXPE attends de l'ISO-8859-1
            $rowData = '';
            foreach (array_keys($row) as $key) {
                $cellData = $encloser.iconv('UTF-8', 'ISO-8859-9', $row[$key]).$encloser.$delimiter;
                $rowData .= $cellData;
            }
            $csvData .= $rowData."\r\n";
        }

        return $csvData;
    }
}