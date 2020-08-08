<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Controller\Comment;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Display Hello on screen
 */
class Add extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    protected $_comment;

    protected $_storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Ves\Blog\Helper\Data
     */
    protected $_blogHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param Context
     * @param \Magento\Store\Model\StoreManager
     * @param \Magento\Framework\View\Result\PageFactory
     * @param \Ves\Blog\Helper\Data
     * @param \Magento\Framework\Controller\Result\ForwardFactory
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ves\Blog\Helper\Data $blogHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
        ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_blogHelper = $blogHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }
	/**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->inlineTranslation->suspend();
    	$resultRedirect = $this->resultRedirectFactory->create();
    	if($data = $this->getRequest()->getPostValue()) {
            if((isset($data['g-recaptcha-response']) && $data['g-recaptcha-response'] !='') || !isset($data['g-recaptcha-response'])){
                $model = $this->_objectManager->create('Ves\Blog\Model\Comment');
                $store = $this->_storeManager->getStore();
                $data['stores'][] = $store->getId();
                $auto_public = $this->_blogHelper->getConfig("post_page/auto_public");
                if($auto_public){
                    $data['is_active'] = 1;
                }else{
                    $data['is_active'] = 0;
                }
                $model->setData($data);
                try{
                    $model->save();
                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($this->scopeConfig->getValue('post_page/email/email_template', $storeScope))
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($this->scopeConfig->getValue('post_page/email/sender_email_identity', $storeScope))
                        ->addTo($this->scopeConfig->getValue('post_page/email/recipient_email', $storeScope))
                        ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();

                    if($auto_public){
                        $this->messageManager->addSuccess(__('Your comment added'));
                    }else{
                        $this->messageManager->addSuccess(__('Your comment added, it will be published soon.'));
                    }
                }catch(\Exception $e){
                    $this->inlineTranslation->resume();
                    $this->messageManager->addError(
                        __('We can\'t process your request right now. Sorry, that\'s all we know.')
                    );
                }
            }else{
                $this->inlineTranslation->resume();
                $this->messageManager->addError(
                    __('Please click reCAPTCHA')
                );
            }
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }
}