<?php
namespace nav\B24\WebHookCredentials;

class MemoryCredentials implements CredentialsInterface
{
    protected $auth;

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

    public function set($domain, $userId, $token)
    {
        $this->auth = [
            'domain' => $domain,
            'userId' => $userId,
            'token' => $token,
        ];
    }
}