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
require_once(__DIR__ . '../../ebaymultiaccount/LargeMerchantServicesPHPSamples/application/InitFunctions.php');

use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\Session;

/**
 * Class FileUpload
 * @package Ced\EbayMultiAccount\Helper
 */
class FileUpload extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * Date/Time
     * @var $dateTime
     */
    public $dateTime;

    /**
     * File Manager
     * @var $fileIo
     */
    public $fileIo;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  */
    public $prodCollection;

    /** @var \Ced\EbayMultiAccount\Model\Profile  */
    public $profileCollection;

    /** @var \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler  */
    public $jobSchedulerModel;

    /** @var \Ced\EbayMultiAccount\Model\FeedDetails  */
    public $jobFeedModel;

    /** @var Data \Ced\EbayMultiAccount\Helper\Data */
    public $dataHelper;

    /** @var Data \Ced\EbayMultiAccount\Helper\EbayMultiAccount */
    public $ebaymultiaccountHelper;

    /** @var \InitFunctions $fileUploadFunctions */
    public $fileUploadFunctions;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var mixed
     */
    public $adminSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $prodCollection,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Ced\EbayMultiAccount\Model\ResourceModel\Profile\Collection $profileResource,
        \Ced\EbayMultiAccount\Helper\Data $dataHelper,
        \Ced\EbayMultiAccount\Helper\EbayMultiAccount $ebaymultiaccountHelper,
        \Ced\EbayMultiAccount\Model\JobSchedulerFactory $jobScheduler,
        \Ced\EbayMultiAccount\Model\FeedDetails $jobFeedModel,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry,
        Session $session
    )
    {
        parent::__construct($context);
        $this->prodCollection = $prodCollection;
        $this->_productloader = $_productloader;
        $this->profileCollection = $profileResource;
        $this->jobSchedulerModel = $jobScheduler;
        $this->jobFeedModel = $jobFeedModel;
        $this->dataHelper = $dataHelper;
        $this->ebaymultiaccountHelper = $ebaymultiaccountHelper;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->adminSession = $session;
        $this->_coreRegistry = $registry;
        $account = false;
        if ($this->_coreRegistry->registry('ebay_account')) {
            $account = $this->_coreRegistry->registry('ebay_account');
        }
        $this->env = ($account) ? trim($account->getAccountEnv()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/environment');
        $this->token = ($account) ? trim($account->getAccountToken()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/token');
        $this->siteID = ($account) ? trim($account->getAccountLocation()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/location');
        $this->compatLevel = 989;
        $this->timestamp = (string)$this->dateTime->gmtTimestamp();
        $this->fileUploadFunctions = new \InitFunctions('XML', 'XML', $this->env, $this->token);
    }

    public function updateAccountVariable() {
        $account = false;
        if ($this->_coreRegistry->registry('ebay_account')) {
            $account = $this->_coreRegistry->registry('ebay_account');
        }
        $this->env = ($account) ? trim($account->getAccountEnv()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/environment');
        $this->token = ($account) ? trim($account->getAccountToken()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/token');
        $this->siteID = ($account) ? trim($account->getAccountLocation()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/location');
        $this->fileUploadFunctions = new \InitFunctions('XML', 'XML', $this->env, $this->token);
    }

    public function getAllAssignedProductCollection()
    {
        $productIdsToSchedule = $accountChunks = array();
        $accounts = $this->multiAccountHelper->getAllAccounts();
        foreach ($accounts as $account) {
            $arrKeys = [];
            $accountId = $account->getId();
            $storeId = $account->getAccountStore();
            $profileAccAttr = $this->multiAccountHelper->getProfileAttrForAcc($account->getId());
            $activeProfileIds = $this->profileCollection
                ->addFieldToFilter('profile_status', 1)
                ->getColumnValues('id');
            $collection = $this->prodCollection->create()
                ->addAttributeToSelect($profileAccAttr);

            $prodIds = $collection
                ->setStoreId($storeId)
                ->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable')))
                ->addAttributeToFilter('visibility', 4)
                ->addAttributeToFilter($profileAccAttr, array('notnull' => true))
                ->addAttributeToFilter($profileAccAttr, array('in' => $activeProfileIds))
                ->getColumnValues('entity_id');

            $prodIdsChunks = array_chunk($prodIds, 100);
            $productIdsToSchedule = array_merge($productIdsToSchedule, $prodIdsChunks);
            $accountChunks[$accountId]['start_index'] = count($productIdsToSchedule) - count($prodIdsChunks);
            $arrKeys = array_keys($productIdsToSchedule);
            $accountChunks[$accountId]['end_index'] = end($arrKeys);
        }
        $this->adminSession->setAccountIndexes($accountChunks);
        return $productIdsToSchedule;
    }

    public function createSchedulerForIds($collectionIds = array(), $accountId = null)
    {
        try {
            $configCollection = $this->prodCollection->create()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('entity_id', $collectionIds)
                ->addAttributeToFilter('type_id', array('configurable'));
            $configIds = array_column($configCollection->getData(), 'entity_id');
            $configIds = array_chunk($configIds, 1000);
            foreach ($configIds as $ids) {
                $idstring = implode(',', $ids);
                /** @var \Ced\EbayMultiAccount\Model\JobScheduler $scheduler */
                $scheduler = $this->jobSchedulerModel->create();
                $scheduler->setProductIds($idstring);
                $scheduler->setCronStatus('scheduled');
                $scheduler->setAccountId($accountId);
                $scheduler->setSchedulerType($scheduler::ADDFIXEDPRICEITEM);
                $scheduler->save();
            }

            $simpleCollection = $this->prodCollection->create()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('entity_id', $collectionIds)
                ->addAttributeToFilter('type_id', array('simple'));
            $simpleIds = array_column($simpleCollection->getData(), 'entity_id');
            $simpleIds = array_chunk($simpleIds, 1000);
            foreach ($simpleIds as $ids) {
                $idstring = implode(',', $ids);
                /** @var \Ced\EbayMultiAccount\Model\JobScheduler $scheduler */
                $scheduler = $this->jobSchedulerModel->create();
                $scheduler->setProductIds($idstring);
                $scheduler->setCronStatus('scheduled');
                $scheduler->setAccountId($accountId);
                $scheduler->setSchedulerType($scheduler::ADDITEM);
                $scheduler->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createSchedulerForIdsWithAction($collectionIds = array(), $actionType = 'ReviseItem', $accountId = null)
    {
        try {
            $prodIds = array_chunk($collectionIds, 1000);
            foreach ($prodIds as $ids) {
                $idstring = implode(',', $ids);
                /** @var \Ced\EbayMultiAccount\Model\JobScheduler $scheduler */
                $scheduler = $this->jobSchedulerModel->create();
                $scheduler->setProductIds($idstring);
                $scheduler->setCronStatus('scheduled');
                $scheduler->setAccountId($accountId);
                $scheduler->setSchedulerType($actionType);
                $scheduler->save();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Prepare File for Bulk Upload
     * @param array $ids
     * @return bool|mixed|string
     */
    public function prepareUploadFile($ids, $variable = 'AddItem', $accountId = null)
    {
        try {
            $finalData = null;
            if ($this->_coreRegistry->registry('ebay_account'))
                $this->_coreRegistry->unregister('ebay_account');
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $this->updateAccountVariable();
            $this->dataHelper->updateAccountVariable();
            $this->ebaymultiaccountHelper->updateAccountVariable();
            foreach ($ids as $prodId) {
                $itemData = $this->ebaymultiaccountHelper->prepareData($prodId);
                $requestVariable = $variable . 'Request';
                if ($itemData['type'] == 'success') {
                    $finalXml = str_replace('<?xml version="1.0"?>', '', $itemData['data']);
                    $xmlHeader = $this->prepareBulkFileHeader($variable);
                    $xmlFooter = "</$requestVariable>";
                    $finalData .= $xmlHeader . $finalXml . $xmlFooter;
                }
            }
            if (isset($finalData) && is_string($finalData) && $finalData != null) {
                $path = $this->createDir('ebaymultiaccount/' . $variable, 'media');
                $path = $path['path'] . '/' . $variable . '_' . $this->timestamp . '.xml';
                $handle = fopen($path, 'w');
                $finalData = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
                    $finalData);
                fwrite($handle, htmlspecialchars_decode($finalData));
                fclose($handle);
                $createUploadJobRes = array('feed_file' => $path);
                return $createUploadJobRes;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Update Price On EbayMultiAccount
     * //$timeStamp = (string)$this->dateTime->gmtDate('Y-m-j\TH:m:s\Z');
     * @param string|[] $ids
     * @return bool
     */
    public function prepareInventoryUpdateFile($ids = null, $variable = 'ReviseInventoryStatus', $accountId = null)
    {
        try {
            $finalData = null;
            if ($this->_coreRegistry->registry('ebay_account'))
                $this->_coreRegistry->unregister('ebay_account');
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $this->dataHelper->updateAccountVariable();
            $this->ebaymultiaccountHelper->updateAccountVariable();
            foreach ($ids as $prodId) {
                $itemData = $this->ebaymultiaccountHelper->getInventoryPrice($prodId);
                $requestVariable = $variable . 'Request';
                if ($itemData['type'] == 'success') {
                    $finalXml = str_replace('<?xml version="1.0"?>', '', $itemData['data']);
                    $xmlHeader = $this->prepareBulkFileHeader($variable);
                    $xmlFooter = "</$requestVariable>";
                    $finalData .= $xmlHeader . $finalXml . $xmlFooter;
                }
            }

            if (isset($finalData) && is_string($finalData) && $finalData != null) {
                $path = $this->createDir('ebaymultiaccount/' . $variable, 'media');
                $path = $path['path'] . '/' . $variable . '_' . $this->timestamp . '.xml';
                $handle = fopen($path, 'w');
                $finalData = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
                    $finalData);
                fwrite($handle, htmlspecialchars_decode($finalData));
                fclose($handle);
                $createUploadJobRes = array('feed_file' => $path);
                return $createUploadJobRes;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    public function uploadPreparedFile($jobSchedulerCollection)
    {
        $accountId = $jobSchedulerCollection->getFirstItem()->getAccountId();
        if ($this->_coreRegistry->registry('ebay_account'))
            $this->_coreRegistry->unregister('ebay_account');
        $account = $this->multiAccountHelper->getAccountRegistry($accountId);
        $accountCode = $account->getAccountCode();
        $this->updateAccountVariable();
        $this->dataHelper->updateAccountVariable();
        $this->ebaymultiaccountHelper->updateAccountVariable();
        $uploadFileRes = array();
        $productIds = null;
        $jobTypeWithIds = array();
        $jobsResponse = $this->fileUploadFunctions->getJobs();
        if (isset($jobsResponse['jobProfile'])) {
            $jobTypeWithIds = array_column($jobsResponse['jobProfile'], 'jobId', 'jobType');
        }
        $finalDataToUpload = null;
        $dataToMerge = array();
        $i = 0;
        foreach ($jobSchedulerCollection as $jobScheduler) {
            $mergedFeedFile = null;
            if ($i >= 5) {
                break;
            }
            $schedulerData = $this->jobSchedulerModel->create()->load($jobScheduler->getId());
            $feedFilePath = $jobScheduler->getFeedFilePath();
            $feedType = $jobScheduler->getSchedulerType();
            $productIds = $jobScheduler->getProductIds();
            if (isset($jobTypeWithIds[$feedType])) {
                $uploadFileRes['error'] = 'Job For ' . $feedType . '  for account ' . $accountCode . " already scheduled.";
                return $uploadFileRes;
            }
            $requestName = $feedType . 'Request';
            if (file_exists($feedFilePath)) {
                $feedFileContent = file_get_contents($feedFilePath);
                $mergedFeedFile = $this->createBulkDataExchangeFile($feedFileContent, $requestName);
                if($mergedFeedFile != null) {
                    $schedulerData->setCronStatus('file_processed');
                }
                $finalDataToUpload .= $mergedFeedFile;
            }
            $schedulerData->setThresholdLimit((int) $schedulerData->getThresholdLimit() + 1);
            $schedulerData->save();
        }
        if (isset($finalDataToUpload) && is_string($finalDataToUpload) && $finalDataToUpload != null) {
            $finalDataToUpload = '<?xml version="1.0" encoding="UTF-8"?>' . $finalDataToUpload;
            $createUploadJobRes = $this->fileUploadFunctions->createUploadJob($feedType, $this->timestamp);
            $parsedResponse = $this->parseResponseForErrors($createUploadJobRes);
            /*$createUploadJobRes['ack'] = 'Success';
            $createUploadJobRes['jobId'] = '50023325718';
            $createUploadJobRes['fileReferenceId'] = '50022452018';*/
            if (isset($createUploadJobRes['ack']) && $createUploadJobRes['ack'] == 'Success') {
                $path = $this->createDir('ebaymultiaccount/' . $feedType, 'media');
                $path = $path['path'] . '/' . 'Final_' . $feedType . '_' . $this->timestamp . '.xml';
                $handle = fopen($path, 'w');
                $finalXml = $finalDataToUpload;
                $finalXml = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
                    $finalXml);
                fwrite($handle, htmlspecialchars_decode($finalXml));
                $gzPath = $this->createGzFile($finalXml, $path);
                $parsedResponse = $this->prepareFeedCollection($createUploadJobRes, $gzPath, $feedType, $productIds);
                if (isset($parsedResponse['success']) && $parsedResponse['success'] == true) {
                    $uploadFileRes = $this->fileUploadFunctions->uploadFile($createUploadJobRes['jobId'], $createUploadJobRes['fileReferenceId'], $gzPath);
                    if (isset($parsedResponse['feed_id'])) {
                        $feedModel =$this->jobFeedModel->load($parsedResponse['feed_id']);
                        $jobSavedResponse = $feedModel->getResponse();
                        $jobSavedResponse = $this->jsonHelper->jsonDecode($jobSavedResponse);
                        if (is_array($jobSavedResponse)) {
                            $jobSavedResponse['uploadFile'] = $uploadFileRes;
                        } elseif ($jobSavedResponse == null) {
                            $jobSavedResponse = array(
                                'uploadFile' => $uploadFileRes
                            );
                        }
                        $jobSavedResponse = $this->jsonHelper->jsonEncode($jobSavedResponse);
                        $feedModel->setResponse($jobSavedResponse)->save();
                    }
                    $parsedResponse = $this->parseResponseForErrors($uploadFileRes);
                }
            }
            return $parsedResponse;
        }
        return false;
    }

    public function createBulkDataExchangeFile($fileContent, $requestName)
    {
        $finalFileContent = '';
        $startPosition = strpos($fileContent, "<$requestName");
        $endPosition = strrpos($fileContent, "</$requestName>") - $startPosition;
        $fileContent = substr($fileContent, $startPosition, $endPosition + strlen("</$requestName>"));
        $finalFileContent .= '<BulkDataExchangeRequests>';
        $finalFileContent .= '<Header>';
        $finalFileContent .= '<SiteID>' . $this->siteID . '</SiteID>';
        $finalFileContent .= '<Version>' . $this->compatLevel . '</Version>';
        $finalFileContent .= '</Header>';
        $finalFileContent .= $fileContent;
        $finalFileContent .= '</BulkDataExchangeRequests>';
        return $finalFileContent;
    }

    public function startUploadJob($feedCollection)
    {
        $returnResponse = $errors = $parsedResponse = array();
        $hasError = false;
        if ($feedCollection->getSize() > 0) {
            foreach ($feedCollection as $feedData) {
                $jobId = $feedData->getJobId();
                $accountId = $feedData->getAccountId();
                if ($this->_coreRegistry->registry('ebay_account'))
                    $this->_coreRegistry->unregister('ebay_account');
                $account = $this->multiAccountHelper->getAccountRegistry($accountId);
                $this->updateAccountVariable();
                $this->dataHelper->updateAccountVariable();
                $this->ebaymultiaccountHelper->updateAccountVariable();
                if ($jobId != null) {
                    $startUploadResponse = $this->fileUploadFunctions->startUploadJob($jobId);
                    $feedModel = $this->jobFeedModel->load($feedData->getId());
                    if (isset($feedModel) && $feedModel->getId()) {
                        $jobSavedResponse = $feedModel->getResponse();
                        $jobSavedResponse = $this->jsonHelper->jsonDecode($jobSavedResponse);
                        if (is_array($jobSavedResponse)) {
                            $jobSavedResponse['startUploadJob'] = $startUploadResponse;
                        } elseif ($jobSavedResponse == null) {
                            $jobSavedResponse = array(
                                'startUploadJob' => $startUploadResponse
                            );
                        }
                        $jobSavedResponse = $this->jsonHelper->jsonEncode($jobSavedResponse);
                        $feedModel->setResponse($jobSavedResponse)->save();
                    }
                    $parsedResponse = $this->parseResponseForErrors($startUploadResponse);
                    if (isset($parsedResponse['error'])) {
                        $hasError = true;
                        $errors[] = 'Job Id : ' . $jobId . ' has error(s) : ' . $parsedResponse['error'];
                    }
                }
            }
        }
        if ($hasError) {
            $returnResponse['error'] = implode(', ', $errors);
        } else {
            $returnResponse['success'] = true;
        }
        return $returnResponse;
    }

    public function syncJobs($feedCollection)
    {
        $returnResponse = $errors = $parsedResponse = array();
        $hasError = false;
        if ($feedCollection->getSize() > 0) {
            foreach ($feedCollection as $feedData) {
                $feedData = $this->jobFeedModel->load($feedData->getId());
                $jobId = $feedData->getJobId();
                $accountId = $feedData->getAccountId();
                if ($this->_coreRegistry->registry('ebay_account'))
                    $this->_coreRegistry->unregister('ebay_account');
                $account = $this->multiAccountHelper->getAccountRegistry($accountId);
                $this->updateAccountVariable();
                $this->dataHelper->updateAccountVariable();
                $this->ebaymultiaccountHelper->updateAccountVariable();
                if ($jobId != null) {
                    $jobStatusResponse = $this->fileUploadFunctions->getJobStatus($jobId);
                    $parsedResponse = $this->parseResponseForErrors($jobStatusResponse);
                    if (isset($parsedResponse['success']) && isset($jobStatusResponse['jobProfile']['jobStatus'])) {
                        $compTime = $startTime = null;
                        $completePercentage = 0;
                        $feedData->setJobStatus($jobStatusResponse['jobProfile']['jobStatus']);
                        $completePercentage = (isset($jobStatusResponse['jobProfile']['percentComplete'])) ? $jobStatusResponse['jobProfile']['percentComplete'] : $completePercentage;
                        $feedData->setJobCompletePercentage($completePercentage);
                        $startTime = (isset($jobStatusResponse['jobProfile']['startTime'])) ? $jobStatusResponse['jobProfile']['startTime'] : '';
                        $feedData->setStartTime($startTime);
                        $compTime = (isset($jobStatusResponse['jobProfile']['completionTime'])) ? $jobStatusResponse['jobProfile']['completionTime'] : '';
                        $feedData->setCompletionTime($compTime);
                        $jobSavedResponse = $feedData->getResponse();
                        $jobSavedResponse = $this->jsonHelper->jsonDecode($jobSavedResponse);
                        if (is_array($jobSavedResponse)) {
                            $jobSavedResponse['getJobStatus'] = $jobStatusResponse;
                        } elseif ($jobSavedResponse == null) {
                            $jobSavedResponse = array(
                                'getJobStatus' => $jobStatusResponse
                            );
                        }
                        $jobSavedResponse = $this->jsonHelper->jsonEncode($jobSavedResponse);
                        $feedData->setResponse($jobSavedResponse);
                        $reportFileResferenceId = (isset($jobStatusResponse['jobProfile']['fileReferenceId'])) ? $jobStatusResponse['jobProfile']['fileReferenceId'] : '';
                        $feedData->setReportFileReferenceId($reportFileResferenceId);
                    } elseif (isset($parsedResponse['error'])) {
                        $hasError = true;
                        $errors[] = 'Job Id : ' . $jobId . ' has error(s) : ' . $parsedResponse['error'];
                    }
                }
                $feedData->setThresholdLimit((int) $feedData->getThresholdLimit() + 1);
                $feedData->save();
            }
        }
        if ($hasError) {
            $returnResponse['error'] = implode(', ', $errors);
        } else {
            $returnResponse['success'] = true;
        }
        return $returnResponse;
    }

    public function startDownloadFile($feedCollection)
    {
        $returnResponse = $errors = $parsedResponse = array();
        $hasError = false;
        if ($feedCollection->getSize() > 0) {
            foreach ($feedCollection as $feedData) {
                $parsedResponse = $this->downloadSingleReportFile($feedData);
                if (isset($parsedResponse['error'])) {
                    $hasError = true;
                    $errors[] = 'Job Id : ' . $feedData->getJobId() . ' has error(s) : ' . $parsedResponse['error'];
                }
            }
        }
        if ($hasError) {
            $returnResponse['error'] = implode(', ', $errors);
        } else {
            $returnResponse['success'] = true;
        }
        return $returnResponse;
    }

    public function downloadSingleReportFile($feedData) {
        if($feedData->getId() > 0) {
            $jobId = $feedData->getJobId();
            $reportFileReferenceId = $feedData->getReportFileReferenceId();
            $feedFilePath = $feedData->getFeedFilePath();
            $accountId = $feedData->getAccountId();
            if ($this->_coreRegistry->registry('ebay_account'))
                $this->_coreRegistry->unregister('ebay_account');
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $this->updateAccountVariable();
            $this->dataHelper->updateAccountVariable();
            $this->ebaymultiaccountHelper->updateAccountVariable();
            if ($jobId != null && $reportFileReferenceId != '') {
                $startDownloadResponse = $this->fileUploadFunctions->startDownloadFile($jobId, $reportFileReferenceId, $feedFilePath);
                $feedModel = $this->jobFeedModel->load($feedData->getId());
                if (isset($feedModel) && $feedModel->getId()) {
                    $jobSavedResponse = $feedModel->getResponse();
                    $jobSavedResponse = $this->jsonHelper->jsonDecode($jobSavedResponse);
                    if (is_array($jobSavedResponse)) {
                        $jobSavedResponse['startDownloadJob'] = $startDownloadResponse;
                    } elseif ($jobSavedResponse == null) {
                        $jobSavedResponse = array(
                            'startDownloadJob' => $startDownloadResponse
                        );
                    }
                    $jobSavedResponse = $this->jsonHelper->jsonEncode($jobSavedResponse);
                    if (is_string($startDownloadResponse) && $startDownloadResponse != null && file_exists($startDownloadResponse)) {
                        $feedModel->setReportFeedFilePath($startDownloadResponse);
                    }
                    $feedModel->setResponse($jobSavedResponse)->save();
                }
                $parsedResponse = $this->parseResponseForErrors($startDownloadResponse);
                return $parsedResponse;
            }
        }
        return false;
    }

    public function processReportFile($ebaymultiaccountFeedIdWithProduct)
    {
        $feedProcessed = false;
        foreach ($ebaymultiaccountFeedIdWithProduct as $feedId => $productIds) {
            $feedData = $this->jobFeedModel->load($feedId);
            try {
                if (sizeof($feedData) > 0) {
                    $productIds = is_string($productIds) ? explode(",", $productIds) : array();
                    $reportFilePath = $feedData->getReportFeedFilePath();
                    $accountId = $feedData->getAccountId();
                    if ($this->_coreRegistry->registry('ebay_account'))
                        $this->_coreRegistry->unregister('ebay_account');
                    $account = $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->updateAccountVariable();
                    $this->dataHelper->updateAccountVariable();
                    $this->ebaymultiaccountHelper->updateAccountVariable();
                    if (is_string($reportFilePath) && $reportFilePath != null && file_exists($reportFilePath)) {
                        $feedProcessed = $this->setProductResponse($productIds, $reportFilePath);
                        $unprocessedProdIds = $feedData->getUnprocessedProductIds();
                        if($unprocessedProdIds == null && is_array($feedProcessed)) {
                            $unprocessedProdIds = array_diff($feedProcessed, $productIds);
                            $feedData->setUnprocessedProductIds(implode(',', $unprocessedProdIds));
                        } elseif($unprocessedProdIds != null && is_array($feedProcessed)) {
                            $unprocessedProdIds = explode(',', $unprocessedProdIds);
                            $unprocessedProdIds = array_diff($unprocessedProdIds, $productIds);
                            $feedData->setUnprocessedProductIds(implode(',', $unprocessedProdIds));
                        }
                        if(is_array($unprocessedProdIds) && count($unprocessedProdIds) <= 0) {
                            $feedData->setJobStatus('Processed');
                        } elseif(is_array($unprocessedProdIds) && count($unprocessedProdIds) > 0) {
                            $feedData->setJobStatus('Processing');
                        }
                    }
                }
                $feedData->setThresholdLimit((int) $feedData->getThresholdLimit() + 1);
                $feedData->save();
            } catch (\Exception $e) {
                return false;
            }
        }
        return $feedProcessed;
    }

    public function setProductResponse($productIds, $reportFilePath)
    {
        try {
            $target = '';
            $file_to_open = $reportFilePath;
            if ($file_to_open != null) {
                $feedFilePathInfo = pathinfo($file_to_open);
                if (isset($feedFilePathInfo['dirname']) && isset($feedFilePathInfo['filename']))
                    $target = $feedFilePathInfo['dirname'] . '/' . $feedFilePathInfo['filename'];
            }
            $zip = new \ZipArchive();
            $x = $zip->open($file_to_open);
            if ($x === true) {
                $zip->extractTo($target);
                $zip->close();
            } else {
                return false;
            }
            $files = array_diff(scandir($target), array('.', '..'));
            if (is_array($files) && count($files) > 0) {
                $corrIds = array();
                $ebayAccount = $this->_coreRegistry->registry('ebay_account');
                $accountId = $ebayAccount->getId();
                $accountCode = $ebayAccount->getAccountCode();
                $itemIdAttr = $this->multiAccountHelper->getProfileAttrForAcc($accountId);
                $prodStatusAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
                foreach ($files as $file) {
                    $responseFilePath = $target . '/' . $file;
                    if (file_exists($responseFilePath)) {
                        $responseFileData = file_get_contents($responseFilePath);
                        $responseFileData = $this->ParseResponse($responseFileData);
                        if (is_array($responseFileData) && count($responseFileData) > 0) {
                            reset($responseFileData);
                            $firstKey = key($responseFileData);
                            if (isset($responseFileData[$firstKey]) && !isset($responseFileData[$firstKey][0])) {
                                $responseFileData[$firstKey] = array($responseFileData[$firstKey]);
                            }
                            $corrIds = array_column($responseFileData[$firstKey], 'CorrelationID');
                            foreach ($responseFileData[$firstKey] as $responseItem) {
                                $corID = (isset($responseItem['CorrelationID'])) ? $responseItem['CorrelationID'] : '';
                                if(!in_array($corID, $productIds)) {
                                    continue;
                                }
                                $product = $this->_productloader->create()->load($corID);
                                if ($product) {
                                    $prodFeedResponse = $this->jsonHelper->jsonEncode($responseItem);
                                    if (isset($responseItem['Ack']) && !empty($responseItem['Ack']) && ($responseItem['Ack'] == 'Success' || $responseItem['Ack'] == 'Warning')) {
                                        $ebaymultiaccountItemID = (isset($responseItem['ItemID'])) ? $responseItem['ItemID'] : $product->getData($itemIdAttr);
                                        $feedResponse = $this->jsonHelper->jsonDecode($product->getEbayMultiAccountFeedResponse());
                                        $feedResponse[$accountCode] = $prodFeedResponse;
                                        $prodFeedResponse = $this->jsonHelper->jsonEncode($feedResponse);
                                        $product->setData($itemIdAttr, $ebaymultiaccountItemID)->setData($prodStatusAttr, 'uploaded')->setEbayMultiAccountFeedResponse($prodFeedResponse);
                                        $product->getResource()->saveAttribute($product, $prodStatusAttr)->saveAttribute($product, $itemIdAttr)->saveAttribute($product, 'ebaymultiaccount_feed_response');
                                    } else {
                                        $feedResponse = $this->jsonHelper->jsonDecode($product->getEbayMultiAccountFeedResponse());
                                        $feedResponse[$accountCode] = $prodFeedResponse;
                                        $prodFeedResponse = $this->jsonHelper->jsonEncode($feedResponse);
                                        $product->setEbayMultiAccountFeedResponse($prodFeedResponse)->setData($prodStatusAttr, 'invalid');
                                        $product->getResource()->saveAttribute($product, 'ebaymultiaccount_feed_response')->saveAttribute($product, $prodStatusAttr);
                                    }
                                }
                            }
                        }
                    }
                }
                return $corrIds;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function parseResponseForErrors($jobResponse)
    {
        try {
            $errors = $responseStatus = array();
            if (isset($jobResponse['ack']) && ($jobResponse['ack'] == 'Success' || $jobResponse['ack'] == 'Warning')) {
                $responseStatus['success'] = true;
            } elseif (isset($jobResponse['ack']) && $jobResponse['ack'] == 'Failure') {
                if (isset($jobResponse['errorMessage']['error']) && !isset($jobResponse['errorMessage']['error'][0])) {
                    $jobResponse['errorMessage']['error'] = array($jobResponse['errorMessage']['error']);
                }
                if (isset($jobResponse['errorMessage']['error'])) {
                    foreach ($jobResponse['errorMessage']['error'] as $error) {
                        $errors[] = isset($error['message']) ? $error['message'] : '';
                    }
                    $responseStatus['error'] = implode(', ', $errors);
                }
            }
            return $responseStatus;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createGzFile($fileContent = null, $path = null)
    {
        $gzdata = gzencode($fileContent, 9);
        $path = str_replace('xml', 'gz', $path);
        $fp = fopen($path, "w");
        fwrite($fp, $gzdata);
        fclose($fp);
        return $path;
    }

    public function prepareFeedCollection($createUploadJobResponse = null, $filePath = null, $feedType = null, $productIds = null)
    {
        try {
            $returnData = array();
            $ebayAccount = $this->_coreRegistry->registry('ebay_account');
            $accountId = $ebayAccount->getId();
            /** @var \Ced\EbayMultiAccount\Model\FeedDetails  */
            $feedModel = $this->jobFeedModel;
            $response = array(
                'createUploadJob' => $createUploadJobResponse
            );
            $feedData = array(
                'job_id' => $createUploadJobResponse['jobId'],
                'job_reference_id' => $createUploadJobResponse['fileReferenceId'],
                'job_status' => 'Created',
                'job_type' => $feedType,
                'product_ids' => $productIds,
                'feed_file_path' => $filePath,
                'account_id' => $accountId,
                'response' => $this->jsonHelper->jsonEncode($response)
            );
            $feedModel->setData($feedData)->save();
            $returnData['success'] = true;
            $returnData['feed_id'] = $feedModel->getId();
            return $returnData;
        } catch (\Exception $e) {
            $returnData['error'] = $e->getMessage();
            return $returnData;
        }
    }

    public function prepareBulkFileHeader($value)
    {
        $xmlHeader = '';
        switch ($value) {
            case 'AddItem':
                $xmlHeader .= '
                            <AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'AddFixedPriceItem':
                $xmlHeader .= '
                            <AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'ReviseItem':
                $xmlHeader .= '
                            <ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'ReviseFixedPriceItem':
                $xmlHeader .= '
                            <ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'AddItems':
                $xmlHeader .= '
                            <AddItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'RelistItem':
                $xmlHeader .= '
                            <RelistItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'EndItem':
                $xmlHeader .= '
                            <EndItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'EndItems':
                $xmlHeader .= '
                            <EndItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'ReviseInventoryStatus':
                $xmlHeader .= '
                            <ReviseInventoryStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            default:
                break;

        }
        $xmlHeader .= '
                                    <Version>989</Version>
                                    <ErrorLanguage>en_US</ErrorLanguage>
                                    <WarningLevel>High</WarningLevel>';

        return $xmlHeader;
    }

    /**
     * Create fruugo directory in the specified root directory.
     * used for storing json/xml files to be synced.
     * @param string $name
     * @param string $code
     * @return array|string
     */
    public function createDir($name = 'ebaymultiaccount', $code='var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if (file_exists($path)) {
            return ['status' => true,'path' => $path, 'action' => 'dir_exists'];
        } else {
            try
            {
                $this->fileIo->mkdir($path, 0775, true);
                return  ['status' => true,'path' => $path,  'action' => 'dir_created'];
            }
            catch (\Exception $e){
                return $code . '/' . $name . "Directory Creation Failed.";
            }
        }
    }

    /**
     * @param $responseXml
     * @return mixed
     */
    public function ParseResponse($responseXml)
    {
        $sxe = new \SimpleXMLElement($responseXml);
        $response = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($sxe));
        return $response;
    }
}
