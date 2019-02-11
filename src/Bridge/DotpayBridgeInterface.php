<?php declare(strict_types=1);

namespace SyliusDotpayPlugin\Bridge;

interface DotpayBridgeInterface
{
    const SANDBOX_ENVIRONMENT = 1;
    const PRODUCTION_ENVIRONMENT = 2;

    const COMPLETED_STATUS = 'completed';
    const NEW_STATUS = 'new';
    const REJECTED_STATUS = 'rejected';
    const PENDING_STATUS = 'processing';

    public function __construct(string $environment, string $sandboxHost, string $prodHost, string $shopId, string $secretKey, string $apiVersion, int $buttonType);

    public function getShopId() : string;

    public function getSecretKey() : string;

    public function generateChecksum(array $data) : string;

    public function generateResponseChecksum(array $data) : string;

    public function getRequestUrl() : string;
}
