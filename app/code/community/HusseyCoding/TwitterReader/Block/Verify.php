<?php
class HusseyCoding_TwitterReader_Block_Verify extends Mage_Page_Block_Html
{
    public function verifyCredentials()
    {
        if (Mage::app()->getRequest()->getParam('section') == 'twitterreader' && !Mage::app()->getRequest()->getParam('website')):
            $key = Mage::getStoreConfig('twitterreader/configuration/consumer_key');
            $secret = Mage::getStoreConfig('twitterreader/configuration/consumer_secret');
            $request = Mage::helper('twitterreader')->getOauthObject('twitterreader/configuration/request_token');
            $access = Mage::helper('twitterreader')->getOauthObject('twitterreader/configuration/access_token');
            if (Mage::registry('twitterreader_problem')):
                return 'problem';
            elseif (!$key || !$secret):
                return 'consumer_missing';
            elseif (!$request && !$access):
                    $config = array(
                        'callbackUrl' => Mage::helper('adminhtml')->getUrl('twitterreader/callback/access'),
                        'siteUrl' => 'https://api.twitter.com/oauth',
                        'consumerKey' => $key,
                        'consumerSecret' => $secret
                    );
                    $consumer = new Zend_Oauth_Consumer($config);

                    try {
                        $request = $consumer->getRequestToken();
                        Mage::helper('twitterreader')->storeOauthObject($request, 'twitterreader/configuration/request_token');
                        $url = $consumer->getRedirectUrl();
                        Mage::getConfig()->saveConfig('twitterreader/configuration/callback_url', $url);
                        Mage::getConfig()->reinit();
                        Mage::app()->reinitStores();
                        return 'verify_credentials';
                    } catch (Exception $e) {
                        return 'bad_callback';
                    }
            elseif ($request && !$access):
                return 'verify_credentials';
            else:
                $config = array(
                    'callbackUrl' => Mage::helper('adminhtml')->getUrl('twitterreader/callback/access'),
                    'siteUrl' => 'https://api.twitter.com/oauth',
                    'consumerKey' => $key,
                    'consumerSecret' => $secret
                );
                $token = Mage::helper('twitterreader')->getAccessToken();
                
                if ($token):
                    $client = $token->getHttpClient($config);
                    $client->setUri('https://api.twitter.com/1.1/application/rate_limit_status.json');
                    $client->setMethod(Zend_Http_Client::GET);
                    $client->setParameterGet('resources', 'account');

                    try {
                        $response = $client->request();

                        $limit = Zend_Json::decode($response->getBody());
                        $limit = $limit['resources']['account']['/account/verify_credentials'];
                        if ($limit['remaining'] > 0):
                            $client->setUri('https://api.twitter.com/1.1/account/verify_credentials.json');
                            $client->setMethod(Zend_Http_Client::GET);
                            $response = $client->request();

                            $status = Zend_Json::decode($response->getBody());

                            if ($status['errors']):
                                return 'bad_verify';
                            endif;
                        elseif ($limit):
                            $timestamp = Mage::getModel('core/date')->timestamp($limit['reset']);
                            return $timestamp;
                        else:
                            return 'bad_callback';
                        endif;
                    } catch (Exception $e) {
                        return 'bad_verify';
                    }
                
                    return 'ready';
                endif;
            endif;
        endif;
        
        return false;
    }
}