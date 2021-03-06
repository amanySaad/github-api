<?php

namespace MPScholten\GitHubApi;

use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Client;
use Guzzle\Plugin\Cache\CachePlugin;
use MPScholten\GitHubApi\Api\Repository\Repository;
use MPScholten\GitHubApi\Api\Search\Search;
use MPScholten\GitHubApi\Api\User\CurrentUser;
use MPScholten\GitHubApi\Api\User\User;
use MPScholten\GitHubApi\Auth\AuthenticationMethodInterface;
use MPScholten\GitHubApi\Auth\NullAuthenticationMethod;
use MPScholten\GitHubApi\Auth\OAuth;
use MPScholten\GitHubApi\Exception\GithubException;

class Github
{
    private $client;

    private $currentUser;
    private $search;

    /**
     * @see Github::create()
     *
     * @param Client $client The http client
     * @param AuthenticationMethodInterface $authenticationMethod
     * @param string $endpoint The GitHub-API's endpoint, in most cases https://api.github.com/
     */
    public function __construct(Client $client, AuthenticationMethodInterface $authenticationMethod, $endpoint)
    {
        $this->client = $client;
        $this->client->addSubscriber($authenticationMethod);
        $this->client->setBaseUrl($endpoint);
        $this->client->setDefaultOption('headers/Accept', 'application/vnd.github.v3');
    }

    /**
     * This is a easy-to-use facade for using this class. In case you need more customization just create the instace
     * via the constructor.
     *
     * @var AuthenticationMethodInterface|string|null $authenticationMethod If $authenticationMethod is a string,
     *                                                                 the string will be used as a token for
     *                                                                 the OAuth login. If null no authentication will
     *                                                                 be used.
     *
     * @var null|string|false $cachePath If $cachePath is null we will use in-memory caching, if it's a string we will
     *                                   use file caching. In case it's false we disable any caching
     *
     * @return Github
     */
    public static function create($authenticationMethod = null, $cachePath = null)
    {
        $client = new Client();

        if ($cachePath === null) {
            $cachePlugin = new CachePlugin();
            $client->addSubscriber($cachePlugin);
        } elseif (is_string($cachePath)) {
            $cachePlugin = new CachePlugin(new DoctrineCacheAdapter(new FilesystemCache($cachePath)));
            $client->addSubscriber($cachePlugin);
        } elseif ($cachePath === false) {
            // disable cache if false
        }

        if (is_string($authenticationMethod)) {
            $authenticationMethod = new OAuth($authenticationMethod);
        } elseif ($authenticationMethod === null) {
            $authenticationMethod = new NullAuthenticationMethod();
        }

        return new Github($client, $authenticationMethod, 'https://api.github.com/');
    }

    /**
     * @return CurrentUser Returns the current logged-in user in case you are using oauth
     */
    public function getCurrentUser()
    {
        if ($this->currentUser === null) {
            $this->currentUser = new CurrentUser($this->client);
        }

        return $this->currentUser;
    }

    /**
     * @return Search
     */
    public function getSearch()
    {
        if ($this->search === null) {
            $this->search = new Search($this->client);
        }

        return $this->search;
    }

    /**
     * @param $login string The login name of the user, e.g. "octocat"
     * @throws Exception\GithubException In case the user was not found
     * @return Search
     */
    public function getUser($login)
    {
        $user = new User($this->client);
        $user->populate(['login' => $login]);

        try {
            $user->getId();
        } catch (GithubException $e) {
            throw new GithubException(sprintf('User %s was not found.', $login), 0, $e);
        }

        return $user;
    }

    /**
     * @param $owner string The login name of the repository owner, e.g. "octocat"
     * @param $name string The repository name, e.g. "Hello-World"
     * @throws Exception\GithubException In case the repository was not found
     * @return Repository
     */
    public function getRepository($owner, $name)
    {
        $repository = new Repository($this->client);
        $repository->populate(['owner' => ['login' => $owner], 'name' => $name]);

        try {
            $repository->getId();
        } catch (GithubException $e) {
            throw new GithubException(sprintf('Repository %s was not found.', $name), 0, $e);
        }

        return $repository;
    }
}
