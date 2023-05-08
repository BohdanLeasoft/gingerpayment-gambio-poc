<?php

require_once __DIR__ . '/gingerpayments/payment.php';

/**
 * Class gingerpayments
 */
class gingerpayments extends payment
{
    const  GUEST_STATUS_ID = '1';
    public $title = 'Ginger Payments';

    public function __construct()
    {
        parent::__construct();
    }
}
MainFactory::load_origin_class('gingerpayments');