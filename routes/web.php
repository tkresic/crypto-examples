<?php

$router->get('/', function () { return view('index'); });
$router->post('/encrypt-symmetric', 'CryptoController@encryptSymmetric');
$router->post('/decrypt-symmetric', 'CryptoController@decryptSymmetric');
$router->post('/encrypt-asymmetric', 'CryptoController@encryptAsymmetric');
$router->post('/decrypt-asymmetric', 'CryptoController@decryptAsymmetric');
$router->post('/authenticate', 'CryptoController@authenticate');
$router->post('/verify-authentication', 'CryptoController@verifyAuthentication');
$router->post('/sign', 'CryptoController@sign');
$router->post('/verify-signature', 'CryptoController@verifySignature');
