<?php

namespace Hurah\CertBot\Helper;

use Composer\Autoload\ClassLoader;
use Hurah\Types\Type\Path;
use Hurah\Types\Util\FileSystem;
use ReflectionClass;

class DirectoryStructure {

    static function getSysRoot(): Path {
        return new Path(dirname(__DIR__, 3));
    }
    static function getDataDir() : Path
    {
        return FileSystem::makePath('./', 'data');
    }
    static function getRootVendorDir() : Path
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        return new Path(dirname($reflection->getFileName(), 2));
    }

    static function getCommandDir(): Path {
        return FileSystem::makePath(self::getSysRoot(), 'src', 'CertBot', 'Command');
    }

}

