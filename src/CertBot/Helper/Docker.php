<?php

namespace Hurah\CertBot\Helper;

use Hurah\Types\Type\DnsNameCollection;
use Hurah\Types\Type\Email;
use Hurah\Types\Type\Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class Docker extends TestCase
{

    function makeCommand(Email $oEmail, DnsNameCollection $oDnsNameCollection, Path $oOutputDir) : array
    {
        $finder = new ExecutableFinder();
        $dockerBin = $finder->find('docker');

        $aCommand = [
            $dockerBin,
            'run',
            '-it',
            '--rm',
            '--name',
            'CertBot',
            '-v',
            "{$oOutputDir}:/etc/letsencrypt",
            '-v',
            "/var/lib/letsencrypt:/var/lib/letsencrypt",
            '-p',
            '80:80',
            'CertBot/CertBot',
            'certonly',
            '--standalone',
            '--preferred-challenges',
            'http',
            '--agree-tos',
            "-m",
            "{$oEmail}",

        ];

        foreach($oDnsNameCollection->toArray() as $oDnsName)
        {
            $aCommand[] = "-d";
            $aCommand[] = "{$oDnsName}";
        }

        return $aCommand;
    }




}
