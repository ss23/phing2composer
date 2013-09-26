phing2composer
==============

Converts from phing dependent_modules to composer.json.

[![Build Status](https://travis-ci.org/ss23/phing2composer.png)](https://travis-ci.org/ss23/phing2composer)

Caveat
------

This tool has an important caveat: It only works if you don't change code in the module.

This applies to the modules listed with "type": "package" in the composer.json file.  This package type avoids the need for a composer.json file in the module itself, but it's much more limited in functionality.  See the “note in the composer docs](http://getcomposer.org/doc/05-repositories.md#package-2)

The fix? When it's time to update a module in a project set up in this way, there are two fixes:

### The quick fix

 * Add a composer.json to the module
 * Update your project composer.json to use "type": "vcs" for that package

### The good fix

 * Add a composer.json to the module and shift to github, *OR*
 * Merge back your project-specific changes where appropriate into the existing OSS module
 * Submit to packagist
 * Remove the custom package definition altogether and make use of the packagist default

