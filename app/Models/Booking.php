<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;

    public const STATUS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'package_id',
        'booking_date',
        'event_name',
        'booking_address',
        'booking_status',
        'deliverable_status',
        'completion_date',
        'discount',
        'link',
    ];

    /**
     * Get the customer that owns the booking.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package that owns the booking.
     *
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the add-ons for the booking.
     *
     * @return BelongsToMany
     */
    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'availed_addon')->using(AvailedAddon::class);
    }

    /**
     * The employees that belong to the booking.
     *
     * @return BelongsToMany
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workload')->using(Workload::class);
    }

    /**
     * Get the feedback associated with the booking.
     *
     * @return HasMany
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the billing record associated with the booking.
     *
     * @return HasOne
     */
    public function billing(): HasOne
    {
        return $this->hasOne(Billing::class);
    }
}
