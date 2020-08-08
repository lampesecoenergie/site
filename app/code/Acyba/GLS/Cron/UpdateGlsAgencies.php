<?php

namespace Acyba\GLS\Cron;


use Magento\Framework\App\ResourceConnection;
use Acyba\GLS\Helper\Tools;


class UpdateGlsAgencies
{
    protected $_helperTools;
    protected $_ftp;
    protected $_user;
    protected $_password;
    protected $_resource;
    protected $_pattern;

    /**
     * UpdateGlsAgencies constructor.
     */
    public function __construct(ResourceConnection $resource, Tools $helperTools)
    {
        $this->_resource = $resource;
        $this->_ftp = 'ftp.gls-france.com';
        $this->_user = 'addonline';
        $this->_password = '-mAfXmTqC';
        $this->_helperTools = $helperTools;
        $this->_pattern = '/^tbzipdeltimes_(\d{8}).csv$/i';
    }


    public function execute()
    {
        $databaseConnection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('gls_agencies_list');

        /* Connects to FTP server */
        $connectionId = ftp_connect($this->_ftp);
        $loginResult = ftp_login($connectionId, $this->_user, $this->_password);

        if ($loginResult) {
            /* Use passive mode for downloads */
            ftp_pasv($connectionId, true);

            // Get directory content
            $contents = ftp_nlist($connectionId, ".");


            // Get file and file date
            $remoteFileDate = 0;
            foreach ($contents as $key => $filename) {
                if (preg_match($this->_pattern, $filename, $matches)) {
                    $remoteFileDate = $matches[1];
                    $remoteFile = $filename;
                }
            }

            if ($remoteFileDate) {
                ob_start();
                $result = ftp_get($connectionId, "php://output", $remoteFile, FTP_BINARY);
                $data = ob_get_contents();
                $agencies = explode("\r\n", $data);
                ob_end_clean();


                if (count($agencies) > 1) {
                    $databaseConnection->query('TRUNCATE '.$tableName);

                    foreach ($agencies as $oneAgency) {
                        $dataAgency = explode(';', $oneAgency);

                        $databaseConnection->beginTransaction();

                        $dataQuery = [];

                        $dataQuery['agencycode'] = $dataAgency[0];
                        $dataQuery['zipcode_start'] = $dataAgency[1];
                        $dataQuery['zipcode_end'] = $dataAgency[2];
                        $dataQuery['validity_date_start'] = $dataAgency[3];
                        $dataQuery['validity_date_end'] = $dataAgency[4];
                        $dataQuery['last_import_date'] = $remoteFileDate;
                        $dataQuery['last_check_date'] = date('Ymd');

                        $databaseConnection->insert($tableName, $dataQuery);
                        $databaseConnection->commit();
                    }
                }else {
                    $this->_helperTools->glsLog(__('GLS file corrupted. Can\'t update agency code list.'), 'error');
                }
            }else {
                $this->_helperTools->glsLog(__('No corresponding file in GLS FTP to get ZIPCODE for express delivery mode. Can\'t update agency code list.'),
                    'error');
            }
        }else {
            $this->_helperTools->glsLog(__('Can\'t connect to GLS FTP to get ZIPCODE for express delivery mode. Can\'t update agency code list.'),
                'error');
        }
    }
}