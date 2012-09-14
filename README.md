Libra CMS
=======================


Libra Installer
-----------------------

###Description
It's applicable for modules with public directory.
It will create symlink to this folder in _public/vendor/youvendor/package-name_
So in view it will be accessible as example for libra/libra-article:
~~~
$this->basePath() . '/vendor/libra/libra-article/css/screen.css';
~~~


__Note__
:   For working this module you should set it first in required list in composer.json.
