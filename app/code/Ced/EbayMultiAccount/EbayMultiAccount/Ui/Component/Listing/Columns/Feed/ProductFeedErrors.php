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
 * Class Response
 */
class ProductFeedErrors extends Column
{
    const URL_PATH_SYNC = 'ebaymultiaccount/product/index';

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
     * Response constructor.
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
                $response = $item[$name];
                $item[$name] = [];
                if (isset($item['id'])) {
                    $feedId = $item['id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("Feed Response #{$feedId}"),
                            'message' => $response,
                            'type' => 'json',
                            'render' => 'html'
                        ],
                    ];
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
            $file = $this->dl->getPath('media') . "/ebaymultiaccount/response/" . $fileName;
            if ($this->file->fileExists($file)) {
                $url = $this->urlBuilder->getBaseUrl() . "pub/media/ebaymultiaccount/response/" . $fileName;
            } else {
                if ($this->file->fileExists($filePath)) {
                    $cpDir = $this->dl->getPath('media') . "/ebaymultiaccount/response/";
                    if (!$this->file->fileExists($cpDir)) {
                        $this->file->mkdir($cpDir);
                    }
                    $this->file->cp($filePath, $cpDir . $fileName);
                    if ($this->file->fileExists($cpDir . $fileName)) {
                        $url = $this->urlBuilder->getBaseUrl() . "pub/media/ebaymultiaccount/response/" . $fileName;
                    }
                }
            }
        }
        return $url;
    }
}
