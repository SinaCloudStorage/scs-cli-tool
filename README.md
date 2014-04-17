scs-cli-tool
============

Cli Tool For 新浪云存储


### Requirements

* PHP >= 5.3.0
* [PHP cURL]

### Installation / Usage

1. Download the [`scs.phar`](http://sdk.sinastorage.com/scs.phar) .
2. Run: `chmod +x /path/to/scs.phar`
3. Run: `php /path/to/scs.phar -h` or `/path/to/scs.phar -h`
``` sh
$ ./scs.phar -h

**************************
*     _____ _____ _____  *
*    / ___// ___// ___/  *
*   (__  )/ /__ (__  )   *
*  / ___/ \____/ ___/    *
*                        *
**************************

scs-cli-tool version 0.1.0


Usage:

 [options] COMMAND [parameters]


Options:

 -h, --help
 	show this help message and exit
 -ck, --clear_keys
 	Clear Access Key and Secret Key


Commands:

 Make bucket
 	scs.phar mb scs://BUCKET
 Remove bucket
 	scs.phar rb scs://BUCKET
 List objects or buckets
 	scs.phar ls [scs://BUCKET[/PREFIX]]
 Get file from bucket
 	scs.phar get scs://BUCKET/OBJECT LOCAL_FILE
 Put file into bucket
 	scs.phar put FILE [FILE...] scs://BUCKET[/PREFIX]
 Delete file from bucket
 	scs.phar del scs://BUCKET/OBJECT
 Get information about Files
 	scs.phar info scs://BUCKET/OBJECT
 Copy object
 	scs.phar cp scs://BUCKET1/OBJECT1 scs://BUCKET2[/OBJECT2]


```

