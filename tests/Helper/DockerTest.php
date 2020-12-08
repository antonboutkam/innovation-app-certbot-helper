<?php

namespace Test\Hurah\CertBot\Helper;

use Hurah\Types\Type\DnsNameCollection;
use Hurah\Types\Type\Email;
use Hurah\Types\Util\FileSystem;
use PHPUnit\Framework\TestCase;
use Hurah\CertBot\Helper\Docker;

class DockerTest extends TestCase
{

    function testMake()
    {
        $oDocker = new Docker();

        $oDnsNameCollection = new DnsNameCollection();
        $oDnsNameCollection->add('x.demo.novum.nu');
        $oOutputDir = FileSystem::makePath('test');
        $aResult = $oDocker->makeRunCommand(new Email('anton@novum.nu'), $oDnsNameCollection, $oOutputDir);

        $this->assertTrue(in_array('x.demo.novum.nu', $aResult));
        $this->assertTrue(strpos($aResult[0], 'docker') > 0);

    }




}
