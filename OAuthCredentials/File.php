<?php
namespace nav\B24\OAuthCredentials;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class File implements CredentialsInterface
{
    /** @var string */
    protected $filepath;

    /** @var array */
    protected $auth;

    public function __construct($params)
    {
        if (empty($params['filepath'])) {
            throw new \InvalidArgumentException('"filepath" is empty');
        }
        
        if (empty($params['clientId'])) {
            throw new \InvalidArgumentException('"clientId" is empty');
        }
        
        if (empty($params['clientSecret'])) {
            throw new \InvalidArgumentException('"clientSecret" is empty');
        }
        
        $this->filepath = $params['filepath'];
        $this->auth = json_decode(@file_get_contents($this->filepath), true);

        if (!is_array($this->auth)) {
            $this->auth = [];
        }

        $this->auth['clientId'] = $params['clientId'];
        $this->auth['clientSecret'] = $params['clientSecret'];
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
        $newAuth = [
            //'clientId' => $this->auth['clientId'],
            'domain' => $data['domain'],
            'accessToken' => $data['access_token'],
            'refreshToken' => $data['refresh_token'],
            'expiresAt' => time() + $data['expires_in'],
        ];

        file_put_contents($this->filepath, json_encode($newAuth));
        $this->auth = $newAuth;
    }

    public function refresh()
    {
    }
}