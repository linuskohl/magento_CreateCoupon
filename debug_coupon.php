<?php
/**
 * Debug MT Coupon API for  Magento
 * 
 * @package    CreateCoupon
 * @author     Munich Trading UG <info@munichtrading.com>
 * @copyright  2015 Munich Trading UG
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 */

$callbackUrl                    = "https://HOSTNAME/debug_coupon.php";
$temporaryCredentialsRequestUrl = "https://HOSTNAME/oauth/initiate?oauth_callback=" . urlencode ( $callbackUrl );
$adminAuthorizationUrl          = 'https://HOSTNAME/ADMIN_DIRECTORY/oauth_authorize';
$accessTokenRequestUrl          = 'https://HOSTNAME/oauth/token';
$apiUrl                         = 'https://HOSTNAME/api/rest';
$consumerKey                    = 'CONSUMER_KEY';
$consumerSecret                 = 'CONSUMER_SECRET';

session_start ();
if (! isset ( $_GET ['oauth_token'] ) && isset ( $_SESSION ['state'] ) && $_SESSION ['state'] == 1) {
	$_SESSION ['state'] = 0;
}
try {
	$authType = ($_SESSION ['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
	$oauthClient = new OAuth ( $consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType );
	$oauthClient->enableDebug ();
	
	if (! isset ( $_GET ['oauth_token'] ) && ! $_SESSION ['state']) {
		$requestToken = $oauthClient->getRequestToken ( $temporaryCredentialsRequestUrl );
		$_SESSION ['secret'] = $requestToken ['oauth_token_secret'];
		$_SESSION ['state'] = 1;
		header ( 'Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken ['oauth_token'] );
		exit ();
	} else if ($_SESSION ['state'] == 1) {
		$oauthClient->setToken ( $_GET ['oauth_token'], $_SESSION ['secret'] );
		$accessToken = $oauthClient->getAccessToken ( $accessTokenRequestUrl );
		$_SESSION ['state'] = 2;
		$_SESSION ['token'] = $accessToken ['oauth_token'];
		$_SESSION ['secret'] = $accessToken ['oauth_token_secret'];
		header ( 'Location: ' . $callbackUrl );
		exit ();
	} else {
		$oauthClient->setToken ( $_SESSION ['token'], $_SESSION ['secret'] );
		$ruleId = 1; //insert the id of your sales rule
		$resourceUrl = "$apiUrl/couponapi/rules/".$ruleId."/codes";
		$oauthClient->fetch ( $resourceUrl, '', 'GET', array (
				'Content-Type' => 'application/json' 
		) );
		$coupon = $oauthClient->getLastResponse ();
		echo $coupon;
	}
} catch ( OAuthException $e ) {
	print_r ( $e->getMessage () );
	echo "&lt;br/&gt;";
	print_r ( $e->lastResponse );
}