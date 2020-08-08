<?php include_once(dirname(__FILE__).'/get-common/ServiceEndpointsAndTokens.php'); ?>
<?php include_once(dirname(__FILE__).'/get-common/LargeMerchantServiceSession.php'); ?>
<?php include_once(dirname(__FILE__).'/UploadFile/MultiPartMessage.php'); ?>
<?php include_once(dirname(__FILE__).'/get-common/DOMUtils.php'); ?>

<?php

class InitFunctions
{
    public function __construct($dataFormat, $responseDataFormat, $environment, $token)
    {
        $this->environment = $environment;
        $this->securityToken = $token;
        $this->merchantSession = new LargeMerchantServiceSession('XML', 'XML', $this->environment, $this->securityToken);
    }

    function createUploadJobRequest($jobType, $uuid)
    {
        $request = '<createUploadJobRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<uploadJobType>' . $jobType . '</uploadJobType>';
        $request .= '<UUID>' . $uuid . '</UUID>';
        $request .= '</createUploadJobRequest>';

        return $request;
    }

    function createUploadJob($jobType, $uuid)
    {
        $request = $this->createUploadJobRequest($jobType, $uuid);
        $response = $this->merchantSession->sendBulkDataExchangeRequest(
            'createUploadJob', $request);

        $xml = simplexml_load_string($response);
        $createJobResponse = json_decode(json_encode($xml), true);

        return $createJobResponse;
    }

    /**
     * Reads the GZIP'd Request file.
     * @return string bytes comprising the request file.
     */
    function readRequestFile($zipFile)
    {
        if(file_exists($zipFile)) {
            $handle = fopen($zipFile, 'r');
            $fileData = fread($handle, filesize($zipFile));
            fclose($handle);
            return $fileData;
        }

        return false;
    }

    function createUploadFileRequest($taskReferenceId, $fileReferenceId, $fileSize)
    {
        $request  = '<uploadFileRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<taskReferenceId>' . $taskReferenceId . '</taskReferenceId>';
        $request .= '<fileReferenceId>' . $fileReferenceId . '</fileReferenceId>';
        $request .= '<fileFormat>gzip</fileFormat>';
        $request .= '<fileAttachment>';
        $request .= '<Size>' . $fileSize . '</Size>';
        $request .= '<Data><xop:Include xmlns:xop="http://www.w3.org/2004/08/xop/include" href="cid:'
            . MultiPartMessage::$URN_UUID_ATTACHMENT . '" /></Data>';
        $request .= '</fileAttachment>';
        $request .= '</uploadFileRequest>';

        return $request;
    }

    function uploadFile($taskReferenceId, $fileReferenceId, $zipPath)
    {
        $file = $this->readRequestFile($zipPath);
        $requestBody = $this->createUploadFileRequest($taskReferenceId, $fileReferenceId, strlen($file) );

        $request = MultiPartMessage::build($requestBody,$file);

        $responseXML = $this->merchantSession->sendFileTransferServiceUploadRequest($request);
        $xml = simplexml_load_string($responseXML);
        $createJobResponse = json_decode(json_encode($xml), true);

        return $createJobResponse;
    }

    function getJobsRequest()
    {
        $request  = '<getJobsRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<jobStatus>Created</jobStatus>';
        $request .= '<jobStatus>InProcess</jobStatus>';
        $request .= '</getJobsRequest>';
        return $request;
    }

    function getJobs()
    {
        $request = $this->getJobsRequest();
        $responseXML = $this->merchantSession->sendBulkDataExchangeRequest(
            'getJobs', $request);
        $xml = simplexml_load_string($responseXML);
        $jobsResponse = json_decode(json_encode($xml), true);

        return $jobsResponse;
    }

    function createGetJobStatusRequest($jobId)
    {
        $request  = '<getJobStatusRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<jobId>' . $jobId . '</jobId>';
        $request .= '</getJobStatusRequest>';

        return $request;
    }

    function getJobStatus($jobId)
    {
        $request = $this->createGetJobStatusRequest( $jobId );
        $responseXML = $this->merchantSession->sendBulkDataExchangeRequest(
            'getJobStatus', $request);

        $xml = simplexml_load_string($responseXML);
        $jobsResponse = json_decode(json_encode($xml), true);

        return $jobsResponse;
    }

    function createStartUploadJobRequest($jobId)
    {
        $request  = '<startUploadJobRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<jobId>' . $jobId . '</jobId>';
        $request .= '</startUploadJobRequest>';

        return $request;
    }

    function startUploadJob($jobId)
    {
        $request = $this->createGetJobStatusRequest( $jobId );
        $responseXML = $this->merchantSession->sendBulkDataExchangeRequest(
            'startUploadJob', $request);

        $xml = simplexml_load_string($responseXML);
        $jobsResponse = json_decode(json_encode($xml), true);

        return $jobsResponse;
    }

    function createStartJobRequest($uuid)
    {
        $request  = '<startDownloadJobRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<downloadJobType>ActiveInventoryReport</downloadJobType>';
        $request .= '<UUID>' . $uuid . '</UUID>';
        $request .= '</startDownloadJobRequest>';

        return $request;
    }

