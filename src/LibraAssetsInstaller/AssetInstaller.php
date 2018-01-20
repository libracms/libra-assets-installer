<?php

namespace LibraAssetsInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;

/**
 * Description of AssetInstaller
 * This class does nothing
 * Should copy root content into public/vendor/pretty-name
 *
 * @author duke
 */
class AssetInstaller extends LibraryInstaller
{
    /**
     * depends on package configuration, default = public
     * @var string
     */
    protected $publicDir = 'public';

    /**
     * {@inheridDoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'asset')
    {
        parent::__construct($io, $composer, $type);

        $config = $composer->getConfig();
        if ($config->has('public-dir')) {
            $this->publicDir = $config->get('public-dir');
        }
        //$this->publicVendorDir = $this->publicDir . '/' . 'vendor';
    }

    /**
     * {@inheridDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'asset';
    }
}
