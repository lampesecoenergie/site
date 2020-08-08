<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Profile;


use Ced\Cdiscount\Model\ResourceModel\Profile\Collection;

class MassDelete extends \Magento\Backend\App\Action
{
    public $config;
    public $profileFactory;
    public $filter;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Cdiscount\Helper\Config $cdiscountConfig,
        \Ced\Cdiscount\Model\ProfileFactory $profileFactory
    ) {
        parent::__construct($context);
        $this->profileFactory = $profileFactory;
        $this->config = $cdiscountConfig;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $parseFilters = $this->filter
                ->getCollection($this->profileFactory->create()->getCollection());
            $profileIds = $parseFilters->getAllIds();
            if (!empty($profileIds)) {
                $storeId = $this->config->getStore();
                try {
                    foreach ($profileIds as $profileId) {
                        $profile = $this->profileFactory->create()->load($profileId);
                        $profile->removeProducts($storeId);
                        $profile->delete();
                    }
                    $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been deleted.', count($profileIds)));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $this->_redirect('*/*/index');
    }
}
