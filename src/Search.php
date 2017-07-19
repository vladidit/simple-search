<?php
/* Simple search engine
 * by vladidit
 * v1.0.1
 * modified 18.07.2017
 *
*/

namespace Vladidit\SimpleSearch;

use DB;
use Schema;
use phpMorphy;

class Search
{
    public $query;
    public $count = 0;
    public $collection = [];
    public $perPage;
    public $locale = null;

    private $searchArray = [];
    private $limit;
    private $cleanQuery = [];
    private $markClass;
    private $marked = [];
    private $minLength = 2;
    private $substr = 0;
    private $morphy;

    public function __construct($query = '', $searchArray = [])
    {

        if ($query) $this->setQuery($query);

        if ($searchArray) $this->setSearchArray($searchArray);

    }

    /**
     * Sets requested query for instance
     * @return $this
     */
    public function setQuery($query = '')
    {
        if ($query)
            $this->query = $query;

        return $this;
    }

    /**
     * Sets search array for instance
     * @param array $searchArray
     * @return $this
     */
    public function setSearchArray(Array $searchArray = [])
    {
        if ($searchArray)
            $this->searchArray = $searchArray;

        return $this;
    }

    /**
     * Sets how many symbols will be removed from end of the each entire word
     * @param array $searchArray
     * @return $this
     */
    public function setSubstr($value)
    {
        $this->substr = $value;

        return $this;
    }

    /**
     * Sets min length of each query word. If entire word's length is not enough it will be ignored
     * @param $length
     * @return $this
     */
    public function setMinLength($length)
    {
        $this->minLength = $length;

        return $this;
    }

    public function searchOne($optionsArray = [])
    {

        $optionsArray = $optionsArray ?: $this->searchArray;

        $this->clearQuery();

        if (!$this->cleanQuery) return $this;

        $preparedResults = $this->prepareResult($optionsArray);

        if ($preparedResults->found)
            $this->collection = array_merge($this->collection, $preparedResults->found);

        $this->count = $this->count + $preparedResults->count;

        $this->sortBy('relTotal');

        if ($this->getLimit())
            $this->collection = array_slice($this->collection, 0, $this->getLimit());

        if (isset($optionsArray['model']))
            $this->fillModels($this->collection);

        return $this;
    }

    public function searchMany()
    {
        foreach ($this->searchArray as $options) {

            $this->searchOne($options);
        }

        $this->sortBy('relTotal');

        if ($this->getLimit())
            $this->collection = array_slice($this->collection, 0, $this->getLimit());

        return $this;
    }


    private function sortBy($field, $direction = 'asc')
    {
        $array = $this->collection;

        usort($array, function ($a, $b) use ($field, $direction) {

            $a = $a[$field];
            $b = $b[$field];

            if ($a == $b) {
                return 0;
            }

            if ($direction == "desc") {
                return ($a < $b) ? -1 : 1;
            } elseif ($direction == "asc") {
                return ($a > $b) ? -1 : 1;
            }

        });

        $this->collection = $array;

        return true;
    }

    /**
     * clean entire query of spaces and special chars, substract words, put result to private var
     * @param string $query
     * @return array|bool
     */
    private function clearQuery()
    {
        if (!$this->query)
            return [];

        $queryRoots = [];

        $query = explode(' ', preg_replace('/[^!,\.\_\w\s]/u', ' ', strip_tags($this->query)));

        foreach ($query as $q) {

            if (!$q || mb_strlen($q) < $this->minLength) continue;

            $roots = $this->getRoots($q);

            if ($roots) {
                foreach ($roots as $root) {
                    $queryRoots[] = $root;
                }
            } else {
                $queryRoots[] = $q;
            }
        }

        $extendedQuery = $this->getQueryExtension($queryRoots);

        $cleanQuery = $queryRoots;

        if ($extendedQuery) {

            foreach ($extendedQuery as $q) {
                $roots = $this->getRoots($q);

                if ($roots) {
                    foreach ($roots as $root) {
                        $cleanQuery[] = $root;
                    }
                } else {
                    $cleanQuery[] = $q;
                }
            }
        }

        $cleanQuery = array_unique($cleanQuery);

        $this->cleanQuery = $cleanQuery;
    }

