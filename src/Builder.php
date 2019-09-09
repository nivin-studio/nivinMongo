<?php

namespace nivinMongo;

use BadMethodCallException;
use InvalidArgumentException;

/**
 *查询构建器
 */
class Builder
{
    /**
     * Model对象
     *
     * @var \nivinmongo\Model
     */
    public $model;

    /**
     * 数据库连接对象
     *
     * @var
     */
    public $connection;

    /**
     * 查询条件
     *
     * @var array
     */
    public $match;

    /**
     * 查询分组
     *
     * @var array
     */
    public $group;

    /**
     * 查询排序
     *
     * @var array
     */
    public $sort;

    /**
     * 返回前多少条记录
     *
     * @var int
     */
    public $limit;

    /**
     * 需要跳过多少条记录
     *
     * @var int
     */
    public $skip;

    /**
     * 条件运算符映射数组
     *
     * @var array
     */
    public static $operators = [
        '='  => '$eq',
        '!=' => '$ne',
        '>'  => '$gt',
        '<'  => '$lt',
        '>=' => '$gte',
        '<=' => '$lte',
        '%'  => '$regex',
    ];

    /**
     * 构造函数
     *
     * @param Collection $connection 连接对象
     */
    public function __construct($model)
    {
        $this->model      = $model;
        $this->connection = $this->model->getConnection();
    }

    /**
     * 条件
     *
     * @param  string $column   条件字段
     * @param  string $operator 操作符
     * @param  mixed  $value    值
     * @param  string $boolean  原子操作符
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = '$and')
    {
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        $expr = [$column => [self::$operators[$operator] => $value]];

        if (empty($this->match)) {
            $this->match = $expr;
        } else {
            if (!isset($this->match['$and']) && !isset($this->match['$or'])) {
                $this->match = [$boolean => [$this->match, $expr]];
            } else {
                $this->match[$boolean][] = $expr;
            }
        }

        return $this;
    }

    /**
     * or条件
     *
     * @param  string $column   条件字段
     * @param  string $operator 操作符
     * @param  mixed  $value    值
     *
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, '$or');
    }

    /**
     * between条件
     *
     * @param  string  $column  条件字段
     * @param  array   $values  值
     *
     * @return $this
     */
    public function whereBetween($column, array $values)
    {
        return $this->baseInWhere([$column => ['$gte' => $values[0], '$lte' => $values[1]]], '$and');
    }

    /**
     * not_between条件
     *
     * @param  string  $column  条件字段
     * @param  array   $values
     *
     * @return $this
     */
    public function whereNotBetween($column, array $values)
    {
        return $this->baseInWhere([$column => ['$not' => ['$gte' => $values[0], '$lte' => $values[1]]]], '$and');
    }

    /**
     * in条件
     *
     * @param  string $column 条件字段
     * @param  array  $values 值
     *
     * @return $this
     */
    public function whereIn($column, array $values)
    {
        return $this->baseInWhere([$column => ['$in' => $values]], '$and');
    }

    /**
     * not_in条件
     *
     * @param  string $column 条件字段
     * @param  array  $values 值
     *
     * @return $this
     */
    public function whereNotIn($column, array $values)
    {
        return $this->baseInWhere([$column => ['$nin' => $values]], '$and');
    }

    /**
     * 基础区间Where
     *
     * @param  array $expr 表达式
     *
     * @return $this
     */
    public function baseInWhere($expr, $boolean = '$and')
    {
        if (empty($this->match)) {
            $this->match = $expr;
        } else {
            if (!isset($this->match['$and']) && !isset($this->match['$or'])) {
                $this->match = [$boolean => [$this->match, $expr]];
            } else {
                $this->match[$boolean][] = $expr;
            }
        }

        return $this;
    }

    /**
     * 排序
     *
     * @param  string $column    排序字段
     * @param  string $direction 排序规则
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $this->sort = [$column => ($direction == 'asc' ? 1 : -1)];

        return $this;
    }

    /**
     * 分组
     *
     * @param  string $column 分组字段
     *
     * @return $this
     */
    public function groupBy($column)
    {
        $this->group = ['_id' => '$' . $column];

        return $this;
    }

    /**
     * 分组计总
     *
     * @param  string $column 分组字段
     *
     * @return $this
     */
    public function groupByWithCount($column)
    {
        $this->group = ['_id' => '$' . $column, 'count' => ['$sum' => 1]];

        return $this;
    }

    /**
     * 限制
     *
     * @param  int $value 初始位置
     *
     * @return $this
     */
    public function limit($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Non-numeric value passed to limit method.');
        }

        if ($value <= 0) {
            throw new InvalidArgumentException('Not lte 0 value passed to limit method.');
        }

        $this->limit = $value;

