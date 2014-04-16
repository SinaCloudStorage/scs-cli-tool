<?php

define('DS', DIRECTORY_SEPARATOR);

define('AROOT', str_replace('phar://', '', dirname(__DIR__)) . DS);

require_once 'config.php';
require_once 'vendor/autoload.php';
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
		
		if (strlen($url) == 0) {
			
			try {
				
				$response = SCS::listBuckets(true);
				
				if (isset($response['buckets'])) {
					
					Console::output('');
					Console::output('total: %Y' . count($response['buckets']) . '%n');
					Console::output('');
					
					foreach ($response['buckets'] as $bucket) {
						
						Console::output(date('Y-m-d H:i:s', $bucket['time']) . "\t{$bucket['consumed_bytes']}\t%Y{$bucket['name']}%n");
					}
				}
				
			} catch (SCSException $e) {
				
				Console::error('%r' . get_error_message_from_scs($e->getMessage()) . '%n');
				exit();
			}
			
		} else {
			
			$scs_url_info = parse_scs_url($cmd[1]);
			
			if ($scs_url_info === false) {
				
				Console::error('%rInvalid argument%n %R"' . $cmd[1] . '"%n');
				exit();
			}
			
			try {
				
				$prefix = isset($scs_url_info['object']) ? $scs_url_info['object'] : null;
				
				$response = SCS::getBucket($scs_url_info['bucket'], $prefix);
				
				if (is_array($response)) {
									
					Console::output('');
					Console::output('total: %Y' . count($response) . '%n');
					Console::output('');
					
					foreach ($response as $object) {
						
						Console::output(date('Y-m-d H:i:s', $object['time']) . "\t{$object['size']}\t%Y{$object['name']}%n");
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
	}
}

//Console::output('');


//echo $cmd[0] . PHP_EOL . $cmd[1] . PHP_EOL . $cmd['help'] . PHP_EOL;

/*
$hello_cmd = new Command();

$hello_cmd->useDefaultHelp(true);

// Define first option
$hello_cmd->option()
    ->require()
    ->describedAs('A person\'s name');

// Define a flag "-t" a.k.a. "--title"
$hello_cmd->option('t')
    ->aka('title')
    ->describedAs('When set, use this title to address the person')
    ->must(function($title) {
        $titles = array('Mister', 'Mr', 'Misses', 'Mrs', 'Miss', 'Ms');
        return in_array($title, $titles);
    })
    ->map(function($title) {
        $titles = array('Mister' => 'Mr', 'Misses' => 'Mrs', 'Miss' => 'Ms');
        if (array_key_exists($title, $titles))
            $title = $titles[$title];
        return "$title. ";
    });

// Define a boolean flag "-c" aka "--capitalize"
$hello_cmd->option('c')
    ->aka('capitalize')
    ->aka('cap')
    ->describedAs('Always capitalize the words in a name')
    ->boolean();

$name = $hello_cmd['capitalize'] ? ucwords($hello_cmd[0]) : $hello_cmd[0];

echo "Hello {$hello_cmd['title']}$name!", PHP_EOL;


*/



//use Clio\Console;



//print_r($argv);


/*
$scs_json = dirname(__DIR__)  . '/scs.json';
$scs_json = str_replace('phar://', '', $scs_json);
*/

//file_put_contents($scs_json, 1);



//Console::output('this is %rcolored%n and %Bstyled%n');


//$sure = Console::confirm('are you sure?');