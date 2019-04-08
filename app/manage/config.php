<?php

return [
    'default_filter' => 'trim,htmlspecialchars,strip_tags',
    'session' => [
            'prefix'  => 'manage',
            'type'       => '',
            'auto_start' => true,
            'expire'     => 7200
        ],

    'paginate' => [
        'type'      => 'Layui',
        'var_page'  => 'page',
        'list_rows' => 15,
        'newstyle'  => true
    ],
];