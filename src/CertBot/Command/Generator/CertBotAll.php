<?php

namespace Hurah\CertBot\Command\Generator;

use Hurah\CertBot\Helper\DnsNameFinder;
use Hurah\Types\Type\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CertBotAll extends Command {

    protected function configure() {

        $this->setName("certificate:generate-all");
        $this->addArgument('email', InputOption::VALUE_REQUIRED);
        $this->setDescription("Seeks virtual hosts without SSL certificate and creates one");
    }

    public function interact(InputInterface $input, OutputInterface $output) {

        $sEmail = $input->getArgument('email');


        $oEmail = new Email($sEmail);
        $iAttempt = 0;

        while(!$oEmail->isValid() && $iAttempt < 3)
        {
            ++$iAttempt;

            if("{$oEmail->isValid()}" == "")
            {
                $output->writeln("<error>No email address specified.</error>");
            }
            else if(!$oEmail->isValid())
            {
                $output->writeln("<error>$oEmail is not a valid e-mail address.</error>");
            }

            $helper = $this->getHelper('question');
            $question = new Question('<question>Please provide a valid e-mail address:</question> ', false);
            $sEmail = $helper->ask($input, $output, $question);
            $oEmail = new Email($sEmail);
        }

        $input->setArgument('email', "{$oEmail}");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $oDnsNameFinder = new DnsNameFinder();
        $oDnsNameCollection = $oDnsNameFinder->collect();
        $oDnsNameCollectionIterator = $oDnsNameCollection->getIterator();
        $aDnsNames = [];
        foreach($oDnsNameCollectionIterator as $oDnsName)
        {
            $aDnsNames[] = "{$oDnsName}";
        }

        $oCommand = $this->getApplication()->find('certificate:generate');


        $aArguments = new ArrayInput($aInput = [
            '--domain' => $aDnsNames,
            '--email' => $input->getArgument('email')
        ]);

        $output->writeln(json_encode($aInput));
        $oCommand->run($aArguments, $output);


        return Command::SUCCESS;

    }

}
