<?php

namespace Hurah\CertBot\Helper;

use Hurah\Types\Type\Path;
use Hurah\Types\Util\FileSystem;

class DirectoryStructure {

    static function getSysRoot(): Path {
        return new Path(dirname(__DIR__, 3));
    }

    static function getCommandDir(): Path {
        return FileSystem::makePath(self::getSysRoot(), 'src', 'CertBot', 'Command');
    }

}

