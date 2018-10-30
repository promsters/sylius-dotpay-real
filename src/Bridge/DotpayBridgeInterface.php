<?php
/**
 * Created by PhpStorm.
 * User: tomasz.ptak
 * Date: 30.10.2018
 * Time: 09:47
 */

namespace Enis\SyliusDotpayPlugin\Bridge;


interface DotpayBridgeInterface
{
    const SANDBOX_ENVIRONMENT = 'sandbox';
    const PRODUCTION_ENVIRONMENT = 'production';

    const SANDBOX_HOST = 'https://ssl.dotpay.pl/test_payment/';
    const PRODUCTION_HOST = 'https://ssl.dotpay.pl/t2/';

    const COMPLETED_STATUS = 'completed';
    const NEW_STATUS = 'new';
    const REJECTED_STATUS = 'rejected';
    const PENDING_STATUS = 'processing';

    const BUTTON_TYPE_VISIBLE = 0;

    /**
     * @param string $shopId
     * @param string $secretKey
     * @param string $environment
     */
    public function setAuthorizationData(
        string $shopId,
        string $secretKey,
        string $environment = self::SANDBOX_ENVIRONMENT
    ): void;

    public function getShopId() : string;

    public function getSecretKey() : string;

    public function generateChecksum($data) : string;

    public function generateResponseChecksum($data) : string;

    public function getRequestUrl() : string;
}