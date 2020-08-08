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
 * @package   Ced_m2.1.9
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Profile;


use Magento\Framework\App\ResponseInterface;

class Options extends \Magento\Backend\App\Action
{
    public $resultPageFactory;

    public $profile;

    public $eav;

    public $jsonFactory;

    public $logger;

    public $storeId;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Cdiscount\Model\Profile $profile,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Config $config
    ) {
        $this->jsonFactory = $resultJsonFactory;
        $this->profile = $profile;
        $this->resultPageFactory = $pageFactory;
        $this->eav = $eavConfig;
        $this->logger = $logger;
        $this->storeId = $config->getStore();
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $response = [
          'success' => false,
          'data' => []
        ];
        try {
            $data = $this->getRequest()->getParams();
            $jsonFactory = $this->jsonFactory->create();
            if (isset($data) and !empty($data)) {
                $magentoCode = $this->getRequest()->getParam('magento_attribute_code');
                $attribute = $this->eav->getAttribute('catalog_product', trim((string)$magentoCode))->setStoreId($this->storeId);
                $options = $attribute->getSource()->getAllOptions();
                $key = $attribute->getData('frontend_input');
                if ($key == 'text') {
                    return $jsonFactory->setData($response);
                }
                $result = $this->resultPageFactory->create(true)
                ->getLayout()->createBlock(
                    'Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View\OptionalMapping',
                    'cdiscount_optional_attributes'
                    )
                    ->setCdiscountOptions(json_encode($this->getRequest()->getParam('cdiscount_attribute_options',[])))
                    ->setMagentoOptions(json_encode($options))
                    ->setCurrentProfile($this->getRequest()->getParam('profile_id'))
                    ->setCurrentAttribute($this->getRequest()->getParam('cdiscount_attribute_code'))
                    ->setAlreadyMappedOptions($this->getRequest()->getParam('alreadyMappedOptions'))
                    ->toHtml();
                return $this->getResponse()->setBody($result);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['path' => __METHOD__]);
        }
        return $jsonFactory->setData($response);
    }
}