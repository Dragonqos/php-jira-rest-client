<?php

namespace JiraRestApi;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use JiraRestApi\Interfaces\ConfigurationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interact jira server with REST API.
 */
class JiraClient
{
    /**
     * Json Mapper.
     *
     * @var \JsonMapper
     */
    protected $json_mapper;

    /**
     * JIRA REST API URI.
     *
     * @var string
     */
    protected $api_uri = '/rest/api/2';

    /**
     * Logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * @var ClientInterface string
     */
    protected $transport;

    /**
     * Jira Rest API Configuration.
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * JiraClient constructor.
     *
     * @param ConfigurationInterface|null $configuration
     * @param ClientInterface             $transport
     * @param LoggerInterface             $log
     */
    public function __construct(ConfigurationInterface $configuration = null, ClientInterface $transport, LoggerInterface $log)
    {
        $this->configuration = $configuration;

        $this->json_mapper = new \JsonMapper();
        $this->json_mapper->bEnforceMapType = false;
        $this->json_mapper->setLogger($log);
        $this->json_mapper->undefinedPropertyHandler = function ($obj, $val) {
            $this->log->debug('Handle undefined property', [$val, get_class($obj)]);
        };

        $this->log = $log;
        $this->transport = $transport;
    }

    /**
     * Execute REST request.
     *
     * @param string $context RestAPI context (ex.:issue, search, etc..)
     * @param null   $post_data
     * @param string $httpMethod
     *
     * @return string
     *
     * @throws JiraException
     */
    public function exec($context, $post_data = null, $httpMethod = Request::METHOD_GET)
    {
        $url = $this->createUrlByContext($context);

        $options = [
            RequestOptions::HEADERS => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'charset' => 'UTF-8'
            ]
        ];

        if ($httpMethod == Request::METHOD_GET) {
            $options[RequestOptions::QUERY] = $post_data;
        }

        if (in_array($httpMethod, [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_DELETE])) {
            $options[RequestOptions::JSON] = $post_data;
        }

        try {
            $this->log->debug('JiraRestApi request: ', [$httpMethod, $url, $options]);
            $response = $this->transport->request($httpMethod, $url, $options);
//            $this->log->info('JiraRestApi response: ', [$response->getHeaders(), (string)$response->getBody()]);
        } catch (ConnectException $e) {
            $this->log->critical('JiraRestApi connection exception: ', [$e->getMessage()]);
        } catch (RequestException $e) {
            $this->log->error('JiraRestApi response fail with code : ' . $e->getCode(), [
                $httpMethod, $url, $options,
                (string)$e->getRequest()->getBody(),
                $e->getRequest()->getHeaders(),
                (string)$e->getResponse()->getBody()
            ]);
            $response = $e->getResponse();
        }

        return isset($response) && $response instanceof ResponseInterface
            ? $this->parseResponse($response)
            : false;
    }

    /**
     * File upload.
     *
     * @param string $context       url context
     * @param array  $filePathArray upload file path.
     *
     * @return array
     *
     * @throws JiraException
     */
    public function upload($context, array $filePathArray)
    {
        $url = $this->createUrlByContext($context);

        $options = [
            RequestOptions::HEADERS => [
                'X-Atlassian-Token' => 'no-check'
            ]
        ];

        $promises = [];

        if(!empty($filePathArray)) {

            foreach ($filePathArray as $filename => $filePath) {
                // load each files separately
                if (file_exists($filePath) == false) {
                    // Ignore if file not found
                    $this->log->error('JiraRestApi: Unable to upload file "' . $filePath . '". File not Found');
                    continue;
                }

                $ex = explode("/", $filePath);
                $options[RequestOptions::MULTIPART] = [
                    [
                        'name'     => 'file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => is_numeric($filename) ? end($ex) : $filename
                    ]
                ];

                $this->log->info('JiraRestApi requestAsync: ', [Request::METHOD_POST, $url, $options]);
                $promises[] = $this->transport
                    ->requestAsync(Request::METHOD_POST, $url, $options)
                    ->then(function (ResponseInterface $response) {
                        $this->log->info('JiraRestApi responseAsync: ', [$response->getHeaders(), (string) $response->getBody()]);
                        return $response;
                    }, function (RequestException $e) {
                        if($e instanceof ConnectException) {
                            $this->log->critical('JiraRestApi connection exception: ', [$e->getMessage()]);
                            return false;
                        } else {
                            $this->log->error('JiraRestApi responseAsync fail with code : ' . $e->getCode(), [(string) $e->getRequest()->getBody(), $e->getRequest()->getHeaders(), (string) $e->getResponse()->getBody()]);
                            return $e->getResponse();
                        }
                    });
            }

            $responses = \GuzzleHttp\Promise\settle($promises)->wait();

            $result = [];
            foreach ($responses as $response) {
                if (isset($response['value']) && $response['value'] instanceof ResponseInterface) {
                    $result[] = $this->parseResponse($response['value']);
                }
            }

            return $result;
        }

        return false;
    }

    /**
     * Access to JiraResources using JiraCredentials
     * @param $fromUrl
     * @param $toResource
     *
     * @return mixed
     */
    public function download($fromUrl, $toResource = null)
    {
        $options = is_null($toResource)
            ? [RequestOptions::STREAM => true]
            : [RequestOptions::SINK => $toResource];

        try {
            $this->log->info('JiraRestApi request: ', ['GET', $fromUrl, $options]);
            $response = $this->transport->get($fromUrl, $options);
            $this->log->info('JiraRestApi response: ', [$response->getHeaders()]);
        } catch (ConnectException $e) {
            $this->log->critical('JiraRestApi connection exception: ', [$e->getMessage()]);
        } catch (RequestException $e) {
            $this->log->error('JiraRestApi response fail with code : ' . $e->getCode(), [(string) $e->getRequest()->getBody(), $e->getRequest()->getHeaders()]);
            $response = $e->getResponse();
        }

        return isset($response) && $response instanceof ResponseInterface
            ? $response
            : false;
    }

    /**
     * @param               $array
     * @param callable|null $callback
     *
     * @return mixed
     */
    protected function filterNullVariable($array, callable $callback = null)
    {
        $array = json_decode(json_encode($array), true); // toArray

        $array = is_callable($callback) ? array_filter($array, $callback) : array_filter((array)$array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = call_user_func([$this, 'filterNullVariable'], $value, $callback);
            }
        }

        return $array;
    }

    /**
     * @param $rawResponse
     *
     * @return mixed
     */
    public function parseResponse(ResponseInterface $rawResponse)
    {
        return (new JiraClientResponse($rawResponse, $this->log))->parse();
    }

    /**
     * @param          $result
     * @param array    $responseCodes
     * @param \Closure $callback
     *
     * @return mixed
     */
    protected function extractErrors($result, array $responseCodes = [200], \Closure $callback)
    {
        if ($result instanceof JiraClientResponse &&
            !$result->hasErrors() &&
            in_array($result->getCode(), $responseCodes)
        ) {
            return $callback();
        }

        if ($result && !in_array($result->getCode(), $responseCodes)) {
            $result->setError('Unexpected response code, expected "' . implode(', ', $responseCodes) . '", ' . $result->getCode() . ' given');
        }

        return $result;
    }

    /**
     * Get URL by context.
     *
     * @param string $context
     *
     * @return string
     */
    protected function createUrlByContext($context)
    {
        return $this->api_uri . '/' . preg_replace('/\//', '', $context, 1);
    }

    /**
     * Jira Rest API Configuration.
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return \JsonMapper
     */
    public function getJsonMapper()
    {
        return $this->json_mapper;
    }
}
