<?php

return [
    
    /*
    |------------------------------------------------------------------
    | 身份验证默认值
    |------------------------------------------------------------------
    |
    | 该选项控制您的应用程序的默认身份验证“警卫”和修改密码选项。
    |
    | 您可以根据需求修改当前默认值，但当前默认值是大多数应用程序的完美开始。
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    
    /*
     |------------------------------------------------------------------
     | 身份验证看守
     |------------------------------------------------------------------
     |
     | 接下来，您可以为您的应用程序定义所有用户类型验证看守。
     | 当然，这里已经为您定义了一个很好的默认配置
     | 默认配置使用的是会话存储和Eloquent用户提供者。
     |
     | 所有认证驱动程序都有用户提供程序 这定义了如何
     | 用户实际上从您的数据库或其他存储中检索出来
     | 用于应用程序保留用户数据的机制。
     |
     | 支持：“session”，“token”
     |
     */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],
    
    /*
    |------------------------------------------------------------------
    | 用户提供者
    |------------------------------------------------------------------
    |
    | 所有认证驱动程序都需要一个用户提供者。
    | 这定义了是通过数据库还是其他存储中检索出用户信息
    | 用于应用程序保留用户数据的机制。
    |
    | 如果您有多个用户表或模型，则可以配置多个
    | 代表每个model/table的来源。 这些来源可能会
    | 被分配给你已经定义的任何额外的认证警卫。
    |
    | 支持: "database", "eloquent"
    |
    */
    
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],
    
    /*
    |------------------------------------------------------------------
    | 重置密码
    |------------------------------------------------------------------
    |
    | 如果应用程序中有多个用户表或模型，
    | 并且希望根据特定的用户类型分别设置密码重置，
    | 可以指定多个密码重置配置。
    |
    | expire是token的有效期，单位是分钟
    | 这个安全功能使token有更少的时间被猜到
    | 您可以根据需要进行更改。
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
