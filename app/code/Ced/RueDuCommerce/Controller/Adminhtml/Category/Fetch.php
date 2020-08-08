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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Ced\RueDuCommerce\Controller\Adminhtml\Cron
 */
class Fetch extends Action
{
    /**
     * ResultPageFactory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Ced\RueDuCommerce\Helper\Category
     *
     * @var PageFactory
     */
    public $category;
    public $_coreRegistry;

    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Ced\RueDuCommerce\Helper\Category $category
    ) {
        parent::__construct($context);
        $this->category = $category;
        $this->_coreRegistry = $registry;
        $this->session =  $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getPost('id');
        $current_profile_id = $this->getRequest()->getPost('current_profile_id');
        $level = $this->getRequest()->getPost('level');
        $loadCatObj = $this->_objectManager->create('Ced\RueDuCommerce\Model\Profile')->load($current_profile_id);
        $check =array();

        if ($loadCatObj && $loadCatObj->getId()) {
            $check = json_decode($loadCatObj->getData('profile_categories'), true);
        }

        $args = array('max_level'=> $level);

        if($id) {
            $this->session->setCategoryM($id);
            $args['hierarchy'] = $id;
        } else {
            $args['hierarchy']='';
        }

        $response = $this->category->getCategories($args);

        if (count($response)) {
            $categoryHtml = '<option></option>';
            foreach ($response as $value) {
                if (isset($value['level']) && ($value["level"] >= $level)) {
                

                    if (isset($check['select-level'.$level]) && $value["code"] == $check['select-level'.$level]) {
                        $categoryHtml .= '<option selected="selected" value="'.$value["code"].'">'.$value["label"].'</option>';
                    } else {
                        $categoryHtml .= '<option value="'.$value["code"].'">'.$value["label"].'</option>';
                    }
                }
            }
            if($categoryHtml=='<option></option>')
                $this->getResponse()->setBody("Unable to fetch category");
            else
            $this->getResponse()->setBody($categoryHtml);
        } else {
            $this->getResponse()->setBody("Unable to fetch category");
        }
    }

}
