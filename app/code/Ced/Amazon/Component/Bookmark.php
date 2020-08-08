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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

class Bookmark extends \Magento\Ui\Component\Bookmark
{
    /** @var \Ced\Amazon\Api\ProfileRepositoryInterface  */
    public $profile;

    public function __construct(
        ContextInterface $context,
        \Ced\Amazon\Api\ProfileRepositoryInterface $profile,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
        $this->profile = $profile;
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        $namespace = $this->getContext()->getRequestParam('namespace', $this->getContext()->getNamespace());
        $config = [];
        if (!empty($namespace)) {
            $profileId = $this->getContext()->getRequestParam('profile_id');
            if (empty($profileId)) {
                // TODO: recheck
                $profileId = $this->getContext()->getRequestParam('id');
            }

            $storeId = $this->getContext()->getRequestParam('store');

            if (empty($storeId)) {
                $storeId = $this->getContext()->getFilterParam('store_id');
            }

            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
            foreach ($bookmarks->getItems() as $bookmark) {
                if ($bookmark->isCurrent()) {
                    $config['activeIndex'] = $bookmark->getIdentifier();
                }

                $config = array_merge_recursive($config, $bookmark->getConfig());
                if (!empty($profileId)) {
//                    $config['current']['filters']['applied'] = [];
                    $config['current']['filters']['applied']['amazon_profile_id'] = (string)$profileId;
                    $storeId =  $this->profile->getById($profileId)->getStoreId();
                }

                if (!empty($storeId)) {
                    // TODO: recheck, update it with field. remove here
                    $config['current']['filters']['applied']['store_id'] = $storeId;
                    //$config['current']['filters']['applied']['store'] = $storeId;
                }
            }
        }

        $this->setData('config', array_replace_recursive($config, $this->getConfiguration($this)));

        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}
