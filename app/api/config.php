<?php

return [
    'AppId'     => '',
    'AppSercert'=> '',
    'default_filter' => 'trim,htmlspecialchars,strip_tags',
    'exec_customer_rows' => 5,
    'customer_key' => 'customer_guzz',
    'session' => [
            'prefix'  => 'weapp',
            'type'       => '',
            'auto_start' => true,
            'expire'     => 7200
        ]
];
