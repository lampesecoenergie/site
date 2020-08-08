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


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

class Search extends Category
{
    /**
     * Do search of category.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $search = $this->getRequest()->getParam('query');

        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Mirasvit\Feed\Helper\CategoryMapping\ReaderMapper $readerMapper */
        $readerMapper = $om->get('Mirasvit\Feed\Helper\CategoryMapping\ReaderMapper');

        /** @var \Mirasvit\Feed\Helper\CategoryMapping\Multiplicity\FileReaderMultiplicity $fileReaderMultiplicity */
        $fileReaderMultiplicity = $om->get('Mirasvit\Feed\Helper\CategoryMapping\Multiplicity\FileReaderMultiplicity');
        $fileReaderMultiplicity->findAll();
        if ($fileReaderMultiplicity->count()) {
            $readerMapper->addMultiplicity($fileReaderMultiplicity);
        }

        $resultPage->setData($readerMapper->getData($search));

        return $resultPage;
    }
}
