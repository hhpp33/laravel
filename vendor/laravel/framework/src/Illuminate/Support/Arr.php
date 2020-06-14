<?php

namespace Illuminate\Support;

use ArrayAccess;
use InvalidArgumentException;
use Illuminate\Support\Traits\Macroable;

/**
 * 数组帮助类
 * Class Arr
 * @package Illuminate\Support
 */
class Arr
{
    //特性：利用 Macroable Trait 对Arr进行扩展功能
    use Macroable;

    /**
     *验证给定的值是否合法
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        //判断给定的值是否数组或数组接口
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * 对数组添加一个元素
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function add($array, $key, $value)
    {
        //若元素不存在，则添加元素
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }
         //返回数据
        return $array;
    }

    /**
     * 将多个数组合并成一个
     * Collapse an array of arrays into a single array.
     *
     * @param  array  $array
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];
        // 遍历数据
        foreach ($array as $values) {
            if ($values instanceof Collection) {// 判断是否集合
                $values = $values->all();
            } elseif (! is_array($values)) {// 判断是否集合，若是非数组跳出循环
                continue;
            }
            //合并数据
            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * 交叉连接给定数组，返回一个包含所有排列组合的笛卡尔乘积
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param  array  ...$arrays
     * @return array
     */
    public static function crossJoin(...$arrays)
    {
        //定义一个二维数组
        $results = [[]];
        //遍历参数，参数是多个数组
        foreach ($arrays as $index => $array) {
            $append = [];

            foreach ($results as $product) {
                //参数数组
                foreach ($array as $item) {
                    //每个参数的键和值复制一份给$product
                    $product[$index] = $item;
                    //$product添加到$append
                    $append[] = $product;
                }
            }
            //最后把$append賦值给$results
            $results = $append;
        }

        return $results;
    }

