<?php
namespace Cminds\AdminLogger\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ActionHistory
 *
 * @package Cminds\AdminLogger\Ui\Component\Listing\Column
 */
class ActionHistory extends Column
{
    /**
     * View action URL.
     */
    const ADMIN_ACTION_URL_PATH_VIEW = 'adminlogger/actionhistory/view';

    /**
     * Delete action URL.
     */
    const ADMIN_ACTION_URL_PATH_DELETE = 'adminlogger/actionhistory/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ActionHistory constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $viewUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * @param array  $dataSource
     *
     * @return array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if (isset($item['id'])) {
                    $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::ADMIN_ACTION_URL_PATH_VIEW,
                            ['id' => $item['id']]
                        ),
                        'label' => __('View')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::ADMIN_ACTION_URL_PATH_DELETE,
                            ['id' => $item['id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete log "${ $.$data.id }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.action_type }" record?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
