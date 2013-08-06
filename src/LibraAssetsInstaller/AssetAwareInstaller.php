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
    protected $publicDirDefault = 'public';
    protected $packageAssetDirDefault = 'public';

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
     * Depends on root config, default = public/vendor
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
     * Flage to add or don't add target directory to public asset path
     * Package specified
     * @var type
     */
    protected $addTargetDir;


    public function __construct(IOInterface $io, Composer $composer, $type = 'asset-aware')
    {
        parent::__construct($io, $composer, $type);

        $config = $composer->getConfig();
        if (isset($config['public-dir'])) {
            $this->publicDir = $config['public-dir'];
        } else {
            $this->publicDir = $this->publicDirDefault;
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

    /**
     * setup package relative variables
     * @param type $package
     */
    protected function setupPackageVars(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['packagea-asset-dir'])) {
            $this->packageAssetDir = $extra['package-asset-dir'];
        } else {
            $this->packageAssetDir = $this->packageAssetDirDefault;
        }
        if (isset($extra['add-target-dir'])) {
            $this->addTargetDir = $extra['add-target-dir'];
        } else {
            $this->addTargetDir = false;
        }

        $this->linkPath = null;
    }

    protected function initializePublicPackagePath(PackageInterface $package)
    {
        $linkReletivePath = $this->getLinkName($package);
        $this->filesystem->ensureDirectoryExists(dirname($linkReletivePath));
        $this->linkPath = realpath(dirname($linkReletivePath)) . '/' . basename($linkReletivePath);
    }

    protected function isAssetExists(PackageInterface $package)
    {
        return file_exists($this->getInstallPath($package) . '/' . $this->packageAssetDir);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $targetDir = $package->getTargetDir();

        return $this->getPackageBasePath($package) . ($this->addTargetDir && $targetDir ? '/'.$targetDir : '');
    }

    /**
     * @param type $package
     * @return string name of link (like public/vendor/vendor-name/package-name
     */
    protected function getLinkName(PackageInterface $package)
    {
        $targetDir = $package->getTargetDir();
        return $this->getPublicPackageBasePath($package) . ($this->addTargetDir && $targetDir ? '/' . $targetDir : '');
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
            $this->getInstallPath($package) . '/' . $this->packageAssetDir
        );

        return $targetPath;
    }

    /**
     * Copy a file or recursively copy a folder<br/>
     * Print warning if file wasn't copied
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       string   $permissions New folder creation permissions
     * @return      bool Returns true on success, false on failure
     */
    public function copy($source, $dest, $permissions = 0755)
    {
        //if file then copy with proper permissions
        if (!is_dir($source)) {
            $oldUmask = umask();
            umask(0777 ^ $permissions);
            $result = copy($source, $dest);
            umask($oldUmask);
            return $result;
        }

        try {
            if (false === mkdir($dest, $permissions)) {
                throw new \ErrorException();
            }
        } catch (\ErrorException $e) {
            $this->io->write(sprintf("    Cann't create %s directory\n    " . $e->getMessage(), $dest));
            return false;
        }

        $dir = opendir($source);
        while ( false !== ($file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $this->copy($source . '/' . $file, $dest . '/' . $file, $permissions);
            }
        }
        closedir($dir);

        return true;
    }

    protected function createPublicAsset(PackageInterface $package)
    {
        $this->initializePublicPackagePath($package); //to setup $this->linkPath

        //Remove link created by assets-installer version 1.x
        if (is_link($this->linkPath)) {
            unlink($this->linkPath);
        }

        try {
            // under linux symlinks are not always supported for example
            // when using it in smbfs mounted folder
            if (false === symlink($this->getAssetLinkTargetPath($package), $this->linkPath)) {
                throw new \ErrorException();
            }
        } catch (\ErrorException $e) {
            $this->copy(
                $this->getInstallPath($package) . '/' . $this->packageAssetDir,
                $this->linkPath
            );
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
