<?php


namespace MPScholten\GithubApi\Api\Organization;


use MPScholten\GithubApi\Api\AbstractApi;

class Organization extends AbstractApi
{
    // attributes
    private $id;
    private $login;
    private $name;
    private $email;
    private $avatarUrl;

    // urls
    private $url;
    private $htmlUrl;

    public function populate(array $data)
    {
        $this->id = $data['id'];
        $this->login = $data['login'];
        $this->url = $data['url'];
        $this->avatarUrl = $data['avatar_url'];

        // because if we call /users/{user}/orgs we only get the 3 attributes above, we need to populate the other attributes only if the name is given
        if (isset($data['name'])) {
            $this->name = $data['name'];
            $this->email = $data['email'];
        }
    }

    protected function load()
    {
        $this->populate($this->get($this->url));
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getName()
    {
        if ($this->name === null) {
            $this->load();
        }

        return $this->name;
    }

    public function getEmail()
    {
        if ($this->email === null) {
            $this->load();
        }

        return $this->email;
    }

    public function getId()
    {
        return $this->id;
    }

    private function getHtmlUrl()
    {
        if ($this->htmlUrl === null) {
            $this->load();
        }

        return $this->htmlUrl;
    }

    public function getUrl($type = 'html')
    {
        switch ($type) {
            case 'html':
                return $this->getHtmlUrl();
            case 'api':
                return $this->url;
        }

        throw new \InvalidArgumentException(vsprintf('Invalid url type "%s", expected one of "%s"', $type, implode(', ', ['html', 'api'])));
    }

    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }


}