<?php

class Magestore_RewardPointsReferFriends_Block_Refer_Gmail extends Magestore_RewardPointsReferFriends_Block_Refer_Abstract {

    /**
     * get Contacts list to show
     * 
     * @return array
     */
    public function getContactss() {
        return $this->test();
        $list = array();
        $request = $this->getRequest();
        if (!$request->getParam('oauth_token') && !$request->getParam('oauth_verifier'))
            return $list;

        $google = Mage::getSingleton('rewardpointsreferfriends/refer_gmail');
        $oauthData = array(
            'oauth_token' => $request->getParam('oauth_token'),
            'oauth_verifier' => $request->getParam('oauth_verifier'),
        );
        $accessToken = $google->getAccessToken($oauthData, unserialize($google->getGmailRequestToken()));
        $oauthOptions = $google->getOptions();

        $httpClient = $accessToken->getHttpClient($oauthOptions);
        $this->test();
        $gdata = new Zend_Gdata($httpClient);
        $query = new Zend_Gdata_Query('https://www.google.com/m8/feeds/contacts/default/full');
        $query->setMaxResults(10000);
        $feed = array();
        try {
            $feed = $gdata->getFeed($query);
        } catch (Exception $e) {
            
        }

        foreach ($feed as $entry) {
            $_contact = array();
            $xml = simplexml_load_string($entry->getXML());
            $_contact['name'] = $entry->title;
            foreach ($xml->email as $e) {
                $email = '';
                if (isset($e['address']))
                    $email = (string) $e['address'];
                if ($email) {
                    $_contact['email'] = $email;
                    $list[] = $_contact;
                }
            }
        }
        return $list;
    }

    public function curl_file_get_contents($url) {
        $curl = curl_init();
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

        curl_setopt($curl, CURLOPT_URL, $url); //The URL to fetch. This can also be set when initializing a session with curl_init().
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); //The number of seconds to wait while trying to connect.	

        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent); //The contents of the "User-Agent: " header to be used in a HTTP request.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); //To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); //To automatically set the Referer: field in requests where it follows a Location: redirect.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //To stop cURL from verifying the peer's certificate.
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }

    public function getContacts() {
        $client_id = $this->_getConsumerKey();
        $client_secret = $this->_getConsumerSecret();
        $redirect_uri = Mage::getUrl("rewardpointsreferfriends/index/gmail", array('_nosid' => true,'_secure'=>false));
        $max_results = 10000;

        $auth_code = $_GET["code"];

        $fields = array(
            'code' => urlencode($auth_code),
            'client_id' => urlencode($client_id),
            'client_secret' => urlencode($client_secret),
            'redirect_uri' => urlencode($redirect_uri),
            'grant_type' => urlencode('authorization_code')
        );
        $post = '';
        foreach ($fields as $key => $value) {
            $post .= $key . '=' . $value . '&';
        }
        $post = rtrim($post, '&');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
        curl_setopt($curl, CURLOPT_POST, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $result = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($result);
        $accesstoken = $response->access_token;

        $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $max_results . '&oauth_token=' . $accesstoken;
        $xmlresponse = $this->curl_file_get_contents($url);
        if ((strlen(stristr($xmlresponse, 'Authorization required')) > 0) && (strlen(stristr($xmlresponse, 'Error ')) > 0)) {
            echo "<h2>OOPS !! Something went wrong. Please try reloading the page.</h2>";
            exit();
        }
        echo "<h3>Email Addresses:</h3>";
        $xml = new SimpleXMLElement($xmlresponse);
        $xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
        $list = array();
        foreach ($xml->entry as $entry) {
            $_contact = array();
            $_contact['name']= (string)$entry->title;	
            foreach ($entry->xpath('gd:email') as $email) {				
                    $_contact['email']= (string)$email->attributes()->address;		
            }
            if (isset($_contact['email']) && $_contact['email'])
                    $list[] = $_contact;
        }
        return $list;
    }

    public function _getHelper() {
        return Mage::helper('rewardpointsreferfriends');
    }

    protected function _getConsumerKey() {
        return $this->_getHelper()->getReferConfig('google_consumer_key');
    }

    protected function _getConsumerSecret() {
        return $this->_getHelper()->getReferConfig('google_consumer_secret');
    }

}
