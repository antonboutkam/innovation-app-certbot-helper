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

        if(!$this->oEmail->isValid())
        {
            throw new InvalidArgumentException("Email field does not contain a valid emailaddress {$this->oEmail}.");
        }

        if(!$this->oDnsNameCollection instanceof DnsNameCollection)
        {
            throw new NullPointerException("No DNS names initialized");
        }
        elseif($this->oDnsNameCollection->isEmpty())
        {
            throw new InvalidArgumentException("No DNS names to request certificates for.");
        }

        $outputStyle = new OutputFormatterStyle('white', 'blue', ['bold', 'blink']);
        $output->getFormatter()->setStyle('bold', $outputStyle);



        if(!$this->oOutputDir->isDir())
        {
            $output->writeln("<comment>Creating directory </comment> <info>{$this->oOutputDir}</info>");
            $this->oOutputDir->makeDir();
        }

        $output->writeln("<comment>Spinning up docker container</comment>");

        $oDockerHelper = new Docker();

        // Running the Docker container once for each domain so each domain will have it's own certificate in a separate
        // file. When running all at once Docker will generate just a single certificate for each domain
        $oDnsNameIterator = $this->oDnsNameCollection->getIterator();
        $bHadErrors = false;
        foreach($oDnsNameIterator as $oDnsName)
        {

            if(file_exists("{$this->oOutputDir}/live/{$oDnsName}"))
            {
                echo "Cert for {$oDnsName} already exists, SKIPPING ". PHP_EOL;
                continue;
            }


            $oDnsNameCollection = new DnsNameCollection();

            $oDnsNameCollection->add($oDnsName);
            $oDockerCertGenerator = new Process($oDockerHelper->makeRunCommand($this->oEmail, $oDnsNameCollection, $this->oOutputDir));

            if($iStatusCode = $oDockerCertGenerator->run() === Command::SUCCESS)
            {
                $output->writeln("<info>Certificate for {$oDnsName} generated succesfully</info>");
            }
            else
            {
                $output->writeln("<error>Certificate generation for {$oDnsName} failed with exit code " . $iStatusCode . '</error>');
                $output->writeln($oDockerCertGenerator->getErrorOutput());
                $bHadErrors = true;
            }
        }

        if($bHadErrors)
        {
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

}
