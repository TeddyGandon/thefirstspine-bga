<?php

namespace thefirstspine\apiwrapper\core;

class QueryBuilder extends Model {

    /**
     * @var string
     */
    protected $restResource = '';

    /**
     * @var string
     */
    protected $restIdField = '';

    /**
     * @param array|null $where
     * @return static
     */
    public static function find($where = null)
    {
        $self = new static();
        $self = is_null($where) ? $self : $self->where($where);
        return $self;
    }

    public static function findOne($where = null)
    {
        return static::find($where)->one();
    }

    public static function findAll($where = null)
    {
        return static::find($where)->all();
    }

    /**
     * @param array $where
     * @return static
     */
    public function where($where)
    {
        $this->queryBuilderParams['where'] = array_merge($this->queryBuilderParams['where'], $where);
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->queryBuilderParams['limit'] = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->queryBuilderParams['offset'] = $offset;
        return $this;
    }

    /**
     * @return static
     */
    public function one()
    {
        $curlResource = static::initCurlQuery($this);
        $result = static::fetchResult($curlResource);

        $resource = new static();
        $resource->setOriginalAttributes(
            $this->queryBuilderParams['collection'] ?
                $result['data'][0] :
                $result['data']
        );

        return $resource;
    }

    /**
     * @return static[]
     */
    public function all()
    {
        $curlResource = static::initCurlQuery($this);
        $result = static::fetchResult($curlResource);

        $resultArray = $this->queryBuilderParams['collection'] ?
            $result['data'] :
            array($result['data']);

        $resources = array();
        foreach ($resultArray as $resultItem)
        {
            $resource = new static();
            $resource->setOriginalAttributes($resultItem);
            $resources[] = $resource;
        }

        return $resources;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $fill = array();
        foreach ($this->currentAttributes as $name => $value)
        {
            if (
                !isset($this->originalAttributes[$name]) ||
                json_encode($this->originalAttributes[$name]) !== json_encode($this->currentAttributes[$name])
            )
            {
                $fill[$name]= is_array($this->currentAttributes[$name]) ?
                    json_encode($this->currentAttributes[$name]) :
                    $this->currentAttributes[$name];
            }
        }

        $curlResource = static::initCurlQuery($this);

        if ($this->queryBuilderParams['collection'])
        {
           curl_setopt($curlResource, CURLOPT_CUSTOMREQUEST, "POST");
        }
        else
        {
            curl_setopt($curlResource, CURLOPT_CUSTOMREQUEST, "PUT");
        }

        curl_setopt($curlResource, CURLOPT_POSTFIELDS, http_build_query($fill));

        $result = static::fetchResult($curlResource);

        $this->setOriginalAttributes($result['data']);

        return true;
    }

    /**
     * @var null|array
     */
    private static $cachedConfiguration = null;

    /**
     * @return array|null
     */
    private static function getConfiguration()
    {
        self::$cachedConfiguration = is_null(self::$cachedConfiguration) ?
            include __DIR__ . '/../config.php' :
            self::$cachedConfiguration;

        return self::$cachedConfiguration;
    }

    /**
     * @var array
     */
    private $queryBuilderParams = array(
        'where' => array(),
        'limit' => null,
        'offset' => null
    );

    /**
     * @param QueryBuilder $resource
     * @return resource
     */
    protected static function initCurlQuery($resource)
    {
        // Get the configuration
        $config = self::getConfiguration();
        $baseURL = $config['baseURL'];

        // Get the ID
        $id = null;
        if (isset($resource->originalAttributes[$resource->restIdField]))
        {
            $id = $resource->originalAttributes[$resource->restIdField];
        }
        elseif (isset($resource->queryBuilderParams['where'][$resource->restIdField]))
        {
            $id = $resource->queryBuilderParams['where'][$resource->restIdField];
            unset($resource->queryBuilderParams['where'][$resource->restIdField]);
        }

        // Gather if the url we will fetch is a collection or not
        $resource->queryBuilderParams['collection'] = is_null($id);

        // Build the URL
        $url = "{$baseURL}/{$resource->restResource}";
        if (!is_null($id))
        {
            $url .= "/{$id}";
        }
        else{
            $url .= '?';
        }

        // Add the parameters
        if (count($resource->queryBuilderParams['where']) > 0)
        {
            $url .= http_build_query($resource->queryBuilderParams['where']) . '&';
        }
        if (!is_null($resource->queryBuilderParams['limit']))
        {
            $url .= 'limit=' . $resource->queryBuilderParams['limit'] . '&';
        }
        if (!is_null($resource->queryBuilderParams['offset']))
        {
            $url .= 'offset=' . $resource->queryBuilderParams['offset'] . '&';
        }

        // Init curl
        $curl = curl_init($url);

        // Add credentials
        $headers = array('X-API-Credentials: ' . $config['credentials']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // Return output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        return $curl;
    }

    /**
     * @param $curlResource
     * @return array
     * @throws \Exception
     */
    protected static function fetchResult($curlResource)
    {
        $raw = curl_exec($curlResource);
        $json = json_decode($raw, true);

        if (isset($json['error']))
        {
            throw new \Exception(json_encode($json), $json['error']);
        }

        return $json;
    }

}