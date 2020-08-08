<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_EbayMultiAccount
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Block;
/**
 * Class Extensions
 * @package Ced\EbayMultiAccount\Block
 */
class Extensions extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var
     */
    protected $_dummyElement;
    /**
     * @var
     */
    protected $_fieldRenderer;
    /**
     * @var
     */
    protected $_values;
    /**
     * @var
     */
    protected $_licenseUrl;

    const LICENSE_USE_HTTPS_PATH = 'web/secure/use_in_adminhtml';

    const LICENSE_VALIDATION_URL_PATH = 'system/license/license_url';

    const HASH_PATH_PREFIX = 'cedcore/extensions/extension_';


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var mixed
     */
    protected $_helper;

    /**
     * Extensions constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\View\Helper\Js $jsHelper,

        array $data = []
    )
    {
        $this->_jsHelper = $jsHelper;
        $this->_authSession = $authSession;
        $this->_objectManager = $objectInterface;
        $this->_helper = $this->_objectManager->get('Ced\EbayMultiAccount\Helper\Data');
        parent::__construct($context, $authSession, $jsHelper);
        $this->_cacheManager = $this->_cache;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $header = $html = $footer = $script = '';

        $header = $this->_getHeaderHtml($element);

        $modules = $this->_objectManager->get('\Ced\EbayMultiAccount\Helper\Feed')->getAllModules();

        $field = $element->addField('extensions_heading', 'note', array(
            'name' => 'extensions_heading',
            'label' => '<a href="javascript:;"><b>Extension Name (version)</b></a>',
            'text' => '<a href="javascript:;"><b>License Information</b></a>',
        ))->setRenderer($this->_getFieldRenderer());
        $html .= $field->toHtml();
        foreach ($modules as $moduleName => $releaseVersion) {
            $moduleProductName = isset($releaseVersion['parent_product_name']) ? $releaseVersion['parent_product_name'] : '';
            if (!is_array($releaseVersion))
                $releaseVersion = isset($releaseVersion['release_version']) ? $releaseVersion['release_version'] : trim($releaseVersion);
            else
                $releaseVersion = isset($releaseVersion['release_version']) ? $releaseVersion['release_version'] : '';

            $html .= $this->_getFieldHtml($element, $moduleName, $releaseVersion, $moduleProductName);
        }
        if (strlen($html) == 0) {
            $html = '<p>' . $this->__('No records found.') . '</p>';
        }
        $footer .= $this->_getFooterHtml($element);

        $script .= $this->_getScriptHtml();

        return $header . $html . $footer . $script;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_objectManager->get('\Magento\Config\Block\System\Config\Form\Field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Framework\DataObject(array('show_in_default' => 1, 'show_in_website' => 1));
        }
        return $this->_dummyElement;
    }

    protected function _getFieldHtml($fieldset, $moduleName, $currentVersion = '0.0.1', $moduleProductName = '')
    {
        $hash = \Ced\EbayMultiAccount\Block\Extensions::HASH_PATH_PREFIX . strtolower($moduleName) . '_hash';
        $level = \Ced\EbayMultiAccount\Block\Extensions::HASH_PATH_PREFIX . strtolower($moduleName) . '_level';

        $helper = $this->_objectManager->create('\Ced\EbayMultiAccount\Helper\Data');

        $configData[$hash] = $helper->getStoreConfig($hash);
        $configData[$level] = $helper->getStoreConfig($level);

        $hash = isset($configData[$hash]) ? $configData[$hash] : '';
        $level = isset($configData[$level]) ? $configData[$level] : '';
        $l = $this->_objectManager->create('\Ced\EbayMultiAccount\Helper\Feed')->getLicenseFromHash($hash, $level);

        $path = self::HASH_PATH_PREFIX . strtolower($moduleName);
        $configData[$path] = $helper->getStoreConfig($path);

        if (isset($configData[$path])) {

            $configData[$path] = $l;
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = '';//(string)$this->getForm()->getConfigRoot()->descend($path);
            $inherit = true;
        }

        $e = $this->_getDummyElement();

        $allExtensions = json_decode($this->_cacheManager->load('all_extensions_by_cedcommerce'), true);
        $name = strlen($moduleProductName) > 0 ? $moduleProductName : $moduleName;
        $releaseVersion = $name . '-' . $currentVersion;
        $warning = '';
        if ($allExtensions && isset($allExtensions[$moduleName])) {
            $url = $allExtensions[$moduleName]['url'];
            $warning = isset($allExtensions[$moduleName]['warning']) ? $allExtensions[$moduleName]['warning'] : '';

            if (strlen($warning) == 0) {
                $releaseVersion = $allExtensions[$moduleName]['release_version'];
                $releaseVersion = '<a href="' . $url . '" target="_blank" title="Upgarde Available(' . $releaseVersion . ')">' . $name . '-' . $currentVersion . '</a>';
            } else {
                $releaseVersion = '<div class="notification-global"><strong class="label">' . $warning . '</strong></div>';
            }
        }
        $buttonHtml = '<div style="float: right;"><div style="font-family:\'Admin Icons\';" class="message-success"></div></div>';
        $type = 'label';
        $title = 'License Number';
        if (strlen($data) == 0) {
            $title = __('Enter the valid license after that you have to click on Save Config button.');

            $buttonHtml = '<div style="clear: both; height:0; width:0; ">&nbsp;</div>';
            $buttonHtml .= '<p class="note"><span>Please fill the valid license number in above field. If you don\'t have license number please <a href="http://cedcommerce.com/licensing?product_name=' . strtolower($moduleName) . '" target="_blank">Get a license number from CedCommerce.com</a></span></p>';
            $type = 'text';
        }

        if ($moduleName && strtolower($moduleName) == 'ced_csvendorpanel') {
            $type = 'label';
            $path = self::HASH_PATH_PREFIX . 'ced_csmarketplace';
            if (isset($configData[$path])) {
                $data = $configData[$path];
                $inherit = false;
            } else {
                $data = (string)$this->getForm()->getConfigRoot()->descend($path);
                $inherit = true;
            }
            if (!$data) {
                $data = 'n/a';
            } else {
                $buttonHtml = '<div style="float: right;"><div  style="font-family:\'Admin Icons\';" class="message-success"></div></div>';
            }
        }

        $field = $fieldset->addField($moduleName, $type,//this is the type of the element (can be text, textarea, select, multiselect, ...)
            array(
                'name' => 'groups[extensions][fields][extension_' . strtolower($moduleName) . '][value]',//this is groups[group name][fields][field name][value]
                'label' => $name . ' (' . $currentVersion . ')',//this is the label of the element
                'value' => $data,//this is the current value
                'title' => $title,
                'inherit' => $inherit,
                'class' => 'validate-cedcommerce-license',
                'style' => 'float:left;',
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),//sets if it can be changed on the default level
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),//sets if can be changed on website level
                'after_element_html' => $buttonHtml,
            ))->setRenderer($this->_getFieldRenderer());

        /* $field = $fieldset->addField(strtolower($moduleName), 'note', array(
            'name'  => 'csmarketplace',
            'label' => '<span style="text-align: center;">'.$name.'-'.$currentVersion.'</span>',
            'text' => '<span style="text-align: center;">'.$releaseVersion.'</span>',
        ))->setRenderer($this->_getFieldRenderer()); */

        return $field->toHtml();
    }

    /**
     * Retrieve local license url
     *
     * @return string
     */
    public function getLicenseUrl()
    {
        if (is_null($this->_licenseUrl)) {
            $secure = false;
            if ($this->_helper->getStoreConfig(self::LICENSE_USE_HTTPS_PATH)) {
                $secure = true;
            }
            $this->_licenseUrl = $this->getUrl($this->_helper->getStoreConfig(self::LICENSE_VALIDATION_URL_PATH), array('_secure' => $secure));
        }
        return $this->_licenseUrl;
    }

    protected function _getScriptHtml()
    {
        $script = '<script type="text/javascript">';
        $script .= "require([
					'jquery', // jquery Library
					'jquery/ui', // Jquery UI Library
					'jquery/validate', // Jquery Validation Library
					'mage/translate' // Magento text translate (Validation message translte as per language)
					], function($){ 
					$.mage.message =  $.mage.__('Please enter a valid License Number.');
					$.validator.addMethod(

					'validate-cedcommerce-license', function (value,licenseElement) {";
        $script .= 'var url = "' . $this->getLicenseUrl() . '?"+licenseElement.id+"="+licenseElement.value;';
        $script .= 'var formId = configForm.formId;';

        $script .= 'var ok = false;console.log(licenseElement.id);';
        $script .= "jQuery.ajax( {
							    url: url,
							    type: 'POST',
							    showLoader: true,
							    data: {form_key:jQuery('input[name=form_key]').val()},
							    async:false
							}).done(function(a) { 
							    var response = a.evalJSON();
								validateTrueEmailMsg = response.message;
								if (response.success == 0) {									
									$.mage.message = validateTrueEmailMsg;
									alert(validateTrueEmailMsg);
									ok = false;
								} else {
									ok = true; 
								}
							});
							return ok;
							";

        $script .= "},$.mage.message);

					});
					 </script>
					";
        return $script;
    }
}
