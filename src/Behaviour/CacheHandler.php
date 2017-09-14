<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 14/09/2017
 * Time: 11:21
 */

namespace curunoir\translation\Behaviour;


trait CacheHandler
{
    private     $cacheTime = 20;

    /**
     * Sets the time to store the translations and locales in cache.
     *
     * @param int $time
     */
    protected function setCacheTime($time)
    {
        if (is_numeric($time)) {
            $this->cacheTime = $time;
        }
    }

    /**
     * Returns the cache time set from the configuration file.
     *
     * @return string|int
     */
    protected function getConfigCacheTime()
    {
        return $this->config->get('translation.cache_time', $this->cacheTime);
    }

}