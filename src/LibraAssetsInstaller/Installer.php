<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraAssetsInstaller;

use Composer\Installer\PackageEvent;

/**
 * Makes accessible vendor assets (js, css, images) by creating/updating symlink to public/vendor/vendor-name folder.<br/>
 * Creats symlink in webserver document root (public) of module assets that contains in `public` directory
 * Aloso supports Windows Vista/Windows Server 2008 or greater
 *
 * @deprecated since version 2.0. <br> Use types {"asset", "asset-aware"} instead of it
 * @author duke
 */
class Installer
{
    /**
     * Creates symlink
     * @param \Composer\Installer\PackageEvent $event
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

    /**
     * Removes symlink
     * @param \Composer\Installer\PackageEvent $event
     */
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
     * Creates/Removes symlink if it appeared/disappeared in new version.
     * @param \Composer\Installer\PackageEvent $event
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
