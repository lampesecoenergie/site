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


namespace Mirasvit\Feed\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mirasvit\Feed\Model\Config\Source\Rule as RuleSource;
use Mirasvit\Feed\Model\Config\Source\Template as TemplateSource;
use Mirasvit\Feed\Model\RuleFactory;
use Mirasvit\Feed\Model\TemplateFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var TemplateSource
     */
    protected $templateSource;

    /**
     * @var RuleSource
     */
    protected $ruleSource;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * {@inheritdoc}
     *
     * @param TemplateSource  $templateSource
     * @param RuleSource      $ruleSource
     * @param TemplateFactory $templateFactory
     * @param RuleFactory     $ruleFactory
     */
    public function __construct(
        TemplateSource $templateSource,
        RuleSource $ruleSource,
        TemplateFactory $templateFactory,
        RuleFactory $ruleFactory
    ) {
        $this->templateSource = $templateSource;
        $this->ruleSource = $ruleSource;
        $this->templateFactory = $templateFactory;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $templatesPath = dirname(__FILE__) . '/data/template/';
        foreach (scandir($templatesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $this->templateFactory->create()->import($templatesPath . $file);
        }

        $rulesPath = dirname(__FILE__) . '/data/rule/';
        foreach (scandir($rulesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            $this->ruleFactory->create()->import($rulesPath . $file);
        }
    }
}
