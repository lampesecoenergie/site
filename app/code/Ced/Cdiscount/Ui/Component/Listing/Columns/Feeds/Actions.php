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
class Actions extends Column
{
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
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as $key => $item) {
                $file = $this->getFileLink($item['feed_file']);
                $dataSource['data']['items'][$key][$fieldName . '_html'] =
                    "
<a href='#' title='Edit' class='cedcommerce actions edit'>Edit</a>
<a href='#' title='Delete' class='cedcommerce actions delete'>Delete</a>
";//$file['html'];
                $dataSource['data']['items'][$key][$fieldName . '_url'] = $file['url'];
        }
        return $dataSource;
    }

    public function getFileLink($filePath)
    {
        $html = "<span>No file available.</span>";
        $url = '';
        $fileName = basename($filePath);
        $file = $this->dl->getPath('media')."/cdiscount/" . $fileName;
        if ($this->file->fileExists($file)) {
            $url = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . "cdiscount/" . $fileName;
            $html = "<a href='" . $url ."' download'>$fileName</a>";
        } else {
            if ($this->file->fileExists($filePath)) {
                $cpDir = $this->dl->getPath('media')."/cdiscount/";
                if (!$this->file->fileExists($cpDir)) {
                    $this->file->mkdir($cpDir);
                }
                $this->file->cp($filePath, $cpDir.$fileName);
                if ($this->file->fileExists($cpDir.$fileName)) {
                    $url = $this->urlBuilder->getBaseUrl(
                        ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . "cdiscount/" . $fileName;
                    $html = "<a href='" . $url ."' download'>$fileName</a>";
                }
            }
        }
        return ['html' => $html, 'url' => $url];
    }
}
