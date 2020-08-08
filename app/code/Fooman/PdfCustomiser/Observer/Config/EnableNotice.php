<?php

namespace Fooman\PdfCustomiser\Observer\Config;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * @author     Kristof Ringleff
 * @copyright  Copyright (c) 2016 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EnableNotice implements ObserverInterface
{

    private $enableNotice;

    /**
     * @param \Fooman\PdfCustomiser\Model\EnableNotice $enableNotice
     */
    public function __construct(
        \Fooman\PdfCustomiser\Model\EnableNotice $enableNotice
    ) {
        $this->enableNotice = $enableNotice;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $path = $observer->getRequest()->getOriginalPathInfo();
        if ($observer->getRequest()->getFullActionName() === 'sales_order_index'
            || strpos($path, 'section/sales_pdf') !== false) {
            $this->enableNotice->canRender();
        }
    }
}
