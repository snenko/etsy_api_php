<?php

include_once "source/function.php";

$etsy = new Etsy($oauthConsumerKey = 's5moesajqivedfa25fmys7bn', $oauthConsumerSecret = 'p6qfo80l9d');

$products = $etsy->getProducts();
$users = $etsy->getUsers();

