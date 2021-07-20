# Lara Query Kit

Lara Query Kit utilizes the Eloquent model. PHP Trait and Laravel local scope are used to implement this utilitiy

### PHP Traits

PHP only supports single inheritance: a child class can inherit only from one single parent.

So, what if a class needs to inherit multiple behaviors? OOP traits solve this problem.

Traits are used to declare methods that can be used in multiple classes. Traits can have methods and abstract methods that can be used in multiple classes, and the methods can have any access modifier (public, private, or protected).

### Laravel local scope

Local scopes allow you to define common sets of constraints that you may easily re-use throughout your application. To define a scope, simply prefix an Eloquent model method with `scope`.

1-Install `cuongnd88/lara-query-kit` using Composer.

```php
	composer require cuongnd88/lara-query-kit
```

2-Push `lara-query-kit` into your application.

```php
	php artisan vendor:publish --provider="Cuongnd88\LaraQueryKit\LaraQueryKitServiceProvider"
```

3-`App\Traits\QueryKit.php` is already to pump your performance. Please add `QueryKit` into the model

```php
. . . .
use App\Traits\QueryKit;

class User extends Authenticatable
{
    use Notifiable;
    use HasOtpAuth;
    use HasGuardian;
    use QueryKit;

. . . .
}
```


## Available methods

Let discuss each method available on the Query Kit.

- [insertDuplicate()](#insertDuplicate)
- [getTableColumns()](#getTableColumns)
- [exclude()](#exclude)
- [filter()](#filter)
- [searchFulltext()](#searchFulltext)

### insertDuplicate

_`insertDuplicate(array $data, array $insertKeys, array $updateKeys)`: Insert new rows or update existed rows._

```php
    public function upsert()
    {
        $data = [
            ['name' => "Dean Ngo", 'email' => 'dinhcuongngo@gmail.com', 'mobile' => '84905005533', 'password' => Hash::make('123456')],
            ['name' => "Robert Neil", 'email' => '1111@gmail.com', 'mobile' => '84905001122', 'password' => Hash::make('123456')],
        ];
        User::insertDuplicate($data, ['name', 'email', 'mobile', 'password'], ['name', 'email', 'mobile']);
    }
```

### getTableColumns

_`getTableColumns()`: Get the array of columns._

```php
    public function listTableColumns()
    {
        $columns = User::getTableColumns();
        dump($columns);
    }
```

### exclude

_`exclude(array $columns)`: Retrieve a subset of the output data._

You should define which model attributes you want to exclude. You may do this using the `$excludable` property on the model.

```php
. . . .
use App\Traits\QueryKit;

class User extends Authenticatable
{
    use Notifiable;
    use HasOtpAuth;
    use HasGuardian;
    use QueryKit;

    protected $excludable = ['deleted_at', 'created_at', 'updated_at'];

. . . .
}
```

```php
    public function listUsers()
    {
        $data = User::exclude()->get()->toArray();
        dump($data);
    }
```

Or pass a array of excludable columns as argument

```php
    public function listUsers()
    {
        $users = User::exclude(['deleted_at', 'created_at', 'updated_at'])
                        ->get()->toArray();
        dump($users);
    }
```

### filter

_`filter(array $params)`: Get the result with filter conditions._


You may use the `fitler` method on a query builder instance to add `where` clauses to the query.
The `$filterable` property should contain an array of conditions that you want to execute searching. The key of filterable array corresponds to table columns, whereas the value is condition to call `where` clauses. The most basic condition requires two arguments:

- The first argument is simple where clause such as: where, orWhere, whereBetween, whereNotBetween, whereIn, whereNotIn, whereNull, whereNotNull, orWhereNull , orWhereNotNull, whereDate, whereMonth, whereDay, whereYear, whereTime

- The second argument is an operator, which can be any of the database's supported operators.

Exceptionally, the third argument is required with the `operator` is `LIKE`, it is for a specified pattern

For convenience, if you verify only column in `fiterable` property, the default clause is `where` with `=` operator

```php
. . . .
use App\Traits\QueryKit;

class User extends Authenticatable
{
    use Notifiable;
    use HasOtpAuth;
    use HasGuardian;
    use QueryKit;

    protected $filterable = [
        'id' => ['whereBetween'],
        'email',
        'name' => ['orWhere', 'LIKE', '%{name}%'],
    ];

. . . .
}
```

```php
    public function filter()
    {
        $where = [
            'id' => [1,5],
            'email' => 'dinhcuongngo@gmail.com',
            'name' => 'ngo',
        ];

        $data = User::->filter($where)->get()->toArray();
    }
```

Dynamically, you can call `filterableCondition()` and assign the filterable conditions

```php
    public function filter()
    {
        $filterable = [
            'email',
            'name' => ['orWhere', 'LIKE', '%{name}%'],
            'deleted_at' => ['whereNull'],
        ];

        $where = [
            'email' => 'dinhcuongngo@gmail.com',
            'name' => 'ngo',
            'deleted_at' => '',
        ];

        $data = User::filterableCondition($filterable)
                        ->filter($where)
                        ->get()
                        ->toArray();
    }
```

### searchFulltext

_`searchFulltext($value, $mode = NATURAL_LANGUAGE)`: Run full-text queries against character-based data in MySQL tables._

There are four modes of full-text searches:

- `NATURAL_LANGUAGE` (is default): IN NATURAL LANGUAGE MODE
- `NATURAL_LANGUAGE_QUERY`: IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION
- `BOOLEAN_MODE`: IN BOOLEAN MODE
- `QUERY_EXPANSION`: WITH QUERY EXPANSION

The `$searchable` property should contain an array of conditions that you search full-

```php
. . . .
use App\Traits\QueryKit;

class User extends Authenticatable
{
    use Notifiable;
    use HasOtpAuth;
    use HasGuardian;
    use QueryKit;


    protected $excludable = ['deleted_at', 'created_at', 'updated_at'];

    protected $searchable = [
        'name', 'address'
    ];

. . . .
}
```

```php
    public function search()
    {
        $data = User::searchFulltext('ngo')->exclude()->get()->toArray();
        dump($data);
    }
```

You flexibly add matched columns by `searchableCols()`

```php
    public function search()
    {
        $data = User::searchableCols(['name', 'address'])
                        ->searchFulltext('ngo')
                        ->exclude()
                        ->get()
                        ->toArray();
        dump($data);
    }
```

You must create a `full-text index` on the table before you run full-text queries on a table. The full-text index can include one or more character-based columns in the table.

```php
ALTER TABLE `users` ADD FULLTEXT(`name`, `address`);
```
