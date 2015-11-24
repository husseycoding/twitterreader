<?php
class HusseyCoding_TwitterReader_Model_Manage
{   
    public function cron()
    {
        if (!Mage::registry('twitterreader_runonce')):
            $count = Mage::getModel('twitterreader/twitterreader')->getCollection();
            $count = count($count);
            if ($this->getLatest() || $this->getCombinedCount() < $count):
                $this->cleanUp();
            endif;
            Mage::register('twitterreader_runonce', true);
        endif;
        
        return true;
    }
    
    private function getCombinedCount()
    {
        $return = 0;
        foreach (Mage::app()->getStores() as $store):
            $return = $return + Mage::helper('twitterreader')->getStoreCount($store->getCode());
        endforeach;
        
        return $return;
    }
    
    private function getLatest()
    {
        $stores = array();
        $storecount = array();
        foreach (Mage::app()->getStores() as $store):
            $screenname = Mage::getStoreConfig('twitterreader/configuration/screen_name', $store->getCode());
            if ($screenname):
                $stores[$store->getCode()] = $screenname;
                $storecount[$store->getCode()] = Mage::helper('twitterreader')->getStoreCount($store->getCode());
            endif;
        endforeach;
        $key = Mage::getStoreConfig('twitterreader/configuration/consumer_key');
        $secret = Mage::getStoreConfig('twitterreader/configuration/consumer_secret');
        $access = Mage::helper('twitterreader')->getAccessToken();

        if (!$key || !$secret || !$access):
            return false;
        endif;

        $config = array(
            'callbackUrl' => Mage::helper('adminhtml')->getUrl('adminhtml/twitterReader_callback/access'),
            'siteUrl' => 'https://api.twitter.com/oauth',
            'consumerKey' => $key,
            'consumerSecret' => $secret
        );

        $client = $access->getHttpClient($config);
        $client->setUri('https://api.twitter.com/1.1/application/rate_limit_status.json');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet('resources', 'account,statuses');

        try {
            $response = $client->request();

            $limit = Zend_Json::decode($response->getBody());
            $credlimit = $limit['resources']['account']['/account/verify_credentials'];
            if ($credlimit['remaining'] > 0):
                $client->setUri('https://api.twitter.com/1.1/account/verify_credentials.json');
                $client->setMethod(Zend_Http_Client::GET);
                $response = $client->request();

                $status = Zend_Json::decode($response->getBody());

                if (isset($status['errors'])) return false;
                
                $userlimit = $limit['resources']['statuses']['/statuses/user_timeline'];
                if ($userlimit['remaining'] >= $this->findRequestCount($stores, $storecount)):
                    $screencontent = array();
                    $storecontent = array();
                    foreach ($stores as $store => $screenname):
                        $screenstore = isset($screencontent[$screenname]) ? $screencontent[$screenname] : false;
                        if ($screenstore && $storecount[$store] <= $storecount[$screenstore]):
                            $storecontent[$store] = $storecontent[$screenstore];
                        else:
                            $client->setUri('https://api.twitter.com/1.1/statuses/user_timeline.json');
                            $client->setMethod(Zend_Http_Client::GET);
                            $client->setParameterGet('screen_name', $screenname);
                            $client->setParameterGet('count', $storecount[$store]);
                            $client->setParameterGet('trim_user', 'true');
                            $client->setParameterGet('exclude_replies', 'true');
                            $client->setParameterGet('include_rts', 'false');

                            $response = $client->request();

                            $content = Zend_Json::decode($response->getBody());
                            $screencontent[$screenname] = $store;
                            $storecontent[$store] = $content;
                        endif;
                    endforeach;
                endif;
            endif;
        } catch (Exception $e) {
            return false;
        }
        
        $cleanup = $this->processStoreContent($storecontent);
        
        return $cleanup;
    }
    
    private function findRequestCount($stores, $storecount)
    {
        $screencontent = array();
        $return = 0;
        foreach ($stores as $store => $screenname):
            $screenstore = isset($screencontent[$screenname]) ? $screencontent[$screenname] : false;
            if ($screenstore && $storecount[$store] <= $storecount[$screenstore]):
                $return++;
            else:
                $screencontent[$screenname] = $store;
            endif;
        endforeach;
        
        return count($stores) - $return;
    }
    
    private function processStoreContent($storecontent)
    {
        $cleanup = false;
        $ids = $this->getIdsByStore();
        foreach ($storecontent as $store => $content):
            foreach ($content as $tweet):
                $newid = $tweet['id_str'];
                if (!in_array($newid, $ids[$store])):
                    $this->storeTweet($tweet, $store);
                    $cleanup = true;
                endif;
            endforeach;
        endforeach;
        
        return $cleanup;
    }
    
    private function getIdsByStore()
    {
        $ids = array();
        foreach(Mage::getModel('twitterreader/twitterreader')->getCollection() as $content):
            $ids[$content->getStore()][] = $content->getTwitterId();
        endforeach;
        
        return $ids;
    }
    
    private function storeTweet($tweet, $store)
    {
        $twitterid = $tweet['id_str'];
        $content = $tweet['text'];
        $timestamp = strtotime($tweet['created_at']);
        $timestamp = date('Y-m-d H:i:s', $timestamp);
        $screenname = Mage::getStoreConfig('twitterreader/configuration/screen_name', $store);
        
        $newtweet = Mage::getModel('twitterreader/twitterreader');
        $newtweet
            ->setTwitterId($twitterid)
            ->setContent($content)
            ->setStore($store)
            ->setScreenName($screenname)
            ->setTimestamp($timestamp);
        
        $newtweet->save();
    }
    
    private function cleanUp()
    {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('twitterreader_write');
        
        $keepids = array();
        foreach (Mage::app()->getStores() as $store):
            $screenname = Mage::getStoreConfig('twitterreader/configuration/screen_name', $store->getCode());
            $count = Mage::helper('twitterreader')->getStoreCount($store->getCode());
            $collection = Mage::getModel('twitterreader/twitterreader')->getCollection();
            $select = $collection->getSelect();
            $select
                ->where('store = ?', $store->getCode())
                ->where('screen_name = ?', $screenname)
                ->order(array('timestamp DESC'))
                ->limit($count);
            
            foreach ($collection as $tweet):
                $keepids[] = $tweet->getId();
            endforeach;
        endforeach;
        
        $write->delete(
            $resource->getTableName('twitterreader/twitter_reader'),
            array('id NOT IN (?)' => $keepids)
        );
    }
}