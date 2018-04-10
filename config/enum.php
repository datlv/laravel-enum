<?php
return [
    /**
     * Khai báo middleware cho Controller
     */
    'middleware' => ['web', 'role:sys.sadmin'],
    // Định nghĩa menus cho enum
    'menus'      => [
        'backend.sidebar.content.enum' => [
            'priority' => 1,
            'url'      => 'route:backend.enum.index',
            'label'    => 'trans:enum::common.enums',
            'icon'     => 'fa-list',
            'active'   => 'backend/enum*',
        ],
    ]
];