    /**
     * 返回两个数组，一个包含原数组的所有键，另外一个包含原数组的所有值
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array  $array
     * @return array
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * 使用「.」号将将多维数组转化为一维数组
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];
        //遍历数组
        foreach ($array as $key => $value) {
            //判断是否数组并非空值
            if (is_array($value) && ! empty($value)) {
                //递归处理将结果合并到$results，$prependq为键前綴
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                //非数组直接賦值给$results
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * 从数组中移除给定键值对
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        //移除给定键值对，$keys为数组
        static::forget($array, $keys);

        return $array;
    }

    /**
     * 检查给定键在数组中是否存在
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {    //判断是否数组接口
        if ($array instanceof ArrayAccess) {
            //调用数组接口类offsetExists方法判断当前key是否存在
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }

    /**
     * 返回使用给定闭包对数组进行过滤第一个元素
     * Return the first element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function first($array, callable $callback = null, $default = null)
    {
        //判断回调函数是否为null
        if (is_null($callback)) {
            //若是空值直接返回
            if (empty($array)) {
                return value($default);
            }
            //若是数组直接返回
            foreach ($array as $item) {
                return $item;
            }
        }
        //若有回调函数则回调处理
        foreach ($array as $key => $value) {
            //返回符合的条件第一元素
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }
        //若以上条件都不满足，则返回给定的默认值
        return value($default);
    }

    /**
     * 返回使用给定闭包对数组进行过滤最后一个元素
     * Return the last element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function last($array, callable $callback = null, $default = null)
    {
        //若无回调函数，则返回当前数组最后一个元素
        if (is_null($callback)) {
            return empty($array) ? value($default) : end($array);
        }
        //若有回调函数，则返回当前数组符合条件最后一个元素
        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * 多维数组转化为一维数组,指定过滤数组的层级
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];
        //遍历数据
        foreach ($array as $item) {
            //若有集合，则返回集合元素，否则返回数组
            $item = $item instanceof Collection ? $item->all() : $item;

            if (! is_array($item)) {//若不是数组，则将$item添加到$result数据
                $result[] = $item;
            } elseif ($depth === 1) {//若指定的过滤数组的层级是1，则获取当前元素的所有值将合并到$result数组
                $result = array_merge($result, array_values($item));
            } else {//否则递归处理将结果合并到$result数组
                $result = array_merge($result, static::flatten($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * 使用「.」号从嵌套数组中移除给定键值对
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        //转化成数组
        $keys = (array) $keys;
         //若$keys数组为0，则直接返回
        if (count($keys) === 0) {
            return;
        }
        //遍历数组
        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            // 跳出本次循环，并删除当前元素
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }
            //以“.”符合分割成数组
            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                //删除数组中的第一个元素，并返回被删除元素的值
                $part = array_shift($parts);
                //若$array[$part]存在并为数组，则将$array[$part]賦值给$array
                //否则跳出本循环
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            //删除数组中的第一个元素，并返回被删除元素的值
            //删除被删除元素的元素
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * 使用「.」号从嵌套数组中获取值
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        //若当前值不是数组，则返回给定的默认值
        if (! static::accessible($array)) {
            return value($default);
        }
        //若给定的键为null，则返回当前数组
        if (is_null($key)) {
            return $array;
        }
        //若元素存在 ，则返回当前元素
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        //若给定的键无“.”符号并当前元素存在，则返回当元素，不存在返回默认值
        if (strpos($key, '.') === false) {
            return $array[$key] ?? value($default);
        }
       // 以"."符号分割成为数数组，遍历数据
        foreach (explode('.', $key) as $segment) {
            //若$array是数据，并且当前元素存在，则将当前元素赋值给$array，否则返回默认值
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * 使用「.」检查给定数据项是否在数组中存在
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $keys
     * @return bool
     */
    public static function has($array, $keys)
    {
        //若给定的键为null，则返回false
        if (is_null($keys)) {
            return false;
        }
        //强制转化为数组
        $keys = (array) $keys;

        if (! $array) {
            return false;
        }
        //若为空数组，则返回false
        if ($keys === []) {
            return false;
        }

        //遍历数组
        foreach ($keys as $key) {
            $subKeyArray = $array;
            //若当前元素存在，则跳出本次循环
            if (static::exists($array, $key)) {
                continue;
            }
            //以"."符号分割为数组
            foreach (explode('.', $key) as $segment) {
                //若$subKeyArray为数组并当前元素存在，则赋值给$subKeyArray，则返回false
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 检查是否为关联数组
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param  array  $array
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        // 获取所有数组中的所有键
        $keys = array_keys($array);
        //若一个数组没有包含从0开始的数字序列键，就被认为是「关联数组」
        return array_keys($keys) !== $keys;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param  array  $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    public static function pluck($array, $value, $key = null)
    {
        $results = [];

        list($value, $key) = static::explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    protected static function explodePluckParameters($value, $key)
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  mixed  $key
     * @return array
     */
    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * Get one or a specified number of random values from an array.
     *
     * @param  array  $array
     * @param  int|null  $number
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function random($array, $number = null)
    {
        $requested = is_null($number) ? 1 : $number;

        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($number)) {
            return $array[array_rand($array)];
        }

        if ((int) $number === 0) {
            return [];
        }

        $keys = array_rand($array, $number);

        $results = [];

        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * 用于在嵌套数组中使用「.」号设置值
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        // 若给定的键为null，则返回给定的设置值
        if (is_null($key)) {
            return $array = $value;
        }
        // 以"."符号分割为数组
        $keys = explode('.', $key);
        //循环$keys
        while (count($keys) > 1) {
            //删除数组中的第一个元素，并返回被删除元素的值
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            //若当前元素不存在或不是数组，则将$array[$key]赋值为空数组
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }
        //删除数组中的第一个元素，并返回被删除元素的值
        //删除掉被删除元素的值的元素
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 随机打乱数组中元素的排序
     * Shuffle the given array and return the result.
     *
     * @param  array  $array
     * @return array
     */
    public static function shuffle($array)
    {
        shuffle($array);

        return $array;
    }

    /**
     * Sort the array using the given callback or "dot" notation.
     *
     * @param  array  $array
     * @param  callable|string|null  $callback
     * @return array
     */
    public static function sort($array, $callback = null)
    {
        return Collection::make($array)->sortBy($callback)->all();
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array  $array
     * @return array
     */
    public static function sortRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }

        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    /**
     * 使用给定闭包对数组进行过滤
     * Filter the array using the given callback.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param  mixed  $value
     * @return array
     */
    public static function wrap($value)
    {
        return ! is_array($value) ? [$value] : $value;
    }
}
