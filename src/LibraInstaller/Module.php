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
class Module
{
    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $targetDir = $package->getTargetDir();
        $name = $package->getName();
        $path = "vendor/$name/public";
        if (is_dir($path)) {
            link("vendor/public/$name", "../../../$path");
        }
    }

    public static function prePackageUnistall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $targetDir = $package->getTargetDir();
        $name = $package->getName();
        $path = "vendor/public/$name";
        if (is_link($path)) {
            unlink($path);
        }
    }
}
