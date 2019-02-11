<?php declare(strict_types=1);

namespace SyliusDotpayPlugin\Bridge;

class DotpayBridge implements DotpayBridgeInterface
{
    /**
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var int
     */
    private $environment;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    public $api_version;

    /**
     * @var int
     */
    public $button_type;

    public function __construct(string $environment, string $sandboxHost, string $prodHost, string $shopId, string $secretKey, string $apiVersion, int $buttonType)
    {
        $this->shopId = $shopId;
        $this->secretKey = $secretKey;
        $this->api_version = $apiVersion;
        $this->button_type = $buttonType;

        $this->mapEnvironmentAndHost($environment, ['sandbox_host' => $sandboxHost, 'production_host' => $prodHost]);
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    private function mapEnvironmentAndHost(string $env, array $config)
    {
        if( $env == "prod" ) {
            $this->environment = DotpayBridgeInterface::PRODUCTION_ENVIRONMENT;
            $this->host = $config['production_host'];
        }
        else {
            $this->environment = DotpayBridgeInterface::SANDBOX_ENVIRONMENT;
            $this->host = $config['sandbox_host'];
        }

    }

    public function generateChecksum(array $params): string
    {
        $chk = $this->secretKey .
        ($params['api_version'] ?? null).
        ($params['lang'] ?? null).
        ($params['id'] ?? null).
        ($params['amount'] ?? null).
        ($params['currency'] ?? null).
        ($params['description'] ?? null).
        ($params['control'] ?? null).
        ($params['url'] ?? null).
        ($params['type'] ?? null).
        ($params['buttontext'] ?? null).
        ($params['urlc'] ?? null).
        ($params['firstname'] ?? null).
        ($params['lastname'] ?? null).
        ($params['email'] ?? null).
        ($params['street'] ?? null).
        ($params['city'] ?? null).
        ($params['postcode'] ?? null).
        ($params['phone'] ?? null).
        ($params['country'] ?? null).
        ($params['code'] ?? null);

        return hash('sha256', $chk);
    }

    public function generateResponseChecksum(array $params): string
    {
        $sign=
            $this->secretKey.
            $params['id'].
            $params['operation_number'].
            $params['operation_type'].
            $params['operation_status'].
            $params['operation_amount'].
            $params['operation_currency'].
            ($params['operation_withdrawal_amount'] ?? null).
            ($params['operation_commission_amount'] ?? null).
            ($params['is_completed'] ?? null).
            $params['operation_original_amount'].
            $params['operation_original_currency'].
            $params['operation_datetime'].
            ($params['operation_related_number'] ?? null).
            $params['control'].
            $params['description'].
            $params['email'].
            $params['p_info'].
            $params['p_email'].
            ($params['credit_card_issuer_identification_number'] ?? null).
            ($params['credit_card_masked_number'] ?? null) .
            ($params['credit_card_brand_codename'] ?? null) .
            ($params['credit_card_brand_code'] ?? null).
            ($params['credit_card_id'] ?? null).
            $params['channel'].
            ($params['channel_country'] ?? null).
            ($params['geoip_country'] ?? null);

        return hash('sha256', $sign);
    }

    public function getRequestUrl(): string
    {
        return $this->host;
    }
}
