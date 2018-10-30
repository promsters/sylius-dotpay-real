<?php

namespace Enis\SyliusDotpayPlugin;

use Enis\SyliusDotpayPlugin\Bridge\DotpayBridgeInterface;
use Payum\Core\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;

final class DotpayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'dotpay',
            'payum.factory_title' => 'Dotpay',
        ]);
        if (false === (bool)$config['payum.api']) {
            $config['payum.default_options'] = [
                'shop_id' => null,
                'secret_key' => null,
                'environment' => DotpayBridgeInterface::SANDBOX_ENVIRONMENT,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'shop_id',
                'secret_key',
            ];
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                return [
                    'shop_id' => $config['shop_id'],
                    'secret_key' => $config['secret_key'],
                    'environment' => $config['environment'],
                    'payum.http_client' => $config['payum.http_client'],
                ];
            };
        }
    }
}