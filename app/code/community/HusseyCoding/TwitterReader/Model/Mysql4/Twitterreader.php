<?php
class HusseyCoding_TwitterReader_Model_Mysql4_Twitterreader extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('twitterreader/twitter_reader', 'id');
    }
}