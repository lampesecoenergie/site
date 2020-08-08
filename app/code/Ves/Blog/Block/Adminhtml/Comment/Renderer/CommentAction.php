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
namespace Ves\Blog\Block\Adminhtml\Comment\Renderer;
use Magento\Framework\UrlInterface;

class CommentAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{

	/**
	 * @var Magento\Framework\UrlInterface
	 */
	protected $_urlBuilder;

    /**
     * @param \Magento\Backend\Block\Context
     * @param UrlInterface
     */
	public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url $url
        ){
		$this->_urlBuilder = $url;
        parent::__construct($context);
	}

	public function _getValue(\Magento\Framework\DataObject $row){
		$editUrl = $this->_urlBuilder->getUrl(
                                'vesblog/comment/edit',
                                [
                                    'comment_id' => $row['comment_id']
                                ]
                            );

		$deleteUrl = $this->_urlBuilder->getUrl(
                                'vesblog/comment/delete',
                                [
                                    'comment_id' => $row['comment_id']
                                ]
                            );
		return sprintf("<a target='_blank' href='%s'>Edit</a>&nbsp;|&nbsp;<a target='_blank' href='%s'>Delete</a>", $editUrl, $deleteUrl);
	}
}