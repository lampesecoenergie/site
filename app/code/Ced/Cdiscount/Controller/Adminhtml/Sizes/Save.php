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


class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    public $cdiscountAttributes;

    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Cdiscount\Model\CdiscountAttributesFactory $cdiscountAttributes,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->cdiscountAttributes = $cdiscountAttributes;
        $this->resultPageFactory = $resultPageFactory;
    }


    public function execute()
    {
        //die('tets');
        $save = $this->save();
        if ($save) {
            $this->messageManager->addSuccessMessage(__('Mapping Saved'));
            return $this->resultRedirectFactory->create()
                ->setPath('cdiscount/sizes/edit', ['id' => $save]);
        } else {
            $this->messageManager->addErrorMessage(__('Failed To Save Mappping'));
        }
        return $this->resultRedirectFactory->create()
            ->setPath('cdiscount/sizes');
    }

    private function save()
    {
        $response = false;
        $attributeName = $this->getRequest()->getParam('size');
        if (isset($attributeName) && !empty($attributeName)) {
            $sizeMappingArray = [];
            foreach ($attributeName as $value) {
                $sizeMappingArray[] = [
                    'magento_size_id' => $value['magento_size'],
                    'cdiscount_size_id' => $value['cdiscount_size'][0]
                ];
            }

            $mapAttr = $this->cdiscountAttributes->create()
                ->load('size', 'attribute_name');
            $mapAttr->setData('attribute_name', 'size')->setData('attribute_mappings', json_encode($sizeMappingArray));
            $mapAttr->save();
            $response = $mapAttr->getId();
        }
        return $response;
    }
}
