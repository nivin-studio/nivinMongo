<?php

namespace nivinMongo;

use Phalcon\Di;

/**
 *
 */
abstract class Model
{
    /**
     * 连接的数据库名
     *
     * @var string
     */
    protected $database;

    /**
     * 连接的数据表名
     *
     * @var string
     */
    protected $table;

    /**
     * 查询构造器对象
     *
     * @var \nivinmongo\Builder
     */
    protected $builder;

    /**
     * 数据库连接对象
     *
     * @var
     */
    protected $connection;

    /**
     * 获取数据库名
     *
     * @return string
     */
    public function getDatabaseName()
    {
        if (is_null($this->database)) {
            return Di::getDefault()->get('config')->mongo->database;
        }

        return $this->database;
    }

    /**
     * 设置数据库名
     *
     * @param string $name
     */
    public function setDatabaseName($name)
    {
        $this->database = $name;

        return $this;
    }

    /**
     * 获取数据表名
     *
     * @return string
     */
    public function getTable()
    {
        if (is_null($this->table)) {
            return strtolower((new \ReflectionClass(static::class))->getShortName());
        }

        return $this->table;
    }

    /**
     * 设置数据表名
     *
     * @param string $name
     */
    public function setTable($name)
    {
        $this->table = $name;

        return $this;
    }

    /**
     * 获取一个查询构造器
     *
     * @return Builder
     */
    public function getBuilder()
    {
        if (is_null($this->builder)) {
            $this->builder = new Builder($this);
        }

        return $this->builder;
    }

    /**
     * 获取一个数据库连接
     *
     * @return [type] [description]
     */
    public function getConnection()
    {
        if (is_null($this->connection)) {
            $this->connection = new \MongoDB\Collection(Di::getDefault()->get('mongo'), $this->getDatabaseName(), $this->getTable());
        }

        return $this->connection;
    }

    /**
     * 将方法调用转发给给定对象
     *
     * @param  object $object     对象
     * @param  string $method     方法名
     * @param  array  $parameters 参数
     *
     * @return mixed
     */
    protected function forwardCallTo($object, $method, $parameters)
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 处理集合中的动态方法调用
     *
     * @param  string $method     方法名
     * @param  array  $parameters 参数
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->getBuilder(), $method, $parameters);
    }

    /**
     * 处理动态静态方法调用
     *
     * @param  string $method     方法名
     * @param  array  $parameters 参数
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static )->$method(...$parameters);
    }
}
