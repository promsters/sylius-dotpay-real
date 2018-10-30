<?php
/**
 * Created by PhpStorm.
 * User: tomasz.ptak
 * Date: 30.10.2018
 * Time: 11:02
 */

namespace Enis\SyliusDotpayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Enis\SyliusDotpayPlugin\Bridge\DotpayBridgeInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Request\GetHttpRequest;

final class StatusAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
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
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $details = $payment->getDetails();
        $this->gateway->execute($httpRequest = new GetHttpRequest());


        if (false === isset($details['dotpay_status'])) {
            $request->markNew();
            return;
        }
        if (DotpayBridgeInterface::NEW_STATUS === $details['dotpay_status']) {
            $request->markNew();
            return;
        }
        if (DotpayBridgeInterface::COMPLETED_STATUS === $details['dotpay_status']) {
            $request->markCaptured();
            return;
        }
        if (DotpayBridgeInterface::REJECTED_STATUS === $details['dotpay_status']) {
            $request->markFailed();
            return;
        }
        if(strpos($details['dotpay_status'], DotpayBridgeInterface::PENDING_STATUS) !== FALSE) {
            $request->markPending();
            return;
        }
        $request->markUnknown();
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface
            ;
    }
}