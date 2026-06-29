<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class StaffUser extends Authenticatable
{
    protected $guard_name = 'web';
    use Notifiable, HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'employee_id',
        'role',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function hrmsEmployee()
    {
        return $this->hasOne(HrmsEmployee::class, 'id', 'employee_id');
    }
}
