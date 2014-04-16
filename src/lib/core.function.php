<?php


function get_error_message_from_scs($message)
{
	preg_match("/\\[(\\w+)\\].+/u", $message, $m);
	return $m[0];
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




