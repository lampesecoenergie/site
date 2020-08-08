<?php

namespace Acyba\GLS\Controller\Adminhtml\Import;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Import extends \Magento\Backend\App\AbstractAction
{

    const URL_IMPORT_INDEX = 'gls/import';

    protected $scopeConfig;
    protected $_import;

    public function __construct(Context $context, ScopeConfigInterface $scopeConfig, \Acyba\GLS\Model\Import $import)
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->_import = $import;
    }

    /**
     * Load the page defined in view/adminhtml/layout/gls_import_index.xml
     *
     * @return mixed
     */
    public function execute()
    {
        $nbrImported = $this->_import->import();
        if ($nbrImported) {
            $this->messageManager->addSuccessMessage(
                $nbrImported . ' ' . __('Orders have been imported')
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('No orders to import in the folder ') . $this->scopeConfig->getValue('gls_section/gls_import_export/gls_import_folder')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(self::URL_IMPORT_INDEX);
    }
}
