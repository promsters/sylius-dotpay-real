<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Dotpay Gateway Plugin</h1>

## Installation

1. Install through composer
``````
composer require enis-promsters/plugin-dotpay
``````
2. Add to bundles.php
``````
Enis\SyliusDotpayPlugin\SyliusDotpayPlugin::class => ['all' => true],
``````
3. Configure new payment method in admin panel