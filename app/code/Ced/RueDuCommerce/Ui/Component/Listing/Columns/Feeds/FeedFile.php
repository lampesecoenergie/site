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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Ui\Component\Listing\Columns\Feeds;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class FeedFile
 */
class FeedFile extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;
    public $file;
    public $dl;
    /**
     * Url path 
     */
    const URL_PATH_RESEND = 'rueducommerce/feeds/resend';

    /**
     * @param ContextInterface   $context
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param UiComponentFactory $uiComponentFactory
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        $components = [],
        $data = []
    ) {
        
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->dl           = $directoryList;
        $this->urlBuilder   = $urlBuilder;
        $this->file         = $fileIo;
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
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
                    $feedId = $item['feed_id'];
                    $item[$name]['view'] = [
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("Feed #{$feedId}"),
                            'file' => $this->getFileLink($feedFile),
                            'message' => 'Feed File Not exist because debug mode off.',
                        ],
                    ];
                    $item[$name]['download'] = [
                        'href' => $this->getFileLink($feedFile),
                        'download' => (isset($feedFile) and !empty($feedFile)) ? basename($feedFile) : '',
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
        if (isset($filePath) and !empty($filePath)) {
            $fileName = basename($filePath);
            $file = $this->dl->getPath('media') . "/rueducommerce/feed/" . $fileName;
            if ($this->file->fileExists($file)) {
                $url = $this->urlBuilder->getBaseUrl() . "pub/media/rueducommerce/feed/" . $fileName;
            } else {
                if ($this->file->fileExists($filePath)) {
                    $cpDir = $this->dl->getPath('media') . "/rueducommerce/feed/";
                    if (!$this->file->fileExists($cpDir)) {
                        $this->file->mkdir($cpDir);
                    }
                    $this->file->cp($filePath, $cpDir . $fileName);
                    if ($this->file->fileExists($cpDir . $fileName)) {
                        $url = $this->urlBuilder->getBaseUrl() . "pub/media/rueducommerce/feed/" . $fileName;
                    }
                }
            }
        }

        return $url;
    }
}
