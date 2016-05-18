<?php

namespace JiraRestApi\Issue;

class IssueType implements \JsonSerializable
{
    /**
     * @var string
     */
    public $self;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $iconUrl;

    /**
     * @var string|null
     */
    public $name;
    
    /**
     * @var bool
     */
    public $subtask;

    /**
     * @var int|null
     */
    public $avatarId;

    public function jsonSerialize()
    {
        return array_filter(get_object_vars($this));
    }
}
