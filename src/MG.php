<?php

namespace nivinMongo;

/**
 *
 */
class MG extends Model
{
    /**
     * 设置集合名
     *
     * @param string $name 集合名
     */
    public static function Table($name)
    {
        return (new static )->setTable($name);
    }
}
