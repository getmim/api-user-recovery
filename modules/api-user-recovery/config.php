<?php

return [
    '__name' => 'api-user-recovery',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/api-user-recovery.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'modules/api-user-recovery' => ['install','update','remove'],
        'app/api-user-recovery' => ['install','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-user-recovery' => NULL
            ],
            [
                'api' => NULL
            ],
            [
                'lib-user' => NULL
            ],
            [
                'lib-otp' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'ApiUserRecovery\\Controller' => [
                'type' => 'file',
                'base' => 'app/api-user-recovery/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'api' => [
            'apiMeRecovery' => [
                'path' => [
                    'value' => '/me/recovery'
                ],
                'handler' => 'ApiUserRecovery\\Controller\\Recovery::recovery',
                'method' => 'POST'
            ],
            'apiMeRecoveryReset' => [
                'path' => [
                    'value' => '/me/recovery/reset/(:hash)',
                    'params'=> [
                        'hash' => 'any'
                    ]
                ],
                'handler' => 'ApiUserRecovery\\Controller\\Recovery::reset',
                'method' => 'PUT'
            ],
            'apiMeRecoveryResent' => [
                'path' => [
                    'value' => '/me/recovery/resent/(:user)/(:otp)',
                    'params'=> [
                        'user' => 'number',
                        'otp' => 'number'
                    ]
                ],
                'handler' => 'ApiUserRecovery\\Controller\\Recovery::resent',
                'method' => 'POST'
            ],
            'apiMeRecoveryVerify' => [
                'path' => [
                    'value' => '/me/recovery/verify/(:user)/(:code)',
                    'params' => [
                        'user' => 'number',
                        'code' => 'any'
                    ]
                ],
                'handler' => 'ApiUserRecovery\\Controller\\Recovery::verify',
                'method' => 'GET'
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'api.me.recovery' => [
                'identity' => [
                    'label' => 'Identity',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ]
            ],
            'api.me.recovery.reset' => [
                'password' => [
                    'label' => 'New Password',
                    'type' => 'password',
                    'rules' => [
                        'required' => true,
                        'empty' => false,
                        'length' => ['min' => 6]
                    ]
                ],
                're-password' => [
                    'label' => 'Retype Password',
                    'type' => 'password',
                    'rules' => [
                        'required' => true,
                        'empty' => false,
                        'equals_to' => 'password'
                    ]
                ]
            ],
            'api.me.recovery.verify' => [
                'code' => [
                    'label' => 'Code',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ]
            ],
        ]
    ]
];