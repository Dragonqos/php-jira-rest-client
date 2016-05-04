<?php

namespace JiraRestApi\Issue;

use JiraRestApi\JiraClient;

class IssueTypeService extends JiraClient {

    private $uri = '/issuetype';

    /**
     * @return mixed
     */
    public function getAllIssuetypes()
    {
        $result = $this->exec($this->uri);

        return $this->extractErrors($result, [200], function () use ($result) {
            return $this->json_mapper->mapArray(
                $result->getRawData(), new \ArrayObject(), '\JiraRestApi\Issue\IssueType'
            );
        });
    }

    /**
     * @param $issuetypeId
     *
     * @return mixed
     */
    public function get($issuetypeId)
    {
        $result = $this->exec($this->uri . '/' . $issuetypeId);

        return $this->extractErrors($result, [200], function () use ($result) {
            return $this->json_mapper->map(
                $result->getRawData(), new \JiraRestApi\Issue\IssueType
            );

        });
    }

    // ToDO: Create update delete

}