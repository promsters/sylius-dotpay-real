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
SyliusDotpayPlugin\SyliusDotpayPlugin::class => ['all' => true],
``````
3. Configure env variables
````
DOTPAY_SANDBOX_HOST=
DOTPAY_PRODUCTION_HOST=
DOTPAY_SHOP_ID=
DOTPAY_SECRET_KEY=
DOTPAY_API_VERSION=
DOTPAY_BUTTON_TYPE=
````
More information on both api version and button type can be found here:
http://www.dotpay.pl/dla-developerow/