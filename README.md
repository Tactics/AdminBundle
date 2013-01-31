AdminBundle
===========


Flash messages
--------------
Flash messages "bootstrap style" are supported by a twig template that's included
in the main layouts.

There are 4 types of messages: warning, info, error and success

### Usage

Simply add messages to the flash bag to use:

    $this->get('session')->getFlashBag()->add('message.error', 'The world was destroyed');

Some goes for message.warning, message.info and message.success



ObjectRouteResolver
-------------------

Provides a service tactics.object_route_resolver that enables you to find a 
matching route for a (Propel) object or class.

### Configuration

Create a object_routes.yml file in your app/config in which you will define
the default routes of each entity.

    parameters:
        object_routes:
            Tactics\Bundle\PersoonBundle\Model\Persoon: persoon_show
            Tactics\Bundle\OrganisationBundle\Model\Plant: plant_show


Include this file in your config.yml:

    imports:
    - { resource: object_routes.yml }


### Usage

    $container->get('tactics.object_route_resolver')->retrieveByClass('Tactics\Bundle\PersoonBundle\Model\Persoon');

### Nice to know
    If you have a menu item that has a route named 'home' this item will not be shown on the homepage
    
    Title attribute and page title can be set as global twig parameters in config.yml 
        twig:
            globals:
                title: 
                page_title:


Error pages
--------------

The default exception-pages can be overwritten by the custom exception controller. 

There are three error pages : 
- 404 Page not found
- 403 Access denied
- All other errors

### Usage

Add to the config (of a certain environment or all)

twig:
  exception_controller: "TacticsAdminBundle:Exception:exception" 
