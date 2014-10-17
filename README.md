# Kern

Kern stands for Kernel, and it started as a simple framework for created modular API, but it's been extended to be used as a simple full web framework.

**NOTE:** This documentation is not quite complete, please read over the documentation, and then look at the source code for a better understanding. There is less than 1000 lines of code in the entire system, so it shouldn't be that hard to overview the source code.

## Basic Idea

The main components in the Kern framework are the Router and Routables. Simply, the router takes a request and the request goes to a routable. The routable then returns a response. That's it.

## Router

The Router is responsible for creating the request from a uri string and data for the request.

Before the router can create a request, routes need to be defined for the Router. A route is simply just a mapping of a uri string to an actual class name and method.

Once the routes are defined for the router, you can then create a request by passing in a uri string. The router first creates an empty request object with the data passed in from the `create_request` method of the router. If you pass an empty string, the Request object will determine the uri from the HTTP request to php. The router will then figure out which route to build from your uri. Now, the Router attaches the defined route object to the Request object that was just created. If a route could not be found, a callback is fired. The default value for the callback just throws an exception. The Router will then return the built request.

### Routes

There are two types of routes, simple and regex routes. A simple route just matches the uri string to the actual request uri passed to the router. The regex routes will compare a regular expression for the routes to the uri string. Simple routes are the most efficient because the Router will just use the Routes uri string as an index to an associative array of routes. So when you create a request, it does an array lookup with the uri passed in as the key. So all simple routes are matched in O(1) time complexity. Use them as much as possible. If no simple route is matched, then the router will loop over the set of regex routes and to match the routes. Regex routes are matched in the order they were defined.

See the route class for more details.

## Dispatcher

Now that you have a request, you need to dispatch it to the class and method defined in the route for the request. The Dispatcher's only method of `dispatch` does that exactly. It takes a request, instantiates the appropriate class and method, verifies that the class is an instance of Routable, set the request for the new class, and calls the appropriate function.

## Request

See the request class for more details

## Response

There are two types of responses currently: Json and Html responses. You can create your own response objects, they just need to implement the `__toString`.

### HtmlResponse

This is more like a string response, but you basically just instantiate the response object with the string to echo into the browser.

### JsonResponse

A little more complicated than the html response, but it's used for JSON API's, see the class for more details.

## Loader

Simple and *efficient* PSR-0 loader to be used to load packages.

The following code will load all classes with a starting namespace of Lib, and it will look for them in the directory of `./path/to/lib/directory/`.

````php
$lib_loader = new Kern\Loader('Lib', './path/to/lib/directory/');
$lib_loader->register();
````

## Config

Simple Config class that allows you to access config files as ini or php. If you use a php file for config then you need to return a php array to export it to the config class.

*php example*

    $cfg = [
        'key'   => 'val',
    ];

    $cfg['other-key'] = some_other_val();
    return $cfg; // return the array

## Bootstrap

Simple application bootstrap class that allows you to initialize the Kern system easily.
