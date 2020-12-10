<?php

namespace Hurah\CertBot\Command\Auto;

use Hurah\CertBot\Helper\DirectoryStructure;
use Hurah\CertBot\Helper\DockerCompose;
use Hurah\Types\Util\JsonUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Installer extends Command {

    protected function configure() {

        $this->setName("certificate:auto-install");
        $this->setDescription("Called nby ");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $oCertsPath = DirectoryStructure::getDataDir()->extend('queue')->extend('certs');
        $oCertsArchive = $oCertsPath->extend('archive');

        $oCertsPathIterator = $oCertsPath->getDirectoryIterator();
        $bFilesFound = false;
        foreach ($oCertsPathIterator as $oCertPath) {
            if ($oCertPath->getExtension() !== 'json') {
                echo "Skipping file {$oCertPath->getPathname()}, not a json file";
                continue;
            }
            $oCertsPath->move($oCertsArchive);

            $sCertJobInfo = file_get_contents($oCertPath->getPathname());
            $aCertJobInfo = JsonUtils::decode($sCertJobInfo);
        }

        if ($bFilesFound) {
            $oDockerCompose = new DockerCompose();
            $output->writeln("Stopping http server");
            $oDockerCompose->run('http', 'stop');

            $output->writeln("Generating certificates");
            $input->setArgument('email', $aCertJobInfo['email']);
            $oCommand = $this->getApplication()->find('certificate:generate-all');
            $oCommand->run($input, $output);

            $output->writeln("Starting http server");
            $oDockerCompose->run('http', 'start');
        }
    }

}
