<?php

namespace Hurah\CertBot\Helper;

use Hurah\Types\Type\Composer;
use Hurah\Types\Util\JsonUtils;

class PluginFinder
{

    /**
     * Collects DNS names that require an SSL certificate
     * @return Composer[]
     */
    function collect():array {

        $aComposerObjects = [];
        $oPackageFinder = new PackageFinder();
        $aComposerPaths = $oPackageFinder->collect();
        $aComposerPathIterator = $aComposerPaths->getIterator();
        foreach ($aComposerPathIterator as $oComposerPath)
        {
            echo "Checking $oComposerPath " . PHP_EOL;
            $sComposer = file_get_contents($oComposerPath);
            $aComposer = JsonUtils::decode((string)$sComposer);
            if(!isset($aComposer['type']))
            {
                echo "Failed because type not set" . PHP_EOL;
                continue;
            }

            if(!preg_match('/^(novum|hurah)-(site|api|domain)/', $aComposer['type']))
            {
                echo "Failed because type incorrect" . PHP_EOL;
                continue;
            }

            echo "Success, is a candidate" . PHP_EOL;
            $aComposerObjects[] = Composer::fromArray($aComposer);
        }
        return $aComposerObjects;
    }
}
