<?php

    namespace Modules\Hotel\Models;

    use App\Models\Tenant\ModelTenant;
    use Illuminate\Database\Eloquent\Builder;
    use App\Models\Tenant\Establishment;

    /**
     * \Modules\Hotel\Models\HotelRoom
     *
     * @method static Builder|HotelRoom newModelQuery()
     * @method static Builder|HotelRoom newQuery()
     * @method static Builder|HotelRoom query()
     * @mixin ModelTenant
     * @mixin \Eloquent
     */
    class HotelRoom extends ModelTenant
    {
        public static $status = [
            'DISPONIBLE',
            'MANTENIMIENTO',
            'OCUPADO',
            'LIMPIEZA'
        ];
        protected $table = 'hotel_rooms';
        protected $fillable = [
            'name',
            'is_clean',
            'cleaner_id',
            'hotel_category_id',
            'hotel_floor_id',
            'active',
            'description',
            'item_id',
            'establishment_id',
        ];

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function category()
        {
            return $this->belongsTo(HotelCategory::class, 'hotel_category_id')->select('id', 'description');
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function rates()
        {
            return $this->hasMany(HotelRoomRate::class);
        }


        public function hotelRents()
        {
            return $this->hasMany(HotelRent::class, 'hotel_room_id');
        }
        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function floor()
        {
            return $this->belongsTo(HotelFloor::class, 'hotel_floor_id')->select('id', 'description');
        }

        public function getActiveAttribute($value)
        {
            return $value ? true : false;
        }

        public function establishment()
        {
            return $this->belongsTo(Establishment::class)->select('id', 'description');;
        }

    }
