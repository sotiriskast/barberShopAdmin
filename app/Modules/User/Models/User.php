<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Define the possible roles for a user.
     */
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_BARBER = 'barber';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SHOP_OWNER = 'shop_owner';

    /**
     * Get all available roles.
     *
     * @return array
     */
    public static function roles(): array
    {
        return [
            self::ROLE_CUSTOMER,
            self::ROLE_BARBER,
            self::ROLE_ADMIN,
            self::ROLE_SHOP_OWNER,
        ];
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if the user is a shop owner.
     *
     * @return bool
     */
    public function isShopOwner(): bool
    {
        return $this->role === self::ROLE_SHOP_OWNER;
    }

    /**
     * Check if the user is a barber.
     *
     * @return bool
     */
    public function isBarber(): bool
    {
        return $this->role === self::ROLE_BARBER;
    }

    /**
     * Check if the user is a customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Relationship: User has one barber profile if they are a barber.
     */
    public function barber()
    {
        return $this->hasOne('App\Modules\Barber\Models\Barber', 'user_id');
    }

    /**
     * Relationship: User has one customer preferences if they are a customer.
     */
    public function customerPreferences()
    {
        return $this->hasOne('App\Modules\User\Models\CustomerPreference', 'customer_id');
    }

    /**
     * Relationship: User owns many shops if they are a shop owner.
     */
    public function shops()
    {
        return $this->hasMany('App\Modules\Shop\Models\Shop', 'owner_id');
    }

    /**
     * Relationship: User has many appointments as a customer.
     */
    public function appointments()
    {
        return $this->hasMany('App\Modules\Appointment\Models\Appointment', 'customer_id');
    }

    /**
     * Relationship: User has many reviews if they are a customer.
     */
    public function reviews()
    {
        return $this->hasMany('App\Modules\Review\Models\Review', 'customer_id');
    }
}