    private function getRoots($word)
    {

        $result = [];

        if ($this->getDictionary()) {
            try {
                $this->morphy = new phpMorphy(__DIR__ . '/morphy/dicts/', $this->getDictionary(), ['storage' => PHPMORPHY_STORAGE_FILE]);
            } catch (\Exception $e) {
                throw new \Exception('Dictionary file for [ ' . $this->getDictionary() . ' ] is not exists');
            }
        }

        if (!$this->morphy)
            return $result;

        /*phpMorphy magic to get roots bulk*/
        $roots = $this->morphy->getPseudoRoot(mb_strtoupper($word, 'UTF-8'));
        /**/

        if ($roots) {
            foreach ($roots as $root) {
                $result[] = mb_strtolower($root);
            }
        }

        return $result;
    }


    /**
     * Check does table search_extension exist
     * @return bool
     */
    private function checkExtensionSchema()
    {

        return Schema::hasTable('search_extensions');
    }


    /**
     * Extends query with extensions from table
     * @param $query
     * @return array
     */
    private function getQueryExtension($query)
    {

        $result = [];

        if (!$this->checkExtensionSchema())
            return $result;

        if (!$query) return $result;

        $property = 'search_extensions_' . $this->getLocale();
        $groups = DB::table('search_extensions')->select($property);

        foreach ($query as $iteration => $word) {

            if (!$iteration) {
                $groups->where($property, 'like', '%' . $word . '%');
            } else {
                $groups->orWhere($property, 'like', '%' . $word . '%');
            }

        }

        $groups = $groups->get();

        foreach ($groups as $group) {

            $groupArray = explode(',', preg_replace('/[^!,\.\_\w\s]/u', ' ', strip_tags($group->$property)));

            $result = array_merge($result, $groupArray);
        }

        $result = array_unique($result);

        return $result;
    }

    public function getLocale()
    {

        return $this->locale ?: config('app.locale');
    }

    public function getDictionary()
    {

        $dictionaries = [
            'ru' => 'ru_RU',
            'ua' => 'uk_UA',
            'en' => 'en_EN'
        ];

        return isset($dictionaries[$this->getLocale()]) ? $dictionaries[$this->getLocale()] : false;
    }


    public function setLocale($locale)
    {

        $this->locale = $locale;

        return $this;
    }

    /**
     * Sets the limits for search results. Ex, in live-search.
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit()
    {

        return $this->limit;
    }

    public function paginate($perPage = 12)
    {

        $this->perPage = $perPage;
        $this->collection = array_slice($this->collection, (int)request()->input('page', 1) * $perPage - $perPage, $perPage);

        return $this->collection;
    }

    public function links($options = [])
    {

        $perPage = $this->perPage;

        if (!$perPage)
            return '';

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $this->collection,
            $this->count,
            $perPage,
            request()->input('page', 1),
            [
                'path'  => request()->getPathInfo(),
                'query' => ['tab' => request()->input('tab'), 'q' => request()->input('q')]
            ]
        );

        return $paginator->links();
    }

    public function setMarkClass($class)
    {
        $this->markClass = $class;

        return $this;
    }

    /**
     * returns array of prepared parts of "like" request with additional options
     * @param array $options
     * @return array
     * @internal param $query
     */
    private function prepareQuery(Array $options = [])
    {

        $queryResult = [];

        foreach ($this->cleanQuery as $q) {

            if ($q) {
                $substrQuery = $q;

                if ($this->substrStart) {
                    $substrQuery = mb_substr($substrQuery, $this->substrStart);
                }

                if ($this->substrEnd) {
                    $substrQuery = mb_substr($substrQuery, 0, -$this->substrEnd);
                }

                $queryResult[] = ' like "%' . $substrQuery . '%"';
                $this->query[] = '/(' . $substrQuery . ')/iu';

                if ($this->markClass) {
                    $this->marked[] = '<span class="' . $this->markClass . '">$1</span>';
                } else {
                    $this->marked[] = '<span style="background-color: ' . $this->config['mark-background-color'] . '">$1</span>';
                }

            }
        }

        $this->preparedQuery = $queryResult;
    }

