<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 17/1/18
 * Time: 6:33 PM
 */
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\RueDuCommerce\Block\Adminhtml\Profile\Ui\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block
 */
class Js extends \Magento\Backend\Block\Template
{

    protected $_template = 'Ced_RueDuCommerce::profile/attribute/js.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $request;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    public function getProfileId()
    {
        $id = $this->request->getParam('id');
        return $id;
    }
}
