# VixPHPCS Coding Standard Rules

This document describes the custom coding standard rules implemented in the VixPHPCS PHPCS ruleset.

## Table of Contents

- [VixPHPCS Coding Standard Rules](#devstrict-coding-standard-rules)
  - [Table of Contents](#table-of-contents)
  - [Functions](#functions)
    - [VixPHPCS.Functions.DisallowCastFunctions](#devstrictfunctionsdisallowcastfunctions)
    - [VixPHPCS.Functions.DisallowHttpFileGetContents](#devstrictfunctionsdisallowhttpfilegetcontents)
    - [VixPHPCS.Functions.PreferModernStringFunctions](#devstrictfunctionsprefermodernstringfunctions)
    - [VixPHPCS.Functions.PreferJsonValidate](#devstrictfunctionspreferjsonvalidate)
  - [Control Structures](#control-structures)
    - [VixPHPCS.ControlStructures.DisallowCountInLoop](#devstrictcontrolstructuresdisallowcountinloop)
    - [VixPHPCS.ControlStructures.DisallowGotoStatement](#devstrictcontrolstructuresdisallowgotostatement)
    - [VixPHPCS.ControlStructures.DisallowThrowInTernary](#devstrictcontrolstructuresdisallowthrowinternary)
    - [VixPHPCS.ControlStructures.UseInArray](#devstrictcontrolstructuresuseinarray)
  - [Formatting](#formatting)
    - [VixPHPCS.Formatting.MethodChainingIndentation](#devstrictformattingmethodchainingindentation)
    - [VixPHPCS.Formatting.MethodChainingPerLine](#devstrictformattingmethodchainingperline)
    - [VixPHPCS.Formatting.ConsistentStatementIndentation](#devstrictformattingconsistentstatementindentation)
    - [VixPHPCS.Formatting.DisallowMultipleThrowsPerLine](#devstrictformattingdisallowmultiplethrowsperline)
  - [PhpDoc](#phpdoc)
    - [VixPHPCS.PhpDoc.DeprecatedTag](#devstrictphpdocdeprecatedtag)
    - [VixPHPCS.PhpDoc.DisallowUnusedTemplate](#devstrictphpdocdisallowunusedtemplate)
    - [VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes](#devstrictphpdocdisallowvoidmixedwithothertypes)
  - [Objects](#objects)
    - [VixPHPCS.Objects.DisallowVariableStaticProperty](#devstrictobjectsdisallowvariablestaticproperty)
  - [Yii2](#yii2)
    - [VixPHPCS.Yii2.DisallowResponseFormatAssignment](#devstrictyii2disallowresponseformatassignment)
    - [VixPHPCS.Yii2.PreferActiveRecordShortcuts](#devstrictyii2preferactiverecordshortcuts)
    - [VixPHPCS.Yii2.PreferExistsOverCount](#devstrictyii2preferexistsovercount)
    - [VixPHPCS.Yii2.PreferIdentityOverFindOne](#devstrictyii2preferidentityoverfindone)
    - [VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck](#devstrictyii2preferisguestoveruseridcheck)
  - [Attributes](#attributes)
    - [VixPHPCS.Attributes.ForbiddenAttributes](#devstrictattributesforbiddenattributes)

---

## Functions

### VixPHPCS.Functions.DisallowCastFunctions

**Type:** Warning

**Description:** Disallows the use of `strval()`, `intval()`, `floatval()`, and `boolval()` functions in favor of type
casts. Type casts are shorter, more consistent with strict typing style, and clearer in intent.

**Bad:**

```php
$string = strval($var);
$integer = intval($var);
$float = floatval($var);
$boolean = boolval($var);
$hex = intval($value, 16);
```

**Good:**

```php
$string = (string) $var;
$integer = (int) $var;
$float = (float) $var;
$boolean = (bool) $var;
$hex = (int) hexdec($value);
```

---

### VixPHPCS.Functions.DisallowHttpFileGetContents

**Type:** Warning

**Description:** Disallows `file_get_contents()` for HTTP requests. Use a proper HTTP client instead so requests have
explicit timeout, error handling, headers, retries, and testable behavior.

**Bad:**

```php
$response = file_get_contents('https://example.com/api');
$response = file_get_contents('http://example.com/feed.xml');
```

**Good:**

```php
$response = $httpClient->request('GET', 'https://example.com/api');
```

---

### VixPHPCS.Functions.PreferModernStringFunctions

**Type:** Warning

**Description:** Suggests using modern string functions (`str_contains()`, `str_starts_with()`, `str_ends_with()`)
instead of `strpos()` with comparisons. PHP 8.0 introduced dedicated string search functions that are more readable
and semantic.

**Bad:**

```php
// Instead of checking if string contains substring
if (strpos($haystack, $needle) !== false) {
    // ...
}

// Instead of checking if string starts with substring
if (strpos($haystack, $needle) === 0) {
    // ...
}

// Case-insensitive variants
if (stripos($haystack, $needle) !== false) {
    // ...
}
```

**Good:**

```php
// More readable and explicit
if (str_contains($haystack, $needle)) {
    // ...
}

if (str_starts_with($haystack, $needle)) {
    // ...
}

if (str_ends_with($haystack, $needle)) {
    // ...
}
```

---

### VixPHPCS.Functions.PreferJsonValidate

**Type:** Warning

**Description:** Suggests using `json_validate()` instead of `json_decode()` when only validation is needed. PHP 8.3 introduced `json_validate()` which is more efficient for checking JSON validity without parsing the entire structure. This rule detects patterns where `json_decode()` is used with `json_last_error()` checks or `JSON_THROW_ON_ERROR` flag.

**Bad:**

```php
// Validation with json_last_error()
$data = json_decode($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('Invalid JSON');
}

// Validation with JSON_THROW_ON_ERROR
try {
    json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    // Handle invalid JSON
}
```

**Good:**

```php
// Direct validation without parsing overhead
if (!json_validate($json)) {
    throw new Exception('Invalid JSON');
}

// Or simply
if (json_validate($json)) {
    $data = json_decode($json, true);
}
```

---

## Control Structures

### VixPHPCS.ControlStructures.DisallowCountInLoop

**Type:** Warning

**Description:** Disallows the use of `count()` function in `for` loop conditions. Calling `count()` in the loop
condition causes it to be executed on every iteration, which is inefficient. Store the count in a variable before the
loop or use `foreach` instead.

**Bad:**

```php
for ($i = 0; $i < count($array); $i++) {
    echo $array[$i];
}

for ($i = 0; $i < count($this->items); $i++) {
    // do something
}
```

**Good:**

```php
$count = count($array);
for ($i = 0; $i < $count; $i++) {
    echo $array[$i];
}

// Or better - use foreach:
foreach ($array as $item) {
    echo $item;
}
```

---

### VixPHPCS.ControlStructures.DisallowGotoStatement

**Type:** Error

**Description:** Disallows the use of `goto` statements. The `goto` statement is considered an anti-pattern in modern
PHP as it makes code harder to read, understand, and maintain. Use proper control structures instead.

**Bad:**

```php
if ($error) {
    goto cleanup;
}
echo "Processing...";
cleanup:
echo "Cleanup";

// Backward jump
$counter = 0;
start:
$counter++;
if ($counter < 5) {
    goto start;
}
```

**Good:**

```php
// Use early returns
if ($error) {
    echo "Cleanup";
    return;
}
echo "Processing...";

// Use proper loops
for ($counter = 0; $counter < 5; $counter++) {
    // do something
}

// Use structured error handling
try {
    echo "Processing...";
} finally {
    echo "Cleanup";
}
```

---

### VixPHPCS.ControlStructures.DisallowThrowInTernary

**Type:** Error

**Description:** Disallows throwing exceptions within ternary and null coalescing operators. Throwing exceptions in
conditional expressions reduces code readability and makes error handling less explicit.

**Bad:**

```php
$value = $condition
    ? $result
    : throw new Exception('Invalid condition');

$data = $isValid ? getData() : throw new RuntimeException('Invalid data');

$model = Model::findOne($id) ?? throw new NotFoundHttpException('Page not found');
```

**Good:**

```php
if (!$condition) {
    throw new Exception('Invalid condition');
}
$value = $result;

if (!$isValid) {
    throw new RuntimeException('Invalid data');
}
$data = getData();

$model = Model::findOne($id);
if ($model === null) {
    throw new NotFoundHttpException('Page not found');
}
```

---

### VixPHPCS.ControlStructures.UseInArray

**Type:** Warning

**Description:** Detects multiple OR/AND comparisons of the same variable and suggests using `in_array()` or
`!in_array()` instead. This makes the code more concise, readable, and easier to maintain. The sniff triggers when there
are 3 or more comparisons of the same variable.

**Bad:**

```php
if ($site_id === 1 || $site_id === 2 || $site_id === 3) {
    // do something
}

if ($status !== 'pending' && $status !== 'processing' && $status !== 'cancelled') {
    // do something
}
```

**Good:**

```php
if (in_array($site_id, [1, 2, 3], true)) {
    // do something
}

if (!in_array($status, ['pending', 'processing', 'cancelled'], true)) {
    // do something
}
```

---

## Formatting

### VixPHPCS.Formatting.MethodChainingIndentation

**Type:** Error

**Description:** Enforces a consistent four-space indentation offset for multi-line method chains. The first chained
call must be indented relative to the anchor expression and every subsequent operator must align with the preceding
line. This mirrors the array indentation rule but for chained method calls.

**Bad:**

```php
User::find()
->where(['id' => $id])
    ->select(['id'])
    ->all();

User::find()
    ->where(['id' => $id])
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

User::find()->where(['id' => $id])->all();
```

---

### VixPHPCS.Formatting.MethodChainingPerLine

**Type:** Error

**Description:** Once a chained call is split across multiple lines, every further call in that chain must also be on
its own line. Inline calls preceding the multi-line block are flagged, and only one operator per physical line is
allowed, which keeps the chain legible.

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

---

### VixPHPCS.Formatting.ConsistentStatementIndentation

**Type:** Warning

**Description:** Keeps statements that share the same nesting level aligned to the same indentation column. This makes
deeply nested callbacks and closures easier to scan and prevents stray blocks that drift to the right.

**Bad:**

```php
Modal::begin([
    'id' => 'photo-modal',
]);
        echo $imgTag; // four spaces too many
    Modal::end();
```

**Good:**

```php
Modal::begin([
    'id' => 'photo-modal',
]);
    echo $imgTag;
Modal::end();
```

---

### VixPHPCS.Formatting.DisallowMultipleThrowsPerLine

**Type:** Warning

**Description:** Disallows multiple exception types in a single `@throws` annotation separated by pipe characters.
Each exception type should have its own separate `@throws` annotation for better documentation clarity and
readability.

**Bad:**

```php
/**
 * @throws JsonException|Exception
 * @throws InvalidArgumentException|RuntimeException|LogicException
 */
function processData($data): void
{
    // implementation
}
```

**Good:**

```php
/**
 * @throws JsonException
 * @throws Exception
 * @throws InvalidArgumentException
 * @throws RuntimeException
 * @throws LogicException
 */
function processData($data): void
{
    // implementation
}
```

---

## PhpDoc

### VixPHPCS.PhpDoc.DeprecatedTag

**Type:** Warning

**Description:** Warns when a `@deprecated` docblock tag is used on a symbol that supports the native
`#[\Deprecated]` attribute (PHP 8.4+): functions, methods, class constants, and enum cases. Class, interface,
trait, and enum declarations are excluded because the attribute cannot target them.

**Bad:**

```php
/**
 * @deprecated Use newFunction() instead.
 */
function oldFunction(): void
{
}
```

**Good:**

```php
#[\Deprecated('use newFunction() instead')]
function oldFunction(): void
{
}
```

---

### VixPHPCS.PhpDoc.DisallowUnusedTemplate

**Type:** Warning

**Description:** Disallows PHPDoc `@template` declarations that are never used by another PHPDoc type in the same
class, trait, interface, enum, or function scope.

**Bad:**

```php
/**
 * @template TModel of ActiveRecord
 */
class Repository
{
}
```

**Good:**

```php
/**
 * @template TModel of ActiveRecord
 * @extends BaseRepository<TModel>
 */
class Repository extends BaseRepository
{
    /**
     * @return TModel
     */
    public function find()
    {
    }
}
```

---

### VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes

**Type:** Error

**Description:** `void` means a function does not return a value. It is semantically invalid to combine `void` with
any other type in a `@return` tag. Use `void` alone, or omit it and list only the actual return types.

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

---

## Objects

### VixPHPCS.Objects.DisallowVariableStaticProperty

**Type:** Error

**Description:** Static properties must be accessed via class names (or `self`/`static`), never via an object variable.
Using an instance to reach static storage can hide bugs and violates common PHP conventions.

**Bad:**

```php
$toast = $model::$toast_array[$model->toast];
$value = ($service)::$cache['key'];
```

**Good:**

```php
$toast = User::$toast_array[$model->toast];
$value = self::$cache['key'];
```

---

## Yii2

### VixPHPCS.Yii2.DisallowResponseFormatAssignment

**Type:** Warning

**Description:** Disallows direct assignment to `Yii::$app->response->format` for JSON and XML formats. In Yii2
controllers, use `$this->asJson()` or `$this->asXml()` instead of manually setting the response format. This makes code
more controller-centric and follows Yii2 best practices. Only checks JSON and XML formats since other formats may not
have dedicated controller methods.

**Bad:**

```php
use yii\web\Response;

class SiteController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok', 'data' => $data];
    }

    public function actionApi()
    {
        Yii::$app->response->format = 'json';
        return ['result' => $result];
    }

    public function actionXml()
    {
        Yii::$app->response->format = Response::FORMAT_XML;
        return ['data' => $data];
    }
}
```

**Good:**

```php
class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->asJson(['status' => 'ok', 'data' => $data]);
    }

    public function actionApi()
    {
        return $this->asJson(['result' => $result]);
    }

    public function actionXml()
    {
        return $this->asXml(['data' => $data]);
    }

    // Or if you need more control:
    public function actionCustom()
    {
        $response = $this->asJson(['data' => $data]);
        $response->headers->set('X-Custom-Header', 'value');
        return $response;
    }

    // Other formats are not checked (no warning):
    public function actionRaw()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        return 'plain text';
    }
}
```

---

### VixPHPCS.Yii2.PreferActiveRecordShortcuts

**Type:** Warning

**Description:** Suggests using ActiveRecord shortcuts like `findOne()` and `findAll()` instead of
`find()->where()->one()/all()`. Yii2 provides convenient shortcut methods that are more concise and readable for simple
queries.

**Bad:**

```php
class UserController extends Controller
{
    public function actionView($id)
    {
        $user = User::find()->where(['id' => $id])->one();
        return $this->render('view', ['user' => $user]);
    }

    public function actionList()
    {
        $users = User::find()->where(['status' => 1])->all();
        return $this->render('list', ['users' => $users]);
    }
}
```

**Good:**

```php
class UserController extends Controller
{
    public function actionView($id)
    {
        $user = User::findOne($id);
        // or for multiple conditions:
        $user = User::findOne(['id' => $id, 'status' => 1]);
        return $this->render('view', ['user' => $user]);
    }

    public function actionList()
    {
        $users = User::findAll(['status' => 1]);
        return $this->render('list', ['users' => $users]);
    }

    // Complex queries are OK - no warning triggered:
    public function actionActive()
    {
        $activeUsers = User::find()
            ->where(['status' => 'active'])
            ->orderBy('created_at DESC')
            ->all();
        return $this->asJson($activeUsers);
    }
}
```

> [!NOTE] This rule **only** triggers for the exact pattern `find()->where()->one()` or `find()->where()->all()` with
> nothing in between. If you have additional method calls like `andWhere()`, `orWhere()`, `orderBy()`, `limit()`, etc.,
> the warning will not be triggered because these complex queries cannot be simplified to `findOne()`/`findAll()`.

---

### VixPHPCS.Yii2.PreferExistsOverCount

**Type:** Warning

**Description:** Suggests using `exists()` instead of `count() > 0` and `->one()` for ActiveQuery existence checks. The
`exists()` method is more efficient than `count()` when you only need to check if any records exist, as it stops after
finding the first match. Similarly, using `->one()` in conditional expressions loads the entire record when you only
need to know if it exists.

**Bad:**

```php
class UserController extends Controller
{
    public function actionCheck()
    {
        if (User::find()->where(['status' => 1])->count() > 0) {
            return 'Has active users';
        }

        if ($query->count() >= 1) {
            // do something
        }

        if ($query->count() == 0) {
            return 'No records';
        }

        if (TimeTracker::find()->where(['datetime_end' => null])->one()) {
            return 'Has active tracker';
        }

        $hasRecords = Post::find()->where(['published' => true])->count() !== 0;
    }
}
```

**Good:**

```php
class UserController extends Controller
{
    public function actionCheck()
    {
        if (User::find()->where(['status' => 1])->exists()) {
            return 'Has active users';
        }

        if ($query->exists()) {
            // do something
        }

        if (!$query->exists()) {
            return 'No records';
        }

        if (TimeTracker::find()->where(['datetime_end' => null])->exists()) {
            return 'Has active tracker';
        }

        $hasRecords = Post::find()->where(['published' => true])->exists();
    }
}
```

---

### VixPHPCS.Yii2.PreferIdentityOverFindOne

**Type:** Warning

**Description:** Detects patterns where the current user is fetched from the database using their ID. Use
`Yii::$app->user->identity` instead, which returns the already loaded user model without additional database queries.

**Bad:**

```php
class ProfileController extends Controller
{
    public function actionView()
    {
        $user = User::findOne(Yii::$app->user->id);
        return $this->render('view', ['user' => $user]);
    }

    public function actionUpdate()
    {
        $model = User::findOne(['id' => Yii::$app->user->id]);
        // ...
    }

    public function actionSettings()
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        // ...
    }

    public function actionEdit()
    {
        $user = User::findOne(Yii::$app->user->getId());
        // ...
    }
}
```

**Good:**

```php
class ProfileController extends Controller
{
    public function actionView()
    {
        $user = Yii::$app->user->identity;
        return $this->render('view', ['user' => $user]);
    }

    public function actionUpdate()
    {
        $model = Yii::$app->user->identity;
        // ...
    }

    public function actionSettings()
    {
        $user = Yii::$app->user->identity;
        // ...
    }
}
```

---

### VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck

**Type:** Warning

**Description:** Suggests using `Yii::$app->user->isGuest` instead of checking `Yii::$app->user->id` directly against
`null` or with `empty()`. The `isGuest` property is more semantic and clearly expresses the intent of checking whether a
user is authenticated. It's also more reliable as it uses Yii2's internal authentication state rather than relying on
the ID being null.

**Bad:**

```php
class SiteController extends Controller
{
    public function actionProfile()
    {
        if (empty(Yii::$app->user->id)) {
            return $this->redirect(['login']);
        }

        if (Yii::$app->user->id === null) {
            throw new ForbiddenHttpException();
        }

        if (Yii::$app->user->id == null) {
            return $this->asJson(['error' => 'Not authenticated']);
        }

        $isLoggedIn = !empty(Yii::$app->user->id);
        $isAuthenticated = Yii::$app->user->id !== null;
    }
}
```

**Good:**

```php
class SiteController extends Controller
{
    public function actionProfile()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }

        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException();
        }

        if (Yii::$app->user->isGuest) {
            return $this->asJson(['error' => 'Not authenticated']);
        }

        $isLoggedIn = !Yii::$app->user->isGuest;
        $isAuthenticated = !Yii::$app->user->isGuest;
    }
}
```

---

## Attributes

### VixPHPCS.Attributes.ForbiddenAttributes

**Type:** Warning

**Description:** Suggests avoiding the use of certain attributes that are considered harmful or unnecessary. This
includes attributes that can lead to confusion, reduce code clarity, or introduce potential bugs.

**Bad:**

```php
class User
{
    #[Deprecated]
    public $name;

    #[Sensitive]
    public $email;

    #[Obsolete]
    public $age;
}
```

**Good:**

```php
class User
{
    public $name;

    public $email;

    public $age;
}
```
