<?php
class HusseyCoding_TwitterReader_CallbackController extends Mage_Adminhtml_Controller_Action
{   
    public function accessAction() 
    {
        $config = array(
            'callbackUrl' => Mage::helper('adminhtml')->getUrl('twitterreader/callback/access'),
            'siteUrl' => 'https://api.twitter.com/oauth',
            'consumerKey' => Mage::getStoreConfig('twitterreader/configuration/consumer_key'),
            'consumerSecret' => Mage::getStoreConfig('twitterreader/configuration/consumer_secret')
        );
        $consumer = new Zend_Oauth_Consumer($config);
        
        try {
            $request = Mage::helper('twitterreader')->getOauthObject('twitterreader/configuration/request_token');
            if ($request):
                $access = $consumer->getAccessToken($_GET, $request);
                Mage::helper('twitterreader')->storeOauthObject($access, 'twitterreader/configuration/access_token');
                Mage::helper('twitterreader')->removeOauthObject('twitterreader/configuration/request_token');
                Mage::getConfig()->saveConfig('twitterreader/configuration/callback_url', '');
            endif;
        } catch (Exception $e) {
            Mage::getConfig()->saveConfig('twitterreader/configuration/callback_url', '');
            Mage::helper('twitterreader')->removeOauthObject('twitterreader/configuration/request_token');
            Mage::helper('twitterreader')->removeOauthObject('twitterreader/configuration/access_token');
        }
        
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/twitterreader');
        $this->getResponse()->setRedirect($url);
    }
    
    public function resetAction()
    {
        Mage::getConfig()->saveConfig('twitterreader/configuration/callback_url', '');
        Mage::helper('twitterreader')->removeOauthObject('twitterreader/configuration/request_token');
        Mage::helper('twitterreader')->removeOauthObject('twitterreader/configuration/access_token');
        
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/twitterreader');
        $this->getResponse()->setRedirect($url);
    }
    
    public function updateAction()
    {
        Mage::getModel('twitterreader/manage')->cron();
        
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/twitterreader');
        $this->getResponse()->setRedirect($url);
    }
}