<?php
/**
 * Created by PhpStorm.
 * User: tomasz.ptak
 * Date: 30.10.2018
 * Time: 10:43
 */

namespace Enis\SyliusDotpayPlugin\Bridge;


class DotpayBridge implements DotpayBridgeInterface
{
    private $shopId;
    private $secretKey;
    private $environment;

    public function setAuthorizationData(
        string $shopId,
        string $secretKey,
        string $environment = self::SANDBOX_ENVIRONMENT
    ): void
    {
        $this->shopId = $shopId;
        $this->secretKey = $secretKey;
        $this->environment = $environment;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function generateChecksum($ParametersArray): string
    {
        $chk = $this->secretKey .
        (isset($ParametersArray['api_version']) ? $ParametersArray['api_version'] : null).
        (isset($ParametersArray['lang']) ? $ParametersArray['lang'] : null).
        (isset($ParametersArray['id']) ? $ParametersArray['id'] : null).
        (isset($ParametersArray['amount']) ? $ParametersArray['amount'] : null).
        (isset($ParametersArray['currency']) ? $ParametersArray['currency'] : null).
        (isset($ParametersArray['description']) ? $ParametersArray['description'] : null).
        (isset($ParametersArray['control']) ? $ParametersArray['control'] : null).
        (isset($ParametersArray['url']) ? $ParametersArray['url'] : null).
        (isset($ParametersArray['type']) ? $ParametersArray['type'] : null).
        (isset($ParametersArray['buttontext']) ? $ParametersArray['buttontext'] : null).
        (isset($ParametersArray['urlc']) ? $ParametersArray['urlc'] : null).
        (isset($ParametersArray['firstname']) ? $ParametersArray['firstname'] : null).
        (isset($ParametersArray['lastname']) ? $ParametersArray['lastname'] : null).
        (isset($ParametersArray['email']) ? $ParametersArray['email'] : null).
        (isset($ParametersArray['street']) ? $ParametersArray['street'] : null).
        (isset($ParametersArray['city']) ? $ParametersArray['city'] : null).
        (isset($ParametersArray['postcode']) ? $ParametersArray['postcode'] : null).
        (isset($ParametersArray['phone']) ? $ParametersArray['phone'] : null).
        (isset($ParametersArray['country']) ? $ParametersArray['country'] : null).
        (isset($ParametersArray['code']) ? $ParametersArray['code'] : null);

        return hash('sha256', $chk);
    }

    public function generateResponseChecksum($data): string
    {
        $sign=
            $this->secretKey.
            $data['id'].
            $data['operation_number'].
            $data['operation_type'].
            $data['operation_status'].
            $data['operation_amount'].
            $data['operation_currency'].
            (isset($data['operation_withdrawal_amount']) ? $data['operation_withdrawal_amount'] : null).
            (isset($data['operation_commission_amount']) ? $data['operation_commission_amount'] : null).
            (isset($data['is_completed']) ? $data['is_completed'] : null).
            $data['operation_original_amount'].
            $data['operation_original_currency'].
            $data['operation_datetime'].
            (isset($data['operation_related_number']) ? $data['operation_related_number'] : null).
            $data['control'].
            $data['description'].
            $data['email'].
            $data['p_info'].
            $data['p_email'].
            (isset($data['credit_card_issuer_identification_number']) ? $data['credit_card_issuer_identification_number'] : null).
            (isset($data['credit_card_masked_number']) ? $data['credit_card_masked_number'] : null) .
            (isset($data['credit_card_brand_codename']) ? $data['credit_card_brand_codename'] : null) .
            (isset($data['credit_card_brand_code']) ? $data['credit_card_brand_code'] : null).
            (isset($data['credit_card_id']) ? $data['credit_card_id'] : null).
            $data['channel'].
            (isset($data['channel_country']) ? $data['channel_country'] : null).
            (isset($data['geoip_country']) ? $data['geoip_country'] : null);

        return hash('sha256', $sign);
    }

    public function getRequestUrl(): string
    {
        return self::SANDBOX_ENVIRONMENT === $this->environment ?
            self::SANDBOX_HOST : self::PRODUCTION_HOST
        ;
    }
}