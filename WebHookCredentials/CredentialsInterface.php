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
     * @param array $data
     */
    public function set($data);
}
