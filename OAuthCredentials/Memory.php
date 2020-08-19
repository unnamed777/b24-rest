<?php
namespace nav\B24\OAuthCredentials;

class Memory implements CredentialsInterface
{
    /** @var string */
    protected $file;

    /** @var array */
    protected $auth = [
        'domain' => null,
        'clientId' => null,
        'clientSecret' => null,
        'refreshToken' => null,
        'expiresIn' => null,
    ];

    public function __construct($params = [])
    {
        if (isset($params['domain']) && isset($params['access_token']) && isset($params['expires_in'])) {
            $this->set($params);
        }
    }

    public function getClientId(): string
    {
        return $this->auth['clientId'];
    }

    public function getClientSecret(): string
    {
        return $this->auth['clientSecret'];
    }

    public function getAccessToken(): string
    {
        return $this->auth['accessToken'];
    }

    public function getRefreshToken(): string
    {
        return $this->auth['refreshToken'];
    }

    public function getExpiresIn(): string
    {
        return $this->auth['expiresIn'];
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

        if (empty($data['access_token'])) {
            throw new \InvalidArgumentException('"access_token" is empty');
        }

        if (empty($data['expires_in'])) {
            throw new \InvalidArgumentException('"expires_in" is empty');
        }
        
        $newAuth = [
            //'clientId' => $this->auth['clientId'],
            'domain' => $data['domain'],
            'accessToken' => $data['access_token'],
            'refreshToken' => $data['refresh_token'] ?? null,
            'expiresAt' => time() + $data['expires_in'],
        ];

        $this->auth = $newAuth;
    }
}