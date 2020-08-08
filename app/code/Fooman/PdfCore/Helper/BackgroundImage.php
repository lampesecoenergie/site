<?php
namespace Fooman\PdfCore\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class BackgroundImage
{

    /**
     * @var FileOps
     */
    private $file;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var State
     */
    private $state;

    const XML_PATH_BACKGROUND_IMAGE = 'sales_pdf/all/page/allbgimage';
    const XML_PATH_NO_BG_IN_ADMIN = 'sales_pdf/all/page/nobginadmin';

    /**
     * @param FileOps          $file
     * @param Filesystem       $filesystem
     * @param RequestInterface $request
     * @param State            $state
     */
    public function __construct(
        FileOps $file,
        Filesystem $filesystem,
        RequestInterface $request,
        State $state
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->request = $request;
        $this->state = $state;
    }

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param                      $storeId
     *
     * @return bool|string
     */
    public function getBackgroundImageFilePath(
        ScopeConfigInterface $scopeConfig,
        $storeId
    ) {

        $suppressAdmin = $scopeConfig->isSetFlag(
            self::XML_PATH_NO_BG_IN_ADMIN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($suppressAdmin && $this->isItAnAdminPrintAction()) {
            return false;
        }

        $file = $scopeConfig->getValue(
            self::XML_PATH_BACKGROUND_IMAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $fullPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('pdf_background/' . $file);
        if ($this->file->fileExists($fullPath)) {
            return $fullPath;
        }
        return false;
    }

    private function isItAnAdminPrintAction()
    {
        try {
            $area = $this->state->getAreaCode();
        } catch (\Exception $e) {
            return false;
        }

        if ($area !== Area::AREA_ADMINHTML) {
            return false;
        }

        $action = $this->request->getActionName();
        return strpos($action, 'print') !== false || strpos($action, 'pdf') !== false;
    }
}
