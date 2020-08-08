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


namespace Mirasvit\Feed\Export\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Mirasvit\Feed\Export\Context;

class StoreResolver extends AbstractResolver
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * {@inheritdoc}
     * @param ScopeConfigInterface   $scopeConfig
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        $this->scopeConfig = $scopeConfig;

        return parent::__construct($context, $objectManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($value, $key = null)
    {
        if (!$key && $value instanceof Store) {
            return $value->getName();
        }

        return parent::toString($value, $key);
    }

    /**
     * Return store base email
     *
     * @param Store $store
     * @return string
     */
    public function getEmail($store)
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
    }
}
