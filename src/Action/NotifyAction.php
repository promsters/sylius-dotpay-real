<?php declare(strict_types=1);

namespace SyliusDotpayPlugin\Action;

use SyliusDotpayPlugin\Bridge\DotpayBridge;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Payum\Core\Exception\InvalidArgumentException;

final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var DotpayBridge
     */
    protected $bridge;

    public function __construct(DotpayBridge $bridge)
    {
        $this->bridge = $bridge;
    }

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        /** @var Notify $request */
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
        $sign = $this->bridge->generateResponseChecksum($request->request);

        return $sign === $request->request['signature'];
    }
}
