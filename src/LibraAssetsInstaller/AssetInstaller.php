<?php

namespace LibraAssetsInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;

/**
 * Description of AssetInstaller
 *
 * @author duke
 */
class AssetInstaller extends LibraryInstaller
{
    protected $publicDir = 'public';

    /**
     * {@inheridDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'asset')
    {
        parent::__construct($io, $composer, $type);

        $extra = $composer->getPackage()->getExtra();
        if ($extra['public-dir'] != '') {
            $this->publicDir = $extra['public-dir'];
        }
        $this->vendorDir = $this->publicDir . '/' . $this->vendorDir;
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset';
    }
}
