<?php
namespace nav\B24\WebHookCredentials;

interface CredentialsInterface
{
    /**
     * @return string
     */
    public function getDomain(): string;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @return string
     */
    public function getUserId(): string;

    /**
     * @param string $domain
     * @param string|int $userId
     * @param string $token
     */
    public function set($domain, $userId, $token);
}
