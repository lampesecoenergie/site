<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 7/10/19
 * Time: 4:19 PM
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Ced\Amazon\Model\Source\Account;

class AcId extends Column
{
    public $urlBuilder;
    public $account;
    const URL_PATH_EDIT = 'amazon/account/edit';

    public function __construct(
        ContextInterface $context,
        Account $account,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->account = $account;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'account_id';
            $options = $this->getData('options');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName], $options[$item[$fieldName]]) ) {
                        $url = $this->urlBuilder->getUrl(
                            self::URL_PATH_EDIT,
                            [
                                'id' => $item[$fieldName]
                            ]
                        );
                        $html = "<a href='" . $url . "' target='_blank'>";
                        $html .= $options[$item[$fieldName]];
                        $html .= "</a>";
                        $item[$fieldName . '_html'] = $html;
                }
            }
        }
        return $dataSource;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        $accountOptions = $this->account->toArray();
        $this->setData('options', $accountOptions);
    }
}