<?php
/**
 * Created by PhpStorm.
 * User: tomasz.ptak
 * Date: 30.10.2018
 * Time: 10:26
 */

namespace Enis\SyliusDotpayPlugin\Action;


use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Enis\SyliusDotpayPlugin\Bridge\DotpayBridgeInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Reply\HttpPostRedirect;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;
    /**
     * @var DotpayBridgeInterface
     */
    private $dotpayBridge;

    /**
     * @param DotpayBridgeInterface $przelewy24Bridge
     */
    public function __construct(DotpayBridgeInterface $dotpayBridge)
    {
        $this->dotpayBridge = $dotpayBridge;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api): void
    {
        if (false === is_array($api)) {
            throw new UnsupportedApiException('Not supported.Expected to be set as array.');
        }
        $this->dotpayBridge->setAuthorizationData($api['shop_id'], $api['secret_key'], $api['environment']);
    }

    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     *
     * @return void
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = $request->getModel();
        if (isset($details['dotpay_status'])) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();

        $details['control'] = uniqid();

        $data = (array) $details;
        $data['api_version'] = 'dev';
        $data['type'] = DotpayBridgeInterface::BUTTON_TYPE_VISIBLE;
        $data['id'] = $this->dotpayBridge->getShopId();

        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $data['url'] = $token->getTargetUrl();
        $data['urlc'] = str_replace('localhost', '405b87a5.ngrok.io', $notifyToken->getTargetUrl());

        $data['chk'] = $this->dotpayBridge->generateChecksum($data);

        throw new HttpPostRedirect($this->dotpayBridge->getRequestUrl(), $data);
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}