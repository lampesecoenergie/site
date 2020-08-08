<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_m2.1.9
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Block\Adminhtml\Profile\Ui\View;

class OptionalMapping extends \Magento\Backend\Block\Template
{
    public $_template = 'Ced_Cdiscount::profile/attribute/optionMapping.phtml';

    public $objectManager;

    public $request;

    public $coreRegistry;
    public $profile;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\Cdiscount\Helper\Profile $profile,
        array $data = []
    ) {
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->coreRegistry = $registry;
        $this->profile = $profile;
        parent::__construct($context, $data);
    }

    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Attribute'),
                'onclick' => 'return cdiscountAttributeOptionControl.addItem()',
                'class' => 'add'
            ]
        );

        $button->setName('cdiscount_add_attribute_mapping_button');
        return $button->toHtml();
    }

    public function magentoOptions()
    {
        $magentoOptions = $this->getMagentoOptions();
        $magentoOptions = json_decode($magentoOptions, true);
        return $magentoOptions;
    }

    public function cdiscountOptions()
    {
        $data = [];
        $cdOptions = $this->getCdiscountOptions();
        if ($cdOptions != '[]') {
            return json_decode($cdOptions, true);
        } else {
            $currentName = $this->getCurrentAttribute();
            $profileId = $this->getProfileId();
            if ($profileId > 0) {
                $profile = $this->profile->getProfile(null, $profileId);
                $requiredAttributes = $profile->getRequiredAttributes();
                foreach ($requiredAttributes as $key => $value) {
                    if ($key == $currentName) {
                        $data = $value['options'];
                    }
                }
            }
        }
        return $data;
    }

    public function render(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getMappedAttributeEdit()
    {
        $data = [];
        $profileId = $this->getProfileId();
        $alreadyMappedOptions = $this->getAlreadyMappedOptions();
        $alreadyMappedOptions = @json_decode($alreadyMappedOptions,true);
        $attribute = [];
        $currentProfileName = $this->getCurrentAttribute();
        if ($profileId > 0) {
            $profile = $this->profile->getProfile(null, $profileId);
            $requiredAttributes = $profile->getRequiredAttributes();
            if (isset($requiredAttributes) and !empty($requiredAttributes)) :
                foreach ($requiredAttributes as $key => $attribute) {
                    if ($key == $currentProfileName) {
                        $data = $attribute['magento_mapped_options'] = $attribute['option_mapping'];
                    }
                }

                $optionalAttributes = $profile->getOptionalAttributes();
                foreach ($optionalAttributes as $key => $attribute) {
                    if ($key == $currentProfileName) {
                        $data =
                        $attribute['magento_mapped_options'] =
                            json_decode($attribute['option_mapping'], true);
                    }
                }
            endif;
        }
        // If Already Mapped Options found from phtml then show this on every edit
        if (isset($alreadyMappedOptions) && !empty($alreadyMappedOptions)) {
            $data = $alreadyMappedOptions;
        }
        return $data;
    }

    public function getProfileId()
    {
        return $this->getCurrentProfile();
    }
}