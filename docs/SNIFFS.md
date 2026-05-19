# VixPHPCS Sniff Catalog

This document describes every custom sniff shipped with VixPHPCS.

The default `VixPHPCS` ruleset covers the main package rules. Some sniffs can also be enabled individually in a custom PHPCS standard when you want a narrower or more opinionated setup.

## Table of Contents

- [VixPHPCS Sniff Catalog](#vixphpcs-sniff-catalog)
  - [Table of Contents](#table-of-contents)
  - [Attributes](#attributes)
    - [VixPHPCS.Attributes.ForbiddenAttributes](#vixphpcsattributesforbiddenattributes)
  - [Constants](#constants)
    - [VixPHPCS.Constants.UppercaseMagicConstants](#vixphpcsconstantsuppercasemagicconstants)
  - [Control Structures](#control-structures)
    - [VixPHPCS.ControlStructures.DisallowCountInLoop](#vixphpcscontrolstructuresdisallowcountinloop)
    - [VixPHPCS.ControlStructures.DisallowGotoStatement](#vixphpcscontrolstructuresdisallowgotostatement)
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
    - [VixPHPCS.Functions.PreferModernStringFunctions](#vixphpcsfunctionsprefermodernstringfunctions)
    - [VixPHPCS.Functions.PreferJsonValidate](#vixphpcsfunctionspreferjsonvalidate)
  - [Objects](#objects)
    - [VixPHPCS.Objects.DisallowVariableStaticProperty](#vixphpcsobjectsdisallowvariablestaticproperty)
  - [PhpDoc](#phpdoc)
    - [VixPHPCS.PhpDoc.DeprecatedTag](#vixphpcsphpdocdeprecatedtag)
    - [VixPHPCS.PhpDoc.DisallowUnusedTemplate](#vixphpcsphpdocdisallowunusedtemplate)
    - [VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes](#vixphpcsphpdocdisallowvoidmixedwithothertypes)
  - [Yii2](#yii2)
    - [VixPHPCS.Yii2.DisallowResponseFormatAssignment](#vixphpcsyii2disallowresponseformatassignment)
    - [VixPHPCS.Yii2.PreferActiveRecordShortcuts](#vixphpcsyii2preferactiverecordshortcuts)
    - [VixPHPCS.Yii2.PreferExistsOverCount](#vixphpcsyii2preferexistsovercount)
    - [VixPHPCS.Yii2.PreferIdentityOverFindOne](#vixphpcsyii2preferidentityoverfindone)
    - [VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck](#vixphpcsyii2preferisguestoveruseridcheck)

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

### VixPHPCS.ControlStructures.DisallowGotoStatement

**Level:** Error

Rejects `goto` statements. Structured control flow with loops, conditionals, early returns, and `try/finally` blocks is easier to follow and safer to maintain.

**Bad:**

```php
if ($failed) {
    goto cleanup;
}

runTask();

cleanup:
closeConnection();
```

**Good:**

```php
if ($failed) {
    closeConnection();
    return;
}

runTask();
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

### VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes

**Level:** Error

Rejects `void` when it is combined with other return types in a `@return` annotation. A return type is either `void` or it is one of the actual value types, but not both.

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
