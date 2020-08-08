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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Ui\Component\Listing\Columns\Feeds;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductValidation
 */
class FeedFile extends Column
{
    /**
     * Url path
     */
    const URL_PATH_RESEND = 'cdiscount/feeds/resend';
    const URL_MEDIA_FEED = 'cdiscount/feed/';

    /**
     * @var UrlInterface
     */
    public $urlBuilder;
    public $file;
    public $dl;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
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
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $feedFile = $item[$name];
                $item[$name] = [];
                if (isset($item['id'])) {
                    $feedId = $item['feed_id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("Feed #{$feedId}"),
                            'file' => $this->getFileLink($feedFile, $feedId),
//                            'message' => $feedFile
                        ],
                        // 'disabled' => 'disabled'
                    ];

                    /* $item[$name]['upload'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_RESEND, ['id' => $item['id']]),
                        'label' => __('Upload'),
                        'class' => 'cedcommerce actions upload'
                    ];*/

                    $item[$name]['download'] = [
                        'href' => $this->getFileLink($feedFile, $feedId),
                        'download' => (isset($feedFile) and !empty($feedFile)) ? basename($feedFile) : '',
                        'label' => __('Download'),
                        'class' => 'cedcommerce actions download'
                    ];
                }
            }
        }
        return $dataSource;
    }

    public function getFileLink($filePath, $feedId)
    {
        $url = '';
        if (isset($filePath) and !empty($filePath)) {
            $fileInfo = $this->file->getPathInfo($filePath);
            $fileName = $feedId.$fileInfo['basename'];
            $file = $this->dl->getPath('media') . "/cdiscount/feed/" . $fileName;
            if ($this->file->fileExists($file)) {
                $url = $this->urlBuilder
                        ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                    self::URL_MEDIA_FEED . $fileName;
            } else {
                if ($this->file->fileExists($filePath)) {
                    $cpDir = $this->dl->getPath('media') . "/cdiscount/feed/";
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
