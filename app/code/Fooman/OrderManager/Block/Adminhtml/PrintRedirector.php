<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\OrderManager\Block\Adminhtml;

class PrintRedirector extends \Magento\Backend\Block\Template
{
    const KNOWN_ACTIONS = ['printDocs', 'printShipments', 'printInvoices'];

    public function toHtml()
    {
        $html = '';
        $printIds = $this->_request->getParam('printIds');
        $printAction = $this->_request->getParam('printAction');
        if ($printIds
            && $printAction
            && in_array($printAction, self::KNOWN_ACTIONS, true)
            && preg_match('/^[0-9,]+$/', $printIds)
        ) {
            $html .= sprintf(
                '<meta http-equiv="refresh" content="2; url= %s">',
                $this->getUrl('ordermanager/order/' . $printAction, ['printIds' => $printIds])
            );
        }

        return $html;
    }
}
