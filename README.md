scs-cli-tool
============

Cli Tool For 新浪云存储


### Requirements

* PHP >= 5.3.0
* [PHP cURL]
* [PHP mbString]
* [PHP json]
* [PHP Phar]
* [PHP pcre]

### Installation / Usage

1. Download the [scs.phar](http://sdk.sinastorage.com/scs.phar) .
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


$ ./scs.phar ls

total: 6

 2014-03-31 09:48:35	   5694878	cloud8                        
 2014-04-09 11:49:21	  19724702	cloud123                      
 2014-04-12 16:43:26	         0	cloudside                     
 2014-04-14 10:55:10	         0	sina-com-cn                   
 2014-04-16 21:49:38	     63265	sdk                           
 2014-04-16 21:50:02	         0	api     

$ ./scs.phar ls scs://cloud123

 2014-04-17 13:31:19	   1011280	composer.phar          
 2014-04-16 22:22:19	      2836	path/to/my/Diamond Heart.gif  
 2014-04-16 22:22:20	      2793	path/to/my/Diamond Round.gif  
 2014-04-16 22:22:20	      2204	path/to/my/Diamond Square.gif 
 2014-04-16 22:22:20	      2789	path/to/my/Emerald Round.gif  
 2014-04-16 22:22:20	      2205	path/to/my/Emerald Square.gif 
 2014-04-16 22:22:21	      2785	path/to/my/Princess Heart.gif 
 2014-04-16 22:22:21	      2792	path/to/my/Princess Round.gif 
 2014-04-16 22:22:21	      2200	path/to/my/Princess Square.gif
 2014-04-16 22:22:21	      2781	path/to/my/Ruby Heart.gif     
 2014-04-16 22:22:21	      2734	path/to/my/Ruby Round.gif     
 ...

```

