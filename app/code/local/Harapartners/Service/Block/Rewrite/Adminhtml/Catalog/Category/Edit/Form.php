<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Category edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form {
    
    // Get current edit store HP Yang
    protected function _getAdminStore(){
    	return Mage::app()->getRequest()->getParam('store', 0);
    }

    protected function _prepareLayout(){
		parent::_prepareLayout();
        $category = $this->getCategory();
        $categoryId = (int) $category->getId(); // 0 when we create category, otherwise some value for editing category
        
        //Haraparnters, Jun/Yang
        if (!in_array($categoryId, $this->getRootIds())) {
        	// Release button HP Yang
            $this->setChild('release_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Disable Products'),
                        'onclick'   => "setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)) . "')",
                        'class' => 'delete'
                    ))
            );
            // Undo Release button HP Yang
            $this->setChild('undo_release_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Enable Products'),
                        'onclick'   => "setLocation('" . $this->getUrl('*/*/release', array('_current' => true, 'store' => $this->_getAdminStore(), 'con' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) . "')",
                        'class' => 'release'
                    ))
            );
            //Jun import products
            $this->setChild('import_product_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Import Products'),
                        'onclick'   => "setLocation('" . $this->getUrl('import/adminhtml_import/newByCategory', array('category_id' => $categoryId)) . "')",
                        'class' => 'add'
                    ))
            );
        }
        
        return $this;
    }

    
    // Release button HP Yang
    public function getReleaseButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('release_button');
        }
        return '';
    } 
    
    // Undo Release button HP Yang
    public function getUndoReleaseButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('undo_release_button');
        }
        return '';
    }
    
    // Harapartners, Jun, Import products
	public function getImportProductButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('import_product_button');
        }
        return '';
    }

  
}