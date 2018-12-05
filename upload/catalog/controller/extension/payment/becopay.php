<?php
/**
 * 2018 Becopay
 *
 * @author    Becopay <io@becopay.com>
 * @copyright 2018 Becopay
 */

require_once DIR_SYSTEM . 'library/becopay/autoload.php';

use Becopay\PaymentGateway;

/**
 * Class ControllerExtensionPaymentBecopay
 */
class ControllerExtensionPaymentBecopay extends Controller
{

    /**
     * Certificate
     */
    const SSL = 'SSL';

    /**
     * Empty value
     */
    const EMPTY_CODE = '';

    /**
     * New int value
     */
    const NEW_VALUE = 1;

    /**
     * Becopay mobile config name
     */
    const CONFIG_BECOPAY_MOBILE = 'payment_becopay_mobile';

    /**
     * Becopay api base url config name
     */
    const CONFIG_BECOPAY_API_BASE_URL = 'payment_becopay_api_base_url';

    /**
     * Becopay api key config name
     */
    const CONFIG_BECOPAY_API_KEY = 'payment_becopay_api_key';

    /**
     * Becopay pending order config name
     */
    const CONFIG_PENDING_STATUS = 'payment_becopay_pending_status_id';

    /**
     * Becopay paid order config name
     */
    const CONFIG_PAID_STATUS = 'payment_becopay_paid_status_id';

    /**
     * Becopay merchant currency config name
     */
    const CONFIG_MERCHANT_CURRENCY = 'payment_becopay_merchant_currency';

    /**
     * Becopay merchant currency config name
     */
    const DEFAULT_MERCHANT_CURRENCY = 'IRR';

    /**
     * Display errors setter
     */
    const DISPLAY_ERROR = 'display_errors';

    /**
     * Template config name
     */
    const TEMPLATE_NAME = 'config_template';

    /**
     * Becopay template function
     */
    const BECOPAY_TEMPLATE = '/template/extension/payment/becopay';

    /**
     * Becopay function
     */
    const BECOPAY_EXTENSION = 'extension/payment/becopay';

    /**
     * cancelurl
     */
    const CANCEL_URL = 'index.php?route=extension/payment/becopay/cancel';

    /**
     * callbackurl
     */
    const CALLBACK_URL = 'index.php?route=extension/payment/becopay/callback';

    /**
     * Becopay confirm order
     */
    const EXTENSION_CONFIRM = 'extension/payment/becopay/confirm';

    /**
     * Guest
     */
    const BECOPAY_GUEST = 'checkout/guest/confirm';

    /**
     * Checkout page
     */
    const CHECKOUT_PAYMENT = 'index.php?route=checkout/payment';

    /**
     * Guest checkout
     */
    const CHECKOUT_GUEST = 'index.php?route=checkout/guest';


