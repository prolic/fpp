Markers are PHP interfaces with no method.

They are defined using the `marker` keyword:

```
namespace Foo;
marker MyMarker;
```

It is possible to extend marker with the following syntax:

```
namespace Foo;
marker MyMarkerA;
marker MyMarkerB : MyMarkerA;
```

It is also possible to extend marker located in another namespace:

```
namespace Foo;
marker MyMarkerA;
namespace Bar;
marker MyMarkerB : \Foo\MyMarkerA;
```

And to extend existing interfaces:

```
namespace Foo;
marker MyMarker : \JsonSerializable
```

Wanna see more? check [Derivings](Derivings.md)

Wanna have [prooph components (http://getprooph.org/)](http://getprooph.org/) integration? check [prooph](prooph.md)
