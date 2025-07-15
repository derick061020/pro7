<?php

use Illuminate\Support\Facades\Route;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
  Route::domain($hostname->fqdn)->group(function () {
    Route::post('hotel/rents/{id}/update-payment-method', 'HotelRentController@updatePaymentMethod');
    Route::middleware(['auth', 'redirect.module', 'locked.tenant'])
      ->prefix('hotels')
      ->group(function () {
        // Reservas
        Route::get('bookings', 'HotelBookingController@index')->name('hotel.bookings.index');
        Route::get('bookings/calendar', 'HotelBookingController@calendarEvents')->name('hotel.bookings.calendar');
        // Ruta para actualización mediante arrastrar/soltar (mantener compatibilidad)
        Route::put('bookings/{id}/update-reservation', 'HotelBookingController@updateReservation')->name('hotel.bookings.update-reservation');
        // Rutas RESTful estándar
        Route::post('bookings', 'HotelBookingController@store')->name('hotel.bookings.store');
        Route::put('bookings/{id}', 'HotelBookingController@update')->name('hotel.bookings.update');
        Route::delete('bookings/destroy/{id}', 'HotelBookingController@destroy')->name('hotel.bookings.destroy');
        
        // Tarifas
        Route::get('rates', 'HotelRateController@index');
        Route::post('rates/store', 'HotelRateController@store');
        Route::put('rates/{id}/update', 'HotelRateController@update');
        Route::delete('rates/{id}/delete', 'HotelRateController@destroy');
        // Categorías
        Route::get('categories', 'HotelCategoryController@index');
        Route::post('categories/store', 'HotelCategoryController@store');
        Route::put('categories/{id}/update', 'HotelCategoryController@update');
        Route::delete('categories/{id}/delete', 'HotelCategoryController@destroy');
        // Pisos
        Route::get('floors', 'HotelFloorController@index');
        Route::post('floors/store', 'HotelFloorController@store');
        Route::put('floors/{id}/update', 'HotelFloorController@update');
        Route::delete('floors/{id}/delete', 'HotelFloorController@destroy');
        // Habitaciones
        Route::get('rooms', 'HotelRoomController@index');
        Route::post('rooms/store', 'HotelRoomController@store');
        Route::put('rooms/{id}/update', 'HotelRoomController@update');
        Route::post('rooms/{id}/clean', 'RoomController@assignCleaner');
        Route::post('rooms/{id}/clean/complete', 'RoomController@completeClean');
        Route::get('/users/type/cleaner', 'RoomController@getCleaners');
        Route::delete('rooms/{id}/delete', 'HotelRoomController@destroy');
        Route::post('rooms/{id}/change-status', 'HotelRoomController@changeRoomStatus');

        Route::get('rooms/tables/{id}', 'HotelRoomController@tables');

        Route::get('rooms/{id}/rates', 'HotelRoomController@myRates');
        Route::post('rooms/{id}/rates/store', 'HotelRoomController@addRateToRoom');
        Route::delete('rooms/{id}/rates/{rateId}/delete', 'HotelRoomController@deleteRoomRate');
        Route::post('rents/{id}/update-payment-history', 'HotelRentController@updatePaymentHistory');

        Route::prefix('reception')->group(function () {
            /**
            hotels/reception
            hotels/reception/search/
            hotels/reception/tables
            hotels/reception/tables/customers
            hotels/reception/{roomId}/rent
            hotels/reception/{roomId}/rent/store
            hotels/reception/{id}/rent/products
            hotels/reception/{id}/rent/products/store
            hotels/reception/{id}/rent/checkout
            hotels/reception/{id}/rent/finalized
            hotels/reception/{id}/rent/extend-time
            hotels/reception/{id}/rent/get-item
              */
            Route::get('', 'HotelReceptionController@index')->name('tenant.hotels.index');
            Route::post('/search', 'HotelReceptionController@searchRooms');
            Route::get('/tables', 'HotelRentController@tables');
            Route::get('/tables/customers', 'HotelRentController@searchCustomers');
            Route::get('/{roomId}/rent', 'HotelRentController@rent');
            Route::post('/{roomId}/rent/store', 'HotelRentController@store');
            Route::get('/{id}/rent/products', 'HotelRentController@showFormAddProduct');
            Route::post('/{id}/rent/products/store', 'HotelRentController@addProductsToRoom');
            Route::get('/{id}/rent/checkout', 'HotelRentController@showFormChekout');
            Route::post('/{id}/rent/finalized', 'HotelRentController@finalizeRent');
            Route::post('/{id}/rent/extend-time', 'HotelRentController@extendTime');
            Route::get('/{id}/rent/get-item', 'HotelReceptionController@getItem');
            Route::get('checkout-tables', 'HotelRentController@checkoutTables');
            Route::get('rent-products-tables', 'HotelRentController@rentProductsTables');
            Route::get('report/{start}/{end}/{establishment_id}', 'HotelRentController@report');
            Route::post('change-user-establishment', 'HotelReceptionController@changeUserEstablishment');
            
            // Ruta para actualizar observaciones de la renta
            Route::post('/{id}/rent/update-observations', 'HotelRentController@updateObservations');
            
            // Ruta para actualizar el historial de pagos
            
            
            // Ruta para hacer check-in de una reserva
            Route::post('/{id}/rent/checkin', 'HotelRentController@checkin');
            
            // Ruta para actualizar una recepción
            Route::put('/{id}/rent/update', 'HotelRentController@update');
            
            // Rutas para cambio de habitación
            Route::get('/rooms/{roomId}/available-for-change', 'HotelRentController@getAvailableRoomsForChange');
            Route::post('/rents/{rentId}/change-room', 'HotelRentController@changeRoom');
            
            // Ruta para actualizar fechas de la renta
            Route::post('/rents/{rentId}/update-dates', 'HotelRentController@updateDates');
            
            // Ruta para eliminar un registro del historial
            Route::delete('/rents/{rentId}/history/{historyId}', 'HotelRentController@deleteHistoryRecord');

        });

        // Reservas
        Route::get('bookings', 'HotelBookingController@index');
    });
  });
}
