<?php

namespace Sovit\TikTokPrivate;

if (!\class_exists('\Sovit\TikTokPrivate\Util')) {
    /**
     * Utility Class
     */
    class Util
    {
        
        /**
         * Find item in array
         *
         * @param array $arr
         * @param callable $func
         * @return Object
         */
        public static function find(array $arr, callable $func)
        {
            foreach ($arr as $item) {
                if ($func($item, $arr)) {
                    return $item;
                }
            }
            return false;
        }
        
        
    }
}
