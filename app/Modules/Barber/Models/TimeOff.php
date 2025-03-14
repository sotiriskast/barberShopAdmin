<?php

namespace App\Modules\Barber\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeOff extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'barber_id',
        'start_datetime',
        'end_datetime',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    /**
     * Get the barber that owns the time off.
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    /**
     * Check if this time off period overlaps with the given time range.
     *
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return bool
     */
    public function overlapsWithRange(\DateTime $startTime, \DateTime $endTime): bool
    {
        return $this->start_datetime < $endTime && $this->end_datetime > $startTime;
    }
}
