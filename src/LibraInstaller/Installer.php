<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraInstaller;

use Composer\Script\PackageEvent;

/**
 * Install modules public dir and linked them
 *
 * @author duke
 */
class Installer
{
    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $targetDir = $package->getTargetDir();
        $name = $package->getName();
        $path = "vendor/$name/public";
        list($vendor, ) = explode('/', $name);
        if (is_dir($path)) {
            if (!file_exists("public")) mkdir("public");
            if (!file_exists("public/vendor")) mkdir("public/vendor");
            if (!file_exists("public/vendor/$vendor")) mkdir("public/vendor/$vendor");
            link("../../../$path", "public/vendor/$name");
        }
    }

    public static function prePackageUnistall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $targetDir = $package->getTargetDir();
        $name = $package->getName();
        $link = "public/vendor/$name";
        if (is_link($link)) {
            unlink($link);
        }
        list($vendor, ) = explode('/', $name);
        @rmdir("public/vendor/$vendor"); //remove if empty
    }
}
