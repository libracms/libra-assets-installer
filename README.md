Libra CMS
=======================


Libra Installer
-----------------------

###Description
It's applicable for modules with public directory.
It will create symlink to this folder in _public/vendor/yourvendor/package-name_
So in view it will be accessible as example for _libra/libra-app_:
~~~
$this->basePath() . '/vendor/libra/libra-app/css/screen.css';
~~~


__Note__
:   For working this module you should set it first in required list in composer.json.
