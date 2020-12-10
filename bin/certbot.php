#!/usr/bin/env php
<?php
use Hurah\CertBot\Helper\ApplicationLoader;

require_once 'vendor/autoload.php';

$oApplicationLoader = new ApplicationLoader('CertBot helper');
$oApplication = $oApplicationLoader->get();


$oApplication->run();
