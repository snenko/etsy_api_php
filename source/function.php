<?php

class Etsy {

    protected $oauthConsumerKey;
    protected $oauthConsumerSecret;
    protected $oauth;

    protected $oauth_token;
    protected $oauth_token_secret;
    protected $oauth_callback_confirmed;
    protected $login_url;

    protected $errors;



    public function __construct($oauthConsumerKey, $oauthConsumerSecret)
    {
        $this->oauthConsumerKey = $oauthConsumerKey;
        $this->oauthConsumerSecret = $oauthConsumerSecret;
        $this->setReqToken();
    }

    protected function getAoth()
    {
        if(!$this->oauth) {
            $this->oauth = new OAuth($this->oauthConsumerKey, $this->oauthConsumerSecret);
        }
        return $this->oauth;
    }


    function setReqToken()
    {
        $oauth = $this->getAoth();
        if($oauth){
            $req_token = $oauth->getRequestToken("https://openapi.etsy.com/v2/oauth/request_token?scope=email_r%20listings_r", 'oob', "GET");

            $this->oauth_token = $req_token['oauth_token'];
            $this->oauth_token_secret = $req_token['oauth_token_secret'];
            $this->oauth_callback_confirmed = $req_token['oauth_callback_confirmed'];
            $this->login_url = $req_token['login_url'];
        }

        return $req_token;
    }

    public function getProducts()
    {
        try {
            $apiurl = "http://openapi.etsy.com/v2/listings/active?api_key=".$this->oauthConsumerKey;
            $results = json_decode(file_get_contents($apiurl));
            return $results;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function getUsers()
    {
        try {
            $apiurl = "http://openapi.etsy.com/v2/users/active?api_key=".$this->oauthConsumerKey;
            $results = json_decode(file_get_contents($apiurl));
            return $results;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }


    public function getShops()
    {
        try {
            $apiurl = "http://openapi.etsy.com/v2/shops/active?api_key=".$this->oauthConsumerKey;
            $results = json_decode(file_get_contents($apiurl));
            return $results;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

}



?>