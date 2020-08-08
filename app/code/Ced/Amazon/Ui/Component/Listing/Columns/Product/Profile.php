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

namespace Ced\Amazon\Ui\Component\Listing\Columns\Product;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Profile
 * @package Ced\Amazon\Ui\Component\Listing\Columns\Product
 */
class Profile extends Column
{
    /**
     * @var SerializerInterface
     */
    public $serializer;

    /** @var \Ced\Amazon\Model\Source\Profile  */
    public $profile;

    /**
     * Profile constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SerializerInterface $serializer
     * @param \Ced\Amazon\Model\Source\Profile $profile
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SerializerInterface $serializer,
        \Ced\Amazon\Model\Source\Profile $profile,
        $components = [],
        $data = []
    ) {
        $this->serializer = $serializer;
        $this->profile = $profile;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'amazon_profile_id';
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $html = "<a href='" .
                        $this->context->getUrl(
                            'amazon/profile/edit',
                            ['id' => $item[$fieldName]]
                        ) . "' target='_blank'>";
                    $html .= $this->profile->getOptionText($item[$fieldName]);
                    $html .= "</a>";
                    $item[$fieldName . '_html'] = $html;
                }
            }
        }
        return $dataSource;
    }
}
