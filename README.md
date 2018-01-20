Vendor Package Assets installer for Composer packages
=======================

##Description
This package will help you to develop your vendor package with comfortable structure of public assets
in __public__ directory at root folder of your package.
It will create symlink to this folder in __public/vendor/vendor-name/package-name__.
So file public/css/screen.css will be accessible in view by:
~~~
$this->basePath() . '/vendor/vendor-name/package-name/css/screen.css';
~~~
At version 2.0.0 was changed logic of installing to use custom installer and rely on package type.

-   If your package is some assets like jquery, jquery-ui or twitter-bootsrap etc., 
    you should set up type of package to __asset__. Then add to require list this package like  

    ~~~
    {
        "name":         "vendor-name/asset-package-name",
        "type":         "asset"
        "require": {
            "libra/libra-assets-installer":     "~2.0"
        }
    }
    ~~~

    After installing assets will be in __public/vendor/vendor-name/asset-package-name__.

-   If you create standard ZF2 module that contains public assets into __public__ directory  
    then use `"type": "asset-aware"`:

    ~~~
    {
        "name":         "vendor-name/package-with-assets-name",
        "type":         "asset-aware"
        "require": {
            "libra/libra-assets-installer":     "~2.0"
        }
    }
    ~~~

    Then contents of __package-with-assets-name/public__ folder will be symlinked or
    on failure copied to __public/vendor/vendor-name/package-with-assets-name__

So URL will be the same in both cases.


##Options
There available options (in format "type default"):

-   public-dir  (only in root package)
    -   if you wish put asset into another folder - put its name above
-   package-asset-dir (not available for type = asset)  
    -   if your asset dir has another name - put it above
-   add-target-dir: bool false/true (may be suitable for symfony bundles)
    -   if enabled add target dir path to public asset path
    -   Since v3.0 always __true__, DEPRECATED.

Some example:
~~~~
    "extra": {
        "package-asset-dir": "assets"
    }
~~~~
In root package:
~~~~
    {
        "config": {
            "public-dir": "httdocs",
        }
    }
~~~~

__Note!__ For Zend Framework 2 modules it don't need to set up any configurations as usually.


----------------
##Instructions for ver. 1.*
Despite of its working even in version 2.* you should'n rely on it feature in late version.
This feature can be removed in any time in higher version but will be present in ver. 1.*.
I encourage to use ver, 2.*.

__Note__
:   For working this module you should set it first in required list in composer.json.
Also supports  Windows Vista/Windows Server 2008 or greater by [php documentation][1].
But really tested only under Linux. It uses __symlink__ function hence behaviour at Windows unknown for me.

###Using:
In root composer.json add this packages to required list as first item.
Add this lines in root composer:
~~~
    "scripts": {
        "post-package-install":  "LibraAssetsInstaller\\Installer::postPackageInstall",
        "pre-package-uninstall": "LibraAssetsInstaller\\Installer::prePackageUninstall",
        "post-package-update":   "LibraAssetsInstaller\\Installer::postPackageUpdate"
    }
~~~

At first It was created for Libra CMS.

[1]: http://www.php.net/manual/en/function.symlink.php#refsect1-function.symlink-changelog