<?php

namespace App\Modules\Barber\Models;

use App\Modules\Appointment\Models\Appointment;
use App\Modules\Service\Models\Service;
use App\Modules\Shop\Models\Shop;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barber extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'shop_id',
        'title',
        'bio',
        'years_experience',
        'instagram_handle',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'years_experience' => 'integer',
    ];

    /**
     * Get the user that owns the barber profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop that the barber belongs to.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the services that the barber provides.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'barber_services')
            ->withPivot('price_override')
            ->withTimestamps();
    }

    /**
     * Get the barber's appointments.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the barber's working hours.
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /**
     * Get the barber's time off periods.
     */
    public function timeOff(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    /**
     * Check if the barber is available at the given time.
     *
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return bool
     */
    public function isAvailable(\DateTime $startTime, \DateTime $endTime): bool
    {
        // Check if the barber has any conflicting appointments
        $conflictingAppointments = $this->appointments()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($query) use ($startTime, $endTime) {
                    $query->where('appointment_datetime', '<', $endTime->format('Y-m-d H:i:s'))
                        ->where('end_datetime', '>', $startTime->format('Y-m-d H:i:s'));
                });
            })
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        if ($conflictingAppointments > 0) {
            return false;
        }

        // Check if the barber has any time off during this period
        $timeOffConflicts = $this->timeOff()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_datetime', '<', $endTime->format('Y-m-d H:i:s'))
                    ->where('end_datetime', '>', $startTime->format('Y-m-d H:i:s'));
            })
            ->count();

        if ($timeOffConflicts > 0) {
            return false;
        }

        // Check if the time is within the barber's working hours
        $dayOfWeek = $startTime->format('w'); // 0 (Sunday) through 6 (Saturday)
        $workingHours = $this->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_day_off', false)
            ->first();

        if (!$workingHours) {
            return false;
        }

        $startTimeObj = new \DateTime($startTime->format('Y-m-d') . ' ' . $workingHours->start_time);
        $endTimeObj = new \DateTime($startTime->format('Y-m-d') . ' ' . $workingHours->end_time);

        return $startTime >= $startTimeObj && $endTime <= $endTimeObj;
    }
}
