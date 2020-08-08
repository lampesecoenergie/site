<?php
namespace Potato\Crawler\Model;

use Magento\Customer\Model\Group;

/**
 * Class Customer
 */
class Customer extends \Magento\Customer\Model\Customer
{
    /**
     * @return int
     */
    public function getGroupId()
    {
        return isset($_COOKIE[Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME]) ?
            $_COOKIE[Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME] : Group::NOT_LOGGED_IN_ID
        ;
    }

    /**
     * @return $this
     */
    public function save()
    {
        return $this;
    }

    /**
     * @param int  $id
     * @param null $field
     *
     * @return $this
     */
    public function load($id, $field=null)
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        return $this;
    }
}