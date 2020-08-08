<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 27/7/19
 * Time: 1:28 PM
 */

namespace Ced\Amazon\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Api\SearchCriteriaBuilder;

class Account extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var
     */
    private $_shippingMethod;

    private  $searchCriteriaBuilder;

    private  $account;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Ced\Amazon\Model\Source\Account $account,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->account = $account;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array|null
     */
    protected function _getShippingMethod()
    {
        if ($this->_shippingMethod === null) {
            $shipMethod = $this->account->toOptionArray();

            $this->_shippingMethod = $shipMethod;
        }
        return $this->_shippingMethod;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getShippingMethod() as $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}