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
 * @package     Ced_EbayMultiAccount
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Model\ResourceModel;

class Profileproducts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {

        $this->_init('ebaymultiaccount_profile_products', 'id');

    }


    /**
     *
     * @param Mage_Core_Model_Abstract $group
     * @see Mage_Core_Model_Resource_Db_Abstract::_beforeSave()
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $profile)
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




    /**
     *
     *
     * @param $profileId
     * @return multitype:
     */
    public function getProfileProducts($profileId)
    {
        $read 	= $this->getConnection();
        $select = $read->select()->from($this->getMainTable(), array('product_id'))->where("(profile_id = '{$profileId}' ) AND product_id > 0");
        return $read->fetchCol($select);
    }


    public function deleteFromProfile($productId)
    {

        if ( $productId <= 0) {
            return $this;
        }

        //$vendorGroup = Mage::getModel('csgroup/group')->loadByField('group_code',$vendor->getGroup());

        $dbh = $this->getConnection();
        $condition = "{$this->getTable('ebaymultiaccount_profile_products')}.product_id = " . $dbh->quote($productId);
        //. " AND {$this->getTable('EbayMultiAccount/profileproducts')}.profile_id = " . $dbh->quote($profileId);
        $dbh->delete($this->getTable('ebaymultiaccount_profile_products'), $condition);
        return $this;




    }


    public function profileProductExists($productId, $profileId)
    {
        if ( $productId > 0 ) {
            $profileTable = $this->getTable('ebaymultiaccount_profile_products');

            $productProfile = \Magento\Framework\App\ObjectManager::getInstance()->get('Ced\EbayMultiAccount\Model\Profileproducts')->loadByField('profile_id',$profileId);
            if($productProfile && $productProfile->getId())
            {
                $dbh    = $this->getConnection();
                $select = $dbh->select()->from($profileTable)
                    ->where("product_id = {$productId} AND profile_id = {$profileId}");
                return $dbh->fetchCol($select);
            }
            else
            {
                return array();
            }
        } else {
            return array();
        }
    }
}

