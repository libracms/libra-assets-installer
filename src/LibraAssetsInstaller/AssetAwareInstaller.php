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

    protected $vendorDirRelative;


    public function __construct(IOInterface $io, Composer $composer, $type = 'asset-aware')
    {
        parent::__construct($io, $composer, $type);

        $extra = $composer->getPackage()->getExtra();
        if (isset($extra['public-dir'])) {
            $this->publicDir = $extra['public-dir'];
        }
        if (isset($extra['packagea-asset-dir'])) {
            $this->packageAssetDir = $extra['package-asset-dir'];
        }

        $this->publicVendorDir = $this->publicDir . '/' . $this->vendorDir;
        $this->vendorDirRelative = $this->vendorDir;
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset-aware';
    }

    protected function initializeVendorAssetDir($package)
    {
        $publicPackageAssetPath = dirname($this->getLinkName($package));
        $this->filesystem->ensureDirectoryExists($publicPackageAssetPath);
    }

    protected function isAssetExists($package)
    {
        return file_exists($this->getInstallPath($package) . '/' . $this->packageAssetDir);
    }

    /**
     * @param type $package
     * @return string name of link (like public/vendor/vendor-name/package-name
     */
    protected function getLinkName($package)
    {
        $targetDir = $package->getTargetDir();
        return $this->getPublicPackageBasePath($package) . ($targetDir ? '/' . $targetDir : '');
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
        $targetDir = $package->getTargetDir();
        return str_repeat('../', $this->findDirDeep())
            . $this->vendorDirRelative
            . '/' . $package->getPrettyName()
            . ($targetDir ? '/'.$targetDir : '')
            . '/' . $this->packageAssetDir;
    }

    protected function getPublicPackageBasePath($package)
    {
        return $this->publicVendorDir . '/' . $package->getPrettyName();
    }

    protected function createPublicAsset($package)
    {
        $this->initializeVendorAssetDir($package);
        $linkName = $this->getLinkName($package);
        if (!file_exists($linkName)) {
            @symlink($this->getAssetLinkTarget($package), $linkName);
        }
    }

    protected function removePublicAsset($package)
    {
        $publicPackageBasePath = $this->getPublicPackageBasePath($package);
        $this->filesystem->remove($publicPackageBasePath);
        if (strpos($package->getPrettyName(), '/')) {
            $packagePublicVendorDir = dirname($publicPackageBasePath);
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

