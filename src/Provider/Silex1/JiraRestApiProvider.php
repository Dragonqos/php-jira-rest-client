<?php

namespace JiraRestApi\Provider\Silex1;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use JiraRestApi\Configuration\ArrayConfiguration;
use Silex\Application;
use Silex\ServiceProviderInterface;

class JiraRestApiProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['jira.config'] = [];

        $app['jira.rest.transport'] = $app->share(function () use ($app) {
            $cfg = $app['jira.rest.configuration'];

            return new Client([
                'base_uri' => $cfg->getJiraHost(),
//                RequestOptions::AUTH => [$cfg->getJiraUser(), $cfg->getJiraPassword()]
                RequestOptions::AUTH => [$cfg->getJiraUser(), $cfg->getJiraToken()]
            ]);
        });

        $app['jira.rest.configuration'] = $app->share(function() use ($app) {
            return new ArrayConfiguration($app['jira.config']);
        });

        $app['jira.rest.service.builder'] = $app->protect(function($serviceName) use ($app) {
            if(class_exists($serviceName)) {
                return new $serviceName($app['jira.rest.configuration'], $app['jira.rest.transport'], $app['logger']);
            }

            throw new \Exception('Service ' . $serviceName .' not found');
        });

        $app['jira.rest.issue'] = $app->share(function() use ($app) {
            $className = '\JiraRestApi\Issue\IssueService';
            return $app['jira.rest.service.builder']($className);
        });

        $app['jira.rest.issuetype'] = $app->share(function() use ($app) {
            $className = '\JiraRestApi\Issue\IssueTypeService';
            return $app['jira.rest.service.builder']($className);
        });

        $app['jira.rest.project'] = $app->share(function() use ($app) {
            $className = '\JiraRestApi\Project\ProjectService';
            return $app['jira.rest.service.builder']($className);
        });

        $app['jira.rest.webhook'] = $app->share(function() use ($app) {
            $className = '\JiraRestApi\Webhook\WebhookService';
            return $app['jira.rest.service.builder']($className);
        });
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}