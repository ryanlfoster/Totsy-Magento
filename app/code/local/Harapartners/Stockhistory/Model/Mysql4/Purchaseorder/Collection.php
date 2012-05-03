<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Stockhistory_Model_Mysql4_Purchaseorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function _construct() {
        parent::_construct();
        $this->_init('stockhistory/purchaseorder');
    }
    
    public function loadByCategoryId($categoryId, $status = null) {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        if(!$category || !$category->getId()){
            return null;
        }
        
        $this->getSelect()->where('category_id = ?', $categoryId);
        if(!!$status){
            $this->getSelect()->where('status = ?', $status);
        }
        $this->load();
        
        return $this;
    }
    
}