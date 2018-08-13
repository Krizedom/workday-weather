<?php

namespace App\Model;

use Nette;


class WeatherManager
{
    use Nette\SmartObject;

    /** @var string weather API url */
    private $weatherApiUrl = 'https://www.metaweather.com/api/';

    /** @var Nette\Caching\Storages\FileStorage cache storage */
    private $cacheStorage;

    /** @var Nette\Caching\Cache cache */
    private $cache;

    public function __construct()
    {
        $path = __DIR__ . '/../../temp/cache/weatherManagerTemp';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $this->cacheStorage = new Nette\Caching\Storages\FileStorage($path);
        $this->cache = new Nette\Caching\Cache($this->cacheStorage);
    }

    /**
     * Get location
     *
     * @param string $query location to be found
     * @return bool|mixed|string location
     * @throws \Exception
     * @throws \Throwable
     */
    public function getLocation($query)
    {
        $query = urlencode($query);

        $location = $this->cache->load($query);

        if (!$location) {
            $location = file_get_contents($this->weatherApiUrl . 'location/search/?query=' . $query);

            if ($location === FALSE) {
                throw new \Exception('Get content from API failed');
            }

            $location = json_decode($location);

            $this->cache->save($query, $location, [
                Nette\Caching\Cache::EXPIRE => '15 minutes'
            ]);
        }

            if (count($location) < 1) {
            throw new \Exception('Location not found.');
        }

        return $location;
    }

    /**
     * Get weather
     *
     * @param mixed $location location
     * @return bool|mixed|string weather
     * @throws \Exception
     * @throws \Throwable
     */
    public function getWeather($location)
    {
        $location = $location[0]->woeid;

        $weather = $this->cache->load('weather-' . $location);

        if (!$weather) {
            $weather = file_get_contents($this->weatherApiUrl . 'location/' . $location);

            if ($weather === FALSE) {
                throw new \Exception('Get content from API failed');
            }

            $weather = json_decode($weather);

            $this->cache->save('weather-' . $location, $weather, [
                Nette\Caching\Cache::EXPIRE => '15 minutes'
            ]);
        }

        return $weather;
    }
}