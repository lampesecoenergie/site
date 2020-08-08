<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic\Variable;

use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Variable as DynamicVariable;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Dynamic\VariableFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Validate extends DynamicVariable
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param VariableFactory          $variableFactory
     * @param Registry                 $registry
     * @param Context                  $context
     * @param ForwardFactory           $resultForwardFactory
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        VariableFactory $variableFactory,
        Registry $registry,
        Context $context,
        ForwardFactory $resultForwardFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($variableFactory, $registry, $context, $resultForwardFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = '';

        $variable = $this->initModel();

        if (strpos($_SERVER['HTTP_HOST'], 'm2.mirasvit.com') === false) {
            $variable->setPhpCode($this->getRequest()->getParam('php_code'));
        }

        $collection = $this->productCollectionFactory->create();

        $productIds = $this->getRequest()->getParam('product_ids');
        if ($productIds) {
            $productIds = explode(',', $productIds);
            $productIds[] = 0;
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        } else {
            $collection->getSelect()->orderRand()
                ->limit(5);
        }

        foreach ($collection as $product) {
            $product = $product->load($product->getId());
            $result .= '<div class="product_head">ID: ' . $product->getId() . '</div>';
            $value = $variable->getValue($product, null);

            if (is_array($value)) {
                $value = print_r($value, true);
            } elseif (is_object($value)) {
                $value = get_class($value);
            }

            $result .= $value;
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response
            ->setBody($result);
    }

    /**
     * @return bool
     */
    public function _processUrlKeys()
    {
        return true;
    }
}
