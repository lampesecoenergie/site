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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Report;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductValidation
 */
class ReportFile extends Column
{
    /** Url path */
    const URL_PATH_RESEND = 'amazon/report/resend';

    const URL_MEDIA_FEED = 'amazon/report/';

    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /** @var \Magento\Framework\Filesystem\Io\File  */
    public $file;

    /** @var \Magento\Framework\Filesystem\DirectoryList  */
    public $dl;

    /**
     * ReportFile constructor.
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
    ) {
        $this->file = $fileIo;
        $this->dl = $directoryList;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $reportFile = $item[$name];
                $fileInfo = $this->file->getPathInfo($reportFile);
                $item[$name] = [];
                if (isset($item['id'])) {
                    $reportId = $item['report_id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("Report #{$reportId}"),
                            'file' => $this->getFileLink($reportFile),
                        ],
                    ];

                    $item[$name]['upload'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_RESEND, ['id' => $item['id']]),
                        'label' => __('Upload'),
                        'class' => 'cedcommerce actions upload'
                    ];

                    $item[$name]['download'] = [
                        'href' => $this->getFileLink($reportFile),
                        'download' => (isset($reportFile) && !empty($reportFile)) ? $fileInfo['basename'] : '',
                        'label' => __('Download'),
                        'class' => 'cedcommerce actions download'
                    ];
                }
            }
        }
        return $dataSource;
    }

    public function getFileLink($filePath)
    {
        $url = '';
        if (isset($filePath) && !empty($filePath)) {
            $fileInfo = $this->file->getPathInfo($filePath);
            $fileName = $fileInfo['basename'];
            $file = $this->dl->getPath('media') . "/amazon/report/" . $fileName;
            if ($this->file->fileExists($file)) {
                $url = $this->urlBuilder
                        ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                    self::URL_MEDIA_FEED . $fileName;
            } else {
                if ($this->file->fileExists($filePath)) {
                    $cpDir = $this->dl->getPath('media') . "/amazon/report/";
                    if (!$this->file->fileExists($cpDir)) {
                        $this->file->mkdir($cpDir);
                    }
                    $this->file->cp($filePath, $cpDir . $fileName);
                    if ($this->file->fileExists($cpDir . $fileName)) {
                        $url = $this->urlBuilder
                                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                            self::URL_MEDIA_FEED . $fileName;
                    }
                }
            }
        }

        return $url;
    }
}
