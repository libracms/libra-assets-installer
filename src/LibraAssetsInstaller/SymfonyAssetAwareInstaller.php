<?php

namespace LibraAssetsInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Install modules that contain public directory with assets
 *
 * @author duke
 */
class SymfonyAssetAwareInstaller extends AssetAwareInstaller
{
    protected $publicDirDefault = 'web/bundles';
    protected $packageAssetDirDefault = 'Resources/assets';

    public function __construct(IOInterface $io, Composer $composer, $type = 'symfony-asset-aware')
    {
        parent::__construct($io, $composer, $type);
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'symfony-asset-aware';
    }
}
