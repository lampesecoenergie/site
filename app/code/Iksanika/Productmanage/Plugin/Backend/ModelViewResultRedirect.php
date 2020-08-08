<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Plugin\Backend;

class ModelViewResultRedirect
{

    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config $config
    ) {
        $this->redirect = $redirect;
        $this->urlBuilder = $urlBuilder;
        $this->_scopeConfig = $config;
    }

    /**
     * Set url by path
     *
     * @param string $path
     * @param array $params
     * @return $this
     */
    public function aroundSetPath(\Magento\Backend\Model\View\Result\Redirect $subject, \Closure $proceed, $path, array $params = [])
    {
        if(($path == 'catalog/*/' || $path == 'catalog/product/index') &&
           ($this->_scopeConfig->getValue('iksanika_productmanage/columns/redirectAdvancedProductManager')))
        {
            $path = 'productmanage/product/index';
        }
        $subject->setUrl($this->urlBuilder->getUrl($path, $this->redirect->updatePathParams($params)));
        return $subject;
    }

}
