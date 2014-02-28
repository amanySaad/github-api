<?php

namespace MPScholten\GithubApi\Api\User;

use MPScholten\GithubApi\Api\Repository\Repository;

/**
 * This class is mostly the same as User, the only difference is that it also loads some
 * private data (e.g. private repositories) if you're allowed to do so.
 *
 * @link http://developer.github.com/v3/users/#update-the-authenticated-user
 */
class CurrentUser extends User
{
    protected $repositories = [];
    protected $organizations;

    /**
     * @link http://developer.github.com/v3/users/#get-the-authenticated-user
     *
     * Loads the current authenticated user
     */
    protected function load()
    {
        $this->populate($this->get('user'));
    }

    /**
     * @link http://developer.github.com/v3/repos/#list-your-repositories
     *
     * @param string $type Can be one of all, owner, public, private, member. Default: all
     * @throws \InvalidArgumentException In case the $type is not valid
     * @return Repository[]
     */
    public function getRepositories($type = 'all')
    {
        $validTypes = ['all', 'owner', 'public', 'private', 'member'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid type, expected one of "%s"',
                implode(', ', $validTypes)
            ));
        }

        if (!isset($this->repositories[$type])) {
            $this->repositories[$type] = $this->loadRepositories($type);
        }

        return $this->repositories[$type];
    }

    protected function loadRepositories($type)
    {
        $url = 'user/repos';
        return $this->createPaginationIterator($url, Repository::CLASS_NAME, ['type' => $type]);
    }
}