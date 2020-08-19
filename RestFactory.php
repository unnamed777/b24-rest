<?php
namespace nav\B24;

use Psr\Log\LoggerInterface;

class RestFactory {
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createOAuth(OauthCredentials\CredentialsInterface $credentials): OAuthRest
    {
        return new OAuthRest($this->logger, $credentials);
    }

    public function createWebHook(WebHookCredentials\CredentialsInterface $credentials): WebHookRest
    {
        return new WebHookRest($this->logger, $credentials);
    }
}