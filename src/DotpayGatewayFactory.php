<?php

namespace SyliusDotpayPlugin;

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
    }
}
