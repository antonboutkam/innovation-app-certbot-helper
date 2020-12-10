inotifywait -m "$1" -e create -e move |
while read path action file; do
  # your preferred command here
  ./certbot.php
done


#!/usr/bin/env php
<?php
use Hurah\CertBot\Helper\ApplicationLoader;
use Hurah\CertBot\Helper\DirectoryStructure;
use Hurah\CertBot\Helper\DockerCompose;
use Hurah\Types\Type\Json;
use Hurah\Types\Util\FileSystem;
use Hurah\Types\Util\JsonUtils;
use Symfony\Component\Console\Input\ArrayInput;

require_once 'vendor/autoload.php';

$oCertsPath = DirectoryStructure::getDataDir()->extend('queue')->extend('certs');
$oCertsArchive = $oCertsPath->extend('archive');

$oCertsPathIterator = $oCertsPath->getDirectoryIterator();
$bFilesFound = false;
foreach($oCertsPathIterator as $oCertPath)
{
    if($oCertPath->getExtension() !== 'json')
    {
        echo "Skipping file {$oCertPath->getPathname()}, not a json file";
        continue;
    }
    $oCertsPath->move($oCertsArchive);

    $sCertJobInfo = file_get_contents($oCertPath->getPathname());
    $aCertJobInfo = JsonUtils::decode($sCertJobInfo);
    $bFilesFound = true;
}

if($bFilesFound)
{
    $oDockerCompose = new DockerCompose();
    $oDockerCompose->run('http', 'stop');



    $oDockerCompose->run('http', 'start');
}



