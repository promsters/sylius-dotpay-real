<?php declare(strict_types=1);

namespace SyliusDotpayPlugin\Action;

use SyliusDotpayPlugin\Bridge\DotpayBridge;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Reply\HttpPostRedirect;

final class CaptureAction implements ActionInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait, GenericTokenFactoryAwareTrait;

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
     * @param Capture $request
     */
    public function execute($request): void
    {
        /** @var Capture $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = $request->getModel();
        if (isset($details['dotpay_status'])) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();

        $details['control'] = uniqid();

        $data = (array) $details;

        $data['api_version'] = $this->bridge->api_version;
        $data['type'] = $this->bridge->button_type;
        $data['id'] = $this->bridge->getShopId();

        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $data['url'] = $token->getTargetUrl();
        $data['urlc'] = $notifyToken->getTargetUrl();

        $data['chk'] = $this->bridge->generateChecksum($data);

        throw new HttpPostRedirect($this->bridge->getRequestUrl(), $data);
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
