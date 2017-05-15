# KleijnWeb\PhpApi\RoutingBundle 
[![Build Status](https://travis-ci.org/kleijnweb/php-api-routing-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/php-api-routing-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/php-api-routing-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/php-api-routing-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/php-api-routing-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/php-api-routing-bundle/?branch=master)
[![Latest Unstable Version](https://poser.pugx.org/kleijnweb/php-api-routing-bundle/v/unstable)](https://packagist.org/packages/kleijnweb/php-api-routing-bundle)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/php-api-routing-bundle/v/stable)](https://packagist.org/packages/kleijnweb/php-api-routing-bundle)

## <a name="config"></a> Install And Configure

Install using composer (`composer require kleijnweb/php-api-routing-bundle`).

Add OpenApi (or RAML) routing to your app, for example:
 
```yml
test:
  resource: "path/to/spec.yml"
  type: php-api
```

The `type` as well as the `php-api` prefix mentioned below is configurable:

```yml
api_routing:
  name: customname
```
## Routing

To view the routes added by PhpApi\RoutingBundle, you can use Symfony's `debug:router`. Route keys include the API spec base filename to prevent collisions. For path parameters,
PhpApiRoutingBundle adds additional requirements to the routes. This way `/foo/{bar}` and `/foo/bar` wont conflict when `bar` is defined to be an integer. 
This also supports Swaggers `pattern` and `enum` when dealing with string path parameters.

### Controller Resolution

All controllers must be defined as services in the DI container. PhpApi\RoutingBundle sees an `operation id` as composed from the following parts:

```
[router].[controller]:[method]
```

`Router` is a DI key namespace in this context. The `router` segment defaults to `php-api.controller`, but can be overwritten at the `Path Object` level using `x-router`:

```yaml
paths:
  x-router: my.default.controller.di.namespace
  /foo:
    ...
  /foo/{bar}:
    ...
```

The `controller` segments defaults to the resource name as extracted from the path by convention. For example, for path `/foo/something` the default router + controller would be: `php-api.controller.foo`.

You can override the whole of `[router].[controller]` using `x-router-controller`. This will not only override the default, but any declaration of `x-router`, too:

```yaml
paths:
  x-router: my.default.controller.di.namespace
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    ...
```

The following is also supported (set controller for a specific method):

```yaml
paths:
  x-router: my.default.controller.di.namespace
  /foo:
    ...
  /foo/{bar}:
    patch:
      x-router-controller: an.alternate.di.namespace.controller
    ...
```

Finally, the `method` segment defaults to the HTTP method name, but may be overridden using Swagger's `operationId` or `x-router-controller-method`. Note the Swagger spec requires `operationId` to be unique, so while `operationId` can contain only the method name, you're usually better off using `x-router-controller-method`.
You can also use a fully qualified operation id using double colon notation, eg "my.controller.namespace.myresource:methodName". Combining `x-router` or `x-router-controller` and a qualified `operationId` ignores the former.

```yaml
paths:
  x-router: my.default.controller.di.namespace
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    post:
      # Ingores declarations above
      operationId: my.controller.namespace.myresource:methodName
    ...
```

```yaml
paths:
  x-router: my.default.controller.di.namespace
  /foo:
    ...
  /foo/{bar}:
    post:
      # Resolves to 'my.default.controller.di.namespace.foo:methodName'
      x-router-controller-method: methodName
    ...
```

```yaml
paths:
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    post:
      # Same as above. Valid, but discouraged
      operationId: methodName
    ...
```
 
## Contributing

Pull request are *very* welcome, as long as:

 - All automated checks were successful
 - Merge would not violate semantic versioning 
 - When applicable, the relevant documentation is updated
 
## License
 
KleijnWeb\PhpApi\RoutingBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
