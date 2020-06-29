# Functional PHP Preprocessor - Immutable data type generator

## Configuration

So far we used FPP without any custom configuration, that's because
FPP ships with a default build-in configuration. If you need to configure
FPP, first you need to generate the default config file, so you can than
adjust it to your needs:

```bash
./vendor/bin/fpp --gen-config
```

This will create a file `fpp-config.php` in your project's root folder.

The first few options are pretty self-explanatory, but we'll go over it anyway:

- `use_strict_types` whether or not to use strict types in the generated PHP code
- `printer` a callback that creates a Printer for FPP to use. Usually the PSR
  Printer is exactly what you want, so you most likely won't change this ever.
  In case you are working with Nette Framework (we use their PhpGenerator component),
  you might want to use the `Nette\PhpGenerator\Printer` class instead. As FPP
  doesn't have any type hints on the printer used, you could also use your very
  own printer implementation here.
- `file_parser` defines the function that will parse files for you. Unless you know
  the internals of FPP and want to mess around a little, you would never touch that
  at all.
- `comment` defines the comment that is added on top of every generated file.
  You can put any string here or set to `null` to disable comments.
- `types` is the list of available types in FPP as well as the `DateTimeImmutable`
  class. That's because FPP can use already `DateTimeImmutable`, as long as you
  import that class.

## Custom type configuration

Maybe the easiest way to learn how to configure FPP is by copy & paste the example
of `DateTimeImmutable`, add to the configuration and adjust. However, let's go
through the various steps quickly.

In the `types` section of the configuration, you need to provide a class name in
the key of the config and a `TypeConfiguration` as value. The `TypeConfiguration`
class has the following constructor:

```
public function __construct(
    ?callable $parse,
    ?callable $build,
    ?callable $fromPhpValue,
    ?callable $toPhpValue,
    ?callable $validator,
    ?callable $validationErrorMessage,
    ?callable $equals
)
```

As you can see, it expects a bunch of callable but all of them are optional.

- `parse` defines the parse function to use in order to parse the given type.
  When importing already existing classes, you would provide `null` here.
  In case you want to add your very own FPP type, you need to provide the parser
  function here.
- `build` defines the function that builds the PHP code that is then printed by
  the printer defined. Again, even when we are using the `Nette\PhpGenerator`,
  there are no type hints at all. So if you replace all the default builders
  shipped with FPP and provide a special printer, you can use FPP to even generate
  code in any other language, for example JavaScript.
- `fromPhpValue` defines the function that will be used to transform a scalar or
  array PHP value to an object. If there is no function provided, the object will
  be required as is in the generated `fromArray` method.
- `toPhpValue` defines the function that will be used to transform your object to
  a scalar or array value in PHP. If there is no function provided, the object
  will be returned as is in the generated `toArray` method.
- `validator` defines the function that will be used to validate a given PHP scalar
  or array value. This will be used in the generated `fromArray` method. If left to
  `null` the value will be be validated at all.
- `validationErrorMessage` is used to display the error message in the `fromArray`
  method, when the given `validator` fails.
- `equals` defines how to compare of two of those objects are equal.

Let's have an example here real quick:

```
Role::class => new TypeConfiguration(
    null,
    null,
    fn (string $type, string $paramName) => "$type::fromName($paramName)",
    fn (string $type, string $paramName) => $paramName . '->getName()',
    fn (string $type, string $paramName) => "\is_string($paramName)",
    fn (string $paramName) => "Error on \"$paramName\", string expected",
    fn (string $paramName, string $otherParamName) => "{$paramName}->equals($otherParamName)"
),
```

So far for the configuration, let's head to [Messages](Messages.md) next.
