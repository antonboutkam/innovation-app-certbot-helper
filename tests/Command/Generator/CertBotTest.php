<?php

namespace Test\Hurah\CertBot\Command\Generator;

use Hurah\CertBot\Helper\ApplicationLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CertBotTest extends TestCase
{

    function testExecute()
    {
        $oApplicationLoader = new ApplicationLoader('Testing CertBot command');
        $oApplication = $oApplicationLoader->get();
        $oCertBotCommand = $oApplication->find('certificate:generate');

        $oCommandTester = new CommandTester($oCertBotCommand);

        $this->assertEquals(
            $oCommandTester->execute([]),
            Command::FAILURE,
            "Command should fail when no arguments given");

    }


}
