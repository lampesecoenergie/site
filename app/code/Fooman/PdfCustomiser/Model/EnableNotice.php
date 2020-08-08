<?php

namespace Fooman\PdfCustomiser\Model;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\ModuleListInterface;

/**
 * @author     Kristof Ringleff
 * @copyright  Copyright (c) 2016 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EnableNotice
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ManagerInterface    $messageManager
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ManagerInterface $messageManager,
        ModuleListInterface $moduleList
    ) {
        $this->messageManager = $messageManager;
        $this->moduleList = $moduleList;
    }

    public function canRender()
    {
        if (!$this->moduleList->has('Fooman_PdfDesign')) {
            $this->messageManager->addErrorMessage(
                'Required module is not enabled, please enable Fooman_PdfDesign by executing '
                . '"bin/magento module:enable Fooman_PdfDesign && bin/magento setup:upgrade" via the command line'
            );
            return false;
        }
        return true;
    }
}
