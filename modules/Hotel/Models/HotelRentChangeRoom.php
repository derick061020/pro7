<?php

namespace Modules\Hotel\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRentChangeRoom extends Model
{
    protected $table = 'hotel_rent_change_rooms';
    protected $fillable = [
        'hotel_rent_id',
        'old_room_id',
        'new_room_id',
        'observations',
        'user_id',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Relación con la renta
     */
    public function rent()
    {
        return $this->belongsTo(HotelRent::class, 'hotel_rent_id');
    }

    /**
     * Habitación anterior
     */
    public function oldRoom()
    {
        return $this->belongsTo(HotelRoom::class, 'old_room_id');
    }

    /**
     * Nueva habitación
     */
    public function newRoom()
    {
        return $this->belongsTo(HotelRoom::class, 'new_room_id');
    }

    /**
     * Usuario que realizó el cambio
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Tenant\User', 'user_id');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->changed_at = now();
        });
    }
}
