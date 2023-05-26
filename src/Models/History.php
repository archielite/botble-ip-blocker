<?php

namespace ArchiElite\IpBlocker\Models;

use Botble\Base\Models\BaseModel;

class History extends BaseModel
{
    protected $table = 'histories';

    protected $fillable = [
        'ip_address',
        'count_requests',
    ];
}
