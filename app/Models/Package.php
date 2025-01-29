<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_name',
        'package_details',
        'package_price',
    ];

    /**
     * Get the bookings for the package.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * The add-ons that belong to the package.
     *
     * @return BelongsToMany
     */
    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'availed_package')->using(AvailedPackage::class);
    }
}
