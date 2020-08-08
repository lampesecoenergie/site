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



namespace Mirasvit\Feed\Helper\CategoryMapping\Multiplicity;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileReaderMultiplicity extends ReaderMultiplicity
{
    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $mappingPaths = $this->getMappingPaths();

        foreach ($mappingPaths as $mappingPath) {
            foreach (glob($mappingPath . "/*.txt") as $filename) {
                /** @var \Mirasvit\Feed\Helper\CategoryMapping\FileInterface $fileReader */
                $fileReader = $this->getReader();
                $this->addItem($fileReader->setFile($filename));
            }
        }

        return $this;
    }

    /**
     * @return \Mirasvit\Feed\Helper\CategoryMapping\FileInterface
     */
    protected function getReader()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        return $om->create('Mirasvit\Feed\Helper\CategoryMapping\FileReader');
    }

    /**
     * @return array
     */
    protected function getMappingPaths()
    {
        $paths = [];

        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $directoryReader = $om->get('Magento\Framework\Module\Dir\Reader');
        $paths[] = realpath($directoryReader->getModuleDir('etc', 'Mirasvit_Feed') . '/../Setup/data/mapping/');

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $om->get('Magento\Framework\Filesystem');

        $paths[] = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'feed/mapping/';

        return $paths;
    }
}