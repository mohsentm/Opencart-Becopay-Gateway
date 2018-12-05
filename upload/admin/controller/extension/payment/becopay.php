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
     * Empty value
     */
    const BECOPAY_EMPTY_VALUE = '';

    /**
     * Default method
     */
    const REQUEST_METHOD_TYPE = 'POST';

    /**
     * Becopay payment module name
     */
    const BECOPAY_PAYMENT = 'payment_becopay';

    /**
     * Success text
     */
    const BECOPAY_SUCCESS = 'text_success';

    /**
     * Becopay extension function
     */
    const BECOPAY_EXTENSION_PAYMENT = 'extension/payment/becopay';

    /**
     * Marketplace extension location
     */
    const BECOPAY_MARKETPLACE_EXTENSIONS = 'marketplace/extension';

    /**
     * Prefix used with an error code
     */
    const BECOPAY_ERROR_PREFIX = 'error_';

    /**
     * Callback url
     */
    const BECOPAY_CALLBACK_URL = 'index.php?route=extension/payment/becopay/callback&orderId=';

    /**
     * Token param
     */
    const BECOPAY_TOKEN_PARAM = 'user_token=';

    /**
     * Token value
     */
    const BECOPAY_TYPE_PAYMENT = '&type=payment';

    /**
     * Becopay header hook controller
     */
    const BECOPAY_HEADER_CONTROLER = 'extension/payment/becopay/becopay_header';

    /**
     * Becopay footer hook controller
     */
    const BECOPAY_FOOTER_CONTROLER = 'extension/payment/becopay/becopay_footer';

    /**
     * Becopay header hook event
     */
    const BECOPAY_EVENT_HEADER = 'catalog/view/common/header/before';

    /**
     * Becopay footer hook event
     */
    const BECOPAY_EVENT_FOOTER = 'catalog/view/common/footer/after';

    /**
     * Becopay header event name
     */
    const BECOPAY_EVENT_HEADER_NAME = 'becopay_header';

    /**
     * Becopay footer event name
     */
    const BECOPAY_EVENT_FOOTER_NAME = 'becopay_footer';

    /**
     * @var array
     */
    private $error = array();

    /**
     * @var array
     */
    private $errorFieldName = array(
        'warning',
        'mobile',
        'api_key',
        'api_url'
    );

    /**
     * @var array
     */
    private $breadcrumbFields = array(
        'text_home' => 'common/dashboard',
        'text_extension' => 'marketplace/extension',
        'heading_title' => 'extension/payment/becopay'
    );

    /**
     * @var array
     */
    private $becopayFieldsName = array(
        'payment_becopay_status',
        'payment_becopay_mobile',
        'payment_becopay_api_base_url',
        'payment_becopay_api_key',
        'payment_becopay_sort_order',
        'payment_becopay_total',
        'payment_becopay_title',
        'payment_becopay_merchant_currency',
        'payment_becopay_description',
        'payment_becopay_geo_zone_id',
        'payment_becopay_paid_status_id',
        'payment_becopay_pending_status_id',

    );

    public function index()
    {
        $this->load->language('extension/payment/becopay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] == $this::REQUEST_METHOD_TYPE
            && $this->validate()) {
            $this->model_setting_setting->editSetting(
                $this::BECOPAY_PAYMENT,
                $this->request->post
            );

            $this->session->data['success'] = $this->generateData(
                $this::BECOPAY_SUCCESS,
                $this::BECOPAY_EMPTY_VALUE
            );

            $this->response->redirect($this->generateData(
                $this::BECOPAY_EMPTY_VALUE,
                $this::BECOPAY_MARKETPLACE_EXTENSIONS
            ));
        }

        foreach ($this->getErrorFieldName() as $fieldName) {
            $dataName = $this::BECOPAY_ERROR_PREFIX . $fieldName;
            $data[$dataName] = $this->errorValue($fieldName);
        }

        foreach ($this->getBreadcrumbFields() as $key => $value) {
            $data['breadcrumbs'][] = $this->generateData($key, $value);
        }

        $data['action'] = $this->generateData(
            $this::BECOPAY_EMPTY_VALUE,
            $this::BECOPAY_EXTENSION_PAYMENT
        );
        $data['cancel'] = $this->generateData(
            $this::BECOPAY_EMPTY_VALUE,
            $this::BECOPAY_MARKETPLACE_EXTENSIONS
        );
        $data['callback'] = HTTP_CATALOG . $this::BECOPAY_CALLBACK_URL;

        foreach ($this->getBecopayFieldsName() as $fieldName) {
            $data[$fieldName] = $this->generateConfigField($fieldName);
        }


        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->document->addStyle('view/stylesheet/becopay/backoffice.css');


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this::BECOPAY_EXTENSION_PAYMENT, $data));
    }

    /**
     * Validate configuration form parameters
     *
     * @return bool
     */
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', $this::BECOPAY_EXTENSION_PAYMENT)) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if (!$this->request->post['payment_becopay_mobile']) {
            $this->error['mobile'] = $this->language->get('error_mobile');
        }

        if (!$this->request->post['payment_becopay_api_base_url']) {
            $this->error['api_url'] = $this->language->get('error_api_url');
        }

        if (!$this->request->post['payment_becopay_api_key']) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }

        try {
            //validate form with PaymentGateway
            new PaymentGateway(
                $this->request->post['payment_becopay_api_base_url'],
                $this->request->post['payment_becopay_api_key'],
                $this->request->post['payment_becopay_mobile']
            );
        } catch (\Exception $e) {
            $this->error['warning'] = $e->getMessage();
        }

        return !$this->error;
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    private function errorValue($fieldName)
    {
        if (isset($this->error[$fieldName])) {
            $data = $this->error[$fieldName];
        } else {
            $data = $this::BECOPAY_EMPTY_VALUE;
        }

        return $data;
    }

    /**
     * @param string $text
     * @param string $path
     *
     * @return array
     */
    private function generateData($text, $path)
    {
        if ($path == $this::BECOPAY_MARKETPLACE_EXTENSIONS) {
            $tokenParam = $this::BECOPAY_EMPTY_VALUE;
        } else {
            $tokenParam = $this::BECOPAY_TYPE_PAYMENT;
        }
        $token = $this::BECOPAY_TOKEN_PARAM . $this->session->data['user_token'] . $tokenParam;

        if (empty($text)) {
            $data = $this->url->link($path, $token, true);
        } elseif (empty($path)) {
            $data = $this->language->get($text);
        } else {
            $data = array(
                'text' => $this->language->get($text),
                'href' => $this->url->link($path, $token, true)
            );
        }

        return $data;
    }

    /**
     * @param string $fieldName
     *
     * @return mixed
     */
    private function generateConfigField($fieldName)
    {
        if (isset($this->request->post[$fieldName])) {
            $data = $this->request->post[$fieldName];
        } else {
            $data = $this->config->get($fieldName);
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getErrorFieldName()
    {
        return $this->errorFieldName;
    }

    /**
     * @return array
     */
    private function getBecopayFieldsName()
    {
        return $this->becopayFieldsName;
    }

    /**
     * @return array
     */
    public function getBreadcrumbFields()
    {
        return $this->breadcrumbFields;
    }

    public function install()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent(
            $this::BECOPAY_EVENT_HEADER_NAME,
            $this::BECOPAY_EVENT_HEADER,
            $this::BECOPAY_HEADER_CONTROLER);
        $this->model_setting_event->addEvent(
            $this::BECOPAY_EVENT_FOOTER_NAME,
            $this::BECOPAY_EVENT_FOOTER,
            $this::BECOPAY_FOOTER_CONTROLER);
    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode($this::BECOPAY_EVENT_HEADER_NAME);
        $this->model_setting_event->deleteEventByCode($this::BECOPAY_EVENT_FOOTER_NAME);
    }
}
