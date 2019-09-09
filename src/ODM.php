<?php

namespace nivinMongo;

/**
 *
 */
class ODM extends Model
{
    protected $table;

    /**
     * 设置集合名
     *
     * @param string $name 集合名
     */
    public static function Table($name)
    {
        return (new static )->setTable($name);
    }

    /**
     * 复写设置集合名
     *
     * @param string $name 集合名
     */
    public function setTable($name)
    {
        $this->table = $name;

        return $this;
    }
}
