<?php
namespace nav\B24;

use Psr\Log\LoggerInterface;

class WebHookRest extends BaseRest {
    protected $baseUrl;
    public $debug = false;

    /** @var WebhookCredentials\CredentialsInterface */
    protected $credentials;

    public function __construct(LoggerInterface $logger, WebhookCredentials\CredentialsInterface $credentials)
    {
        parent::__construct($logger);

        $this->credentials = $credentials;
        $this->baseUrl = $this->createBaseUrl();
    }

    /**
     * @return string
     */
    protected function createBaseUrl()
    {
        return 'https://' . $this->credentials->getDomain() . '/rest/' . $this->credentials->getUserId() . '/' . $this->credentials->getToken() . '/';
    }

    protected function configureRequest($method, $data)
    {
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $this->baseUrl . $method,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ));
    }
}