    /**
     * @return mixed
     */
    public function index()
    {

        $this->load->language($this::BECOPAY_EXTENSION);

        $data['action'] = $this->url->link($this::EXTENSION_CONFIRM, $this::EMPTY_CODE, $this::SSL);

        if ($this->request->get['route'] != $this::BECOPAY_GUEST) {
            $data['back'] = HTTPS_SERVER . $this::CHECKOUT_PAYMENT;
        } else {
            $data['back'] = HTTPS_SERVER . $this::CHECKOUT_GUEST;
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get($this::TEMPLATE_NAME) . $this::BECOPAY_TEMPLATE)) {
            return $this->load->view($this->config->get($this::TEMPLATE_NAME) . $this::BECOPAY_TEMPLATE, $data);
        } else {
            return $this->load->view($this::BECOPAY_EXTENSION, $data);
        }
    }

    /**
     * Redirect here for create invoice
     * then redirect customer to payment gateway
     */
    public function confirm()
    {
        $this->load->language($this::BECOPAY_EXTENSION);

        error_reporting(E_ALL);
        ini_set($this::DISPLAY_ERROR, $this::NEW_VALUE);

        $this->load->model('checkout/order');

        $orderID = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder($orderID);

        if (!isset($_SERVER['HTTPS'])) {
            $_SERVER['HTTPS'] = false;
        }

        $mobile = $this->config->get($this::CONFIG_BECOPAY_MOBILE);
        $apiBaseUrl = $this->config->get($this::CONFIG_BECOPAY_API_BASE_URL);
        $apiKey = $this->config->get($this::CONFIG_BECOPAY_API_KEY);
        $merchantCurrency = $this->config->get($this::CONFIG_MERCHANT_CURRENCY) ?: $this::DEFAULT_MERCHANT_CURRENCY;

        $description = array(
            'orderId:' . $order['order_id'],
            'amount:' . $this->getAmountInCents($order['total'], $order['currency_code']) . ' ' . $order['currency_code'],
            'customer email:' . $order['email']
        );

        try {
            $gateway = new PaymentGateway(
                $apiBaseUrl,
                $apiKey,
                $mobile
            );

            $invoice = $gateway->create(
                $order['order_id'],
                $this->getAmountInCents($order['total'], $order['currency_code']),
                implode($description, ', '),
                $order['currency_code'],
                $merchantCurrency
            );
            if ($invoice) {

                //validate the invoice response
                if (
                    $order['currency_code'] != $invoice->payerCur ||
                    $this->getAmountInCents($order['total'], $order['currency_code']) != $invoice->payerAmount ||
                    $merchantCurrency != $invoice->merchantCur
                )
                    exit($this->language->get('invoice_invalid_response'));


                $this->model_checkout_order->addOrderHistory(
                    $orderID,
                    $this->config->get($this::CONFIG_PENDING_STATUS),
                    $this->language->get('order_history_create') . '"' . $invoice->id . '", ' .
                    $this->language->get('order_merchant_receive') . '"' . $invoice->merchantAmount . ' ' . $invoice->merchantCur . '"'
                );

                $this->response->redirect($invoice->gatewayUrl);
            } else
                exit($gateway->error);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * If payment is not success redirect here
     */
    public function cancel()
    {
        $this->response->redirect($this->url->link('checkout/cart', $this::EMPTY_CODE, true));
    }

    /**
     * Call this function on gateway callback
     */
    public function callback()
    {
        if (!isset($_REQUEST['orderId']))
            exit('Invalid request');

        $pendingStatus = $this->config->get($this::CONFIG_PENDING_STATUS);

        $orderId = $_REQUEST['orderId'];

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($orderId);

        if (empty($order) || $order['order_status_id'] != $pendingStatus) {
            $this->response->redirect($this::CANCEL_URL);
            die;
        }

        $mobile = $this->config->get($this::CONFIG_BECOPAY_MOBILE);
        $apiBaseUrl = $this->config->get($this::CONFIG_BECOPAY_API_BASE_URL);
        $apiKey = $this->config->get($this::CONFIG_BECOPAY_API_KEY);

        try {
            $gateway = new PaymentGateway(
                $apiBaseUrl,
                $apiKey,
                $mobile
            );

            $invoice = $gateway->checkByOrderId($orderId);
            if ($invoice) {
                if ($invoice->status == 'success') {

                    $price = $this->getAmountInCents($order['total'], $order['currency_code']);
                    if ($invoice->payerAmount != $price) {
                        throw new Exception(
                            'Wrong pay amount: ' . $invoice->payerAmount
                            . ', expected: ' . $price
                        );
                    }

                    $paidOrder = $this->config->get($this::CONFIG_PAID_STATUS);

                    $this->load->language($this::BECOPAY_EXTENSION);
                    $this->model_checkout_order->addOrderHistory($orderId, $paidOrder,
                        $this->language->get('order_history_paid') . $invoice->id . '", ' .
                        $this->language->get('order_merchant_receive') . '"' . $invoice->merchantAmount . ' ' . $invoice->merchantCur . '"',
                        true);

                    $this->response->redirect($this->url->link('checkout/success', $this::EMPTY_CODE, true));

                } else
                    $this->response->redirect($this::CANCEL_URL);

            } else
                exit($gateway->error);

        } catch (Exception $e) {
            exit(get_class($e) . ': ' . $e->getMessage());
        }
    }

    /**
     * @param string $currency
     *
     * @return double
     */
    private function getRate($currency)
    {
        return $this->currency->getvalue($currency);
    }

    /**
     * @param double $total
     * @param string $currency currency code
     *
     * @return int
     */
    private function getAmountInCents($total, $currency)
    {
        return round($total * $this->getRate($currency), 2);
    }

}
