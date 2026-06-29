# VixPHPCS Sniff Catalog

This document describes every custom sniff shipped with VixPHPCS.

The default `VixPHPCS` ruleset covers the main package rules. Some sniffs can also be enabled individually in a custom PHPCS standard when you want a narrower or more opinionated setup.

## Table of Contents

- [VixPHPCS Sniff Catalog](#vixphpcs-sniff-catalog)
  - [Table of Contents](#table-of-contents)
  - [Arrays](#arrays)
    - [VixPHPCS.Arrays.MixedArrayKeyTypes](#vixphpcsarraysmixedarraykeytypes)
    - [VixPHPCS.Arrays.DuplicateArrayKey](#vixphpcsarraysduplicatearraykey)
  - [Attributes](#attributes)
    - [VixPHPCS.Attributes.ForbiddenAttributes](#vixphpcsattributesforbiddenattributes)
  - [Constants](#constants)
    - [VixPHPCS.Constants.UppercaseMagicConstants](#vixphpcsconstantsuppercasemagicconstants)
  - [Control Structures](#control-structures)
    - [VixPHPCS.ControlStructures.DisallowCountInLoop](#vixphpcscontrolstructuresdisallowcountinloop)
    - [VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach](#vixphpcscontrolstructuresdisallowsamekeyandvalueinforeach)
    - [VixPHPCS.ControlStructures.DisallowThrowInTernary](#vixphpcscontrolstructuresdisallowthrowinternary)
    - [VixPHPCS.ControlStructures.UseInArray](#vixphpcscontrolstructuresuseinarray)
  - [Formatting](#formatting)
    - [VixPHPCS.Formatting.MethodChainingIndentation](#vixphpcsformattingmethodchainingindentation)
    - [VixPHPCS.Formatting.MethodChainingPerLine](#vixphpcsformattingmethodchainingperline)
    - [VixPHPCS.Formatting.ConsistentStatementIndentation](#vixphpcsformattingconsistentstatementindentation)
    - [VixPHPCS.Formatting.DisallowMultipleThrowsPerLine](#vixphpcsformattingdisallowmultiplethrowsperline)
  - [Functions](#functions)
    - [VixPHPCS.Functions.DisallowCastFunctions](#vixphpcsfunctionsdisallowcastfunctions)
    - [VixPHPCS.Functions.DisallowHttpFileGetContents](#vixphpcsfunctionsdisallowhttpfilegetcontents)
    - [VixPHPCS.Functions.DisallowNullableBoolReturnType](#vixphpcsfunctionsdisallownullableboolreturntype)
    - [VixPHPCS.Functions.PreferModernStringFunctions](#vixphpcsfunctionsprefermodernstringfunctions)
    - [VixPHPCS.Functions.PreferJsonValidate](#vixphpcsfunctionspreferjsonvalidate)
  - [Objects](#objects)
    - [VixPHPCS.Objects.DisallowReturnInConstructorDestructor](#vixphpcsobjectsdisallowreturninconstructordestructor)
    - [VixPHPCS.Objects.DisallowReturnInSetter](#vixphpcsobjectsdisallowreturninsetter)
    - [VixPHPCS.Objects.StaticInFinalClass](#vixphpcsobjectsstaticinfinalclass)
    - [VixPHPCS.Objects.RequireStringableInterface](#vixphpcsobjectsrequirestringableinterface)
    - [VixPHPCS.Objects.DisallowVariableStaticProperty](#vixphpcsobjectsdisallowvariablestaticproperty)
    - [VixPHPCS.Objects.DisallowNullsafeThis](#vixphpcsobjectsdisallownullsafethis)
    - [VixPHPCS.Objects.RequireFinalTraitMethods](#vixphpcsobjectsrequirefinaltraitmethods)
  - [Operators](#operators)
    - [VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation](#vixphpcsoperatorspreferbooleancastoverdoublenegation)
  - [PhpDoc](#phpdoc)
    - [VixPHPCS.PhpDoc.DeprecatedTag](#vixphpcsphpdocdeprecatedtag)
    - [VixPHPCS.PhpDoc.DisallowUnusedTemplate](#vixphpcsphpdocdisallowunusedtemplate)
    - [VixPHPCS.PhpDoc.DisallowInvalidTypeUsage](#vixphpcsphpdocdisallowinvalidtypeusage)
    - [VixPHPCS.PhpDoc.DisallowSuspiciousLiteralTypes](#vixphpcsphpdocdisallowsuspiciousliteraltypes)
    - [VixPHPCS.PhpDoc.DisallowRedundantTypes](#vixphpcsphpdocdisallowredundanttypes)
    - [VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes](#vixphpcsphpdocdisallowvoidmixedwithothertypes)
  - [Yii2](#yii2)
    - [VixPHPCS.Yii2.DisallowResponseFormatAssignment](#vixphpcsyii2disallowresponseformatassignment)
    - [VixPHPCS.Yii2.PreferActiveRecordShortcuts](#vixphpcsyii2preferactiverecordshortcuts)
    - [VixPHPCS.Yii2.PreferExistsOverCount](#vixphpcsyii2preferexistsovercount)
    - [VixPHPCS.Yii2.PreferIdentityOverFindOne](#vixphpcsyii2preferidentityoverfindone)
    - [VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck](#vixphpcsyii2preferisguestoveruseridcheck)

## Arrays

### VixPHPCS.Arrays.MixedArrayKeyTypes

**Level:** Warning

Flags array literals that mix integer and string keys. Keeping one key style per array makes shapes easier to scan and avoids hidden key casting rules.

**Bad:**

```php
$data = [
    'id' => 1,
    0 => 'Anton',
];

$data = [
    'id' => 1,
    'Anton',
];
```

**Good:**

```php
$data = [
    'id' => 1,
    'name' => 'Anton',
];

$data = [
    0 => 'first',
    1 => 'second',
    'third',
];
```

### VixPHPCS.Arrays.DuplicateArrayKey

**Level:** Error

Detects duplicate explicit keys in array declarations. Duplicate keys silently overwrite earlier values, which usually means part of the array definition is dead code or a bug.

**Bad:**

```php
$config = [
    'host' => 'primary',
    'host' => 'secondary',
];

$map = array(
    '1' => 'one',
    1 => 'duplicate',
);
```

**Good:**

```php
$config = [
    'host' => 'primary',
    'port' => 443,
];

$map = [
    '1' => 'one',
    '01' => 'distinct string key',
];
```

## Attributes

### VixPHPCS.Attributes.ForbiddenAttributes

**Level:** Warning

Flags attributes that your project has decided to ban. This is useful for keeping IDE-only helpers, deprecated attributes, or project-specific anti-patterns out of the codebase.

**Bad:**

```php
#[ArrayShape(['id' => 'int'])]
function loadUser(): array
{
    return ['id' => 1];
}

#[JetBrains\PhpStorm\Pure]
function calculate(): int
{
    return 1;
}
```

**Good:**

```php
function loadUser(): array
{
    return ['id' => 1];
}

function calculate(): int
{
    return 1;
}
```

**Parameters:**

- `forbiddenAttributes`: list of short or fully qualified attribute names to report. The default `VixPHPCS` ruleset forbids `ArrayShape`, `JetBrains\PhpStorm\ArrayShape`, `Pure`, `JetBrains\PhpStorm\Pure`, `Deprecated`, and `JetBrains\PhpStorm\Deprecated`.

```xml
<rule ref="VixPHPCS.Attributes.ForbiddenAttributes">
    <properties>
        <property name="forbiddenAttributes" type="array">
            <element value="ArrayShape" />
            <element value="JetBrains\PhpStorm\ArrayShape" />
            <element value="Pure" />
        </property>
    </properties>
</rule>
```

## Constants

### VixPHPCS.Constants.UppercaseMagicConstants

**Level:** Warning

Enforces uppercase spelling for PHP native magic constants. Mixed-case variants still work in PHP, but the canonical uppercase form is easier to scan and keeps built-in language constructs visually distinct from user-defined identifiers.

**Bad:**

```php
$path = __file__;
$directory = __Dir__;
$method = __method__;
```

**Good:**

```php
$path = __FILE__;
$directory = __DIR__;
$method = __METHOD__;
```

This sniff checks PHP native magic constants such as `__CLASS__`, `__DIR__`, `__FILE__`, `__FUNCTION__`, `__LINE__`, `__METHOD__`, `__NAMESPACE__`, `__PROPERTY__`, and `__TRAIT__` when the active PHP runtime exposes the corresponding tokenizer tokens.

## Control Structures

### VixPHPCS.ControlStructures.DisallowCountInLoop

**Level:** Warning

Prevents `count()` from being evaluated inside `for` loop conditions. Recomputing the size on every iteration is unnecessary and often makes the loop harder to read than a cached count or a `foreach` loop.

**Bad:**

```php
for ($i = 0; $i < count($items); $i++) {
    echo $items[$i];
}

for ($index = 0; $index < count($this->rows); $index++) {
    process($this->rows[$index]);
}
```

**Good:**

```php
$itemCount = count($items);

for ($i = 0; $i < $itemCount; $i++) {
    echo $items[$i];
}

foreach ($this->rows as $row) {
    process($row);
}
```

### VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach

**Level:** Warning

Warns when a `foreach` loop uses the same variable for both the key and value. Reusing one variable name for both positions hides the key immediately and makes the loop harder to read.

**Bad:**

```php
foreach ($items as $item => $item) {
    echo $item;
}

foreach ($items as $entry => &$entry) {
    $entry = normalize($entry);
}
```

**Good:**

```php
foreach ($items as $key => $item) {
    echo $item;
}

foreach ($items as $entryKey => &$entry) {
    $entry = normalize($entry);
}
```

### VixPHPCS.ControlStructures.DisallowThrowInTernary

**Level:** Error

Disallows `throw` expressions inside ternary and null coalescing expressions. Moving exceptions into a dedicated `if` block keeps the happy path and failure path obvious.

**Bad:**

```php
$value = $isValid ? $result : throw new RuntimeException('Invalid result');

$model = User::findOne($id) ?? throw new NotFoundHttpException('User not found');
```

**Good:**

```php
if (!$isValid) {
    throw new RuntimeException('Invalid result');
}

$value = $result;

$model = User::findOne($id);

if ($model === null) {
    throw new NotFoundHttpException('User not found');
}
```

### VixPHPCS.ControlStructures.UseInArray

**Level:** Warning

Detects repeated comparisons of the same value joined by `||` or `&&` and suggests `in_array()` or `!in_array()` instead. This keeps the intent compact and avoids long chains of identical checks.

**Bad:**

```php
if ($siteId === 1 || $siteId === 2 || $siteId === 3) {
    allowSite();
}

if ($status !== 'pending' && $status !== 'processing' && $status !== 'queued') {
    rejectStatus();
}
```

**Good:**

```php
if (in_array($siteId, [1, 2, 3], true)) {
    allowSite();
}

if (!in_array($status, ['pending', 'processing', 'queued'], true)) {
    rejectStatus();
}
```

**Parameters:**

- `minComparisons`: minimum number of repeated comparisons required before the sniff reports a condition. Default: `3`.

```xml
<rule ref="VixPHPCS.ControlStructures.UseInArray">
    <properties>
        <property name="minComparisons" value="4" />
    </properties>
</rule>
```

## Formatting

### VixPHPCS.Formatting.MethodChainingIndentation

**Level:** Warning

Enforces a four-space indentation step for multi-line method chains. Once a chain is broken across lines, every chained call should align cleanly under the anchor expression.

**Bad:**

```php
User::find()
->where(['id' => $id])
    ->select(['id'])
  ->limit(10)
    ->all();
```

**Good:**

```php
User::find()
    ->where(['id' => $id])
    ->select(['id'])
    ->limit(10)
    ->all();
```

### VixPHPCS.Formatting.MethodChainingPerLine

**Level:** Warning

Requires one chained call per line once the chain becomes multi-line. Mixing inline and multi-line calls in the same chain makes long expressions harder to scan.

**Bad:**

```php
User::find()
    ->where(['id' => $id])
    ->select(['id'])->limit(10)
    ->all();

User::find()->where(['id' => $id])
    ->select(['id'])
    ->all();
```

**Good:**

```php
User::find()
    ->where(['id' => $id])
    ->select(['id'])
    ->limit(10)
    ->all();

User::find()->where(['id' => $id])->all();
```

### VixPHPCS.Formatting.ConsistentStatementIndentation

**Level:** Warning

Keeps statements at the same nesting level aligned to the same indentation column. This is especially useful in view files, callbacks, and mixed PHP/HTML templates where indentation can drift.

**Bad:**

```php
Modal::begin([
    'id' => 'photo-modal',
]);
        echo $imageTag;
    Modal::end();
```

**Good:**

```php
Modal::begin([
    'id' => 'photo-modal',
]);
    echo $imageTag;
Modal::end();
```

**Parameters:**

- `indent`: indentation width in spaces used when the sniff calculates the expected column. Default: `4`.

```xml
<rule ref="VixPHPCS.Formatting.ConsistentStatementIndentation">
    <properties>
        <property name="indent" value="2" />
    </properties>
</rule>
```

### VixPHPCS.Formatting.DisallowMultipleThrowsPerLine

**Level:** Warning

Requires one exception type per `@throws` annotation. Splitting combined types into separate lines makes PHPDoc easier to read and simplifies tooling.

**Bad:**

```php
/**
 * @throws JsonException|RuntimeException
 * @throws InvalidArgumentException|LogicException
 */
function process(array $payload): void
{
}
```

**Good:**

```php
/**
 * @throws JsonException
 * @throws RuntimeException
 * @throws InvalidArgumentException
 * @throws LogicException
 */
function process(array $payload): void
{
}
```

## Functions

### VixPHPCS.Functions.DisallowCastFunctions

**Level:** Warning

Disallows `strval()`, `intval()`, `floatval()`, and `boolval()` in favor of direct casts. Cast syntax is shorter, more idiomatic in strict codebases, and easier to spot in expressions.

**Bad:**

```php
$string = strval($value);
$integer = intval($value);
$float = floatval($value);
$boolean = boolval($value);
$hex = intval($hexValue, 16);
```

**Good:**

```php
$string = (string) $value;
$integer = (int) $value;
$float = (float) $value;
$boolean = (bool) $value;
$hex = (int) hexdec($hexValue);
```

### VixPHPCS.Functions.DisallowHttpFileGetContents

**Level:** Warning

Disallows `file_get_contents()` for HTTP and HTTPS requests. Network calls should go through a real HTTP client so timeouts, retries, headers, and failures are explicit.

**Bad:**

```php
$response = file_get_contents('https://example.com/api');
$feed = file_get_contents('http://example.com/feed.xml');
```

**Good:**

```php
$response = $httpClient->request('GET', 'https://example.com/api');
$feed = $httpClient->request('GET', 'http://example.com/feed.xml');
```

### VixPHPCS.Functions.DisallowNullableBoolReturnType

**Level:** Error

Rejects nullable `bool` return declarations in both native signatures and `@return` annotations. If a function or method returns a boolean state, it should return `true` or `false`, not `null`.

**Bad:**

```php
function isEnabled(): ?bool
{
    return null;
}

/**
 * @return bool|null
 */
function isVisible()
{
    return null;
}
```

**Good:**

```php
function isEnabled(): bool
{
    return false;
}

/**
 * @return bool
 */
function isVisible(): bool
{
    return true;
}
```

### VixPHPCS.Functions.PreferModernStringFunctions

**Level:** Warning

Suggests `str_contains()`, `str_starts_with()`, and `str_ends_with()` instead of `strpos()` or similar index-based patterns. Dedicated string helpers make the intent obvious.

**Bad:**

```php
if (strpos($haystack, $needle) !== false) {
    matchFound();
}

if (strpos($haystack, $needle) === 0) {
    matchFound();
}

if (stripos($haystack, $needle) !== false) {
    matchFound();
}
```

**Good:**

```php
if (str_contains($haystack, $needle)) {
    matchFound();
}

if (str_starts_with($haystack, $needle)) {
    matchFound();
}

if (str_ends_with($haystack, $needle)) {
    matchFound();
}
```

### VixPHPCS.Functions.PreferJsonValidate

**Level:** Warning

Suggests `json_validate()` when JSON is only being checked for validity. This avoids unnecessary decoding work and expresses the validation-only intent directly.

**Bad:**

```php
$data = json_decode($json);

if (json_last_error() !== JSON_ERROR_NONE) {
    throw new RuntimeException('Invalid JSON');
}

try {
    json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    report($exception);
}
```

**Good:**

```php
if (!json_validate($json)) {
    throw new RuntimeException('Invalid JSON');
}

$data = json_decode($json, true);
```

## Objects

### VixPHPCS.Objects.DisallowReturnInConstructorDestructor

**Level:** Error

Rejects `return` statements inside `__construct()` and `__destruct()`. Object lifecycle methods should run to completion without explicit returns, and returning a value there is invalid PHP.

**Bad:**

```php
class Example
{
    public function __construct()
    {
        return;
    }

    public function __destruct()
    {
        return $this->cleanup();
    }
}
```

**Good:**

```php
class Example
{
    public function __construct()
    {
        $this->boot();
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}
```

### VixPHPCS.Objects.DisallowReturnInSetter

**Level:** Error

Rejects `return` statements inside setter-like methods whose names match `set...()`. Setters should update object state and finish normally instead of returning early or returning a value for chaining.

**Bad:**

```php
class Example
{
    public function setName(string $name): void
    {
        return;
    }

    public function setEnabled(bool $enabled): self
    {
        return $this;
    }
}
```

**Good:**

```php
class Example
{
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
```

### VixPHPCS.Objects.StaticInFinalClass

**Level:** Warning

Warns when a method inside a `final` class declares `static` as its return type. Because the class cannot be extended, `self` communicates the same type more directly.

**Bad:**

```php
final class UserFactory
{
    public static function make(): static
    {
        return new self();
    }
}
```

**Good:**

```php
final class UserFactory
{
    public static function make(): self
    {
        return new self();
    }
}
```

### VixPHPCS.Objects.RequireStringableInterface

**Level:** Warning

Requires classes that declare `__toString()` to also implement `Stringable`. This keeps the contract explicit for consumers and matches the intent of PHP's dedicated string-conversion interface.

**Bad:**

```php
class Example
{
    public function __toString(): string
    {
        return 'example';
    }
}
```

**Good:**

```php
class Example implements Stringable
{
    public function __toString(): string
    {
        return 'example';
    }
}
```

### VixPHPCS.Objects.DisallowVariableStaticProperty

**Level:** Warning

Forbids static property access through an object variable such as `$object::$property`. Static state should always be accessed through a class name, `self`, or `static`.

**Bad:**

```php
$toast = $model::$toastArray[$model->toast];
$value = ($service)::$cache['key'];
```

**Good:**

```php
$toast = User::$toastArray[$model->toast];
$value = self::$cache['key'];
```

### VixPHPCS.Objects.DisallowNullsafeThis

**Level:** Warning

Warns when `$this` is accessed through the nullsafe operator. `$this` is always available inside object context, so `?->` adds no useful null handling there.

**Bad:**

```php
$this?->foo();
$name = $this?->name;
```

**Good:**

```php
$this->foo();
$name = $this->name;
```

### VixPHPCS.Objects.RequireFinalTraitMethods

**Level:** Warning

Requires concrete trait methods that are not private to be declared `final`. This keeps trait behavior stable and makes intended extension points explicit through private or abstract methods instead of silent overrides.

**Bad:**

```php
trait PublishesEvents
{
    public function dispatchEvent(string $eventName): void
    {
    }

    protected static function normalizePayload(array $payload): array
    {
        return $payload;
    }
}
```

**Good:**

```php
trait PublishesEvents
{
    final public function dispatchEvent(string $eventName): void
    {
    }

    final protected static function normalizePayload(array $payload): array
    {
        return $payload;
    }

    private function buildChannelName(): string
    {
        return 'events';
    }

    abstract protected function logger(): LoggerInterface;
}
```

## Operators

### VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation

**Level:** Warning

Suggests an explicit `(bool)` cast instead of `!!` for boolean coercion. Cast syntax makes the intent clearer and avoids the visual noise of stacked negations.

**Bad:**

```php
$isActive = !!$user->active;
$hasItems = !!count($items);
```

**Good:**

```php
$isActive = (bool) $user->active;
$hasItems = (bool) count($items);
```

## PhpDoc

### VixPHPCS.PhpDoc.DeprecatedTag

**Level:** Warning

Warns when `@deprecated` is used on symbols that support the native `#[\Deprecated]` attribute in PHP 8.4+. Prefer the attribute for functions, methods, class constants, and enum cases.

**Bad:**

```php
/**
 * @deprecated Use newMethod() instead.
 */
function oldMethod(): void
{
}
```

**Good:**

```php
#[\Deprecated('Use newMethod() instead.')]
function oldMethod(): void
{
}
```

### VixPHPCS.PhpDoc.DisallowUnusedTemplate

**Level:** Warning

Disallows PHPDoc template declarations that are never referenced by any other type in the same scope. Unused generics add noise and can mislead readers into thinking a type is more generic than it really is.

**Bad:**

```php
/**
 * @template TModel of ActiveRecord
 */
final class Repository
{
}
```

**Good:**

```php
/**
 * @template TModel of ActiveRecord
 * @extends BaseRepository<TModel>
 */
final class Repository extends BaseRepository
{
    /**
     * @return TModel
     */
    public function find(): ActiveRecord
    {
    }
}
```

### VixPHPCS.PhpDoc.DisallowInvalidTypeUsage

**Level:** Error

Rejects objectively invalid PHPDoc type usage: `void` and `never` in value positions, scalar or literal
`@throws` types, scalar `@mixin` types, impossible scalar intersections, invalid nested value types, and duplicate
array-shape keys. The rule also checks common PHPStan/Psalm tag variants, `@param-out`, and generic type arguments in
`@extends`, `@implements`, and `@use`.

**Bad:**

```php
/**
 * @param void $value
 * @phpstan-param never $other
 * @extends Collection<void>
 * @throws string
 * @mixin int
 * @var array{0: string, 0: int} $data
 */
```

**Good:**

```php
/**
 * @param string $value
 * @throws RuntimeException
 * @mixin SomeClass
 * @var array{0: string, 1: int} $data
 */
```

### VixPHPCS.PhpDoc.DisallowSuspiciousLiteralTypes

**Level:** Warning

Warns about PHPDoc types that are technically possible but usually signal a broken annotation: sole `null`, `false`,
numeric, or string-literal value types, suspicious nested literals, template names that conflict with native PHPDoc
types, and template bounds such as `null`, `void`, or `never`. PHPStan/Psalm tag variants and generic inheritance tags
are checked as well.

**Bad:**

```php
/**
 * @param null $value
 * @psalm-var array{foo: null} $shape
 * @return 0
 * @var array<false> $flags
 * @template void
 * @phpstan-template T of void
 */
```

**Good:**

```php
/**
 * @param string|null $value
 * @return int
 * @var array<bool> $flags
 * @template T of object
 */
```

### VixPHPCS.PhpDoc.DisallowRedundantTypes

**Level:** Warning

Warns about redundant PHPDoc union members, including duplicate nested types and broad types that make narrower
alternatives unreachable or unnecessary.

**Bad:**

```php
/**
 * @var string|string $name
 * @var mixed|string $value
 * @var array<string|string> $items
 * @var bool|true|false $flag
 */
```

**Good:**

```php
/**
 * @var string $name
 * @var mixed $value
 * @var bool $flag
 */
```

### VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes

**Level:** Error

Rejects `void` when it is combined with other return types in a `@return` annotation or in callable PHPDoc return types. A return type is either `void` or it is one of the actual value types, but not both.

**Bad:**

```php
/**
 * @return array|null|void
 */
function foo() {}

/**
 * @return string|void
 */
function bar() {}

/**
 * @param callable(): void|int|string $callback
 */
function baz(callable $callback): void {}
```

**Good:**

```php
/**
 * @return void
 */
function foo(): void {}

/**
 * @return string|null
 */
function bar(): ?string {}

/**
 * @var callable(): void $callback
 */
$callback = static function (): void {};
```

## Yii2

### VixPHPCS.Yii2.DisallowResponseFormatAssignment

**Level:** Warning

Discourages direct assignment to `Yii::$app->response->format` for JSON and XML responses. In controllers, `asJson()` and `asXml()` are clearer and keep response formatting closer to the returned payload.

**Bad:**

```php
use yii\web\Response;

class SiteController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['status' => 'ok'];
    }
}
```

**Good:**

```php
class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->asJson(['status' => 'ok']);
    }
}
```

### VixPHPCS.Yii2.PreferActiveRecordShortcuts

**Level:** Warning

Suggests `findOne()` and `findAll()` instead of the exact `find()->where()->one()` and `find()->where()->all()` patterns. Yii2 already provides dedicated shortcuts for those simple queries.

**Bad:**

```php
$user = User::find()->where(['id' => $id])->one();
$activeUsers = User::find()->where(['status' => 1])->all();
```

**Good:**

```php
$user = User::findOne($id);
$activeUsers = User::findAll(['status' => 1]);
```

### VixPHPCS.Yii2.PreferExistsOverCount

**Level:** Warning

Suggests `exists()` instead of `count() > 0`, `count() == 0`, and similar existence checks. It also catches `->one()` in boolean conditions when only existence matters.

**Bad:**

```php
if (User::find()->where(['status' => 1])->count() > 0) {
    return true;
}

if ($query->count() == 0) {
    return false;
}

if (TimeTracker::find()->where(['datetime_end' => null])->one()) {
    return true;
}
```

**Good:**

```php
if (User::find()->where(['status' => 1])->exists()) {
    return true;
}

if (!$query->exists()) {
    return false;
}

if (TimeTracker::find()->where(['datetime_end' => null])->exists()) {
    return true;
}
```

### VixPHPCS.Yii2.PreferIdentityOverFindOne

**Level:** Warning

Detects cases where the current authenticated user is loaded from the database by ID. `Yii::$app->user->identity` reuses the already resolved identity object and avoids an extra query.

**Bad:**

```php
$user = User::findOne(Yii::$app->user->id);
$model = User::findOne(['id' => Yii::$app->user->getId()]);
$profile = User::find()->where(['id' => Yii::$app->user->id])->one();
```

**Good:**

```php
$user = Yii::$app->user->identity;
$model = Yii::$app->user->identity;
$profile = Yii::$app->user->identity;
```

### VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck

**Level:** Warning

Suggests `Yii::$app->user->isGuest` instead of comparing `Yii::$app->user->id` with `null` or wrapping it in `empty()`. The property is more explicit and reflects Yii2 authentication semantics directly.

**Bad:**

```php
if (empty(Yii::$app->user->id)) {
    return $this->redirect(['login']);
}

if (Yii::$app->user->id === null) {
    throw new ForbiddenHttpException();
}

$isLoggedIn = !empty(Yii::$app->user->id);
```

**Good:**

```php
if (Yii::$app->user->isGuest) {
    return $this->redirect(['login']);
}

if (Yii::$app->user->isGuest) {
    throw new ForbiddenHttpException();
}

$isLoggedIn = !Yii::$app->user->isGuest;
```
