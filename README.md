Phalcon MongoDB ODM
===============
## 写入一个
```php
$res = MG::Table('test')
    ->insert(['name' => 'a', 'sex' => 0, 'age' => 15]);
```

## 写入多个
```php
$res = MG::Table('test')
    ->insert([
        ['name' => 'b', 'sex' => 1, 'age' => 16],
        ['name' => 'c', 'sex' => 0, 'age' => 14],
        ['name' => 'd', 'sex' => 1, 'age' => 18],
        ['name' => 'e', 'sex' => 0, 'age' => 20],
        ['name' => 'f', 'sex' => 1, 'age' => 19],
        ['name' => 'g', 'sex' => 0, 'age' => 22],
        ['name' => 'h', 'sex' => 0, 'age' => 15],
    ]);
```

## 查询name等于a的一个记录
```php
$res = MG::Table('test')
    ->where('name', 'a')
    ->first();
```

## 查询sex等于1的所有记录
```php
$res = MG::Table('test')
    ->where('sex', 1)
    ->get();
```

## 查询name等于a或者sex等于0的所有记录
```php
$res = MG::Table('test')
    ->where('name', 'a')
    ->orWhere('sex', 0)
    ->get();
```

## 查询age在15~18之间的所有记录
```php
$res = MG::Table('test')
    ->whereBetween('age', [15, 18])
    ->get();
```

## 查询age不在15~18之间的所有记录
```php
$res = MG::Table('test')
    ->whereNotBetween('age', [15, 18])
    ->get();
```

## 查询age是15，18，16的所有记录
```php
$res = MG::Table('test')
    ->whereIn('age', [15, 18, 16])
    ->get();
```

## 查询age不是15，18，16的所有记录
```php
$res = MG::Table('test')
    ->whereNotIn('age', [15, 18, 16])
    ->get();
```

## 查询age是15，18，16的所有记录,按age升序排序(默认)
```php
$res = MG::Table('test')
    ->whereIn('age', [15, 18, 16])
    ->orderBy('age')
    ->get();
```

## 查询age是15，18，16的所有记录,按age降序排序
```php
$res = MG::Table('test')
    ->whereIn('age', [15, 18, 16])
    ->orderBy('age', 'desc')
    ->get();
```

## 查询age是15，18，16的所有记录,按age分组
```php
$res = MG::Table('test')
    ->whereIn('age', [15, 18, 16])
    ->groupBy('age')
    ->get();
```

## 查询age是15，18，16的所有记录,按age分组统计
```php
$res = MG::Table('test')
    ->whereIn('age', [15, 18, 16])
    ->groupByWithCount('age')
    ->get();
```

## 按age分组，分别计算age最大，最小，平均，总和。
```php
$res = MG::Table('test')
    ->groupBy('sex')
    ->max('age')
    ->min('age')
    ->avg('age')
    ->sum('age')
    ->get();
```

## 给name等于a的记录age加1
```php
$res = MG::Table('test')
    ->where('name', 'a')
    ->increment('age');
```

## 给name等于a的记录age加2
```php
$res = MG::Table('test')
    ->where('name', 'a')
    ->increment('age', 2);
```

## 给name等于a的记录age减2
```php
$res = MG::Table('test')
    ->where('name', 'a')
    ->decrement('age');
```

## 给name等于a的记录age减2
```php
$res = MG::Table('test')
    ->where('name', 'a')
    ->decrement('age', 2);
```