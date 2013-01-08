<?php
class HusseyCoding_TwitterReader_Block_Tweets extends Mage_Core_Block_Text_List
{
    public function verifyBlock($config, $block)
    {
        if (!Mage::getStoreConfig('twitterreader/position/' . $config)) $this->unsetChild($block);
    }
}