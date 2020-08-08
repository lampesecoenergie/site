<?php
namespace Potato\Crawler\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;

/**
 * Class Useragent
 */
class Useragent extends AbstractFieldArray
{
    const RULE_CONFIG_DEFAULT_VALUE_JSON = '{"_1429288830188_188":{"title":"Magento Crawler","useragent":"MagentoCrawler"}}';
    const RULE_CONFIG_DEFAULT_VALUE_SERIALIZED = 'a:1:{s:18:"_1429288830188_188";a:2:{s:5:"title";s:15:"Magento Crawler";s:9:"useragent";s:14:"MagentoCrawler";}}';

    /** @var mixed|null  */
    protected $serializer = null;

    /**
     * Rule constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        if (@class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
    }
    
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('title', [
            'label' => __('Title'),
            'style' => 'width:120px',
        ]);
        $this->addColumn('useragent', [
            'label' => __('User Agent'),
            'style' => 'width:240px',
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Overwrite default method for compatibility with 2.0.x - 2.x versions
     * @return array|null
     */
    public function getArrayRows()
    {
        if (!$this->getElement()->getValue() || !is_array($this->getElement()->getValue())) {
            if ($this->serializer) {
                $newValue = $this->serializer->unserialize(self::RULE_CONFIG_DEFAULT_VALUE_JSON);
            } else {
                $newValue = unserialize(self::RULE_CONFIG_DEFAULT_VALUE_SERIALIZED);
            }
            $this->getElement()->setValue($newValue);
        }
        return parent::getArrayRows();
    }
}