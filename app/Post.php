<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Post  extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $auditStrict = true;
    protected $auditInclude = [
        'post',
    ];

    protected $auditEvents = [
        'deleted',
        'restored',
        'created',
        'saved',
        'deleted',
        'updated'
    ];

    protected $fillable = [
        'post',
    ];

}
