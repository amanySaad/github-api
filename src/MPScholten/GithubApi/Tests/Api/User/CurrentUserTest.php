<?php


namespace MPScholten\GithubApi\Tests\Api\User;


use Guzzle\Http\QueryString;
use MPScholten\GithubApi\Api\Repository\Repository;
use MPScholten\GithubApi\Api\User\CurrentUser;
use MPScholten\GithubApi\Tests\AbstractTestCase;

class CurrentUserTest extends AbstractTestCase
{
    public function testAutomaticallyPopulates()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture_user.json')));

        $user = new CurrentUser($httpClient);
        $user->getId();
    }

    public function testLazyLoadingRepositories()
    {
        $httpClient = $this->createHttpClientMock();

        $user = new CurrentUser($httpClient);
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture_repositories.json')));

        $repositories = $user->getRepositories();
        $this->assertCount(1, $repositories);

        foreach ($repositories as $repository) {
            $this->assertInstanceOf(Repository::CLASS_NAME, $repository);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidRepositoryTypeOnGetRepositories()
    {
        $organization = new CurrentUser();
        $organization->getRepositories('this is invalid');
    }
}
