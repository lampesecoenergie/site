<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Deposit;

use Zend_Pdf;
use Zend_Pdf_Page;
use Zend_Pdf_Font;
use Zend_Pdf_Color_Rgb;
use Zend_Pdf_Color_GrayScale;
use Zend_Pdf_Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Pdf
 */
class Pdf
{
    /**
     * @var Zend_Pdf $zendPdf
     */
    protected $zendPdf;

    /**
     * @var DateTime $dateTime
     */
    protected $dateTime;

    /**
     * @param DateTime $dateTime
     */
    public function __construct(
        DateTime $dateTime
    ) {
        $this->zendPdf  = new Zend_Pdf();
        $this->dateTime = $dateTime;
    }

    /**
     * Retrieve PDF file
     *
     * @param array $data
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function getFile($data)
    {
        $pdf  = $this->zendPdf;
        $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);

        $minYPosToChangePage = 60;
        $xPos = 20;
        $yPos = $page->getHeight() - 40;
        $lineHeight = 15;

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
        $fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);

        /* Date */
        $page->setFont($font, 11);
        $page->drawText(__('Date: %1', $this->dateTime->date('Y-m-d')), $page->getWidth() - 105, $yPos, 'UTF-8');
        $page->setFont($font, 12);

        /* Title */
        $page->setFont($fontBold, 12);
        $page->drawText(__('Deposit %1', 'Mondial Relay'), $xPos, $yPos, 'UTF-8');
        $page->setFont($font, 12);

        $yPos -= 10;

        $page->drawLine($xPos, $yPos, $page->getWidth() - 20, $yPos);

        $yPos -= 40;

        /* Company */
        $page->drawText(__('Company:'), $xPos, $yPos, 'UTF-8');
        $page->drawText($data['company']['name'], $xPos + 60, $yPos, 'UTF-8');

        $yPos -= $lineHeight;

        /* Shipments */
        $yPos -= 30;

        $page->setFont($fontBold, 12);
        $page->drawText(__('Packages'), $xPos, $yPos, 'UTF-8');

        $yPos -= 10;

        $page->drawLine($xPos, $yPos, $page->getWidth() - 20, $yPos);

        $yPos -= ($lineHeight + 5);

        $page->setFont($font, 12);

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.85, 0.85, 0.85));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($xPos, $yPos, 570, $yPos - 20);
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));

        $yPos -= 15;

        $page->drawText(__('Order ID'), $xPos + 5, $yPos, 'UTF-8');
        $page->drawText(__('Name'), $xPos + 90, $yPos, 'UTF-8');
        $page->drawText(__('Package'), $xPos + 240, $yPos, 'UTF-8');
        $page->drawText(__('Postcode'), $xPos + 340, $yPos, 'UTF-8');
        $page->drawText(__('Country'), $xPos + 415, $yPos, 'UTF-8');
        $page->drawText(__('Weight'), $xPos + 470, $yPos, 'UTF-8');

        $yPos -= 5;

        foreach ($data['shipments'] as $shipment) {
            $page->setFont($font, 12);
            $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle($xPos, $yPos, 570, $yPos - 20);
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));

            $yPos -= 15;

            $page->drawText($shipment['increment_id'], $xPos + 5, $yPos, 'UTF-8');
            $page->drawText($shipment['name'], $xPos + 90, $yPos, 'UTF-8');
            $page->drawText($shipment['tracking'], $xPos + 240, $yPos, 'UTF-8');
            $page->drawText($shipment['postcode'], $xPos + 340, $yPos, 'UTF-8');
            $page->drawText($shipment['country'], $xPos + 415, $yPos, 'UTF-8');
            $page->drawText($shipment['weight'], $xPos + 470, $yPos, 'UTF-8');

            $yPos -= 5;

            if ($yPos <= $minYPosToChangePage) {
                $pdf->pages[] = $page;
                $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);

                $yPos = $page->getHeight() - 20;
            }
        }

        /* Summary */
        $yPos -= 50;

        $page->setFont($fontBold, 12);
        $page->drawText(__('Summary'), $xPos, $yPos, 'UTF-8');
        $page->setLineColor(new Zend_Pdf_Color_Rgb(0, 0, 0));

        $yPos -= 10;

        $page->drawLine($xPos, $yPos, $page->getWidth() - 20, $yPos);

        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));

        $yPos -= ($lineHeight + 5);

        $page->setFont($font, 12);

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.85, 0.85, 0.85));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($xPos, $yPos, 570, $yPos - 20);
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));

        $yPos -= 15;

        $page->drawText(__('Total'), $xPos + 5, $yPos, 'UTF-8');
        $page->drawText(__('Weight'), $xPos + 180, $yPos, 'UTF-8');

        $yPos -= 5;

        $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($xPos, $yPos, 570, $yPos - 20);
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));

        $yPos -= 15;

        $page->drawText($data['summary']['total_shipment'], $xPos + 5, $yPos, 'UTF-8');
        $page->drawText($data['summary']['total_weight'], $xPos + 180, $yPos, 'UTF-8');

        $yPos -= 5;

        if ($yPos <= $minYPosToChangePage) {
            $pdf->pages[] = $page;
            $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);

            $yPos = $page->getHeight() - 20;
        }

        $pdf->pages[] = $page;

        return $pdf->render();
    }

    /**
     * Retrieve deposit file name
     *
     * @return string
     */
    public function getFileName()
    {
        return 'deposit-' . $this->dateTime->date('Y-m-d') . '.pdf';
    }
}
