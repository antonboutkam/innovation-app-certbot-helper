<?php

namespace Hurah\CertBot\Helper;

use Hurah\Types\Type\DnsNameCollection;
use Hurah\Types\Type\Email;
use Hurah\Types\Type\Path;
use Symfony\Component\Process\ExecutableFinder;

class Docker
{

    private function getDockerExecutable():string
    {
        $finder = new ExecutableFinder();
        return $finder->find('docker');
    }

    public function makeRunCommand(Email $oEmail, DnsNameCollection $oDnsNameCollection, Path $oOutputDir) : array
    {
        $dockerBin = $this->getDockerExecutable();

        $aCommand = [
            $dockerBin,
            'run',
            '--rm',
            '--name',
            'CertBot',
            '-v',
            "{$oOutputDir}:/etc/letsencrypt",
            '-v',
            "/var/lib/letsencrypt:/var/lib/letsencrypt",
            '-p',
            '80:80',
            'certbot/certbot',
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
