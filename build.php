<?php
$phar = new Phar('bin/scs.phar');
$phar->setAlias('scs.phar');
$phar->setStub('#!/usr/bin/env php
<?php Phar::mapPhar(); 
include "phar://scs.phar/bootstrap.php";
__HALT_COMPILER(); ?>');
$phar->buildFromDirectory(dirname(__FILE__) . '/src');
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();

?>