    private function prepareResult($options)
    {

        $result = (object)['found' => [], 'count' => 0];

        $likeArray = [];
        $caseArray = [];

        $tableFields = [$options['table'] . '.id'];
        $testKey = 0;

        foreach ($options['fields'] as $field => $weight) {

            if (is_numeric($field)) {
                $field = $weight;
                $weight = 1;
            }

            $tableFields[] = $options['table'] . '.' . $field;

            foreach ($this->cleanQuery as $tableKey => $word) {

                $likeArray[] = 'p' . $tableKey . '.' . $field . ' like "%' . $word . '%"';
                $caseArray['test' . $testKey] = '(CASE WHEN p' . $tableKey . '.' . $field . ' like "%' . $word . '%" THEN ' . $weight . ' ELSE 0 END) AS test' . $testKey;

                $testKey++;
            }
        }

        $subTables = [];

        foreach ($this->cleanQuery as $tableKey => $word) {
            $subTables[] = $options['table'] . ' as p' . $tableKey;
        }

        $relTotal = 'res.' . implode(' + res.', array_keys($caseArray));

        $subFields = ['p0.id'];

        foreach ($options['fields'] as $field => $weight) {

            if (is_numeric($field)) {
                $field = $weight;
                $weight = 1;
            }

            $subFields[] = 'p0.' . $field;
        }

        $subQuery = DB::table(DB::raw(implode(',', $subTables)));

        $subQuery->select(DB::raw('   
            ' . implode(',', $subFields) . ',
            ' . implode(',', $caseArray) . '
        '));

        foreach (array_keys($this->cleanQuery) as $table) {
            if ($table)
                $subQuery->whereRaw('p0.id = p' . $table . '.id');
        }

        $subQuery->whereRaw('(' . implode(' or ', $likeArray) . ')');

        $query = DB::table(DB::raw($options['table'] . ', ( ' . $subQuery->toSql() . ') as res'));

        $query->whereRaw('res.id = ' . $options['table'] . '.id');

        if (isset($options['fill']) && $options['fill']) {

            foreach ($options['fill'] as &$fillField) {
                $fillField = $options['table'] . '.' . $fillField;
            }
            $query->select(DB::raw('SQL_CALC_FOUND_ROWS ' . implode(',', $options['fill']) . ', "' . $options['model'] . '" as `model`,' . $relTotal . ' as relTotal'));
        } else {
            $query->select(DB::raw('SQL_CALC_FOUND_ROWS ' . $options['table'] . '.*,' . $relTotal . ' as relTotal'));
        }

        if (isset($options['scopes']) && $options['scopes']) {
            foreach ($options['scopes'] as $scope) {
                $query = $scope($query);
            }
        }

        if ($this->getLimit()) {
            $query->take($this->getLimit());
        }

        $result->found = $query->get()->toArray();

        foreach ($result->found as &$obj) {
            $obj = (array)$obj;
        }
        $result->count = DB::select(DB::raw('select FOUND_ROWS() as found_count'))[0]->found_count;

        return $result;
    }

    private function fillModels(&$collection)
    {
        foreach ($collection as &$foundItem) {

            $model = new $foundItem->model;
            $model->setRawAttributes((array)$foundItem);
            $foundItem->model = $model;

        }
    }

    public function mark($string)
    {

        $marked = preg_replace($this->query, $this->marked, strip_tags($string));

        return $marked;
    }


}
