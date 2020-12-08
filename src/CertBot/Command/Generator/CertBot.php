<?php

namespace Hurah\CertBot\Command\Generator;

use Hurah\CertBot\Helper\Docker;
use Hurah\Types\Exception\NullPointerException;
use Hurah\Types\Type\DnsName;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\DnsNameCollection;
use Hurah\Types\Type\Email;
use Hurah\Types\Type\Path;
use Hurah\Types\Util\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;


class CertBot extends Command {

    private DnsNameCollection $oDnsNameCollection;
    private Email $oEmail;
    private Path $oOutputDir;

    protected function configure() {

        $this->setName("certificate:generate");
        $this->addOption('email', InputOption::VALUE_REQUIRED);
        $this->addOption('domain', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED);

        $this->oOutputDir = FileSystem::makePath(getcwd(), 'data', 'CertBot');

        $this->setDescription("Generates SSL certificates for the given domains");
    }

    public function interact(InputInterface $input, OutputInterface $output) {

        $oDnsNameCollection = new DnsNameCollection();
        $sEmail = $input->getOption('email');
        $aDomains = $input->getOption('domain');

        $iAttempt = 0;
        while(empty($aDomains) && $iAttempt < 3)
        {
            ++$iAttempt;
            $helper = $this->getHelper('question');
            $question = new Question('<question>No domains specified, please provide a domain:</question> ', false);
            $sDomain = $helper->ask($input, $output, $question);

            try {

                DnsName::fromString($sDomain);
                $aDomains[] = $sDomain;
            }
            catch (InvalidArgumentException $e)
            {
                $output->writeln("<error>$sDomain does not seem to be a valid DNS name.</error>");
                continue;
            }
        }
        if(is_iterable($aDomains))
        {
            foreach($aDomains as $sDomain)
            {
                try
                {
                    $oDnsNameCollection->add($sDomain);
                }
                catch (InvalidArgumentException $e)
                {
                    $output->writeln("<error>$sDomain does not seem to be a valid DNS name, skipping.</error>");
                }
            }
        }


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
            $oEmail = new Email($helper->ask($input, $output, $question));
        }

        $this->oEmail = $oEmail;
        $this->oDnsNameCollection = $oDnsNameCollection;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        if(!$this->oEmail instanceof Email)
        {
            throw new NullPointerException("Email not initialized");
        }
        if(!$this->oDnsNameCollection instanceof DnsNameCollection)
        {
            throw new NullPointerException("No DNS names initialized");
        }

        $outputStyle = new OutputFormatterStyle('white', 'blue', ['bold', 'blink']);
        $output->getFormatter()->setStyle('bold', $outputStyle);



        if(!$this->oOutputDir->isDir())
        {
            $output->writeln("<comment>Creating directory <info>{$this->oOutputDir}</info></comment>");
            $this->oOutputDir->makeDir();
        }

        $output->writeln("<comment>Spinning up docker container</comment>");

        $oDockerHelper = new Docker();
        $oDockerCertGenerator = new Process($oDockerHelper->makeCommand($this->oEmail, $this->oDnsNameCollection, $this->oOutputDir));

        if($iStatusCode = $oDockerCertGenerator->run() === Command::SUCCESS)
        {
            $output->writeln('<info>Certificates generated succesfully</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Certificate generation failed with exit code ' . $iStatusCode . '</info>');
        $output->writeln($oDockerCertGenerator->getErrorOutput());
        return Command::FAILURE;
    }

}
