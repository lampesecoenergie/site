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

namespace Ced\EbayMultiAccount\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ced\EbayMultiAccount\Helper\Data;
use Ced\EbayMultiAccount\Model\Config\Location;

class PaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var Filesystem
     */
    public $filesystem;
    /**
     * @var Data
     */
    public $data;
    /**
     * @var \Ced\EbayMultiAccount\Model\Config\Location
     */
    public $location;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Data $data,
        Location $location
    )
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->data = $data;
        $this->location = $location;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray($location=null)
    {
        $locationName = '';
        $result = $payments = [];
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('ced/ebaymultiaccount/');
        $locationList = $this->location->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        $path = $folderPath . $locationName . '/payment-methods.json';
        $payMethods = $this->data->loadFile($path, '', '');
        if ($payMethods) {
            $allMethods = [   'Visa/Mastercard' => 'VisaMC',
                'PrePayDelivery' => 'PrePayDelivery',
                'PostalTransfer' => 'PostalTransfer',
                'PersonalCheck' => 'PersonalCheck',
                'PayUponInvoice' => 'PayUponInvoice',
                'PayPalCredit' => 'PayPalCredit',
                'PayPal' => 'PayPal',
                'PayOnPickup' => 'PayOnPickup',
                'PaymentSeeDescription' => 'PaymentSeeDescription',
                'PaisaPayEscrowEMI' => 'PaisaPayEscrowEMI',
                'PaisaPayEscrow' => 'PaisaPayEscrow',
                'PaisaPay (for India site only)' => 'PaisaPayAccepted',
                'OtherOnlinePayments' => 'OtherOnlinePayments',
                'Other' => 'Other',
                'None' => 'None',
                'MoneyXferAcceptedInCheckout' => 'MoneyXferAcceptedInCheckout',
                'MoneyXferAccepted' => 'MoneyXferAccepted',
                'MOCC' => 'MOCC',
                'LoanCheck' => 'LoanCheck',
                'IntegratedMerchantCreditCard' => 'IntegratedMerchantCreditCard',
                'Escrow' => 'Escrow',
                'Discover' => 'Discover',
                'DirectDebit' => 'DirectDebit',
                'Diners' => 'Diners',
                'CustomCode' => 'CustomCode',
                'CreditCard' => 'CreditCard',
                'CODPrePayDelivery' => 'CODPrePayDelivery',
                'COD' => 'COD',
                'CCAccepted' => 'CCAccepted',
                'CashOnPickup' => 'CashOnPickup',
                'CashInPerson' => 'CashInPerson',
                'American Express' => 'AmEx'
            ];
            foreach ($payMethods as $payMethod) {
                if (is_array($payMethod)) {
                    foreach ($payMethod as $value) {
                        $payments[] = $value;
                    }
                } else {
                    $payments[] = $payMethod;
                }
            }
            $paymentMethods = array_intersect($allMethods, $payments);
            foreach ($paymentMethods as $key => $value) {
                $result[] = ['label' => $key, 'value' => $value];
            }
        }
        return $result;
    }
}
