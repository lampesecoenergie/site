<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Sizes;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Edit extends \Magento\Framework\App\Action\Action
{
    public $pageFacotry;

    public $cdiscountAttributes;

    public function __construct
    (
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Ced\Cdiscount\Model\CdiscountAttributes $cdiscountAttributes
    )
    {
        $this->cdiscountAttributes = $cdiscountAttributes;
        $this->pageFacotry = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $pagefactory = $this->pageFacotry->create();
        $pagefactory->setActiveMenu(__('Ced_Cdiscount::cdiscount_sizes'));
        $pagefactory->getConfig()->getTitle()->prepend(__('Map Sizes'));
        return $pagefactory;
    }
}