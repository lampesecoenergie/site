<?php
namespace Fooman\PdfCore\Block\Pdf;

use Magento\Framework\App\Area;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DocumentRenderer extends PdfAbstract implements DocumentRendererInterface
{
    const DEFAULT_FOOTER_LAYOUT_HANDLE = 'fooman_pdfcore_footer';

    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $maliciousCode;

    /**
     * @var \Fooman\PdfCore\Model\Template
     */
    protected $template;

    /**
     * @param \Magento\Backend\Block\Template\Context       $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Fooman\PdfCore\Model\Template                $template
     * @param \Magento\Framework\App\AreaList               $areaList
     * @param array                                         $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Fooman\PdfCore\Model\Template $template,
        \Magento\Framework\App\AreaList $areaList,
        array $data = []
    ) {
        $this->maliciousCode = $maliciousCode;
        $this->template = $template;

        //workaround until https://github.com/magento/magento2/issues/8412 is fixed
        //can stay even after fix is merged as load only loads if not previously loaded
        if ($context->getAppState()->getAreaCode() == Area::AREA_CRONTAB) {
            $areaList->getArea(Area::AREA_CRONTAB)->load(AREA::PART_TRANSLATE);
        }

        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function renderHtmlTemplate()
    {
        $this->template->setArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->template->setTemplateText($this->maliciousCode->filter($this->getTemplateText()));
        $this->template->setVars($this->getTemplateVars());

        $this->template->setDesignConfig(
            [
                'store' => $this->getStoreId(),
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND
            ]
        );

        return $this->template->processTemplate();
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    protected function getTemplateText()
    {
        return $this->prepareTemplateText();
    }

    protected function getFooterLayoutHandle()
    {
        return self::DEFAULT_FOOTER_LAYOUT_HANDLE;
    }

    protected function prepareTemplateText($handle = 'fooman_pdfcore_generic')
    {
        $templateVars = array_keys($this->getTemplateVars());
        $templateText = '{{layout handle="' . $handle . '"';
        foreach ($templateVars as $var) {
            $templateText .= ' ' . $var . '=$' . $var;
        }
        $templateText .= '}}';

        return $templateText;
    }

    protected function getTemplateVars()
    {
        return [];
    }

    public function getStoreId()
    {
        $store = $this->_storeManager->getDefaultStoreView();
        return $store ? $store->getId() : null;
    }

    public function getIntegratedLabelsContent()
    {
        return null;
    }

    public function canApplyIntegratedLabelsContent()
    {
        return false;
    }

    public function getFooterContent()
    {
        /** @var $this->template \Fooman\PdfCore\Model\Template */
        $this->template->setArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $this->template->setTemplateText(
            $this->maliciousCode->filter($this->prepareTemplateText($this->getFooterLayoutHandle()))
        );
        $this->template->setVars($this->getTemplateVars());

        $this->template->setDesignConfig(
            [
                'store' => $this->getStoreId(),
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND
            ]
        );

        return $this->template->processTemplate();
    }

    public function getTitle()
    {
        return 'pdfdocs';
    }

    public function getIncrement()
    {
        return '';
    }

    public function getForcedPageOrientation()
    {
        return false;
    }
}
