<?php
/*
Plugin Name: iTunes Affiliate
Plugin URI: https://github.com/floschliep/YOURLS-iTunes-Affiliate
Description: Add your iTunes Affiliate-Token to all iTunes URLs before redirection
Version: 1.1.1
Author: Florian Schliep
Author URI: http://floschliep.com
*/

yourls_add_action('pre_redirect', 'flo_addToken');

function flo_addToken($args) {
	$token = 'YOUR_TOKEN_HERE';
	$url = $args[0];
	
	// check if URL is an iTunes URL. check for both desktop and mobile URLs.
	if (preg_match("/(itunes\\.apple\\.com\\/)([a-z]{2,3}\\/)|([a-z].+\\/)id[0-9]+/ui", $url) == true || preg_match("/(appsto\\.re/)([a-z]{2,3}\\/)(.{5})\\.i/ui", $url) == true) {
		
		// check if last char is an "/" (in case it is, remove it)
		if (substr($url, -1) == "/") {
			$url = substr($url, 0, -1);
		}
		
		// remove existing affiliate token if needed
		
		$existingToken;
		if (preg_match("/(\\?|&)ign-mpt=.+\\&/ui", $url, $matches) == true) { // first way affiliate tokens can appear (encoded and not at end of string)
			$existingToken = $matches[0]; 
			$existingToken = substr($existingToken, 0, -1); // last char is an "&"
		} else if (preg_match("/(\\?|&)ign-mpt=.+/uim", $url, $matches) == true) { // second way affiliate tokens can appear (encoded and at end of string)
			$existingToken = $matches[0]; 
		} else if (preg_match("/(\\?|\\&)at=[A-Za-z_0-9]+/uim", $url, $matches) == true) { // third way affiliate tokens can appear (clear)
			$existingToken = $matches[0]; 
		}
		
		if ($existingToken) { // if we got an existing token, remove it
			$url = str_replace($existingToken, "", $url);
		}
		
		// check if query is broken (in case it is, fix it by replacing the "&" with an "?")
		if (preg_match("/\\/id[0-9]+\\&/ui", $url, $matches) == true) {
			$brokenQuery = $matches[0];
			$fixedQuery = $matches[0];
			$fixedQuery = substr($fixedQuery, 0, -1);
			$fixedQuery = $fixedQuery.'?';
			$url = str_replace($brokenQuery, $fixedQuery, $url);
		}
		
		// add our token to the URL
		if (strpos($url,'?') !== false) { // there's already a query string in our URL, so add our token with "&"
		    $url = $url.'&at='.$token;
		} else { // start a new query string
			$url = $url.'?at='.$token;
		}
		
		if (strpos($url,'uo=4') == false) { // if "uo=4" is not in the query, add it manually otherwise the affiliate URL won't work
			$url = $url.'&uo=4';
		}
		
		// redirect
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
		echo($url);
		
		// now die so the normal flow of event is interrupted
		die();
				
	} 
	
}
