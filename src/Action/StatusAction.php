<?php declare(strict_types=1);

namespace SyliusDotpayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Enis\Component\Payment\Bridge\DotpayBridgeInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Request\GetHttpRequest;

final class StatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        /** @var GetStatusInterface $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $details = $payment->getDetails();
        $this->gateway->execute($httpRequest = new GetHttpRequest());


        if (!isset($details['dotpay_status'])) {
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
            $request->getModel() instanceof PaymentInterface;
    }
}
