<?php

namespace Hurah\CertBot\Helper;

use Hi\Helpers\DirectoryStructure;
use Hurah\Types\Type\DnsNameCollection;
use Hurah\Types\Type\PluginType;
use Provider\Helpers\DomainCreator;

class DnsNameFinder
{
    
    /**
     * Collects DNS names that require an SSL certificate
     */
    function collect():DnsNameCollection {

        $oPluginFinder = new PluginFinder();
        $aComposerObjects = $oPluginFinder->collect();
        $oDirectoryStructure = new DirectoryStructure();
        $aDnsList = new DnsNameCollection();

        foreach ($aComposerObjects as $oComposer)
        {
            if("{$oComposer->getType()}" === PluginType::DOMAIN)
            {
                echo "This package is a domain " . PHP_EOL;
                $sDomainConfigFile = DomainCreator::makePath('.', 'vendor', $oComposer->getName(), 'config.php');
                $aDomainConfig = require $sDomainConfigFile;
                echo "Loading $sDomainConfigFile " . PHP_EOL;
                $bIsSsl = false;
                if(isset($aDomainConfig['PORT']) && $aDomainConfig['PORT'] === 443)
                {
                    $bIsSsl = true;
                }
                else if(isset($aDomainConfig['PROTOCOL']) && $aDomainConfig['PROTOCOL'] === 'https')
                {
                    $bIsSsl = true;
                }
                if($bIsSsl && isset($aDomainConfig['DOMAIN']))
                {
                    echo "Adding domain {$aDomainConfig['DOMAIN']} " . PHP_EOL;
                    $aDnsList->add($aDomainConfig['DOMAIN']);
                }
                else
                {
                    echo "SKipping domain {$aDomainConfig['DOMAIN']} " . PHP_EOL;
                }
            }
            else if(in_array((string) $oComposer->getType(), [PluginType::API, PluginType::SITE]))
            {
                echo "This package is a domain " . PHP_EOL;
                $aDnsList->add($oComposer->getExtra()['install_dir']);
            }
            else
            {
                echo "Unsupported type  " . $oComposer->getType() . PHP_EOL;
            }

        }
        return $aDnsList;
    }
}
