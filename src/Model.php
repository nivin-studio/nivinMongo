<?php

namespace nivinMongo;

use Phalcon\Di;

/**
 *
 */
abstract class Model
{
    protected $table;
    protected $dbname;

    protected $builder;
    protected $connection;

    /**
     * 获取数据库名
     *
     * @return string
     */
    public function getDbName()
    {
        if (is_null($this->dbname)) {
            return Di::getDefault()->get('config')->mongo->database;
        }

        return $this->dbname;
    }

    /**
     * 获取集合名
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
     * 设置数据库名
     *
     * @param string $name
     */
    public function setDbName($name)
    {
        $this->dbname = $name;
    }

    /**
     * 设置集合名
     *
     * @param string $name
     */
    public function setTable($name)
    {
        $this->table = $name;
    }

    /**
     * 获取一个连接器
     *
     * @return [type] [description]
     */
    public function getConnection()
    {
        if ($this->connection == null) {
            $this->connection = new \MongoDB\Collection(Di::getDefault()->get('mongo'), $this->getDbName(), $this->getTable());
        }
        return $this->connection;
    }

    /**
     * 获取一个查询构造器
     *
     * @return Builder
     */
    public function getBuilder()
    {
        $this->builder = new Builder($this);

        return $this->builder;
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
