<?php
class HusseyCoding_TwitterReader_Model_Twitterreader extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('twitterreader/twitterreader');
    }
}