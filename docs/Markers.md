Markers are PHP interfaces with no method.

## Definition

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

And to extend existing markers:

```php
namespace App;

class MyMarker
{
}
```

```
namespace Foo;
marker MyMarker : \App\MyMarker;
```

And even to extend multiple markers:

```
namespace Foo;
marker MyMarkerA;
marker MyMarkerB;
marker MyMarkerC : MyMarkerA, MyMarkerB;
```

## Usage

Use them on your `data` definition:

```
namespace Foo;
marker MyMarkerA;
marker MyMarkerB;
marker MyMarkerC : MyMarkerA, MyMarkerB;
marker MyMarkerD;
data MyData : MyMarkerC, MyMarkerD = MyData;
```

Wanna see more? check [Derivings](Configuration.md)

Wanna have [prooph components](http://getprooph.org/) integration? See the [prooph integration](prooph.md)
