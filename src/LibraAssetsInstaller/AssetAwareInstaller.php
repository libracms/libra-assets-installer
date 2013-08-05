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
    /**
     * depends on package config, default = public
     * @var string
     */
    protected $publicDir;
    
    /**
     * depends on package config, default = public
     * @var string
     */
    protected $packageAssetDir;

    /**
     * depends on package config, default = public/vendor
     * @var string
     */
    protected $publicVendorDir;

    /**
     * Absolute path to link <br>
     * Depends on package
     * @var string
     */
    protected $linkPath;

    /**
     * relative path for link target to make it transportable into file system
     * @var string
     */
    protected $vendorDirRelative;


    public function __construct(IOInterface $io, Composer $composer, $type = 'asset-aware')
    {
        parent::__construct($io, $composer, $type);
        $this->vendorDirRelative = $this->vendorDir;
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset-aware';
    }

    /**
     * setup package relative variables
     * @param type $package
     */
    protected function setupPackageVars(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['public-dir'])) {
            $this->publicDir = $extra['public-dir'];
        } else {
            $this->publicDir = 'public';
        }
        if (isset($extra['packagea-asset-dir'])) {
            $this->packageAssetDir = $extra['package-asset-dir'];
        } else {
            $this->packageAssetDir = 'public';
        }

        $this->linkPath = null;

        $this->publicVendorDir = $this->publicDir . '/' . $this->vendorDirRelative;
    }

    protected function initializePublicPackagePath(PackageInterface $package)
    {
        $publicPackagePackagePath = dirname($this->getLinkName($package));
        $this->filesystem->ensureDirectoryExists($publicPackagePackagePath);
        $this->linkPath = realpath($publicPackagePackagePath) . '/' . $this->packageAssetDir;
    }

    protected function isAssetExists(PackageInterface $package)
    {
        return file_exists($this->getInstallPath($package) . '/' . $this->packageAssetDir);
    }

    /**
     * @param type $package
     * @return string name of link (like public/vendor/vendor-name/package-name
     */
    protected function getLinkName(PackageInterface $package)
    {
        $targetDir = $package->getTargetDir();
        return $this->getPublicPackageBasePath($package) . ($targetDir ? '/' . $targetDir : '');
    }

    protected function getPublicPackageBasePath(PackageInterface $package)
    {
        return $this->publicVendorDir . '/' . $package->getPrettyName();
    }

    /**
     * Relative path to target
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function getAssetLinkTargetPath(PackageInterface $package)
    {
        $targetPath = $this->filesystem->findShortestPath(
            $this->linkPath,
            $this->getInstallPath($package) . $this->packageAssetDir,
            true
        );

        return $targetPath;
    }

    protected function createPublicAsset(PackageInterface $package)
    {
        $this->initializePublicPackagePath($package); //to setup $this->linkPath
        if (!file_exists($this->linkPath)) {
            @symlink($this->getAssetLinkTargetPath($package), $this->linkPath);
        }
    }

    protected function removePublicAsset(PackageInterface $package)
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
        $this->setupPackageVars($package);
        if ($this->isAssetExists($package)) {
            $this->createPublicAsset($package);
        }
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->setupPackageVars($initial);
        if ($this->isAssetExists($initial)) {
            $this->removePublicAsset($initial);
        }
        $this->setupPackageVars($target);
        if ($this->isAssetExists($target)) {
            $this->createPublicAsset($target);
        }
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->setupPackageVars($package);
        if ($this->isAssetExists($package)) {
            $this->removePublicAsset($package);
        }
        parent::uninstall($repo, $package);
    }
}

