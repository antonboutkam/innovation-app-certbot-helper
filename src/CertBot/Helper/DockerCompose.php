<?php

namespace Hurah\CertBot\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class DockerCompose {

    private function getDockerComposeExecutable():string
    {
        $finder = new ExecutableFinder();
        return $finder->find('docker-compose');
    }


    public function run(string $sService, string $sAction)
    {
        $aCommand = [
            $this->getDockerComposeExecutable(),
            $sAction,
            $sService
        ];
        $oDockerCertGenerator = new Process($aCommand);

        if($oDockerCertGenerator->run() === Command::SUCCESS)
        {
            echo "Docker $sService service started successfully" . PHP_EOL;
            return Command::SUCCESS;
        }
        echo "Starting Docker $sService service failed" . PHP_EOL;

        return Command::FAILURE;
    }
}
