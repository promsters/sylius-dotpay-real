<?php declare(strict_types=1);

namespace SyliusDotpayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\OrderInterface;

final class ConvertPaymentAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * @var PaymentDescriptionProviderInterface
     */
    private $paymentDescriptionProvider;

    /**
     * @param PaymentDescriptionProviderInterface $paymentDescriptionProvider
     */
    public function __construct(PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
        $this->paymentDescriptionProvider = $paymentDescriptionProvider;
    }

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request): void
    {
        /** @var Convert $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $paymentData = $this->getPaymentData($payment);
        $customerData = $this->getCustomerData($order);

        $details = array_merge($paymentData, $customerData);
        $request->setResult($details);
    }


    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }

    private function getPaymentData(PaymentInterface $payment) : array
    {
        $paymentData = [];

        $paymentData['amount'] = $this->getParsedPrice($payment->getAmount());
        $paymentData['currency'] = $payment->getCurrencyCode();
        $paymentData['description'] = $this->paymentDescriptionProvider->getPaymentDescription($payment);

        return $paymentData;
    }

    private function getParsedPrice(?int $price) : string
    {
        return bcdiv((string) $price, '100', 2);
    }

    private function getCustomerData(OrderInterface $order) : array
    {
        $customerData = [];

        if( null !== $customer = $order->getCustomer() ) {
            $customerData['email'] = $customer->getEmail();
        }

        if( null !== $address = $order->getShippingAddress() ) {
            $customerData['firstname'] = $address->getFirstName();
            $customerData['lastname'] = $address->getLastName();
            $customerData['street'] = $address->getStreet();
            $customerData['city'] = $address->getCity();
            $customerData['postcode'] = $address->getPostcode();
            $customerData['phone'] = $address->getPhoneNumber();
            $customerData['country'] = $address->getCountryCode();
        }

        return $customerData;
    }
}
