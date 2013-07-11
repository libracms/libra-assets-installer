Vendor Package Assets installer for Composer advantaged projects
=======================

###Description
This package will help you to develop your vendor package with comfortable structure of public assets
at __public__ directory at root folder of your module.
It will create symlink to this folder in _public/vendor/yourvendor/package-name_.
So in view it will be accessible as example for _libra/libra-app_:
~~~
$this->basePath() . '/vendor/libra/libra-app/css/screen.css';
~~~


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