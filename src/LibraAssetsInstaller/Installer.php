<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraAssetsInstaller;

use Composer\Script\PackageEvent;
use \ErrorException;

/**
 * Makes accessible vendor assets (js, css, images) by creating/updating symlink to public/vendor/vendor-name folder.<br/>
 * Creats symlink in webserver document root (public) of module assets that contains in `public` directory
 * Aloso supports Windows Vista/Windows Server 2008 or greater
 *
 * @author duke
 */
class Installer
{
    const MKDIR_MODE = 0755;

    /**
     * Copy a file or recursively copy a folder<br/>
     * Print warning if file wasn't copied
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       string   $permissions New folder creation permissions
     * @return      bool Returns true on success, false on failure
     */
    protected static function copy($source, $dest, $permissions = 0755)
    {
        umask(0777 ^ $permissions);

        if (!is_dir($source)) {
            return copy($source, $dest);
        }

        if (!mkdir($dest, $permissions)) {
            printf("Couldn't copy %s to %s\n", $source, $dest);
            return false;
        }
        $dir = opendir($source);
        while ( false !== ($file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                //warning only on folders to reduce mess
//                $result = static::copy($source . '/' . $file, $dest . '/' . $file);
//                if ($result === false) {
//                    printf("Couldn't copy %s to %s\n", $source . '/' . $file, $dest . '/' . $file);
//                }
                static::copy($source . '/' . $file, $dest . '/' . $file);
            }
        }
        closedir($dir);
        return true;
    }

    /**
     * Delete direcotry recursively or single file
     * @param type $dirname
     */
    protected static function rmdir($dirname)
    {
        if (!is_dir($dirname)) {
            return unlink($dirname);
        }

        $dir = opendir($dirname);
        while ( false !== ($file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                static::rmdir($file);
            }
        }
        closedir($dir);
        if (!@rmdir($file)) printf("Couldn't delete %s folder\n", $file);

        return true;
    }

    /**
     * Creates link or issues warning if cann't.<br/>
     * For virtual box shared folder copies folder.
     * @param string $fullPackageName = vendor/package-name
     * @param bool $deleteIfNotExests - delete assets if target folder doesn't exist
     */
    protected static function createLinkOrCopy($fullPackageName, $deleteIfNotExests = false)
    {
        $path     = "vendor/$fullPackageName/public";
        list($vendor, $packageName) = explode('/', $fullPackageName);
        $linkName = "public/vendor/$vendor/$packageName";

        if (is_dir($path)) {
            if (!file_exists("public/vendor/$vendor")) {
                $vendorFolder = "public/vendor/$vendor";
                if (mkdir($vendorFolder, static::MKDIR_MODE, true) === false) {
                    echo "Cann't create folder $vendorFolder\n";
                    return 1;
                }
            }
            if (!file_exists($linkName)) {
                /*
                 * virtualbox shared folder don't allow symlinks and symlink throw excaption
                 * @see issue#1  (https://github.com/libracms/libra-assets-installer/issues/1)
                 */
                try {
                    if (symlink("../../../$path", $linkName) === false) {
                        echo "Haven't permissions to create link or copy files to $linkName\n";
                    }
                } catch (ErrorException $exc) {
                    static::copy("../../../$path", $linkName, static::MKDIR_MODE);
                }
            }
        } elseif ($deleteIfNotExests === true && file_exists($linkName) === true) {
            static::unlinkOrDelete($linkName);
            @rmdir("public/vendor/$vendor"); //remove if empty
        }
    }

    /**
     * Remove link. If it's was folder - issues warning about it. <br/>
     * Warning if hasn't permissions.
     * @param string $linkName - link path or direcotry that should be deleted
     */
    protected static function unlinkOrDelete($linkName)
    {
        if (is_link($linkName)) {
            if (unlink($linkName) === false) {
                echo "Cann't delete link $linkName\n";
            }
        } else {
            //@todo: shuld be recursive deleted
            //@rmdir($linkName);
            echo "This script doesn't suport recursive deletion.\n"
               . "You should manual delete $linkName folder as it was copied instead of linked.\n";
        }
    }

    /**
     * Tries to create symlink, otherwise copy (virtualbox shared folder don't allow symlinks)
     * @param \Composer\Script\PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        static::createLinkOrCopy($package->getName());
    }

    /**
     * Removes symlink
     * @param \Composer\Script\PackageEvent $event
     */
    public static function prePackageUninstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $name = $package->getName();
        $linkName = "public/vendor/$name";
        static::unlinkOrDelete($linkName);
    }

    /**
     * Creates/Removes symlink if it appeared/disappeared in new version.
     * @param \Composer\Script\PackageEvent $event
     */
    public static function postPackageUpdate (PackageEvent $event)
    {
        $package = $event->getOperation()->getTargetPackage();
        static::createLinkOrCopy($package->getName(), true);
    }
}
