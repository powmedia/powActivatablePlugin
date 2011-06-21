TODO:
=====
- Add tests to make sure caching of getActiveXxx and countActiveXxx are working and correct


pmActivatable plugin     
================

Overview
--------

This plugin adds the Activatable Doctrine behavior which is useful for when you want to hide or 'switch off' certain records.  For example, for moderation, approval etc.

This adds an is_active column (can be renamed) and helper methods to the Table model class for modifying queries.

Installation
------------

    php symfony plugin:install pmActivatablePlugin

Upgrade plugins
---------------

    php symfony plugin:upgrade -r=1.1.11 pmActivatablePlugin

Uninstallation
--------------

    php symfony plugin:uninstall pmActivatablePlugin

Requirements
------------

Doctrine 1.4
	
Usage
-----

These methods are added to the Table model class:
    addIsActiveQuery
    addIsNotActiveQuery
    countActive
    countNotActive
    findActive
    findNotActive

License
-------

For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
