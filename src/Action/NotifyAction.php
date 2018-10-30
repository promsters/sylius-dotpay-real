<?php
/**
 * Created by PhpStorm.
 * User: tomasz.ptak
 * Date: 30.10.2018
 * Time: 11:12
 */

namespace Enis\SyliusDotpayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Enis\SyliusDotpayPlugin\Bridge\DotpayBridgeInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Payum\Core\Exception\InvalidArgumentException;

final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
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
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = ArrayObject::ensureArrayObject($request->getModel());
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (!isset($httpRequest->request['control']) || $details['control'] !== $httpRequest->request['control']) {
            throw new NotFoundHttpException();
        }
        if (false === $this->verifyChecksum($httpRequest)) {
            throw new InvalidArgumentException("Invalid sign.");
        }

        $details['dotpay_order_id'] = $httpRequest->request['operation_number'];
        $details['dotpay_status'] = $httpRequest->request['operation_status'];
        $details['dotpay_amount'] = $httpRequest->request['operation_amount'];
        $details['dotpay_currency'] = $httpRequest->request['operation_currency'];

        throw new HttpResponse('OK', 200);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }

    /**
     * @param GetHttpRequest $request
     *
     * @return bool
     */
    private function verifyChecksum(GetHttpRequest $request): bool
    {
        $sign = $this->dotpayBridge->generateResponseChecksum($request->request);

        return $sign === $request->request['signature'];
    }
}