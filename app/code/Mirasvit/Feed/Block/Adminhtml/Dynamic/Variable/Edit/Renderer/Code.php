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


namespace Mirasvit\Feed\Block\Adminhtml\Dynamic\Variable\Edit\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;

class Code extends AbstractElement
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Fieldset
     */
    protected $fieldset;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        Fieldset $fieldset,
        Registry $registry,
        LayoutInterface $layout,
        Factory $factory,
        CollectionFactory $collectionFactory,
        Escaper $escaper
    ) {
        $this->fieldset = $fieldset;
        $this->registry = $registry;
        $this->layout = $layout;

        parent::__construct($factory, $collectionFactory, $escaper);
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        return $this->layout
            ->createBlock('Magento\Backend\Block\Template')
            ->setData('php_code', $this->getVariable()->getPhpCode())
            ->setData('id', $this->getVariable()->getId())
            ->setTemplate('Mirasvit_Feed::dynamic/variable/edit/form.phtml')
            ->toHtml();
    }

    /**
     * @return \Mirasvit\Feed\Model\Dynamic\Variable
     */
    public function getVariable()
    {
        return $this->registry->registry('current_model');
    }
}
