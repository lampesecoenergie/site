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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\RedirectFactory;

class Sync extends Action
{
    /**
     * @var RedirectFactory
     */
    public $redirectFactory;

    /**
     * Sync constructor.
     * @param Action\Context $context
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Action\Context $context,
        RedirectFactory $redirectFactory
    ) {
        parent::__construct($context);
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // TODO: add sync all action.
        $redirect = $this->redirectFactory->create()->setPath('*/*/index');
        return $redirect;
    }
}
