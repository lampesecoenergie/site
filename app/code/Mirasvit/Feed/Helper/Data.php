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



namespace Mirasvit\Feed\Helper;

use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Feed\Model\Feed;
use Mirasvit\Feed\Model\Config;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var BackendUrlInterface
     */
    protected $backendUrl;

    public function __construct(
        StoreManagerInterface $storeManager,
        BackendUrlInterface $backendUrl,
        Config $config,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->backendUrl = $backendUrl;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * Feed delivery URL
     *
     * @param Feed $feed
     * @return string
     */
    public function getFeedDeliverUrl(Feed $feed)
    {
        return $this->backendUrl->getUrl('*/*/delivery', ['id' => $feed->getId()]);
    }

    /**
     * Feed preview URL
     *
     * @param Feed $feed
     * @return string
     */
    public function getFeedPreviewUrl(Feed $feed)
    {
        return $this->backendUrl->getUrl(
            '*/*/preview',
            [
                'id'   => $feed->getId(),
                'skip' => 'rules',
            ]
        );
    }

    /**
     * Feed export URL
     *
     * @param Feed $feed
     * @return string
     */
    public function getFeedExportUrl(Feed $feed)
    {
        $url = $feed->getStore()->getBaseUrl() . 'feed/export/execute';

        $url = strtok($url, '?');

        return $url;
    }

    /**
     * Feed progress URL
     *
     * @param Feed $feed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFeedProgressUrl(Feed $feed)
    {
        $stateUrl = $this->backendUrl->getUrl('*/*/progress');
        $stateUrl = strtok($stateUrl, '?');

        return $stateUrl;
    }

    /**
     * @param  object $entity
     * @param  string $entityName
     * @return string
     */
    public function getEntityConfigPath($entity, $entityName)
    {
        $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $entityName))) . 'Path';
        $path = $this->config->$methodName() . '/' . $entity->getName() . '.yaml';

        return $path;
    }
}
