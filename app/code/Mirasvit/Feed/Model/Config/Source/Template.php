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
use Mirasvit\Feed\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

class Template implements ArrayInterface
{
    /**
     * @var TemplateCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param TemplateCollectionFactory $collectionFactory
     * @param Config                    $config
     */
    public function __construct(
        TemplateCollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($filesystem = false, $path = null)
    {
        $result = [];

        if ($filesystem) {
            if (!$path) {
                $path = $this->config->getTemplatePath();
            }
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (substr($entry, 0, 1) != '.') {
                        $result[] = [
                            'label' => pathinfo($entry, PATHINFO_FILENAME),
                            'value' => $path . '/' . $entry,
                        ];
                    }
                }
                closedir($handle);
            }
        } else {
            $result[] = [
                'label' => 'Empty Template',
                'value' => ''
            ];

            /** @var \Mirasvit\Feed\Model\Template $template */
            foreach ($this->collectionFactory->create() as $template) {
                $result[] = [
                    'label' => $template->getName() . ' (' . $template->getType() . ')',
                    'value' => $template->getId(),
                ];
            }
        }
        sort($result);

        return $result;
    }
}
