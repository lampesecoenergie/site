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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class Fetchotherdetails
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Account
 */
class Fetchotherdetails extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @var \Ced\EbayMultiAccount\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Ced\EbayMultiAccount\Helper\Logger
     */
    public $logger;
    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    public $multiAccountHelper;

    /**
     * @var \Ced\EbayMultiAccount\Model\Config\Location
     */
    public $location;
    /**
     * @var Filesystem
     */
    public $filesystem;
    /**
     * @var Filesystem\Io\File
     */
    public $file;


    /**
     * Fetchotherdetails constructor.
     * @param Action\Context $context
     * @param \Ced\EbayMultiAccount\Helper\Data $dataHelper
     * @param \Ced\EbayMultiAccount\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\EbayMultiAccount\Helper\Data $dataHelper,
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Ced\EbayMultiAccount\Model\Config\Location $location,
        Filesystem $filesystem,
        Filesystem\Io\File $file,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->location = $location;
        $this->filesystem = $filesystem;
        $this->file = $file;        
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $success = $error = [];
            $data = $this->getRequest()->getParams();
            if (isset($data['id'])) {
                $account = $this->multiAccountHelper->getAccountRegistry($data['id']);
                $this->dataHelper->updateAccountVariable();
            }
            $locationList = $this->location->toOptionArray();
            foreach ($locationList as $value) {
                if ($value['value'] == $account->getAccountLocation()) {
                    $locationName = $value['label'];
                }
            }

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $folderPath = $mediaDirectory->getAbsolutePath('ced/ebaymultiaccount/');
            if (!file_exists($folderPath . $locationName)) {
                $this->file->mkdir($folderPath . $locationName, 0777, true);
            }

            $path = $folderPath . $locationName . '/payment-methods.json';
            if (!file_exists($path)) {
                $getPayMethods = $this->dataHelper->getSiteSpecificPaymentMethods();
                if ($getPayMethods != 'error') {                
                    $file = fopen($path, "w");
                    if (!fwrite($file, $getPayMethods)) {
                        $error[] = 'payment methods';
                    } else {
                        $success[] = 'payment methods';
                    }
                    fclose($file);
                } else {
                    $error[] = 'payment methods';
                }
            } else {
                $success[] = 'payment methods';
            }
            

            // fetch return policy
            $path = $folderPath . $locationName . '/returnPolicy.json';
            if (!file_exists($path)) {
                $getReturnPolicy = $this->dataHelper->getSiteSpecificReturnPolicy();
                if ($getReturnPolicy != 'error') {
                    
                    $file = fopen($path, "w");
                    if (!fwrite($file, $getReturnPolicy)) {
                        $error[] = 'return policy';
                    } else {
                        $success[] = 'return policy';
                    }
                    fclose($file);
                } else {
                    $error[] = 'return policy';
                }
            } else {
                $success[] = 'return policy';
            }

            // fetch shipping details
            $path = $folderPath . $locationName . '/shippingDetails.json';
            if (!file_exists($path)) {
                $getShippingDetails = $this->dataHelper->getSiteSpecificShippingDetails();
                if ($getShippingDetails != 'error') {
                    
                    $file = fopen($path, "w");
                    if (!fwrite($file, $getShippingDetails)) {
                        $error[] = 'shipping details';
                    } else {
                        $success[] = 'shipping details';
                    }
                    fclose($file);
                } else {
                    $error[] = 'shipping details';
                }
            } else {
                $success[] = 'shipping details';
            }


            // fetch Excluded Location
            $path = $folderPath . $locationName . '/ExcludedLocations.json';
            if (!file_exists($path)) {
                $excludedLocation = $this->dataHelper->getSiteSpecificExcludedLocations();
                if ($excludedLocation != 'error') {
                    
                    $file = fopen($path, "w");
                    if (!fwrite($file, $excludedLocation)) {
                        $error[] = 'excluded locations';
                    } else {
                        $success[] = 'excluded locations';
                    }
                    fclose($file);
                } else {
                    $error[] = 'excluded locations';
                }
            } else {
                $success[] = 'excluded locations';
            }

            // Currency And Country
            $path = $folderPath . '/country-currency.json';
            if (!file_exists($path)) {
                $getCountryAndCountry = $this->dataHelper->getCountryAndCountry();
                if ($getCountryAndCountry != 'error') {
                    $file = fopen($path, "w");
                    if (!fwrite($file, $getCountryAndCountry)) {
                        $error[] = 'country and currency';
                    } else {
                        $success[] = 'country and currency';
                    }
                    fclose($file);
                } else {
                    $error[] = 'country and currency';
                }
            } else {
                $success[] = 'country and currency';
            }

            //fetch category
            $levels = [1, 2, 3, 4, 5, 6];
            foreach ($levels as $level) {
                $path = $folderPath . $locationName . '/categoryLevel-' . $level . '.json';
                if (!file_exists($path)) {
                    $getCat = $this->dataHelper->getCategories($level);
                    if ($getCat != "error") {
                        $file = fopen($path, "w");
                        $pieces = str_split(json_encode($getCat), 1024);
                        foreach ($pieces as $piece) {
                            if (!fwrite($file, $piece, strlen($piece))) {
                                $error[] = $level.' level category';
                            }
                        }
                        fclose($file);
                    } else {
                        $error[] = $level.' level category';
                    }
                }
            }
        } catch (\Exception $e) {
            $error[] = $e->getMessage();
            $this->logger->addError('In Fetch Other Details Call: '.$e->getMessage(), ['path' => __METHOD__]);
        }
        if (!empty($error)) {
            $this->messageManager->addErrorMessage('error while fetching '.implode(', ', $error));
        }
        if (!empty($success)) {
            $this->messageManager->addSuccessMessage(implode(', ', $success).'fetched successfully');
        }     
        $this->_redirect('ebaymultiaccount/account/index');
    }
}