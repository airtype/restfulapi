# Restful Api

Restful Api is a plugin for [Craft CMS](http://buildwithcraft.com) that exposes elements as resources via a restful api. While similar to Craft's own [Element Api](https://github.com/pixelandtonic/ElementAPI) plugin, Restful Api offers a few more features such as support for more HTTP verbs, authentication and element type based permissions.

Restful Api utilizes [Fractal] (http://fractal.thephpleague.com/) to transform and serialize responses. Restful Api is also [PSR-7] (http://www.php-fig.org/psr/psr-7/) complaint. Its request and response objects can even be utilized by third party plugins if desired, although this has not been extensively tested yet.

## Installation
[Download] (https://github.com/airtype/RestfulApi/archive/master.zip) the latest version of the zip file. Place the `restfulapi` directory within the `plugins` directory. In the Craft control panel, install the plugin.

## Usage
The url structure for the api endpoints are as follows (with the `?` denoting optional uri components):

    http://example.com/{{apiRoutePrefix}}/{{elementType}}/{{?elementId}}/{{?action}}?{{?elementCriteriaModelParams}}

* `{{apiRoutePrefix}}` is configurable via the plugin config file.
* `{{elementType}}` can be any element type is enabled in the plugin config file. Any element type can be enabled or disabled. Custom element types will need to be added to the config file.
* `{{elementId}}` is the element's id. Hopefully support for uuid will be added down the road.
* `{{action}}` maps to a custom method on a controller. This allows you to do something custom action on a defined resource.
* `{{elementCriteriaModel}}` accepts any criteria that you would normally use to query Elements in Craft with.

Currently, Rest Api supports the following HTTP verbs:

* __GET__
* __POST__
* __PUT__
* __PATCH__
* __DELETE__

Example endpoints include:

* `GET  http://example.com/api/Entry?page=2&limit=10&order=postDate asc`__ returns the second page of a paginated listing of 10 Entry elements, ordered by the `postDate` attribute in ascending order
* `GET http://example.com/api/Entry/2` returns the entry with the element id of 2
* `POST http://example.com/api/Entry` creates a new Entry element
* `PUT http://example.com/api/Entry/2` updates the entry with the element id of 2
* `DELETE http://example.com/api/Entry/2` deletes the entry with the element id of 2


## Configuration
To override any values defined in the default configuration, create a `restfulApi.php` file in the config directory and place the keys to override in a returned array.

### devMode
```php
'devMode' => \Craft\craft()->config->get('devMode'),
```
If`devMode` is enabled, the stack trace for any exception that throws `RestfulApi\Exceptions\RestfulApiExpception` is output in an error response. If `devMode` is disabled, then the error is still displayed with a message, but without the stack trace. By default, `devMode` observes the setting of `devMode` defined in the `general.php` config.

### apiRoutePrefix
```php
'apiRoutePrefix' => 'api',
```
The Api Route Prefix acts as a namespace and is prepended to all routes that the API plugin defines.

### defaultHeaders
```php
'defaultHeaders' => [
    'Pragma'        => [
        'no-cache',
    ],
    'Cache-Control' => [
        'no-store',
        'no-cache',
        'must-revalidate',
        'post-check=0',
        'pre-check=0',
    ],
    'Content-Type' => [
        'application/json; charset=utf-8',
    ],
],
```
These headers will be sent with every Response.

### paginationParameter
```php
'paginationParameter' => 'page',
```
If Craft detects it's default pagination parameter in a query string, it will hijack the request and handle the response itself. To avoid this, define a pagination parameter that is different than the `paginationParameter` defined in the general config.

### contentModelFieldsLocation
```php
'contentModelFieldsLocation' => 'fields',
```
This is the key, in the body of $_POST or php://input, which holds content that will need to be added to the element's content model.

### auth
```php
'auth' => [

    'basicAuth' => [
        'username' => '',
        'password' => '',
    ],

    'craft' => [
        'permissions' => [],
        'users'       => [],
        'groups'      => [],
    ],

],
```
Restful Api has support for basic authentication or utilizing Craft to verify information about the current user.

For basic auth, simply provide a username and password that will be sent over with each request to the api.

Craft verification relies on an authenticated session already being established. This means that a user will need to be logged in to potentially be authorized to use the api. Restful Api can check against the user's permissions, username, or groups to ensure that the current user is allowed to make an authenticated request.

### defaultAuth
```php
'defaultAuth' => null,
```
The authentication method that should be applied to all requests. `null` disables authentication altogether.

### autoload
```php
'autoload' => [
    'transformers' => true,
    'validators'   => true,
],
```
By default, Restful Api will import any classes found in any plugin's `transformers` or `validators` directory. This allows custom transformers and validators to be defined in third plugins.

### defaultSerializer
```php
'defaultSerializer' => 'ArraySerializer',
```
A Serializer structures your transformed data in certain ways. For more info, see the Fractal documentation on [serializers] (http://fractal.thephpleague.com/serializers/).

### serializers
```php
'serializers' => [
    'ArraySerializer'     => 'League\\Fractal\\Serializer\\ArraySerializer',
    'DataArraySerializer' => 'League\\Fractal\\Serializer\\DataArraySerializer',
    'JsonApiSerializer'   => 'League\\Fractal\\Serializer\\JsonApiSerializer',
],
```
Available serializers that can be specified as default serializer.

### exceptionTransformer
```php
'exceptionTransformer' => 'RestfulApi\\Transformers\\ArrayTransformer',
```
This transformer will be applied to all caught exceptions that either are or extent `RestfulApi\Exceptions\RestfulApiException`.

### elementTypes
```php
'elementTypes' => [

    '*' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\ArrayTransformer',
        'validator'   => null,
        'permissions' => [
            'public'        => ['GET'],
            'authenticated' => ['POST', 'PUT', 'PATCH'],
        ],
    ],

    'Asset' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\AssetTransformer',
        'validator'   => 'RestfulApi\\Validators\AssetValidator',
    ],

    'Category' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\CategoryTransformer',
        'validator'   => 'RestfulApi\\Validators\\CategoryValidator',
    ],

    'Entry' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\EntryTransformer',
        'validator'   => 'RestfulApi\\Validators\\EntryValidator',
    ],

    'GlobalSet' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\GlobalSetTransformer',
        'validator'   => 'RestfulApi\\Validators\\GlobalSetValidator',
    ],

    'MatrixBlock' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\MatrixBlockTransformer',
        'validator'   => 'RestfulApi\\Validators\\MatrixBlockValidator',
    ],

    'Tag' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\TagTransformer',
        'validator'   => 'RestfulApi\\Validators\\TagValidator',
    ],

    'User' => [
        'enabled'     => true,
        'transformer' => 'RestfulApi\\Transformers\\UserTransformer',
        'validator'   => 'RestfulApi\\Validators\\UserValidator',
    ],

],
```
Above are configurations for each element type. If no setting is defined for an element type, the default settings defined in the `*` wildcard will be inherited.

Permissions can be added to each element type individually. For example, if you only wanted authenticated requests to view users, your configuration would be something like this:

```php
'User' => [
    'enabled'     => true,
    'transformer' => 'RestfulApi\\Transformers\\UserTransformer',
    'validator'   => 'RestfulApi\\Validators\\UserValidator',
    'permissions' => [
        'public'        => [],
        'authenticated' => ['GET'],
    ],
],
```

## Disclaimer
This plugin is still considered to be in early development. Use with caution. If you see something wrong, please create an issue or feel free to fork and make a pull request.
