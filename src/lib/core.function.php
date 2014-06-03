<?php

function base_name($filepath)
{
	$path_parts = explode(DIRECTORY_SEPARATOR, $filepath);
	
	$path_parts_filter = array_filter($path_parts, function($item){
				
		return trim($item) != '';
	});
	
	return end($path_parts_filter);
}

function __dnsBucketName($bucket)
{
	if (preg_match("/^([a-z]+[a-z0-9\.-]*[a-z0-9]+)$/u", $bucket) && 
		strlen($bucket) >= 3 && 
		strlen($bucket) <= 63) {
		
		return true;
	}

	return false;
}

function mb_parse_url($url) {

    $encodedUrl = preg_replace('%[^:/?#&=\.]+%usDe', 'urlencode(\'$0\')', $url);
    $components = parse_url($encodedUrl);
    foreach ($components as &$component)
        $component = urldecode($component);
    return $components;
}

function parse_scs_url($url)
{
	$parse_url = mb_parse_url($url);
	
	if (isset($parse_url['scheme']) && strtolower($parse_url['scheme']) == 'scs') {
		
		if (isset($parse_url['host']) && __dnsBucketName($parse_url['host'])) {
			
			$scs_url_info['bucket'] = $parse_url['host'];
			
			if (isset($parse_url['path']) && strlen($parse_url['path']) > 0) {
				
				$scs_url_info['object'] = substr($parse_url['path'], 1);
			}
			
			return $scs_url_info;
		}
	}
	
	return false;
}

function get_error_message_from_scs($message)
{
	$r = preg_match("/\\[(\\w+)\\].+/u", $message, $m);
	
	if ($r) {
		
		return $m[0];
	}
	
	return end(explode(": ", $message, 2));
}

function z( $str )
{
	return strip_tags( $str );
}

function c( $str )
{
	return isset( $GLOBALS['config'][$str] ) ? $GLOBALS['config'][$str] : false;
}

function g( $str )
{
	return isset( $GLOBALS[$str] ) ? $GLOBALS[$str] : false;
}

function t( $str )
{
	return trim($str);
}

function u( $str )
{
	return urlencode( $str );
}

function ru( $str )
{
	return rawurlencode( $str );
}



function wintval( $string )
{
	$array = str_split( $string );
	$ret = '';
	foreach( $array as $v )
	{
		if( is_numeric( $v ) ) $ret .= intval( $v );
	}
	
	return $ret;
}


function uses( $m )
{
	load( 'lib/' . basename($m) );
}

function load( $file_path ) 
{
	$file = AROOT . $file_path;
	if( file_exists( $file ) )
	{
		return require( $file );
	}
}


function not_empty( $value )
{
	if( strlen( $value ) < 1 ) return false;
	else return $value ;
}	

function is_mail( $value )
{
	if( filter_var($value, FILTER_VALIDATE_EMAIL) === false ) 
		return false;
	else
		return $value;
}




