<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Productslider
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Productslider\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magetop\Productslider\Helper\Data;
use Magetop\Productslider\Model\Config\Source\ProductType;

/**
 * Class AddBlock
 * @package Magetop\AutoRelated\Observer
 */
class AddBlock implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magetop\Productslider\Helper\Data
     */
    protected $helperData;

    /**
     * @var ProductType
     */
    protected $productType;

    /**
     * AddBlock constructor.
     * @param RequestInterface $request
     * @param Data $helperData
     * @param ProductType $productType
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData,
        ProductType $productType
    )
    {
        $this->request     = $request;
        $this->helperData  = $helperData;
        $this->productType = $productType;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $type = array_search($observer->getEvent()->getElementName(), [
            'content' => 'content',
            'sidebar' => 'catalog.leftnav'
        ]);
        if ($type !== false) {
            /** @var \Magento\Framework\View\Layout $layout */
            $layout         = $observer->getEvent()->getLayout();
            $fullActionName = $this->request->getFullActionName();
            $output         = $observer->getTransport()->getOutput();
            foreach ($this->helperData->getActiveSliders() as $slider) {
                list($pageType, $location) = explode('.', $slider->getLocation());
                if ($fullActionName == $pageType || $pageType == 'allpage') {
                    $content = $layout->createBlock($this->productType->getBlockMap($slider->getProductType()))
                        ->setSlider($slider)
                        ->toHtml();

                    if (strpos($location, $type)!== false) {
                        if (strpos($location, 'top') !== false) {
                            $output = "<div id=\"magetop-productslider-block-before-{$type}-{$slider->getId()}\">$content</div>" . $output;
                        } else {
                            $output .= "<div id=\"magetop-productslider-block-after-{$type}-{$slider->getId()}\">$content</div>";
                        }
                    }
                }
            }
            $observer->getTransport()->setOutput($output);
        }

        return $this;
    }
}
