<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'preferred_barber_id',
        'preferred_service_id',
        'notes',
        'last_haircut_date',
        'hair_length',
        'hair_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_haircut_date' => 'date',
    ];

    /**
     * Get the customer that owns the preferences.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the preferred barber.
     */
    public function preferredBarber()
    {
        return $this->belongsTo('App\Modules\Barber\Models\Barber', 'preferred_barber_id');
    }

    /**
     * Get the preferred service.
     */
    public function preferredService()
    {
        return $this->belongsTo('App\Modules\Service\Models\Service', 'preferred_service_id');
    }
}
