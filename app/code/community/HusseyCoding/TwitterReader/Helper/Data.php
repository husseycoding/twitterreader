<?php
class HusseyCoding_TwitterReader_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_collection;
    
    public function collectFeedContent()
    {
        if (!$this->_collection):
            $store =  Mage::app()->getStore()->getCode();
            $screenname = Mage::getStoreConfig('twitterreader/configuration/screen_name');
            $collection = Mage::getModel('twitterreader/twitterreader')->getCollection();
            $select = $collection->getSelect();
            $select
                ->where('store = ?', $store)
                ->where('screen_name = ?', $screenname)
                ->order(array('timestamp DESC'))
                ->limit($this->getDisplayCount());

            $collection = count($collection) ? $collection : false;
            $this->_collection = $collection;
            return $collection;
        else:
            return $this->_collection;
        endif;
    }
    
    private function getDisplayCount($store = null)
    {
        if ($store):
            $count = Mage::getStoreConfig('twitterreader/configuration/tweet_display', $store);
        else:
            $count = Mage::getStoreConfig('twitterreader/configuration/tweet_display');
        endif;
        $count = $count ? (int) $count : 5;
        
        return $count ? $count : 5;
    }
    
    public function getStoreCount($store = null)
    {
        if ($store):
            return $this->getDisplayCount($store) + 10;
        else:
            return $this->getDisplayCount() + 10;
        endif;
    }
    
    public function storeOauthObject($object, $path)
    {
        $object = Zend_Serializer::serialize($object);
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('twitterreader_write');
        $query = 'UPDATE `' . $resource->getTableName('core/config_data') . '` SET `value` = ' . $write->quote($object) . ' WHERE `path` = \'' . $path . '\'';
        $write->query($query);
    }
    
    public function getOauthObject($path)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('twitterreader_read');
        $query = 'SELECT `value` FROM `' . $resource->getTableName('core/config_data') . '` WHERE `path` = \'' . $path . '\'';
        $object = $read->fetchOne($query);
        
        if ($object):
            $object = stripslashes($object);
            try {
                $object = Zend_Serializer::unserialize($object);
            } catch (Exception $e) {
                Mage::register('twitterreader_problem', 1);
                $object = false;
            }
        endif;
        
        return $object;
    }
    
    public function removeOauthObject($path)
    {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('twitterreader_write');
        $query = 'UPDATE `' . $resource->getTableName('core/config_data') . '` SET `value` = NULL WHERE `path` = \'' . $path . '\'';
        $write->query($query);
    }
    
    public function checkCoreRow($path)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('twitterreader_read');
        $query = 'SELECT `config_id` FROM `' . $resource->getTableName('core/config_data') . '` WHERE `path` = \'' . $path . '\'';
        $object = $read->fetchOne($query);
        
        return $object ? true : false;
    }
    
    public function getAccessToken()
    {
        return $this->getOauthObject('twitterreader/configuration/access_token');
    }
    
    public function displayDate($timestamp)
    {
        $timestamp = strtotime($timestamp);
        $format = Mage::getStoreConfig('twitterreader/configuration/date_format');
        $format = $format ? $format : 'D j';
        $return = date($format, $timestamp);
        
        return $return ? ' - ' . $return : '';
    }
    
    public function formatContent($content)
    {
        $length = (int) Mage::getStoreConfig('twitterreader/configuration/tweet_length');
        if ($length && strlen($content) > $length):
            $content = substr($content, 0, $length) . '...';
        endif;
        
        return $content;
    }
    
    public function show()
    {
        if (Mage::getStoreConfig('twitterreader/position/custom')):
            return Mage::app()->getLayout()->getBlock('twitterreader')->toHtml();
        endif;
        
        return '';
    }
}