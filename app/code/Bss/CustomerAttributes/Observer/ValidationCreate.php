<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Observer;

use Magento\Framework\Event\Observer as Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Visitor Observer
 */
class ValidationCreate implements ObserverInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Framework\Session\SessionManagerFactory
     */
    protected $session;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * ValidationCreate constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SessionManagerFactory $session
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $urlManager
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerFactory $session,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlManager
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->actionFlag = $actionFlag;
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->urlManager = $urlManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $maxSizeMagento = \Magento\Customer\Model\Customer\DataProvider::MAX_FILE_SIZE;
        $files = $observer->getRequest()->getFiles();
        $attributeCollection = $this->helper->getUserDefinedAttributes();
        foreach ($files as $file => $value) {
            $name = explode('.', $value['name']);
            $name = end($name);
            $size = $value['size'];
            foreach ($attributeCollection as $attribute) {
                if ($file == $attribute->getAttributeCode() && $attribute->getFrontendInput()== 'file') {
                    $this->getRedirectCreate($attribute, $size, $observer, $maxSizeMagento, $name);
                }
            }
        }
        return $this;
    }

    /**
     * @param Attribute $attribute
     * @param int $size
     * @param Observer $observer
     * @param int $maxSizeMagento
     * @param string $name
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRedirectCreate($attribute, $size, $observer, $maxSizeMagento, $name)
    {
        $validateInputFile = $this->helper->getValueValidateFile($attribute);
        if ($size && $size > $maxSizeMagento) {
            $maxSizeMagento = floor($maxSizeMagento/1024);
            $this->messageManager->addErrorMessage(__($attribute->getStoreLabel($this->helper->getStoreId()) .
                ' : The file size should not exceed ' . $maxSizeMagento . 'Kb'));
            $this->redirectCreate($observer);
        } elseif (isset($validateInputFile['max_file_size']) && $size && $size > $validateInputFile['max_file_size']*1000) {
            $sizeKb = $validateInputFile['max_file_size'];
            $this->messageManager->addErrorMessage(__($attribute->getStoreLabel($this->helper->getStoreId()) .
                ' : The file size should not exceed ' . $sizeKb . 'Kb'));
            $this->redirectCreate($observer);
        }
        if (isset($validateInputFile['file_extensions'])) {
            $arrInputFile = explode(',', $validateInputFile['file_extensions']);
            if ($validateInputFile['file_extensions'] && $name && !in_array($name, $arrInputFile)) {
                $this->messageManager->addErrorMessage(__($attribute->getStoreLabel($this->helper->getStoreId()) .
                    ' : Allowed Input type are ' . $validateInputFile['file_extensions']));
                $this->redirectCreate($observer);
            }
        }
    }

    /**
     * @param Observer $observer
     */
    private function redirectCreate($observer)
    {
        $controller = $observer->getControllerAction();
        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        $this->session->create()->setCustomerFormData($controller->getRequest()->getPostValue());
        $url = $this->urlManager->getUrl('*/*/create', ['_nosecret' => true]);
        $controller->getResponse()->setRedirect($this->redirect->error($url));
    }
}