        return $this;
    }

    /**
     * 限制
     *
     * @param  int $value 初始位置
     *
     * @return $this
     */
    public function skip($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Non-numeric value passed to skip method.');
        }

        if ($value <= 0) {
            throw new InvalidArgumentException('Not lte 0 value passed to skip method.');
        }

        $this->skip = $value;

        return $this;
    }

    /**
     * 求最大值
     *
     * @param  string $column 计算字段
     *
     * @return
     */
    public function max($column)
    {
        $index = 'max_' . $column;

        if (!empty($this->group)) {
            $this->group[$index] = ['$max' => '$' . $column];
        } else {
            $this->group = ['_id' => null, $index => ['$max' => '$' . $column]];
        }

        return $this;
    }

    /**
     * 求最小值
     *
     * @param  string $column 计算字段
     *
     * @return
     */
    public function min($column)
    {
        $index = 'min_' . $column;

        if (!empty($this->group)) {
            $this->group[$index] = ['$min' => '$' . $column];
        } else {
            $this->group = ['_id' => null, $index => ['$min' => '$' . $column]];
        }

        return $this;
    }

    /**
     * 求平均值
     *
     * @param  string $column 计算字段
     *
     * @return
     */
    public function avg($column)
    {
        $index = 'avg_' . $column;

        if (!empty($this->group)) {
            $this->group[$index] = ['$avg' => '$' . $column];
        } else {
            $this->group = ['_id' => null, $index => ['$avg' => '$' . $column]];
        }

        return $this;
    }

    /**
     * 求总和
     *
     * @param  string $column 计算字段
     *
     * @return
     */
    public function sum($column)
    {
        $index = 'sum_' . $column;

        if (!empty($this->group)) {
            $this->group[$index] = ['$sum' => '$' . $column];
        } else {
            $this->group = ['_id' => null, $index => ['$sum' => '$' . $column]];
        }

        return $this;
    }

    /**
     * 获取全部
     *
     * @return Collection
     */
    public function get()
    {
        $res = $this->connection->aggregate($this->getPipeline());

        return $res->toArray();
    }

    /**
     * 获取一个
     *
     * @return Collection
     */
    public function first()
    {
        return $this->connection->findOne($this->match);
    }

    /**
     * 总和
     *
     * @return int
     */
    public function count()
    {
        return $this->connection->count($this->match);
    }

    /**
     * 自增
     *
     * @param  string    $column 自增字段
     * @param  float|int $value  值
     *
     * @return Collection
     */
    public function increment($column, $value = 1)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Non-numeric value passed to increment method.');
        }

        $res = $this->connection->updateMany($this->match, ['$inc' => [$column => $value]]);

        return $res->isAcknowledged();
    }

    /**
     * 自减
     *
     * @param  string    $column 自减字段
     * @param  float|int $value  值
     *
     * @return Collection
     */
    public function decrement($column, $value = 1)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
        }

        return $this->increment($column, -$value);
    }

    /**
     * 更新
     *
     * @param  array  $values 跟新内容
     *
     * @return Collection
     */
    public function update(array $values)
    {
        $res = $this->connection->updateMany($this->match, ['$set' => $values]);

        return $res->isAcknowledged();
    }

    /**
     * 删除
     *
     * @return Collection
     */
    public function delete()
    {
        $res = $this->connection->deleteMany($this->match);

        return $res->isAcknowledged();
    }

    /**
     * 删除列
     *
     * @param  string $column 删除的字段
     *
     * @return Collection
     */
    public function deleteColumn($column)
    {
        $res = $this->connection->updateMany($this->match, ['$unset' => [$column => '']]);

        return $res->isAcknowledged();
    }

    /**
     * 写入一个文档
     *
     * @param  array  $values 文档数据
     *
     * @return int
     */
    public function insert(array $values)
    {
        if (!is_array(reset($values))) {
            $res = $this->connection->insertOne($values);
            if ($res->isAcknowledged()) {
                return $res->getInsertedId();
            } else {
                return false;
            }
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }

            $res = $this->connection->insertMany($values);
            if ($res->isAcknowledged()) {
                return $res->getInsertedIds();
            } else {
                return false;
            }
        }
    }

    /**
     * 判断操作符是否合法
     *
     * @param  string $operator 操作符
     *
     * @return boolean
     */
    public function invalidOperator($operator)
    {
        return !in_array($operator, self::$operators, true);
    }

    /**
     * 获取聚合查询Pipeline参数
     *
     * @return array
     */
    public function getPipeline()
    {
        $pipeline = [];
        //$match参数
        if (!empty($this->match)) {
            $pipeline[] = [
                '$match' => $this->match,
            ];
        } else {
            $pipeline[] = [
                '$match' => [
                    '_id' => [
                        '$exists' => true,
                    ],
                ],
            ];
        }

        //$group参数
        if (!empty($this->group)) {
            $pipeline[] = [
                '$group' => $this->group,
            ];
        }

        //$group参数
        if (!empty($this->sort)) {
            $pipeline[] = [
                '$sort' => $this->sort,
            ];
        }

        //$group参数
        if (!empty($this->limit)) {
            $pipeline[] = [
                '$limit' => $this->limit,
            ];
        }

        //$group参数
        if (!empty($this->skip)) {
            $pipeline[] = [
                '$skip' => $this->skip,
            ];
        }

        return $pipeline;
    }

    /**
     * 处理构造器中的动态方法调用
     *
     * @param  string $method     方法名
     * @param  array  $parameters 参数
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        if (method_exists($this->model, 'scope' . ucfirst($method))) {
            array_unshift($parameters, $this);
            return $this->model->{'scope' . ucfirst($method)}(...$parameters);
        }

        throw new BadMethodCallException();
    }
}
