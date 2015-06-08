eDemyMainBundle
=============

The eDemyMainBundle is the main bundle for the eDemy Framework. It adds base functionality for other bundles.

Installation
------------

    $ composer require edemy/mainbundle:dev-master

app/AppKernel.php

    new JMS\SerializerBundle\JMSSerializerBundle(),
    new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
    new FOS\UserBundle\FOSUserBundle(),
    new eDemy\MainBundle\eDemyMainBundle(),
    Optional
    new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),

app/routing.yml

    edemy_main:
        resource: .
        type: extra

app/config.yml

    fos_user:
        db_driver: orm # other valid values are 'mongodb', 'couchdb' and 'propel'
        firewall_name: main
        user_class: eDemy\MainBundle\Entity\User

app/security.yml

    security:
        encoders:
            FOS\UserBundle\Model\UserInterface: sha512
    
        role_hierarchy:
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
    
        providers:
            fos_userbundle:
                id: fos_user.user_provider.username
    
        firewalls:
            dev:
                pattern:  ^/(_(profiler|wdt)|css|images|js)/
                security: false
    
            main:
                pattern:    ^/
                form_login:
                    provider: fos_userbundle
                    csrf_provider: form.csrf_provider
                logout:       true
                anonymous:    true

License
-------

This bundle is under the GNUv2 license. See the complete license in the bundle:

    Resources/meta/LICENSE

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle.

