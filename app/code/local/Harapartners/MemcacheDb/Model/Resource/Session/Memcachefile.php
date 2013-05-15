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

class Harapartners_MemcacheDb_Model_Resource_Session_Memcachefile extends Harapartners_MemcacheDb_Model_Resource_Session_File {
    
    //Confirmed: this bug is caused by php-fpm, 'service php-fpm restart' must be excuted everytime!
    //WARNING, const defined in this class will cause 'Fatal error: Undefined class constant' on magento-totsy server
    //This bug is NOT reproducible on other servers, but for simplicity, all const are protected variable now
    protected $_sessionDataPrefix = 'MDBS_';
    protected $_sessionDataExpire = 604800; //7 days, safety value for garbage collection
    protected $_sessionDataSyncInterval = 900; //in seconds, how often Memcache sync with File
    protected $_automaticCleaningFactor = 50000; //garbage Collection with 1/50000 chance per session close
    
    protected $_memcache = null;
    protected $_lastDbSyncTimestamp = null; //read-from/write-to are both considered sync
    
    public function __construct(){
        $this->_memcache = Mage::getSingleton('memcachedb/resource_memcache');
    }
    
    public function getMemcache(){
        if(!$this->_memcache){
            $this->_memcache = Mage::getSingleton('memcachedb/resource_memcache');
        }
        return $this->_memcache;
    }
    
    protected function _readFromMemcache($sessId){
        $sessData = '';
        if(!($memcache = $this->getMemcache())){
            return '';
        }
           try{
               $rawData = $memcache->read($this->_sessionDataPrefix . $sessId);
               if(!!$rawData
                       && !!($sessionWrapper = json_decode($rawData, true))
                       && ($sessionWrapper['head']['expire'])
                       && $sessionWrapper['head']['expire'] > Varien_Date::toTimestamp(true)
                       && isset($sessionWrapper['body'])
                       && !!$sessionWrapper['body']){
                   $sessData = $sessionWrapper['body'];
                   if(isset($sessionWrapper['head']['db_updated_at']) 
                           && !!$sessionWrapper['head']['db_updated_at']){
                       $this->_lastDbSyncTimestamp = $sessionWrapper['head']['db_updated_at'];
                   }
               }
           }catch(Exception $e){
           }
        return $sessData;
    }
    
    //Note: write will need $this->_lastDbSyncTimestamp, be sure the maintain this value carefully: update when read-from/write-to database
    protected function _writeToMemcache($sessId, $sessData){
        $isSuccess = false;
        if(!($memcache = $this->getMemcache())){
            return false;;
        }
           try{
               $sessionWrapper = array();
               $sessionWrapper['head'] = array(
                       'updated_at'    =>    Varien_Date::toTimestamp(true),
                       'db_updated_at'    =>    $this->_lastDbSyncTimestamp,
                       'expire'         =>    Varien_Date::toTimestamp(true) + $this->getLifeTime()
               );
               $sessionWrapper['body'] = $sessData;
               $rawData = json_encode($sessionWrapper);
               //$rawData = gzdeflate($rawData, 9); //'DEFLATE' compression may have a small avantage over 'ZLIB';
               $isSuccess = $memcache->write($this->_sessionDataPrefix . $sessId, $rawData, MEMCACHE_COMPRESSED, $this->_sessionDataExpire);
           }catch(Exception $e){
           }
        return $isSuccess;
    }
    
    // ================================================== //
    // Session related functions //
    // ================================================== //

    public function read($sessId){
        $sessData = $this->_readFromMemcache($sessId);
        //fallback to DB if memcache is not available or memcache miss
        if(!$this->getMemcache()->hasConnection()
                || !$sessData){
            $sessData = parent::read($sessId);
            $this->_lastDbSyncTimestamp = Varien_Date::toTimestamp(true); //update the last sync timestamp
            $this->_writeToMemcache($sessId, $sessData);
        }
        return $sessData;
    }

    public function write($sessId, $sessData){
        $isSuccess = false;
        //fallback to DB is memcache is not available or memcached timestamp is too old
        if(!$this->getMemcache()->hasConnection()
                || !$this->_lastDbSyncTimestamp
                || $this->_lastDbSyncTimestamp + $this->_sessionDataSyncInterval < Varien_Date::toTimestamp(true)){
            $isSuccess = parent::write($sessId, $sessData);
            if($isSuccess){
                $this->_lastDbSyncTimestamp = Varien_Date::toTimestamp(true); //update the last sync timestamp
            }
        }
        $isSuccess = $this->_writeToMemcache($sessId, $sessData);
        return $isSuccess;        
    }

    public function destroy($sessId){
        $this->getMemcache()->delete($this->_sessionDataPrefix . $sessId);
        parent::destroy($sessId);
        return true;
    }
    
}