<?php

namespace Hurah\CertBot\Helper;

use Hurah\Types\Type\PathCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class PackageFinder extends TestCase
{


    private function scan():Finder
    {
        $oRootVendorDir = DirectoryStructure::getRootVendorDir();
        $oFinder = new Finder();
        return $oFinder->files()->followLinks()->name('composer.json')->in($oRootVendorDir)->depth('< 3');
    }


    /**
     * Collects DNS names that require an SSL certificate
     */
    function collect():PathCollection {

        $oPathCollection = new PathCollection();
        $oFound = $this->scan();
        foreach ($oFound as $oFileInfo)
        {
            $oPathCollection->add($oFileInfo->getPathname());
        }
        return $oPathCollection;
    }
}
