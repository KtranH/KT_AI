<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * Tên các cookie cần không được mã hóa
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
