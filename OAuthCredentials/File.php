<?php
namespace nav\B24\OAuthCredentials;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class File implements CredentialsInterface
{
    /** @var string */
    protected $file;

    /** @var array */
    protected $auth;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->file = $parameterBag->get('b24.storage_file');
        $this->auth = json_decode(@file_get_contents($this->file), true);

        if (!is_array($this->auth)) {
            $this->auth = [];
        }

        $this->auth['clientId'] = $parameterBag->get('b24.client_id');
        $this->auth['clientSecret'] = $parameterBag->get('b24.client_secret');
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

        file_put_contents($this->file, json_encode($newAuth));
        $this->auth = $newAuth;
    }

    public function refresh()
    {
    }
}