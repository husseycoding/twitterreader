<?php
class HusseyCoding_TwitterReader_Model_Observer
{
    public function adminhtmlCoreConfigDataSaveBefore($observer)
    {
        $config = $observer->getConfigData();
        if ($config->getPath() == 'twitterreader/configuration/request_token'):
            if (Mage::helper('twitterreader')->checkCoreRow('twitterreader/configuration/request_token')):
                $config->disableSave();
            endif;
        elseif ($config->getPath() == 'twitterreader/configuration/access_token'):
            if (Mage::helper('twitterreader')->checkCoreRow('twitterreader/configuration/access_token')):
                $config->disableSave();
            endif;
        endif;
    }
}