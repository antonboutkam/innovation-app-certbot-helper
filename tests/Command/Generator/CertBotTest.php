<?php

namespace Test\Hurah\CertBot\Command\Generator;

use Hurah\CertBot\Helper\ApplicationLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Hurah\Types\Exception\InvalidArgumentException;

class CertBotTest extends TestCase
{

    function testExecuteNoDomain()
    {
        $this->expectException(InvalidArgumentException::class);

        $oApplicationLoader = new ApplicationLoader('Testing CertBot command');
        $oApplication = $oApplicationLoader->get();
        $oCertBotCommand = $oApplication->find('certificate:generate');

        $oCommandTester = new CommandTester($oCertBotCommand);
        $oCommandTester->execute(['--email' => 'fake@novum.nu']);
    }

    function testExecuteNoEmail()
    {
        $this->expectException(InvalidArgumentException::class);

        $oApplicationLoader = new ApplicationLoader('Testing CertBot command');
        $oApplication = $oApplicationLoader->get();
        $oCertBotCommand = $oApplication->find('certificate:generate');

        $oCommandTester = new CommandTester($oCertBotCommand);
        $oCommandTester->execute(['--domain' => ['fake.test.com']]);
    }

    function testExecuteInvalidEmail()
    {
        $this->expectException(InvalidArgumentException::class);

        $oApplicationLoader = new ApplicationLoader('Testing CertBot command');
        $oApplication = $oApplicationLoader->get();
        $oCertBotCommand = $oApplication->find('certificate:generate');

        $oCommandTester = new CommandTester($oCertBotCommand);
        $oCommandTester->execute(['--domain' => ['fake.test.com'], '--email' => 'anton@']);
    }

}
