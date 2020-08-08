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


namespace Mirasvit\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class Rule implements ArrayInterface
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param Config                $config
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        Config $config
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($filesystem = false)
    {
        $result = [];

        if ($filesystem) {
            $path = $this->config->getRulePath();
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (substr($entry, 0, 1) != '.') {
                        $result[] = [
                            'label' => $entry,
                            'value' => $path . '/' . $entry,
                        ];
                    }
                }
                closedir($handle);
            }
        } else {
            $result = $this->ruleCollectionFactory->create()->toOptionArray();
        }

        sort($result);

        return $result;
    }
}
