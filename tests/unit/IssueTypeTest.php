<?php

namespace JiraRestApi\Tests;

use GuzzleHttp\Psr7\Response;
use JiraRestApi\Issue\IssueType;
use JiraRestApi\Issue\IssueTypeService;

/**
 * Class IssueTypeTest
 * @package JiraRestApi\Tests
 */
class IssueTypeTest extends MockGuzzleClient
{
    public function testGetAllIssuetype()
    {
        $response = $this->getLocalResponse('issuetype.all.json');
        /** @var IssueTypeService $issuetypeService */
        $issuetypeService = $this->app['jira.rest.issuetype'];
        $this->mockHandler->append(new Response(200, [], $response));

        $result = $issuetypeService->getAllIssuetypes();

        $this->assertInternalType('object', $result);
        $this->assertInstanceOf(\ArrayObject::class, $result);
        $this->assertInstanceOf(IssueType::class, $result->offsetGet(0));
    }

    public function testGetIssuetype()
    {
        $response = $this->getLocalResponse('issuetype.get.json');
        /** @var IssueTypeService $issuetypeService */
        $issuetypeService = $this->app['jira.rest.issuetype'];
        $this->mockHandler->append(new Response(200, [], $response));

        $result = $issuetypeService->get(1);

        $this->assertInternalType('object', $result);
        $this->assertInstanceOf(IssueType::class, $result);
    }
}