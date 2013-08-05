<?php

namespace LibraAssetsInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

/**
 * Description of AssetInstaller
 *
 * @author duke
 */
class AssetInstaller extends LibraryInstaller
{
    /**
     * depends on package config, default = public
     * @var string
     */
    protected $publicDir;

    /**
     * Original and relative path of vendor dir
     * @var string
     */
    protected $vendorDirOriginal;

    /**
     * Flage to add or don't add target directory to public asset path
     * Package specified
     * @var type
     */
    protected $addTargetDir;

    /**
     * {@inheridDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'asset')
    {
        parent::__construct($io, $composer, $type);
        $this->vendorDirOriginal = $this->vendorDir;
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset';
    }

    /**
     * Redefine vendor dir hence config and defaults
     * @param type $package
     */
    protected function redefineVendorDir(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['public-dir'])) {
            $this->publicDir = $extra['public-dir'];
        } else {
            $this->publicDir = 'public';
        }

        if (isset($extra['add-target-dir'])) {
            $this->addTargetDir = $extra['add-target-dir'];
        } else {
            $this->addTargetDir = false;
        }

        $this->vendorDir = $this->publicDir . '/' . $this->vendorDirOriginal;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $this->redefineVendorDir($package);

        $targetDir = $package->getTargetDir();

        return $this->getPackageBasePath($package) . ($this->addTargetDir && $targetDir ? '/'.$targetDir : '');
    }
}
