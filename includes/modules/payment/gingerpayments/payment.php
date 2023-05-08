<?php

require_once __DIR__ . '/../../../../GXModules/GingerPayments/GingerPayments/autoload.php';

use Ginger\Ginger;

defined('GM_HTTP_SERVER') or define('GM_HTTP_SERVER', HTTP_SERVER);
/**
 * Class payment
 */
class payment
{
    public $code;
    public $description;
    public $enabled;
    public $client;
    public $endpoint = 'https://api.dev.gingerpayments.com/';

    public function __construct()
    {
        $this->client = Ginger::createClient($this->endpoint, '96a45a35c2314647b31851595bf35b40');
        $this->code = get_class($this);
        $this->description = $this->code.' description';
        $this->enabled = defined('MODULE_PAYMENT_' . strtoupper($this->code) . '_STATUS') && filter_var(constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_STATUS'), FILTER_VALIDATE_BOOLEAN);
    }

    public function keys()
    {
        return '';
    }

    /**
     * @return array|false
     */
    public function selection()
    {
        $selection = array(
            [
            'id' => 'credit-card',
            'module' => $this->title,
            'name' => $this->title,
            'description' => 'Credit Card',
            'logo'    => GM_HTTP_SERVER.'/images/icons/payment/'.$this->code.'png',
            'logo_alt'    => $this->title,
            ],
            [
                'id' => 'ideal',
                'module' => $this->title,
                'name' => $this->title,
                'description' => 'iDeal',
                'logo_url'    => GM_HTTP_SERVER.'/images/icons/payment/'.$this->code.'png',
                'logo_alt'    => $this->title,
            ]
        );

        return $selection;
    }

    public function _configuration() {
        $config = [
            'STATUS' => [
                'value' => 'True',
                'type' => 'switcher',
            ],
            'ALLOWED' => [
                'value' => '',
            ],
            'SORT_ORDER' => [
                'value' => '0',
            ],
            'ORDER_STATUS_ID' => [
                'value' => '1',
                'type' => 'order-status',
            ],
            'ALIAS' => [
                'value' => 'Ginger',
            ]
        ];
        return $config;
    }

    /**
     * Install the payment module
     */
    public function install() {
        $config = $this->_configuration();
        $sort_order = 0;
        foreach ($config as $key => $data) {
            $install_query = "insert into `gx_configurations` (`key`, `value`, `sort_order`, `type`, `last_modified`) "
                . "values ('configuration/MODULE_PAYMENT_" . strtoupper($this->code) . "_" . $key . "', '"
                . $data['value'] . "', '" . $sort_order . "', '" . addslashes($data['type'])
                . "', now())";
            xtc_db_query($install_query);
            $sort_order++;
        }
    }

    public function remove()
    {
        xtc_db_query("delete from `gx_configurations` where `key` in ('" . implode("', '", $this->keys()) . "')");
    }

    /**
     * Probably not used
     */
    public function update_status()
    {
        $t_order = $GLOBALS['order'];
    }

    public function refresh()
    {
    }

    /**
     * Not used
     *
     * @return boolean
     */
    public function before_process()
    {
        return false;
    }

    public function after_process()
    {
        $order = $GLOBALS['order'];
        global $insert_id;

        $transaction = $this->client->createOrder(
            [
                'merchant_order_id' => $insert_id.'',
                'currency' => $order->info['currency'],
                'amount' => $order->info['total']*100,
                'description' => 'Gambio order',
                'return_url' => xtc_href_link('checkout_success.php', $this->code),
                'transactions' => [
                    [
                        'payment_method' => $order->info['payment_method']
                    ]
                ]
            ]
        );

        xtc_redirect(current($transaction['transactions'])['payment_url']);
    }

    public function javascript_validation()
    {
        return false;
    }

}
