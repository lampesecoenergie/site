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



namespace Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\Schema;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Registry;
use Mirasvit\Feed\Helper\Output as OutputHelper;

class Xml extends Form
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var OutputHelper
     */
    protected $outputHelper;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        OutputHelper $outputHelper,
        Registry $registry,
        Context $context
    ) {
        $this->registry = $registry;
        $this->outputHelper = $outputHelper;

        $this->_template = 'Mirasvit_Feed::template/edit/tab/schema/xml.phtml';


        parent::__construct($context);
    }

    /**
     * Current template or feed model
     *
     * @return \Mirasvit\Feed\Model\AbstractTemplate
     */
    public function getModel()
    {
        return $this->registry->registry('current_model');
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            "*" => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'schema_xml' => [
                            'component' => 'Mirasvit_Feed/js/template/edit/tab/schema/xml',
                            'config'    => [
                                'liquidTemplate' => $this->getModel()->getLiquidTemplate(),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
