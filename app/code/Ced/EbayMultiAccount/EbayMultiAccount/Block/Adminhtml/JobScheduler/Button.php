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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Block\Adminhtml\JobScheduler;

/**
 * Class Button
 * @package Ced\EbayMultiAccount\Block\Adminhtml\JobScheduler
 */
class Button extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Button constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $schedulerButtonProps = [
            'id' => 'ebaymultiaccount_jobscheduler_button',
            'label' => __('Scheduler Actions'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getSchedulerButtonOptions(),
        ];
        $this->buttonList->add('scheduler_ids', $schedulerButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function _getSchedulerButtonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['sch_bulk_upload'] = [
            'label' => __('Schedule Bulk Upload'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'ebaymultiaccount/jobScheduler/scheduleBulkUpload') . "')",
            'default' => true,
        ];

        $splitButtonOptions['sch_bulk_revise'] = [
            'label' => __('Schedule Bulk Revise'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'ebaymultiaccount/jobScheduler/scheduleBulkRevise') . "')",
            'default' => false,
        ];

        $splitButtonOptions['sch_bulk_inventory'] = [
            'label' => __('Schedule Bulk Inventory/Price Sync'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'ebaymultiaccount/jobScheduler/scheduleBulkInventory') . "')",
            'default' => false,
        ];

        return $splitButtonOptions;
    }

}
