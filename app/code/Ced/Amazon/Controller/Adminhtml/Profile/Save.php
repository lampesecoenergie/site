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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Profile;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Ced\Amazon\Controller\Adminhtml\Profile
 */
class Save extends \Ced\Amazon\Controller\Adminhtml\Profile\Base
{
    public function execute()
    {
        $id = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_ID, null);
        $back = $this->getRequest()->getParam('back', false);

        if ($this->validate()) {
            // Saving attribute mapping
            $this->addAttributes();

            $this->repository->load($this->profile, $this->data->getData(\Ced\Amazon\Model\Profile::COLUMN_ID));
            $oldStoreId = $this->profile->getData(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID);
            $storeId = $this->data->getData(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID);
            $this->profile->addData($this->data->getData());
            $id = $this->repository->save($this->profile);

            $params = $this->getRequest()->getParams();
            $filter = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_FILTER);
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection|null $collection */
            $collection = null;

            if (!empty($filter)) {
                try {
                    $filter = json_decode($filter, true);
                    // sting value is used
                    if (isset($filter['excluded']) && $filter['excluded'] == false) {
                        $filter['excluded'] = 'false';
                    }

                    // Converting value to string true
                    if (isset($filter['filters']) && is_array($filter['filters'])) {
                        foreach ($filter['filters'] as &$item) {
                            if ($item === true) {
                                $item = 'true';
                            }
                            if ($item === 'false') {
                                $item = 'false';
                            }
                        }
                    }

                    if (!empty($filter) && is_array($filter)) {
                        // Updating products
                        $params = array_merge($params, $filter);
                        $this->getRequest()->setParams($params);
                        try {
                            $collection = $this->filter->getCollection($this->catalog->create());
                            $updateIds = $collection->getAllIds();
                        } catch (LocalizedException $e) {
                            // If no products are selected, remove all selected.
                            $updateIds = [];
                        }

                        $profileIds = $this->repository->getProfileIdsByProductIds($updateIds);

                        $ids = $this->product->getIds($id, $oldStoreId);
                        if ($storeId == $oldStoreId) {
                            // Find ids which are not already added
                            $temp = array_diff($updateIds, $ids);

                            // Remove the ids not present in currently added ids from any other profile
                            $ids = array_merge(array_diff($ids, $updateIds), array_diff($updateIds, $ids));

                            $updateIds = $temp;
                        }

                        $this->product->remove(
                            $id,
                            $oldStoreId,
                            $ids
                        );

                        $this->product->add(
                            $id,
                            $this->data->getData(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID),
                            $updateIds
                        );

                        // Removing cache for products
                        foreach ($profileIds as $storeId => $profileId) {
                            if ($profileId != $id) {
                                $this->product->purge($storeId, $profileId);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    //TODO: log
                }
            }

            $this->messageManager->addSuccessMessage('Profile saved successfully.');
            $this->getRequest()->setParams([]);
        } else {
            $this->messageManager->addErrorMessage('Profile saving failed. Kindly try again.');
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();
        if (isset($back) && $back == 'edit') {
            if ($id) {
                $redirect->setPath(
                    '*/profile/edit',
                    ['id' => $id]
                );
            } else {
                $redirect->setPath(
                    '*/profile/edit'
                    //, ['_current' => true] // current adds the params in url.
                );
            }
        } else {
            $redirect->setPath('*/profile/index');
        }

        return $redirect;
    }
}
