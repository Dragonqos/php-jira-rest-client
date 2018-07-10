<?php

namespace JiraRestApi\Issue;

use JiraRestApi\Project\Project;

class IssueField implements \JsonSerializable
{
    /**
     * @var string
     */
    public $summary;

    /**
     * @var array
     */
    public $progress;

    /**
     * @var \JiraRestApi\Issue\TimeTracking
     */
    public $timeTracking;

    /**
     * @var \JiraRestApi\Issue\IssueType
     */
    public $issuetype;

    /**
     * @var string|null
     */
    public $timespent;

    /**
     * @var \JiraRestApi\Issue\Reporter|null
     */
    public $reporter;

    /**
     * @var \DateTime
     */
    public $created;

    /**
     * @var \DateTime
     */
    public $updated;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var \JiraRestApi\Issue\Priority|null
     */
    public $priority;

    /**
     * @var object
     */
    public $status;

    /**
     * @var array
     */
    public $labels;

    /**
     * @var \JiraRestApi\Project\Project
     */
    public $project;

    /**
     * @var string|null
     */
    public $environment;

    /**
     * @var array
     */
    public $components;

    /**
     * @var \JiraRestApi\Issue\Comments
     */
    public $comment;

    /**
     * @var object
     */
    public $votes;

    /**
     * @var object|null
     */
    public $resolution;

    /**
     * @var array
     */
    public $fixVersions;

    /**
     * @var \JiraRestApi\Issue\Reporter
     */
    public $creator;

    /**
     * @var object
     */
    public $watches;

    /**
     * @var object
     */
    public $worklog;

    /**
     * @var \JiraRestApi\Issue\Reporter|null
     */
    public $assignee;

    /**
     * @var \JiraRestApi\Issue\Version[]
     */
    public $versions;

    /**
     * @var \JiraRestApi\Issue\Attachment[]
     */
    public $attachment;

    /**
     * @var string|null
     */
    public $aggregatetimespent;

    /**
     * @var string|null
     */
    public $timeestimate;

    /**
     * @var string|null
     */
    public $aggregatetimeoriginalestimate;

    /**
     * @var string|null
     */
    public $resolutiondate;

    /**
     * @var \DateTime|null
     */
    public $duedate;

    /**
     * @var array
     */
    public $issuelinks;

    /**
     * @var array
     */
    public $subtasks;

    /**
     * @var int|null
     */
    public $workratio;

    /**
     * @var object|null
     */
    public $aggregatetimeestimate;

    /**
     * @var object|null
     */
    public $aggregateprogress;

    /**
     * @var object|null
     */
    public $lastViewed;

    /**
     * @var object|null
     */
    public $timeoriginalestimate;

    /**
     * IssueField constructor.
     * @param bool $updateIssue
     */
    public function __construct($updateIssue = false)
    {
        if (!$updateIssue) {
            $this->project = new Project();

            $this->assignee = new Reporter();
            $this->priority = new Priority();
            $this->versions = [];

            $this->issuetype = new IssueType();
        }
    }

    public function jsonSerialize()
    {
        return array_filter(get_object_vars($this));
    }

    public function getProjectKey()
    {
        return $this->project->key;
    }

    public function getProjectId()
    {
        return $this->project->id;
    }

    public function getIssueType()
    {
        return $this->issuetype;
    }

    public function setProjectKey($key)
    {
        if(is_null($this->project)) {
            $this->project = new Project();
        }

        $this->project->key = (string) $key;

        return $this;
    }

    public function setProjectId($id)
    {
        if(is_null($this->project)) {
            $this->project = new Project();
        }

        $this->project->id = (string) $id;

        return $this;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function setReporterName($name)
    {
        if (is_null($this->reporter)) {
            $this->reporter = new Reporter();
        }

        $this->reporter->name = (string) $name;

        return $this;
    }

    public function setAssigneeName($name)
    {
        if (is_null($this->assignee)) {
            $this->assignee = new Reporter();
        }

        $this->assignee->name = (string) $name;

        return $this;
    }

    public function setPriorityId($id)
    {
        if (is_null($this->priority)) {
            $this->priority = new Priority();
        }

        $this->priority->id = (string) $id;

        return $this;
    }

    public function setPriorityName($name)
    {
        if (is_null($this->priority)) {
            $this->priority = new Priority();
        }

        $this->priority->name = (string) $name;

        return $this;
    }

    public function addVersion($name)
    {
        if (is_null($this->versions)) {
            $this->versions = [];
        }

        $v = new Version();
        $v->name = (string) $name;

        array_push($this->versions, $v);

        return $this;
    }

    public function addComment($comment)
    {
        if (is_null($this->comment)) {
            $this->comment = [];
        }

        array_push($this->comment, $comment);

        return $this;
    }

    public function addLabel($label)
    {
        if (is_null($this->labels)) {
            $this->labels = [];
        }

        array_push($this->labels, $label);

        return $this;
    }

    public function setIssueTypeName($name)
    {
        if (is_null($this->issuetype)) {
            $this->issuetype = new IssueType();
        }

        $this->issuetype->name = (string) $name;

        return $this;
    }

    public function setIssueTypeId($id)
    {
        if (is_null($this->issuetype)) {
            $this->issuetype = new IssueType();
        }

        $this->issuetype->id = (string) $id;

        return $this;
    }
}

