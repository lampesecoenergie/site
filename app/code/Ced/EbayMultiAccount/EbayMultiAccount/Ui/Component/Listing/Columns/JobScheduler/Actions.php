<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Ui\Component\Listing\Columns\JobScheduler;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package Ced\EbayMultiAccount\Ui\Component\Listing\Columns\JobScheduler
 */
class Actions extends Column
{
    const URL_PATH_SYNC = 'ebaymultiaccount/jobscheduler/processReportFile';

    /**
     * @var UrlInterface
     */
    public $urlBuilder;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $file;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $dl;

    /**
     * Actions constructor.
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        $components = [],
        $data = []
    )
    {
        $this->file = $fileIo;
        $this->dl = $directoryList;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $feedFile = $item[$name];
                $feedType = $item['job_type'];
                $item[$name] = [];
                if (isset($item['id'])) {
                    if (is_string($feedFile) && $feedFile != null && @fopen($feedFile, 'r')) {
                        $item[$name]['download'] = [
                            'href' => $this->getFileLink($feedFile),
                            'download' => (isset($feedFile) and !empty($feedFile)) ? basename($feedFile) : '',
                            'label' => __('Download'),
                            'class' => 'cedcommerce actions download'
                        ];

                        if ($feedType == "AddItem" || $feedType == "AddFixedPriceItem") {
                            $item[$name]['sync'] = [
                                'href' => $this->urlBuilder->getUrl(self::URL_PATH_SYNC, ['id' => $item['id']]),
                                'label' => __('Process'),
                                'class' => 'cedcommerce actions sync'
                            ];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $filePath
     * @return string
     */
    public function getFileLink($filePath)
    {
        $url = '';
        if (isset($filePath) and !empty($filePath)) {
            $fileName = basename($filePath);
            $fileexplode = explode('media', $filePath);
            $subFilePath = (isset($fileexplode[1])) ? $fileexplode[1] : '';
            $file = $this->dl->getPath('media') . $subFilePath;
            if ($this->file->fileExists($file)) {
                $url = $this->urlBuilder->getBaseUrl() . "pub/media" . $fileexplode[1];
            } else {
                if ($this->file->fileExists($filePath)) {
                    $cpDir = $this->dl->getPath('media') . "/ebaymultiaccount/bulkfeeds/";
                    if (!$this->file->fileExists($cpDir)) {
                        $this->file->mkdir($cpDir);
                    }
                    $this->file->cp($filePath, $cpDir . $fileName);
                    if ($this->file->fileExists($cpDir . $fileName)) {
                        $url = $this->urlBuilder->getBaseUrl() . "pub/media/ebaymultiaccount/bulkfeeds/" . $fileName;
                    }
                }
            }
        }

        return $url;
    }
}
