<?php
namespace nav\B24;

use Psr\Log\LoggerInterface;

abstract class BaseRest {
    /** @var bool */
    public $debug = false;

    /** @var static */
    protected static $instance;

    protected $logger;

    /** @var resource */
    protected $curl;

    /** @var bool */
    protected $returnObjects = false;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return static
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            throw new \Exception('No instance was set');
        }

        return static::$instance;
    }

    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }

    public function setLogger($logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function setReturnObjects(bool $value): self
    {
        $this->returnObjects = $value;
        return $this;
    }

    /**
     * @param string $method
     * @param array $data
     * @return mixed
     */
    public function call($method, $data = array())
    {
        $result = $this->request($method, $data);

        if (is_array($result)) {
            if (!empty($result['error'])) {
                $error = new \StdClass;
                $error->error = $result['error'];
                $error->error_description = $result['error_description'];
            }
        } else {
            if (!empty($result->error)) {
                $error = new \StdClass;
                $error->error = $result->error;
                $error->error_description = $result->error_description;
            }
        }

        if (isset($error)) {
            switch ($error->error) {
                case 'expired_token':
                    if ($this->refreshToken()) {
                        $result = $this->request($method, $data);
                    } else {
                        throw new \Exception('[' . $error->error . '] ' . $error->error_description);
                    }
                    break;

                default:
                    if ($this->logger) {
                        $this->logger->error('B24 API error response', (array) $result);
                    }

                    throw new \Exception('[' . $error->error . '] ' . $error->error_description);
                    break;
            }
        }

        return $result;
    }

    /**
     * @param string $method
     * @param array $data
     * @return mixed
     */
    protected function request($method, $data = array())
    {
        $this->curl = curl_init();

        curl_setopt_array($this->curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ));

        $this->configureRequest($method, $data);

        if ($this->debug) {
            print 'Method: ' . $method . "\n";
            print 'Data: ' . var_export($data, true) . "\n";
        }

        if ($this->logger) {
            $this->logger->debug('B24 API call', ['method' => $method, 'data' => $data]);
        }

        $result = curl_exec($this->curl);

        if ($this->logger) {
            $this->logger->debug('B24 API response info', (array) curl_getinfo($this->curl));
        }

        if ($this->debug) {
            print "Result:\n";
            var_dump($result);
        }

        curl_close($this->curl);

        return json_decode($result, (int) !$this->returnObjects);
    }

    /**
     * Prepares CURL resource
     *
     * @param string $method
     * @param array $data
     * @return void
     */
    abstract protected function configureRequest($method, $data);

    /**
     * @param string $method
     * @param mixed $data
     * @return array
     */
    public function fetch($method, $data = [])
    {
        $result = $this->call($method, $data);
        return $result['result'];
    }

    /**
     * @param string $method
     * @param array $data
     * @return array
     */
    public function fetchAll($method, $data = [])
    {
        $result = [];
        $limit = 0;

        if (!empty($data['_limit'])) {
            $limit = $data['_limit'];
            unset($data['_limit']);
        }

        while (true) {
            $stepResult = $this->call($method, $data);
            $result = array_merge($result, is_array($stepResult) ? $stepResult['result'] : $stepResult->result);

            if (!empty($limit) && count($result) >= $limit) {
                break;
            }

            $next = null;

            if (is_array($stepResult)) {
                if (isset($stepResult['next'])) {
                    $next = $stepResult['next'];
                }
            } else {
                if (isset($stepResult->next)) {
                    $next = $stepResult->next;
                }
            }

            if (isset($next)) {
                $data['start'] = $next;
            } else {
                break;
            }
        }

        return $result;
    }

    /**
     * @param string $method
     * @param array $data
     * @param string|null $callId
     * @return int|string call ID
     */
    public function addBatchCall($method, $data = [], $callId = null)
    {
        $entry = [
            'method' => $method,
            'data' => $data
        ];

        $return = null;

        if ($callId !== null) {
            $this->batchCalls[$callId] = $entry;
            $return = $callId;
        } else {
            $this->batchCalls[] = $entry;
            $return = key(array_slice($this->batchCalls, -1));
        }

        return $return;
    }

    /**
     * Sets batch queue manually
     *
     * @param array $calls
     */
    public function setBatchQueue($calls)
    {
        $this->batchCalls = $calls;
    }

    public function processBatchCalls($halt = 0)
    {
        $commands = [];

        foreach ($this->batchCalls as $key => $call) {
            $commands[$key] = $call['method'] . '?' . http_build_query($call['data']);
        }

        $result = $this->call('batch', [
            'halt' => $halt,
            'cmd' => $commands,
        ]);

        return is_array($result) ? $result['result'] : $result->result;
    }

    public function refreshToken()
    {
        return false;
    }
}
