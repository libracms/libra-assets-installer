<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraInstaller;

use Composer\Script\PackageEvent;

/**
 * Creaty symlink in webserver document root (public) of module assets containing in public dir
 * Install modules public dir and linked them
 * Wokrs also at Windows Vista/Windows Server 2008 or greater
 *
 * @author duke
 */
class Installer
{
    /**
     * Create symlink
     * @param \Composer\Script\PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $name = $package->getName();
        $path = "vendor/$name/public";
        list($vendor, ) = explode('/', $name);
        if (is_dir($path)) {
            if (!file_exists("public")) mkdir("public");
            if (!file_exists("public/vendor")) mkdir("public/vendor");
            if (!file_exists("public/vendor/$vendor")) mkdir("public/vendor/$vendor");
            symlink("../../../$path", "public/vendor/$name");
        }
    }

    public static function prePackageUninstall(PackageEvent $event)
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
