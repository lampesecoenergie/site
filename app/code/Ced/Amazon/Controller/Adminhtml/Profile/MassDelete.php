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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Profile;

use Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;
use Ced\Amazon\Repository\Profile as ProfileRepository;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var ProfileCollectionFactory  */
    public $profileCollectionFactory;

    /** @var ProfileRepository  */
    public $profileRepository;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param Filter $filter
     * @param ProfileCollectionFactory $profileCollectionFactory
     * @param ProfileRepository $profileRepository
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        ProfileCollectionFactory $profileCollectionFactory,
        ProfileRepository $profileRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->profileRepository = $profileRepository;
    }

    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->profileCollectionFactory->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) && !empty($id)) {
                $collection = $this->profileCollectionFactory->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        $status = false;
        if (isset($collection) && $collection->getSize() > 0) {
            $status = true;
            $ids = $collection->getAllIds();
            foreach ($ids as $id) {
                $this->profileRepository->delete($id);
            }
        }

        return $this->_redirect('*/*/index');
    }
}
