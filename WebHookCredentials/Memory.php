<?php
namespace nav\B24\WebHookCredentials;

class Memory implements CredentialsInterface
{
    protected $auth;
    
    public function __construct($params = [])
    {
        if (isset($params['domain']) && isset($params['userId']) && isset($params['token'])) {
            $this->set($params);
        }
    }

    public function getUserId(): string
    {
        return $this->auth['userId'];
    }

    public function getToken(): string
    {
        return $this->auth['token'];
    }

    public function getDomain(): string
    {
        return $this->auth['domain'];
    }

    public function set($data)
    {
        if (empty($data['domain'])) {
            throw new \InvalidArgumentException('"domain" is empty');
        }
        
        if (empty($data['userId'])) {
            throw new \InvalidArgumentException('"userId" is empty');
        }
        
        if (empty($data['token'])) {
            throw new \InvalidArgumentException('"token" is empty');
        }
        
        $this->auth = [
            'domain' => $data['domain'],
            'userId' => $data['userId'],
            'token' => $data['token'],
        ];
    }
}