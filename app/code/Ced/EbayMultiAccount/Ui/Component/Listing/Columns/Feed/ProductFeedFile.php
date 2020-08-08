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

namespace Ced\EbayMultiAccount\Ui\Component\Listing\Columns\Feed;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class FeedFile
 * @package Ced\EbayMultiAccount\Ui\Component\Listing\Columns\JobScheduler
 */
class ProductFeedFile extends Column
{
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

    const URL_PATH_RESEND = 'ebaymultiaccount/product/index';

    /**
     * FeedFile constructor.
     * @param ContextInterface $context
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        $components = [],
        $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->dl = $directoryList;
        $this->urlBuilder = $urlBuilder;
        $this->file = $fileIo;
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
                $item[$name] = [];
                if (isset($item['id'])) {
                    $feedId = $item['id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("Feed #{$feedId}"),
                            'file' => $this->getFileLink(str_replace('.gz', '.xml', $feedFile)),
                            'message' => 'Feed File Not exist because debug mode off.',
                        ],
                    ];
                    if (is_string($feedFile) && $feedFile != null && @fopen($feedFile, 'r')) {
                        $item[$name]['download'] = [
                            'href' => $this->getFileLink($feedFile),
                            'download' => (isset($feedFile) and !empty($feedFile)) ? basename($feedFile) : '',
                            'label' => __('Download'),
                            'class' => 'cedcommerce actions download'
                        ];
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
                    $cpDir = $this->dl->getPath('media') . "/ebaymultiaccount/";
                    if (!$this->file->fileExists($cpDir)) {
                        $this->file->mkdir($cpDir);
                    }
                    $this->file->cp($filePath, $cpDir . $fileName);
                    if ($this->file->fileExists($cpDir . $fileName)) {
                        $url = $this->urlBuilder->getBaseUrl() . "pub/media/ebaymultiaccount/" . $fileName;
                    }
                }
            }
        }

        return $url;
    }
}
