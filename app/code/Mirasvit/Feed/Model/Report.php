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



namespace Mirasvit\Feed\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * @method $this setSession($session)
 * @method $this setProductId($id)
 * @method $this setFeedId($id)
 * @method $this setOrderId($id)
 * @method $this setIsClick($isClick)
 * @method $this setStoreId($id)
 * @method $this setSubtotal($subtotal)
 * @method $this setCreatedAt($createdAt)
 */
class Report extends AbstractTemplate
{
    /**
     * @var ReportFactory
     */
    protected $factory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @param ReportFactory          $factory
     * @param StoreManagerInterface  $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param Context                $context
     * @param Registry               $registry
     */
    public function __construct(
        ReportFactory $factory,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        Context $context,
        Registry $registry
    ) {
        $this->factory = $factory;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Feed\Model\ResourceModel\Report');
    }

    /**
     * @param string $session
     * @param int    $feedId
     * @param int    $productId
     *
     * @return Report
     */
    public function addClick($session, $feedId, $productId)
    {
        return $this->factory->create()
            ->setSession($session)
            ->setFeedId($feedId)
            ->setProductId($productId)
            ->setStoreId($this->storeManager->getStore()->getId())
            ->setIsClick(1)
            ->save();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function addOrder($order)
    {
        if ($this->cookieManager->getCookie('feed_session')
            && $this->cookieManager->getCookie('feed_id')
        ) {
            $session = $this->cookieManager->getCookie('feed_session');
            $feedId = (int)$this->cookieManager->getCookie('feed_id');

            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                $this->factory->create()
                    ->setSession($session)
                    ->setFeedId($feedId)
                    ->setStoreId($this->storeManager->getStore()->getId())
                    ->setProductId($item->getProductId())
                    ->setOrderId($order->getId())
                    ->setSubtotal($item->getBaseRowTotal())
                    ->setIsClick(0)
                    ->setCreatedAt($order->getCreatedAt())
                    ->save();
            }
        }

        return $this;
    }
}
