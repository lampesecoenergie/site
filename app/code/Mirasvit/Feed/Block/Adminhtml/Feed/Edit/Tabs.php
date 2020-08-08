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



namespace Mirasvit\Feed\Block\Adminhtml\Feed\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Mirasvit\Feed\Api\Data\ValidationInterface;
use Mirasvit\Feed\Api\Repository\ValidationRepositoryInterface;

class Tabs extends WidgetTabs
{
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var ValidationRepositoryInterface
     */
    private $validationRepository;

    /**
     * {@inheritdoc}
     * @param Registry         $registry
     * @param Context          $context
     * @param EncoderInterface $jsonEncoder
     * @param Session          $authSession
     */
    public function __construct(
        ValidationRepositoryInterface $validationRepository,
        Registry $registry,
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession
    ) {
        $this->validationRepository = $validationRepository;
        $this->registry = $registry;

        parent::__construct($context, $jsonEncoder, $authSession);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Feed Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        if ($this->getFeed()->getId() > 0) {
            $this->addTab('general_section', [
                'label'   => __('Feed Information'),
                'title'   => __('Feed Information'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\General')->toHtml(),
            ]);

            if ($this->getFeed()->isCsv()) {
                $this->addTab('csv_section', [
                    'label'   => __('Content Settings'),
                    'title'   => __('Content Settings'),
                    'content' => $this->getLayout()
                        ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Csv')->toHtml(),
                ]);
            }

            if ($this->getFeed()->isXml()) {
                $this->addTab('xml_section', [
                    'label'   => __('Content Settings'),
                    'title'   => __('Content Settings'),
                    'content' => $this->getLayout()
                        ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Xml')->toHtml(),
                ]);
            }

            $this->addTab('filter_section', [
                'label'   => __('Filters'),
                'title'   => __('Filters'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\Rule')->toHtml(),
            ]);

            $this->addTab('ga_section', [
                'label'   => __('Google Analytics'),
                'title'   => __('Google Analytics'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\Ga')->toHtml(),
            ]);

            $this->addTab('cron_section', [
                'label'   => __('Scheduled Task'),
                'title'   => __('Scheduled Task'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\Cron')->toHtml(),
            ]);

            $this->addTab('ftp_section', [
                'label'   => __('FTP Settings'),
                'title'   => __('FTP Settings'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\Ftp')->toHtml(),
            ]);

            $this->addTab('additional_section', [
                'label'   => __('Additional'),
                'title'   => __('Additional'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\Additional')->toHtml(),
            ]);

            $this->addTab('history_section', [
                'label'   => __('History'),
                'title'   => __('History'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\History')->toHtml(),
            ]);

            if ($this->validationRepository->getCollection()
                    ->addFieldToFilter(ValidationInterface::FEED_ID, $this->getFeed()->getId())->getSize()
            ) {
                $this->addTab('report_section', [
                    'label'   => __('Report'),
                    'title'   => __('Report'),
                    'content' => $this->getLayout()
                        ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\Report')->toHtml(),
                ]);
            }
        } else {
            $this->addTab('general_section', [
                'label'   => __('Settings'),
                'title'   => __('Settings'),
                'content' => $this->getLayout()
                    ->createBlock('\Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab\NewTab')->toHtml(),
            ]);
        }

        return parent::_beforeToHtml();
    }

    /**
     * Current Feed Model
     *
     * @return \Mirasvit\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->registry->registry('current_model');
    }
}
