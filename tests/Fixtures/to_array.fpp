namespace FppTest\Fixtures\Generated;

data User = User {
    UserId $id,
    string $name,
    Email $email,
    Email[] $secondaryEmails,
    string[] $nickNames,
    ?PhoneNumber[] $phoneNumbers
} deriving (FromArray, ToArray);

data UserId = UserId deriving ( Uuid );
data Email = Email { string $email }  deriving ( FromString, ToString );
data PhoneNumber = PhoneNumber { string $number } deriving ( FromString, ToString );
