<?php

namespace LibraAssetsInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Install modules that contain public directory with assets
 *
 * @author duke
 */
class AssetAwareInstaller extends LibraryInstaller
{
    protected $publicDir = 'public';
    
    protected $packageAssetDir = 'public';

    protected $publicVendorDir;


    public function __construct(IOInterface $io, Composer $composer, $type = 'asset-aware')
    {
        parent::__construct($io, $composer, $type);

        $extra = $composer->getPackage()->getExtra();
        if ($extra['publicDir'] != '') {
            $this->publicDir = $extra['publicDir'];
        }
        if ($extra['packageAssetDir'] != '') {
            $this->packageAssetDir = $extra['packageAssetDir'];
        }

        $this->publicVendorDir = $this->publicDir . '/' . $this->vendorDir;
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset-aware';
    }

    protected function initializeVendorAssetDir()
    {
        $this->filesystem->ensureDirectoryExists($this->publicVendorDir);
        $this->publicVendorDir = realpath($this->publicVendorDir);
    }

    protected function isAssetExists($package)
    {
        return file_exists($this->getInstallPath($package) . '/' . $this->publicDir);
    }

    /**
     * @param type $package
     * @return string name of link (like public/vendor/vendor-name/package-name
     */
    protected function getLinkName($package)
    {
        return $this->publicDir . '/' . $this->getInstallPath($package);
    }

    protected function isLinkExists($package)
    {
        return file_exists($this->getLinkName($package));
    }

    protected function findDirDeep()
    {
        //without target dir it will be '../../../'
        //@todo: change it if target dir set
        return 3;
    }

    protected function getAssetLinkTarget($package)
    {
        return str_repeat('../', $this->findDirDeep())
            . $this->vendorDir
            . '/' . $package->getPrettyName()
            . ($targetDir ? '/'.$targetDir : '')
            . '/' . $this->packageAssetDir;
    }

    protected function createPublicAsset($package)
    {
        $targetDir = $package->getTargetDir();
        $this->initializeVendorAssetDir();
        @symlink($this->getAssetLinkTarget($package), $this->getLinkName($package));
    }

    protected function removePublicAsset($package)
    {
        $link = $this->getLinkName($package);
        unlink($link);
        if (strpos($package->getPrettyName(), '/')) {
            $packagePublicVendorDir = dirname($link);
            if (is_dir($packagePublicVendorDir) && !glob($packagePublicVendorDir.'/*')) {
                @rmdir($packagePublicVendorDir);
            }
        }
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        if ($this->isAssetExists($package)) {
            $this->createPublicAsset($package);
        } elseif ($this->isLinkExists($package)) {
            //remove asset if in new version it disappeared
            $this->removePublicAsset($package);
        }
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        if ($this->isAssetExists($package)) {
            $this->createPublicAsset($package);
        } elseif ($this->isLinkExists($package)) {
            //remove asset if in new version it disappeared
            $this->removePublicAsset($package);
        }
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if ($this->isAssetExists($package)) {
            $this->removePublicAsset($package);
        }
        parent::uninstall($repo, $package);
    }
}
