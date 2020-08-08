<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Grid input column renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

class Datepicker extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    protected $_values;

    /**
     * Date format string
     *
     * @var string
     */
    protected static $_format = null;

    /**
     * @var DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->_authorization = $context->getAuthorization();
    }

    /**
     * Retrieve date format
     *
     * @return string
     */
    protected function _getFormat()
    {
        $format = $this->getColumn()->getFormat();
        if (!$format) {
            if (self::$_format === null) {
                try {
                    self::$_format = $this->_localeDate->getDateFormat(
                        \IntlDateFormatter::MEDIUM
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
            $format = self::$_format;
        }
        return $format;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $option = $this->getOption();
        
        $value = htmlspecialchars($row->getData($this->getColumn()->getIndex()));//$this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date');

        $yearStart = '2010'; //$this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = '2020'; //$this->_catalogProductOptionTypeDate->getYearEnd();

        ////////////////
        $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = '';

        if ($this->getColumn()->getFilterTime()) {
            $timeFormat = $this->_localeDate->getTimeFormat(
                \IntlDateFormatter::SHORT
            );
        }
//
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $timezone = $this->getColumn()->getTimezone() !== false ? $this->_localeDate->getConfigTimezone() : 'UTC';
            $value = $this->dateTimeFormatter->formatObject(
                $this->_localeDate->date(
                    new \DateTime(
                        $data,
                        new \DateTimeZone($timezone)
                    )
                ),
                $format //$this->_getFormat()
            );
        }else
        {
            $value = $this->getColumn()->getDefault();
        }
        
        $calendar = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Date'
        )->setId(
            $this->getColumn()->getId() . '_' . $row->getData('entity_id')
        )->setName(
            $this->getColumn()->getId()
        )->setClass(
            'product-custom-option datetime-picker input-text admin__control-text '.$this->getColumn()->getInlineCss()
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $format
        )->setTimeFormat(
            $timeFormat
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }
    
}