    function startDownloadJob($uuid)
    {
        $request = $this->createStartJobRequest( $uuid );
        $responseXML = $this->merchantSession->sendBulkDataExchangeRequest(
            'startDownloadJob', $request);

        $xml = simplexml_load_string($responseXML);
        $jobsResponse = json_decode(json_encode($xml), true);

        return $jobsResponse;
    }

    function createDownloadRequest($taskReferenceId, $fileReferenceId)
    {
        $request  = '<downloadFileRequest xmlns:sct="http://www.ebay.com/soaframework/common/types" xmlns="http://www.ebay.com/marketplace/services">';
        $request .= '<taskReferenceId>' . $taskReferenceId . '</taskReferenceId>';
        $request .= '<fileReferenceId>' . $fileReferenceId . '</fileReferenceId>';
        $request .= '</downloadFileRequest>';

        return $request;
    }

    /**
     * Parses for the Error Message in the MIME multipart message.
     * @param $response MIME multipart message
     * @return string XML Error Message Response
     */
    function parseForErrorMessage($response)
    {
        $beginErrorMessage = strpos($response, '<?xml');
        $endErrorMessage = strpos($response, '</errorMessage>', $beginErrorMessage);
        $endErrorMessage += strlen('</errorMessage>');

        return substr($response, $beginErrorMessage, $endErrorMessage - $beginErrorMessage);
    }

    /**
     * Parses for the XML Response in the MIME multipart message.
     * @param string $response MIME multipart message
     * @return string XML Response
     */
    function parseForResponseXML($response)
    {
        $beginResponseXML = strpos($response, '<?xml');

        $endResponseXML = strpos($response, '</downloadFileResponse>',
            $beginResponseXML);

        //Assume a service level error and die.
        if($endResponseXML === FALSE) {
            $errorXML = $this->parseForErrorMessage($response);
        }

        $endResponseXML += strlen('</downloadFileResponse>');

        return substr($response, $beginResponseXML,
            $endResponseXML - $beginResponseXML);
    }

    /**
     * Parses for the file bytes between the MIME boundaries.
     * @param $uuid UUID corresponding to the Content-ID of the file bytes.
     * @param string $response MIME multipart message
     * @return string bytes of the file
     */
    function parseForFileBytes($uuid, $response)
    {
        $contentId = 'Content-ID: <' . $uuid . '>';

        $mimeBoundaryPart = strpos($response,'--MIMEBoundaryurn_uuid_');

        $beginFile = strpos($response, $contentId, $mimeBoundaryPart);
        $beginFile += strlen($contentId);

        //accounts for the standard 2 CRLFs.
        $beginFile += 4;

        $endFile = strpos($response,'--MIMEBoundaryurn_uuid_',$beginFile);

        //accounts for the standard 1 CRLFs.
        $endFile -= 2;

        $fileBytes = substr($response, $beginFile, $endFile - $beginFile);

        return $fileBytes;
    }

    /**
     * Parses the XML Response for the UUID to ascertain the
     * index of the file bytes in the MIME Message.
     * @param DomDocument $responseDOM DOM of the XML Response.
     * @return string UUID referring to the message body
     */
    function parseForXopIncludeUUID($responseDOM)
    {
        $xopInclude = $responseDOM->getElementsByTagName('Include')->item(0);
        $uuid = $xopInclude->getAttributeNode('href')->nodeValue;
        $uuid = substr($uuid, strpos($uuid,'urn:uuid:'));

        return $uuid;
    }

    /**
     * Writes the response file's bytes to disk.
     * @param string $bytes bytes comprising a file
     * @param string $zipFilename name of the zip to be created
     */
    function writeZipFile($bytes, $zipFilename)
    {
        $handler = fopen($zipFilename, 'wb');
        fwrite($handler, $bytes);
        fclose($handler);

    }

    function startDownloadFile($jobId, $fileReferenceId, $feedFilePath = null)
    {
        $downloadFilePath = 'DownloadReportFile_'.strtotime(date("Y-m-d h:i:sa")).'.zip';
        if($feedFilePath != null) {
            $feedFilePathInfo = pathinfo($feedFilePath);
            if(isset($feedFilePathInfo['dirname']) && isset($feedFilePathInfo['filename']))
                $downloadFilePath = $feedFilePathInfo['dirname'].'/Download_'.$feedFilePathInfo['filename'].'.gz';
        }
        $request = $this->createDownloadRequest($jobId, $fileReferenceId);
        $response = $this->merchantSession->sendFileTransferServiceDownloadRequest($request);
        $responseXML = $this->parseForResponseXML($response);

        $xml = simplexml_load_string($responseXML);
        $jobsResponse = json_decode(json_encode($xml), true);

        if(isset($jobsResponse['ack']) && $jobsResponse['ack'] == 'Success') {
            $responseDOM = DOMUtils::createDOM($responseXML);
            $uuid = $this->parseForXopIncludeUUID($responseDOM);
            $fileBytes = $this->parseForFileBytes($uuid, $response);
            $this->writeZipFile($fileBytes, $downloadFilePath);
            return $downloadFilePath;
        } else {
            return $jobsResponse;
        }
        return false;
    }
}

?>

