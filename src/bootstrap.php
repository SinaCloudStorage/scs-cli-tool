<?php

define('DS', DIRECTORY_SEPARATOR);

define('AROOT', str_replace('phar://', '', dirname(__DIR__)) . DS);

require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'class/BigFileTools.php';
require_once 'class/SCS.php';
require_once 'class/SCSWrapper.php';
require_once 'lib/core.function.php';

//date_default_timezone_set('UTC');
date_default_timezone_set('PRC');

use Clio\Console;
use Commando\Command;


$cmd = new Command();
$cmd->useDefaultHelp(false);
//$cmd->doNotTrapErrors();

$options = array(
	
	'help' 			=> " -h, --help\r\n \tshow this help message and exit",
	//'access_key' 	=> " -ak [ACCESS_KEY], --access_key [ACCESS_KEY]\r\n \tSCS Access Key",
	//'secret_key' 	=> " -sk [SECRET_KEY], --secret_key [SECRET_KEY]\r\n \tSCS Secret Key",
	'clear_keys' 	=> " -ck, --clear_keys\r\n \tClear Access Key and Secret Key",
);

$commands = array(

	'mb' => " Make bucket\r\n \tscs.phar mb scs://BUCKET",
	'rb' => " Remove bucket\r\n \tscs.phar rb scs://BUCKET",
	'ls' => " List objects or buckets\r\n \tscs.phar ls [scs://BUCKET[/PREFIX]]",
	'get' => " Get file from bucket\r\n \tscs.phar get scs://BUCKET/OBJECT LOCAL_FILE",
	'put' => " Put file into bucket\r\n \tscs.phar put FILE [FILE...] scs://BUCKET[/PREFIX]",
	'del' => " Delete file from bucket\r\n \tscs.phar del scs://BUCKET/OBJECT",
	'info' => " Get information about Files\r\n \tscs.phar info scs://BUCKET/OBJECT",
	'cp' => " Copy object\r\n \tscs.phar cp scs://BUCKET1/OBJECT1 scs://BUCKET2[/OBJECT2]",
);

$cmd->option();

$cmd->option('h')
	->aka('help')
	->boolean();

/*
$cmd->option('ak')
	->aka('access_key');
	
$cmd->option('sk')
	->aka('secret_key');
*/
	
$cmd->option('ck')
	->aka('clear_keys')
	->boolean();

$scs_json_path = AROOT . '.scs.json';

if ($cmd['clear_keys']) {
	
	@unlink($scs_json_path);	
	exit();
}


if ($cmd['help'] || !isset($commands[$cmd[0]])) {
	
	Console::output(c('logo'));
	
	Console::output('scs-cli-tool version %y' . c('version') . '%n');
	Console::output('');
	Console::output('');
	
	Console::output('%yUsage:%n');
	Console::output('');
	Console::output(" [options] COMMAND [parameters]");
	
	Console::output('');
	Console::output('');
	
	Console::output('%yOptions:%n');
	Console::output('');
	
	foreach ($options as $item) {
		
		Console::output($item);
	}
	
	Console::output('');
	Console::output('');
	
	Console::output('%yCommands:%n');
	Console::output('');
	
	foreach ($commands as $item) {
			
		Console::output($item);
	}
	
	Console::output('');
		
	exit();
}




//--------------------------

$scs_keys = json_decode(@file_get_contents($scs_json_path), true);

$access_key = '';
$secret_key = '';

SCS::setExceptions(true);
SCS::setTimeCorrectionOffset(36000);

if ($scs_keys && isset($scs_keys['access_key']) && isset($scs_keys['secret_key'])) {
	
	$access_key = $scs_keys['access_key'];
	$secret_key = $scs_keys['secret_key'];
	
	SCS::setAuth($access_key, $secret_key);

} else {
	
	$access_key = Console::prompt('Your Access Key', array(
	
		'required' => true,
		'validator' => function ($v) { return strlen($v) == 10; }
	));
	
	$secret_key = Console::prompt('Your Secret Key', array(
	
		'required' => true,
		'validator' => function ($v) { return strlen($v) == 40; }
	));
	
	try {
		
		SCS::setAuth($access_key, $secret_key);
		
		SCS::listBuckets();
		
		$scs_keys = array(
			
			'access_key' => $access_key,
			'secret_key' => $secret_key
		);
		
		@file_put_contents($scs_json_path, json_encode($scs_keys));
		
	} catch (SCSException $e) {
		
		Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
		exit();
	}
}



