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
    protected $publicDirDefault = 'public';

    /**
     * depends on package config, default = public
     * @var string
     */
    protected $publicDir;

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

        $config = $composer->getConfig();
        if ($config->has('public-dir')) {
            $this->publicDir = $config->get('public-dir');
        } else {
            $this->publicDir = $this->publicDirDefault;
        }
        //$this->vendorDir = $this->publicDir . '/' . $this->vendorDir; // vendor dir now is absolute path and
        // doesnt pass when vendor-dir changed in config
        $this->publicVendorDir = $this->publicDir . '/' . 'vendor';
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset';
    }

    /**
     * setup package relative variables
     * @param type $package
     */
    protected function setupPackageVars(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['add-target-dir'])) {
            $this->addTargetDir = $extra['add-target-dir'];
        } else {
            $this->addTargetDir = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $this->setupPackageVars($package);

        $targetDir = $package->getTargetDir();

        return $this->getPackageBasePath($package) . ($this->addTargetDir && $targetDir ? '/'.$targetDir : '');
    }
}
