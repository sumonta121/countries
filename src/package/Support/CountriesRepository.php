<?php

namespace PragmaRX\Countries\Package\Support;

class CountriesRepository extends Base
{
    /**
     * Timezones.
     *
     * @var
     */
    public $timezones;

    /**
     * Countries json.
     *
     * @var
     */
    public $countriesJson;

    /**
     * Countries.
     *
     * @var array
     */
    public $countries = [];

    /**
     * Hydrator.
     *
     * @var Hydrator
     */
    public $hydrator;

    private $general;

    /**
     * CountriesRepository constructor.
     *
     * @param Cache $cache
     * @param Hydrator $hydrator
     * @param General $general
     */
    public function __construct(Cache $cache, Hydrator $hydrator, General $general)
    {
        $this->setCache($cache);

        $this->hydrator = $hydrator;

        $this->general = $general;

        $this->cache = $cache;

        $this->loadCountries();
    }

    /**
     * Call a method currencies collection.
     *
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function call($name, $arguments)
    {
        if ($value = $this->getCached($keyParameters = [$name, $arguments])) {
            return $value;
        }

        $result = call_user_func_array([$this, $name], $arguments);

        if (config('countries.hydrate.before')) {
            $result = $this->hydrator->hydrate($result);
        }

        return $this->cache($keyParameters, $result);
    }

    /**
     * Hydrator getter.
     *
     * @return Hydrator
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * Load countries.
     *
     * @return \PragmaRX\Coollection\Package\Coollection
     */
    public function loadCountries()
    {
        $this->countriesJson = $this->loadCountriesJson();

        $overload = $this->general->loadJsonFiles($this->general->dataDir('countries/overload'))->mapWithKeys(function ($country, $code) {
            return [upper($code) => $country];
        });

        $this->countriesJson = $this->countriesJson->overwrite($overload);

        return $this->countriesJson;
    }

    /**
     * Load countries json file.
     *
     * @return \PragmaRX\Coollection\Package\Coollection
     */
    public function loadCountriesJson()
    {
        return $this->general->loadJson(
            $this->general->dataDir('countries/default/_all_countries.json')
        );
    }

    /**
     * Load currency json file.
     *
     * @param $code
     * @return string
     */
    public function loadCurrenciesForCountry($code)
    {
        $currencies = $this->general->loadJson(
            $this->general->dataDir('currencies/default/'.strtolower($code).'.json')
        );

        return $currencies;
    }

    /**
     * Get all countries.
     *
     * @return \PragmaRX\Coollection\Package\Coollection
     */
    public function all()
    {
        return $this->collection($this->countriesJson);
    }

    /**
     * Get all currencies.
     *
     * @return \PragmaRX\Coollection\Package\Coollection
     */
    public function currencies()
    {
        $currencies = $this->general->loadJsonFiles($this->general->dataDir('currencies/default'))->mapWithKeys(function ($country, $code) {
            return [upper($code) => $country];
        });

        $overload = $this->general->loadJsonFiles($this->general->dataDir('currencies/overload'))->mapWithKeys(function ($country, $code) {
            return [upper($code) => $country];
        });

        return $currencies->overwrite($overload);
    }

    /**
     * Call magic method.
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        return call_user_func_array([$this->all(), $name], $arguments);
    }

    /**
     * Make flags array for a coutry.
     *
     * @param $country
     * @return array
     */
    public function makeAllFlags($country)
    {
        return [
            // https://www.flag-sprites.com/
            // https://github.com/LeoColomb/flag-sprites
            'sprite' => '<span class="flag flag-'.($flag = strtolower($country['cca3'])).'"></span>',

            // https://github.com/lipis/flag-icon-css
            'flag-icon' => '<span class="flag-icon flag-icon-'.$flag.'"></span>',
            'flag-icon-squared' => '<span class="flag-icon flag-icon-'.$flag.' flag-icon-squared"></span>',

            // https://github.com/lafeber/world-flags-sprite
            'world-flags-sprite' => '<span class="flag '.$flag.'"></span>',

            // Internal svg file
            'svg' => $this->getFlagSvg($country['cca3']),
        ];
    }

    /**
     * Read the flag svg file.
     *
     * @param $country
     * @return string
     */
    public function getFlagSvg($country)
    {
        return $this->general->loadFile(
            $this->general->dataDir('flags/'.strtolower($country).'.svg')
        );
    }

    /**
     * Get country geometry.
     *
     * @param $country
     * @return string
     */
    public function getGeometry($country)
    {
        return $this->general->loadFile(
            $this->general->dataDir('geo/'.strtolower($country).'.geo.json')
        );
    }

    /**
     * Get country topology.
     *
     * @param $country
     * @return string
     */
    public function getTopology($country)
    {
        return $this->general->loadFile(
            $this->general->dataDir('topo/'.strtolower($country).'.topo.json')
        );
    }

    /**
     * Hydrate a country element.
     *
     * @param $collection
     * @param null $elements
     * @return \PragmaRX\Coollection\Package\Coollection
     */
    public function hydrate($collection, $elements = null)
    {
        return $this->hydrator->hydrate($collection, $elements);
    }

    /**
     * Find a country timezone.
     *
     * @param $countryCode
     * @return null
     */
    public function findTimezones($countryCode)
    {
        return $this->general->loadJson(
            $this->general->dataDir('timezones/countries/default/'.strtolower($countryCode).'.json')
        );
    }
}
