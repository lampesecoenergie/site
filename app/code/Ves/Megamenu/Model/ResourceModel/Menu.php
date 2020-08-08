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
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Megamenu\Model\ResourceModel;
use Magento\Framework\App\Filesystem\DirectoryList;

class Menu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var \Ves\Megamenu\Helper\Editor
     */
    protected $editor;

    protected $_vesData;

    /**
     * Menu constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Ves\Megamenu\Helper\Editor $editor
     * @param \Ves\Megamenu\Helper\Data $vesData
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Ves\Megamenu\Helper\Editor $editor,
        \Ves\Megamenu\Helper\Data $vesData,
        $connectionName = null
        ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->editor = $editor;
        $this->_vesData = $vesData;
    }

    protected function _construct()
    {
        $this->_init('ves_megamenu_menu', 'menu_id');
    }

    public function extractItem($items){
        if(is_array($items)){
            foreach ($items as $item) {
                if(isset($item['children']) && is_array($item['children'])){
                    $this->extractItem($item['children']);
                }
                unset($item['children']);
                $this->_data[] = $item;
            }
        }
    }

    public function decodeImg($str){
        $count = substr_count($str, "<img");
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $firstPosition = 0;
        for ($i=0; $i < $count; $i++) {
            if($firstPosition==0) $tmp = $firstPosition;
            if($tmp>strlen($str)) continue;
            $firstPosition = strpos($str, "<img", $tmp);
            $nextPosition = strpos($str, "/>", $firstPosition);
            $tmp = $nextPosition;
            $length = $nextPosition - $firstPosition;
            $img = substr($str, $firstPosition, $length+2);
            $newImg = $this->_vesData->filter($img);
            $f = strpos($newImg, 'src="', 0)+5;
            $n = strpos($newImg, '"', $f+5);
            $src = substr($newImg, $f, ($n-$f));
            if( !strpos($img, 'placeholder.gif')){
                $src1 = '';
                if( strpos($newImg, '___directive')){
                    $e = strpos($newImg, '___directive', 0) + 13;
                    $e1 = strpos($newImg, '/key', 0);
                    $src1 = substr($newImg, $e, ($e1-$e));
                    $src1 = base64_decode($src1);
                }else{
                    $mediaP = strpos($src, "wysiwyg", 0);
                    $src1 = substr($src, $mediaP);
                    $src1 = '{{media url="'.$src1.'"}}';
                }
                $newImg = str_replace($src, $src1, $newImg);
                $str = str_replace($img, $newImg, $str);
            }
        }
        return $str;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $table = $this->getTable('ves_megamenu_item');
        $where = ['menu_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($table, $where);
        $params = json_decode($object->getParams(), true);
        $this->extractItem($params);
        $items = $this->_data;
        $strucuture = json_decode($object->getStructure(), true);
        $fields = $this->editor->getFields();
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if(count($strucuture)>0){
            try{
                $data = [];
                if(is_array($items)){
                    foreach ($items as $k => $v) {
                        $v['menu_id'] = $object->getId();
                        unset($v['id']);
                        unset($v['htmlId']);
                        $v['show_name'] = 1;
                        if(is_array($v)){
                            foreach ($v as $x => $y) {
                                if(isset($fields[$x]) && ($fields[$x]['type']=='image' || $fields[$x]['type']=='file') ){
                                    $v[$x] = str_replace($mediaUrl, "", $y);
                                }
                                if(isset($fields[$x]) && $fields[$x]['type']=='editor'){
                                    $v[$x] = $this->_vesData->decodeImg($y);
                                }
                            }
                        }

                        foreach ($fields as $k1 => $v1) {
                            if ($v1['type'] != 'fieldset') {
                                if(!isset($v[$k1])) {
                                    $v[$k1] = '';
                                }
                            }
                        }

                        $data[] = $v;
                    }
                }
                $this->getConnection()->insertMultiple($table, $data);
            }catch(\Exception $e){
                die($e->getMessage());
            }
        }


        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('ves_megamenu_menu_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['menu_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['menu_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'alias';
        }
        return parent::load($object, $value, $field);
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $mainTable = $this->getMainTable();
        $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
        $select = $this->getConnection()
        ->select();
        if($this->getStore() && $storeId=$this->getStore()->getId()){
            $select->join(
                ["cbs" => $this->getTable("ves_megamenu_menu_store")],
                "{$mainTable}.menu_id = cbs.menu_id",
                []
                )
            ->where(
                "cbs.store_id IN (0,?)",
                $storeId
                );
        }
        $select->from($this->getMainTable())->where($field . '=?', $value);
        return $select;
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $menuId = $object->getId();
        if ($menuId && ( $object->getStructure() != '' || $object->getStructure() != '[]') ) {
            $select = $this->getConnection()->select()->from($this->getTable('ves_megamenu_item'))
            ->where(
                'menu_id = ?',
                (int)$menuId
                );
            $data = $this->getConnection()->fetchAll($select);
            $fields = $this->editor->getFields();
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $menuItems = [];
            if(!empty($data)){
                foreach ($data as $k => $v) {

                    if(is_array($v)){
                        foreach ($v as $x => $y) {
                            if(isset($fields[$x]) && ($fields[$x]['type']=='image' || $fields[$x]['type']=='file') && $y!='' ){
                                $v[$x] = $mediaUrl.$y;
                            }
                        }
                    }
                    $v['htmlId'] = 'vesitem-' . $v['id'] . time() . rand();
                    $menuItems[$v['item_id']] = $v;
                }
            }
            $object->setData('menuItems', $menuItems);
        }

        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    public function lookupStoreIds($pageId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('ves_megamenu_menu_store'),
            'store_id'
            )
        ->where(
            'menu_id = ?',
            (int)$pageId
            );
        return $connection->fetchCol($select);
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['menu_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('ves_megamenu_item'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if($this->_store){
            return $this->_storeManager->getStore($this->_store);
        }else{
            return false;
        }
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->getIsUniqueBlockToStores($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('A menu alias with the same properties already exists in the selected store.')
                );
        }
        return $this;
    }

    public function getIsUniqueBlockToStores(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->_storeManager->hasSingleStore()) {
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->getConnection()->select()->from(
            ['cb' => $this->getMainTable()]
            )->join(
            ['cbs' => $this->getTable('ves_megamenu_menu_store')],
            'cb.menu_id = cbs.menu_id',
            []
            )->where(
            'cb.alias = ?',
            $object->getData('alias')
            )->where(
            'cbs.store_id IN (?)',
            $stores
            );

            if ($object->getId()) {
                $select->where('cb.menu_id <> ?', $object->getId());
            }

            if ($this->getConnection()->fetchRow($select)) {
                return false;
            }

            return true;
        }
    }
