<?php
namespace nav\B24\OAuthCredentials;

interface CredentialsInterface
{
    /**
     * @return string
     */
    public function getClientId(): string;

    /**
     * @return string
     */
    public function getClientSecret(): string;

    /**
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * @return string
     */
    public function getRefreshToken(): string;

    /**
     * @return string
     */
    public function getExpiresIn(): string;

    /**
     * @return string
     */
    public function getDomain(): string;

    /**
     * @param array $data
     */
    public function set($data);
}
