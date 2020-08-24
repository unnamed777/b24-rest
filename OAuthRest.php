<?php
namespace nav\B24;

use Psr\Log\LoggerInterface;

class OAuthRest extends BaseRest {
    /** @var string */
    protected $clientId;

    /** @var string */
    protected $clientSecret;

    /** @var string */
    protected $domain;

    /** @var string */
    protected $accessToken;

    /** @var OAuthCredentials\CredentialsInterface */
    protected $credentials;

    public function __construct(LoggerInterface $logger, OAuthCredentials\CredentialsInterface $credentials)
    {
        parent::__construct($logger);
        $this->credentials = $credentials;
    }

    protected function configureRequest($method, $data)
    {
        $url = 'https://' . $this->credentials->getDomain() . '/rest/' . $method . '.json';
        $data['access_token'] = $this->credentials->getAccessToken();

        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);
    }

    public function refreshToken()
    {
        $curl = curl_init();

        $url = 'https://oauth.bitrix.info/oauth/token/?' . http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $this->credentials->getClientId(),
            'client_secret' => $this->credentials->getClientSecret(),
            'refresh_token' => $this->credentials->getRefreshToken(),
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
        ));

        $result = json_decode(curl_exec($curl), true);

        if (!empty($result['error'])) {
            throw new \Exception('[' . $result['error'] . '] ' . $result['error_description']);
        }

        preg_match('#://([^/]*)/#si', $result['client_endpoint'], $matches);
        $result['domain'] = $matches[1];

        $this->credentials->set($result);

        return true;
    }

    /**
     * @return OAuthCredentials\CredentialsInterface
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param string $domain
     * @param string $applicationId
     * @param string $state
     */
    public static function redirectToAuth($domain, $applicationId, $state = null)
    {
        header('Location: https://' . $domain . '/oauth/authorize/?' . http_build_query([
            'client_id' => $applicationId,
            'state' => $state,
        ]));
        exit;
    }

    /**
     * @param string $applicationId
     * @param string $applicationSecret
     * @param string $code
     */
    public function getFirstAccessToken($applicationId, $applicationSecret, $code)
    {
        $url = 'https://oauth.bitrix.info/oauth/token/?' . http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $applicationId,
            'client_secret' => $applicationSecret,
            'code' => $code,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
        ));

        $result = json_decode(curl_exec($curl), true);
        
        if (!empty($result['error'])) {
            throw new \Exception('[' . $result['error'] . '] ' . ($result['error_description'] ?? ''));
        }
        
        return $result;
    }
}
