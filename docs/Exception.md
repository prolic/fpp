# Definition

## Basic

```
namespace App;
data UserNotFound = UserNotFound deriving (Exception);
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class UserNotFound extends \Exception
{
}
```

## With marker and parent exception
```
namespace App;
marker UserLandException;
data UserNotFound: UserLandException = UserNotFound deriving (Exception: \RuntimeException);
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

interface UserLandException
{
}

class UserNotFound extends \RuntimeException implements UserLandException
{
}
```

## Without marker but with parent exception
```
namespace App;
data UserNotFound = UserNotFound deriving (Exception: \RuntimeException);
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class UserNotFound extends \RuntimeException
{
}
```

## With custom parent exception
```
namespace App;
data UserException = UserException deriving (Exception: \RuntimeException);
data UserNotFound = UserNotFound deriving (UserException);
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class UserException extends \RuntimeException
{
}

class UserNotFound extends UserException
{
}
```


## With custom properties
```
namespace App;
data UserId = UserId deriving (Uuid);
data UserNotFound = UserNotFound { UserId $id } deriving (Exception);
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class UserId
{
    //...
}

class UserNotFound extends \Exception
{
    private $id;

    public function __construct(UserId $id, string $message, int $code = 0, \Exception $previous = null)
    {
        $this->id = $id;

        parent::__construct($message, $code, $previous);
    }

    public function id(): UserId
    {
        return $this->id;
    }
}
```


## With named constructor
```
namespace App;
data EmailAlreadyUsed = EmailAlreadyUsed deriving (Exception) with
    | withEmail { string $email } => 'Email {{$email}} is already used';
```

generates

```php

<?php

declare(strict_types=1);

namespace App;

class EmailAlreadyUsed extends \Exception
{
    public static function withEmail(string $email, int $code = 0, \Throwable $previous = null): self
    {
        return new self(sprintf('Email %s is already used', $email), $code, $previous);
    }
}

```

## With default message
```
namespace App;
data NotOldEnough = NotOldEnough deriving (Exception) with
    | _ => 'User is not old enough';
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class NotOldEnough extends \Exception
{
    public function __construct(string $message = 'User is not old enough', int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
```

## With custom property and named constructor
```
namespace App;
data TooYoung = TooYoung { int $age } deriving (Exception) with
    | withAge { int $age, int $requiredAge } => 'User must be {{$requiredAge}}';
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class TooYoung extends \Exception
{
    private $age;

    public function __construct(int $age, string $message, int $code = 0, \Exception $previous = null)
    {
        $this->age = $age;

        parent::__construct($message, $code, $previous);
    }

    public static function withAge(int $age, int $requiredAge, int $code = 0, \Throwable $previous = null): self
    {
        return new self($age, sprintf('User must be %s', $requiredAge), $code, $previous);
    }

    public function age(): int
    {
        return $this->age;
    }
}
```

## Full featured definition
```
namespace App;
data UserId = UserId deriving (Uuid);
marker UserLandException;
data InvalidUserException: UserLandException = InvalidUserException deriving (Exception: \RuntimeException);
data UserNotFound = UserNotFound { UserId $id } deriving (Exception: InvalidUserException) with
    | withId { UserId $id } => 'User {{$id}} does not exist'
    | withEmail { string $email, UserId $id } => 'User {{$email}} does not exist'
    | _ => 'An user is no one';
```

generates

```php
<?php

declare(strict_types=1);

namespace App;

class UserId
{
    //...
}

interface UserLandException
{
}

class InvalidUserException extends \RuntimeException implements UserLandException
{
}

class UserNotFound extends InvalidUserException
{
    private $id;

    public function __construct(UserId $id, string $message = 'An user is no one', int $code = 0, \Throwable $previous = null)
    {
        $this->id = $id;

        parent::__construct($message, $code, $previous);
    }

    public static function withId(UserId $id, int $code = 0, \Throwable $previous = null): self
    {
        return new self($id, sprintf('User %s does not exist', $id), $code, $previous);
    }

    public static function withEmail(string $email, UserId $id, int $code = 0, \Throwable $previous = null): self
    {
        return new self($id, sprintf('User %s does not exist', $email), $code, $previous);
    }

    public function id(): UserId
    {
        return $this->id;
    }
}
```

# Error cases

## With non instance of \Throwable parent
```
data UserNotFound = UserNotFound deriving (Foo);
```

will throw a \RuntimeException: "App\UserNotFound" cannot extend "Foo" because it is not an instance of "\Throwable"

## With named constructor not defining exception properties
```
namespace App;
data TooYoung = TooYoung { int $age } deriving (Exception) with
    | withRequiredAge { int $requiredAge } => 'User must be {{$requiredAge}}';
```

will throw a \RuntimeException: "App\UserNotFound::withRequiredAge" is missing required argument "$age"
