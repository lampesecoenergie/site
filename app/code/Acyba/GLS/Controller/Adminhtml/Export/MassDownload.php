<?php

namespace Acyba\GLS\Controller\Adminhtml\Export;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Magento\Backend\App\Action\Context;
use \Magento\Ui\Component\MassAction\Filter;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Backend\Model\Auth\Session;
use \Magento\Framework\Filesystem;
use Acyba\GLS\Model\Export;

class MassDownload extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;
    /**
     * @var \Acyba\GLS\Model\Export $export
     */
    protected $export;


    protected $_session;

    protected $_fileSystem;

    /**
     * MassDownload constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param Export $export
     * @param Session $session
     * @param Filesystem $fileSystem
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        Export $export,
        Session $session,
        Filesystem $fileSystem
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->_fileFactory = $fileFactory;
        $this->export = $export;
        $this->_session = $session;
        $this->_fileSystem = $fileSystem;
    }

    /**
     * Download selected orders
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $csvData = $this->export->export($collection, true);

        if ($this->_session->isFirstPageAfterLogin()) {
            $this->_session->setIsFirstPageAfterLogin(false);
        }

        $fileName = 'GlsCmd_' . date('Ymdhis') . '.csv';

        $this->_fileFactory->create($fileName, $csvData, DirectoryList::TMP, 'text/csv');

        $tmpDirectory = $directory = $this->_fileSystem->getDirectoryWrite(
            DirectoryList::TMP
        );
        $tmpDirectory->delete($tmpDirectory->getRelativePath($fileName));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->redirectUrl);
    }
}
