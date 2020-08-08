<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;

/**
 * Class Fee
 */
class Fee extends AbstractFieldArray
{
    /**
     * @var Factory $elementFactory
     */
    protected $elementFactory;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var CollectionFactory $countryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param ShippingHelper $shippingHelper
     * @param CollectionFactory $countryCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        ShippingHelper $shippingHelper,
        CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->shippingHelper           = $shippingHelper;
        $this->elementFactory           = $elementFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * Initialise form fields
     *
     * @return void
     * @phpcs:disable
     */
    protected function _construct()
    {
        $this->addColumn('country', ['label' => __('Country')]);
        $this->addColumn('postcode', ['label' => __('Postcode')]);
        $this->addColumn('fee', ['label' => __('Fee*')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'country' && isset($this->_columns[$columnName])) {
            $collection = $this->countryCollectionFactory->create();

            $originalData = $this->getElement()->getOriginalData();
            if (isset($originalData['method'])) {
                $countries = $this->shippingHelper->getConfig($originalData['method'] . '/countries');
                if (is_array($countries)) {
                    if (!empty($countries)) {
                        $collection->addCountryIdFilter($countries);
                    }
                }
            }

            $options = $collection->loadData()->toOptionArray(false);

            foreach ($options as $key => $option) {
                foreach ($option as $field => $value) {
                    if (is_string($value)) {
                        $options[$key][$field] = addslashes($value);
                    }
                }
            }

            $element = $this->elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            );

            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }
}
