<?php

namespace Codeplugtech\CreemPayments\Tests\Models;

use Codeplugtech\CreemPayments\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $guarded = [];

    protected $table = 'users';
}
