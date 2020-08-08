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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel;

class Profileproducts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\Profileproducts::NAME, 'id');
    }

    /**
     * @param $profileId
     * @return array
     */
    public function getProfileProducts($profileId)
    {
        $read = $this->getConnection();
        $select = $read->select()->from($this->getMainTable(), ['product_id'])
            ->where("(profile_id = '{$profileId}' ) AND product_id > 0");
        return $read->fetchCol($select);
    }

    public function deleteFromProfile($productId)
    {

        if ($productId <= 0) {
            return $this;
        }

        $dbh = $this->getConnection();
        $condition = "{$this->getTable('amazon_profile_products')}.product_id = " . $dbh->quote($productId);
        //. " AND {$this->getTable('amazon/profileproducts')}.profile_id = " . $dbh->quote($profileId);
        $dbh->delete($this->getTable('amazon_profile_products'), $condition);
        return $this;
    }

    public function deleteProducts($productIds)
    {

        if (empty($productIds) or !is_array($productIds) or count($productIds) == 0) {
            return $this;
        }

        $productIds = array_unique($productIds);
        $dbh = $this->getConnection();
        $condition =
            "{$this->getTable('amazon_profile_products')}.product_id IN (" . implode(',', $productIds) . ")";
        $dbh->delete($this->getTable('amazon_profile_products'), $condition);
        return $this;
    }

    public function addProducts($productIds, $profileId)
    {

        if (empty($productIds) or !is_array($productIds) or count($productIds) == 0 or empty($profileId)) {
            return $this;
        }

        $productIds = array_unique($productIds);
        $data = [];
        foreach ($productIds as $productId) {
            $data[] = [
                'profile_id' => (int)$profileId,
                'product_id' => (int)$productId
            ];
        }

        $dbh = $this->getConnection();
        $dbh->insertMultiple($this->getTable('amazon_profile_products'), $data);
        return $this;
    }

    public function profileProductExists($productId, $profileId)
    {
        if ($productId > 0) {
            $profileTable = $this->getTable('amazon_profile_products');
            $productProfile = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Ced\Amazon\Model\Profileproducts')->loadByField('profile_id', $profileId);
            if ($productProfile && $productProfile->getId()) {
                $dbh = $this->getConnection();
                $select = $dbh->select()->from($profileTable)
                    ->where("product_id = {$productId} AND profile_id = {$profileId}");
                return $dbh->fetchCol($select);
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $profile
     * @return $this
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $profile)
    {
        if ($profile->getId() == '') {
            if ($profile->getIdFieldName()) {
                $profile->unsetData($profile->getIdFieldName());
            } else {
                $profile->unsetData('id');
            }
        }

        $profile->setProfileName($profile->getName());
        return $this;
    }
}
