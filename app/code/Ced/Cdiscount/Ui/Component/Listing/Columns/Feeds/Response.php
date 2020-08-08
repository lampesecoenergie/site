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
 * Class Response
 */
class Response extends Column
{
    /**
 * Url path 
*/
    const URL_PATH_SYNC = 'cdiscount/feeds/sync';
    const URL_MEDIA_FEED = 'cdiscount/response/';

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


    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $response = $item[$name];
                $item[$name] = [];
                if (isset($item['id'])) {
                    $feedId = $item['feed_id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("Feed Response #{$feedId}"),
                            'message' => $response,
                            //'type' => 'json',
                            //'file' =>  $this->getFileLink($responseFile)
                        ],
                    ];

                    /*$item[$name]['download'] = [
                        'href' => $this->getFileLink($responseFile),
                        'download' => (isset($feedFile) and !empty($feedFile)) ? basename($feedFile) : '',
                        'label' => __('Download'),
                        'class' => 'cedcommerce actions download'
                    ];*/

                    /*$item[$name]['sync'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_SYNC, ['id' => $item['id']]),
                        'label' => __('Sync'),
                        'class' => 'cedcommerce actions sync'
                    ];*/
                }
            }
        }

        return $dataSource;
    }

    public function getFileLink($filePath)
    {
        $url = '';
        if (isset($filePath) and !empty($filePath)) {
            $fileInfo = $this->file->getPathInfo($filePath);
            $fileName = $fileInfo['basename'];
            $file = $this->dl->getPath('media') . "/cdiscount/response/" . $fileName;
            if ($this->file->fileExists($file)) {
                $url = $this->urlBuilder
                        ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                    self::URL_MEDIA_FEED . $fileName;
            } else {
                if ($this->file->fileExists($filePath)) {
                    $cpDir = $this->dl->getPath('media') . "/cdiscount/response/";
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
