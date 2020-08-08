<?php

namespace Acyba\GLS\Block\Import;

use Magento\Framework\Data\Form\Element\AbstractElement;


class Form extends \Magento\Config\Block\System\Config\Form\Field
{


    /**
     * Informations constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve form action
     *
     * @return string
     */
    public function getFormAction()
    {
        // compagnymodule is given in routes.xml
        // controller_name is folder name inside controller folder
        // action is php file name inside above controller_name folder

        return $this->getUrl('/import/import');
    }

}