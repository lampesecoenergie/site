<?php
namespace Fooman\PdfDesign\Model;

/**
 * Pick configured design for current store, emit event to allow custom
 * overriding based on content
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfDesign
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DesignDecider
{

    const XML_PATH_PDF_DESIGN = 'sales_pdf/all/pdfdesign';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
    }

    public function pick($storeId, array $templateVars = [])
    {
        $transport = new \Magento\Framework\DataObject(
            ['design' => $this->getConfiguredDesign($storeId)]
        );
        $this->eventManager->dispatch(
            'fooman_pdfcustomiser_design_decider',
            [
                'transport' => $transport,
                'template_vars' => $templateVars
            ]
        );
        return $transport->getDesign();
    }

    private function getConfiguredDesign($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PDF_DESIGN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
