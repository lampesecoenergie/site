<?php

namespace Potato\Crawler\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * @api
 */
interface PopularityInterface extends CustomAttributesDataInterface
{
    const ID = 'id';
    const URL = 'url';
    const VIEW = 'view';
    
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
        
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url);
        
    /**
     * @return int
     */
    public function getView();

    /**
     * @param int $view
     * @return $this
     */
    public function setView($view);
}