<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraAssetsInstaller;

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
            if (!file_exists("public/vendor/$vendor")) {
                mkdir("public/vendor/$vendor", 0777, true);
            }
            $linkName = "public/vendor/$name";
            if (!file_exists($linkName)) {
                symlink("../../../$path", $linkName);
            }
        }
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $name = $package->getName();
        $link = "public/vendor/$name";
        if (is_link($link)) {
            unlink($link);
        }
        list($vendor, ) = explode('/', $name);
        @rmdir("public/vendor/$vendor"); //remove if empty
    }

    /**
     * Create symlink if it appeared in new version
     * @param \Composer\Script\PackageEvent $event
     */
    public static function postPackageUpdate (PackageEvent $event)
    {
        $package = $event->getOperation()->getTargetPackage();
        $name = $package->getName();
        $path = "vendor/$name/public";
        list($vendor, ) = explode('/', $name);
        $linkName = "public/vendor/$name";
        if (is_dir($path)) {
            if (!file_exists("public/vendor/$vendor")) {
                mkdir("public/vendor/$vendor", 0777, true);
            }
            if (!file_exists($linkName)) {
                symlink("../../../$path", $linkName);
            }
        } elseif (is_link($linkName)) {
            unlink($linkName);
            @rmdir("public/vendor/$vendor"); //remove if empty
        }
    }
}