if (isset($commands[$cmd[0]])) {
	
	if ($cmd[0] == 'ls') {
		
		$url = trim($cmd[1]);
		
		$mask = "%20s\t%10s\t%%Y%-30s%%n";
		
		if (strlen($url) == 0) {
			
			try {
				
				$response = SCS::listBuckets(true);
				
				if (isset($response['buckets'])) {
					
					Console::output('');
					Console::output('total: %Y' . count($response['buckets']) . '%n');
					Console::output('');
					
					foreach ($response['buckets'] as $bucket) {
						
						//Console::output(date('Y-m-d H:i:s', $bucket['time']) . "\t{$bucket['consumed_bytes']}\t%Y{$bucket['name']}%n");
						$output = sprintf($mask, date('Y-m-d H:i:s', $bucket['time']), $bucket['consumed_bytes'], $bucket['name']);
						Console::output($output);
					}
				}
				
			} catch (SCSException $e) {
				
				Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
				exit();
			}
			
		} else {
			
			$scs_url_info = parse_scs_url($cmd[1]);
			
			//print_r($scs_url_info);
			
			if ($scs_url_info === false) {
				
				Console::error('%rInvalid argument%n %R"' . $cmd[1] . '"%n');
				exit();
			}
			
			Console::output('...');
			
			try {
				
				$marker = null;
				$prefix = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
				//$response = SCS::getBucket($scs_url_info['bucket'], $prefix);
				
				$isTruncated = true;
				$marker = null;
				
				while ($isTruncated) {
					
					$response = SCS::getBucket($scs_url_info['bucket'], $prefix, $marker, $maxKeys = 100, $delimiter = null, $returnCommonPrefixes = false, $nextMarker, $isTruncated);
					
					if (is_array($response)) {
												
						//Console::output('');
						//Console::output('total: %Y' . count($response) . '%n');
						//Console::output('');
						
						foreach ($response as $object) {
							
							//Console::output(date('Y-m-d H:i:s', $object['time']) . "\t{$object['size']}\t%Y{$object['name']}%n");
							$output = sprintf($mask, date('Y-m-d H:i:s', $object['time']), $object['size'], $object['name']);
							Console::output($output);
						}
					
					} else {
						
						$isTruncated = false;
					}
					
					if ($isTruncated && $nextMarker && $nextMarker != $marker) {
											
						$marker = $nextMarker;
					
					} else {
						
						$isTruncated = false;
					}
					
					if ($isTruncated) {
						
						if (!Console::confirm('Show more?')) {
							
							exit();
						}
					}
				}

			} catch (SCSException $e) {
			
				Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
				exit();
			}
		}
		
	} elseif ($cmd[0] == 'mb') {
		
		$scs_url_info = parse_scs_url($cmd[1]);
		
		if ($scs_url_info === false) {
			
			Console::error('%rInvalid argument%n %R"' . $cmd[1] . '"%n');
			exit();
		}
		
		try {
		
			if (SCS::putBucket($scs_url_info['bucket'], SCS::ACL_PRIVATE)) {
			
				Console::output('%GSuccess.%n');
			}
			
		} catch (SCSException $e) {
			
			Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
			exit();
		}
		
	} elseif ($cmd[0] == 'rb') {
			
		$scs_url_info = parse_scs_url($cmd[1]);
		
		if ($scs_url_info === false) {
			
			Console::error('%rInvalid argument%n %R"' . $cmd[1] . '"%n');
			exit();
		}
		
		if (!Console::confirm('Are you sure delete %G"' . $scs_url_info['bucket'] . '"%n ?')) {
			
			Console::output('%GCanceled.%n');
			exit();
		}
		
		try {
		
			if (SCS::deleteBucket($scs_url_info['bucket'])) {
			
				Console::output('%GSuccess.%n');
			}
			
		} catch (SCSException $e) {
			
			Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
			exit();
		}
	
	} elseif ($cmd[0] == 'get') {
		
		$scs_url_info = parse_scs_url($cmd[1]);
		
		if ($scs_url_info !== false) {
			
			$object = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
		}
		
		if ($scs_url_info === false || strlen($object) <= 0) {
			
			Console::error('%rInvalid argument 1%n %R"' . $cmd[1] . '"%n');
			exit();
		}
		
		$local_file = $cmd[2];
		
		if (strlen($local_file) <= 0) {
			
			Console::error('%rInvalid argument 2%n %R"' . $cmd[2] . '"%n');
			exit();
		}
		
		Console::output('...');
		
		try {
		
			$response = SCS::getObject($scs_url_info['bucket'], $object, $local_file);
			Console::output('%GSuccess.%n');
			//print_r($response);
			
		} catch (SCSException $e) {
			
			@unlink($local_file);
			Console::error('Resources: ' . $cmd[1]);
			Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
			exit();
		}
	
	} elseif ($cmd[0] == 'put') {
	
		$arguments = $cmd->getArgumentValues();
		
		$ar_count = count($arguments);
		
		if ($ar_count < 3) {
			
			Console::error('%rInvalid arguments%n');
			exit();
		}
		
		$scs_url_info = parse_scs_url($arguments[$ar_count-1]);
		
		//print_r($scs_url_info);
			
		if ($scs_url_info === false) {
			
			Console::error('%rInvalid argument ' . (string)($ar_count-1) . '%n %R"' . $arguments[$ar_count-1] . '"%n');
			exit();
		}
		
		$prefix = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
		
		for ($i=1; $i<$ar_count-1; $i++) {
			
			Console::output('Uploading ' . $arguments[$i] . '...');
			
			try {
				
				$basename = base_name($arguments[$i]);
				
				if ($prefix === null || strlen($prefix) == 0) {
				
					$uri = $basename;
					
				} elseif (substr($prefix, -1) == '/') {
					
					$uri = $prefix . $basename;
					
				} else {
					
					$uri = $prefix . '/' . $basename;
				}
				
				$response = SCS::putObjectFile($arguments[$i], $scs_url_info['bucket'], $uri);
				Console::output('%GSuccess.%n');
				
			} catch (SCSException $e) {
				
				Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
				exit();
			}
		}
		
	} elseif ($cmd[0] == 'del') {
	
		$scs_url_info = parse_scs_url($cmd[1]);
		
		if ($scs_url_info !== false) {
			
			$object = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
		}
		
		if ($scs_url_info === false || strlen($object) <= 0) {
			
			Console::error('%rInvalid argument 1%n %R"' . $cmd[1] . '"%n');
			exit();
		}
		
		Console::output('Deleting...');
		
		try {
		
			$response = SCS::deleteObject($scs_url_info['bucket'], $object);
			Console::output('%GSuccess.%n');
			
		} catch (SCSException $e) {
			
			Console::error('Resources: ' . $cmd[1]);
			Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
			exit();
		}
		
	} elseif ($cmd[0] == 'info') {
	
		$scs_url_info = parse_scs_url($cmd[1]);
		
		if ($scs_url_info !== false) {
			
			$object = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
		}
		
		if ($scs_url_info === false || strlen($object) <= 0) {
			
			Console::error('%rInvalid argument 1%n %R"' . $cmd[1] . '"%n');
			exit();
		}
		
		Console::output('Loading...');
		
		try {
		
			$response = SCS::getObjectInfo($scs_url_info['bucket'], $object, true);
			
			if ($response !== false && is_array($response)) {
				
				unset($response['date']);
				
				$mask = "|%20s | %%Y%-50s%%n|";
				
				$output = sprintf($mask, 'key', 'value');
				
				Console::output($output);
				
				$output = sprintf($mask, '', '');
				
				Console::output($output);
						
				foreach ($response as $key => $value) {
					
					if ($key == 'time') {
						
						$value = date('Y-m-d H:i:s', $value);
					}
					
					$output = sprintf($mask, $key, $value);
					Console::output($output);
				}
				
			} else {
				
				Console::output('%yNULL%n');
			}
			
			
		} catch (SCSException $e) {
			
			Console::error('Resources: ' . $cmd[1]);
			Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
			exit();
		}
		
	} elseif ($cmd[0] == 'cp') {
	
		$scs_url_info = parse_scs_url($cmd[1]);
		
		if ($scs_url_info !== false) {
			
			$bucket1 = $scs_url_info['bucket'];
			$object1 = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
		}
		
		if ($scs_url_info === false || strlen($object1) <= 0) {
			
			Console::error('%rInvalid argument 1%n %R"' . $cmd[1] . '"%n');
			exit();
		}
		
		$scs_url_info2 = parse_scs_url($cmd[2]);
		
		if ($scs_url_info2 !== false) {
			
			$bucket2 = $scs_url_info2['bucket'];
			$object2 = isset($scs_url_info2['object']) ? $scs_url_info2['object'] : null;
		}
		
		if ($scs_url_info2 === false) {
			
			Console::error('%rInvalid argument 2%n %R"' . $cmd[2] . '"%n');
			exit();
		}
		
		if ($object2 === null || strlen($object2) <= 0) {
			
			$object2 = $object1;
		}
		
		Console::output('Copying...');
		
		try {
		
			SCS::copyObject($bucket1, $object1, $bucket2, $object2);
			Console::output('%GSuccess.%n');
			
		} catch (SCSException $e) {
			
			Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
			exit();
		}
	}
	
}