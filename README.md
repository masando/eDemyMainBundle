eDemyMainBundle
=============

The eDemyMainBundle is the main bundle for the eDemy Framework. It adds base functionality for other bundles.

Installation
------------

$ composer require edemy/mainbundle:dev-master

In AppKernel.php
new eDemy\MainBundle\eDemyMainBundle(),
Optional
new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),

In app/routing.yml
edemy_main:
    resource: .
    type: extra

License
-------

This bundle is under the GNUv2 license. See the complete license in the bundle:

    Resources/meta/LICENSE

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle.

