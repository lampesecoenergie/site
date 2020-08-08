<?php

namespace Potato\Crawler\Plugin\Customer;

use Potato\Crawler\Model\Customer as CrawlerCustomer;
use Magento\Customer\Model\Group;
use Potato\Crawler\Model\Warmer;

/**
 * Class Clean
 */
class Session
{
    /** @var CrawlerCustomer  */
    protected $crawlerCustomer;

    /**
     * Session constructor.
     * @param CrawlerCustomer $crawlerCustomer
     */
    public function __construct(
        CrawlerCustomer $crawlerCustomer
    ) {
        $this->crawlerCustomer = $crawlerCustomer;
    }

    /**
     * @return array|null
     */
    public function beforeSetCustomer()
    {
        if ($this->_isCrawler()) {
            //replace customer model for crawler
            return [$this->crawlerCustomer];
        }
        return null;
    }

    /**
     * @return bool
     */
    protected function _isCrawler()
    {
        return isset($_COOKIE[Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME]) &&
            $_COOKIE[Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME] != Group::NOT_LOGGED_IN_ID;
    }

    /**
     * @param $subject
     * @param $result
     * @return bool
     */
    public function afterIsLoggedIn($subject, $result)
    {
        if ($this->_isCrawler()) {
            return true;
        }
        return $result;
    }
}