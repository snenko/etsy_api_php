<?php

$oauth = new OAuth(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
$oauth->setToken($access_token, $access_token_secret);


try {

    $url = "https://openapi.etsy.com/v2/private/listings";

    $params = array('listing_id' => $result->listing_id,
        //'quantity' => $result->quantity,
        //'title' => $result->title,
        'description' => $new_description);

    $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
    $json = $oauth->getLastResponse();
    print_r(json_decode($json, true));
} catch (OAuthException $e) {

    echo $e->getMessage();
    echo $oauth->getLastResponse();
    echo $oauth->getLastResponseInfo();
}