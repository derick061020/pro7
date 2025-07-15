<?php

namespace Modules\Hotel\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelFloor;
use Modules\Hotel\Models\HotelRent;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\User;

class HotelReceptionController extends Controller
{
	public function index()
	{
		$rooms = $this->getRooms();

		if (request()->ajax()) {
			return response()->json([
				'success' => true,
				'rooms'   => $rooms,
			], 200);
		}
		$floors = HotelFloor::where('active', true)
                ->where('establishment_id',auth()->user()->establishment_id)
				->orderBy('description')
				->get();

		$roomStatus = HotelRoom::$status;

        $userType = auth()->user()->type;
		$establishmentId = auth()->user()->establishment_id;
        $establishments = Establishment::select('id','description')->get();

		return view('hotel::rooms.reception', compact('rooms', 'floors', 'roomStatus','userType','establishmentId','establishments'));
	}

    /**
     * Busqueda avanzada de cuartos.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchRooms(Request $request){
        $rooms = HotelRoom::with('category', 'floor', 'rates')
            ->where('establishment_id', auth()->user()->establishment_id)
            ->orderBy('id', 'ASC');
        if (auth()->user()->type == 'cleaner') {
            $rooms->where('cleaner_id', auth()->user()->id);
        }

        if ($request->has('hotel_floor_id') && !empty($request->hotel_floor_id)) {
            $rooms->where('hotel_floor_id', $request->hotel_floor_id);
        }
        if ($request->has('hotel_status_room') && !empty($request->hotel_status_room)) {
            $rooms->where('status', $request->hotel_status_room);
        }
        if ($request->has('hotel_name_room') && !empty($request->hotel_name_room)) {
            $searchTerm = strtolower($request->hotel_name_room);
        
            $rooms->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere(function ($subquery) use ($searchTerm) {
                          $subquery->whereHas('hotelRents', function ($q) use ($searchTerm) {
                              $q->whereNotIn('status', ['FINALIZADO', 'ELIMINADO'])
                                ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(customer, '$.name'))) LIKE ?", ['%' . $searchTerm . '%']);
                          });
                      });
            });
        }
        
        
        $now = now();
        $currentDateTime = now()->format('Y-m-d H:i');
        $today = now()->startOfDay();
        $oneWeekLater = now()->addWeek();
        
        $rooms = $rooms->orderBy('name')->where('active', true)->get()->each(function ($room) use ($now, $currentDateTime, $today, $oneWeekLater) {
            // Verificar si hay una reserva activa (check-in hoy o antes y check-out después de ahora)
            $activeBooking = HotelRent::where('hotel_room_id', $room->id)
                ->where('is_booking', true)
                ->where(function($query) use ($today, $currentDateTime) {
                    // Check if the booking spans across the current day
                    $query->where(function($q) use ($today) {
                        $q->whereDate('input_date', '<=', $today);
                    })
                    // Or if the booking is for today and the current time is within the booking time range
                    ->orWhere(function($q) use ($currentDateTime) {
                        $q->whereRaw('CONCAT(input_date, " ", IFNULL(input_time, "00:00:00")) <= ?', [$currentDateTime]);
                    });
                })
                ->where('status', '!=', 'FINALIZADO')
                ->where('status', '!=', 'ELIMINADO')
                ->where(function ($query) {
                    $query->where('status', 'INICIADO')
                        ->orWhere('status', 'RESERVADO');
                })
                ->orderBy('id', 'DESC')
                ->first();

            // Si hay una reserva activa, marcar la habitación como ocupada
            if ($activeBooking && $room->status !== 'OCUPADO') {
                $room->status = 'OCUPADO';
                $room->save();
            }

            // Verificar si la habitación está ocupada
            if ($room->status === 'OCUPADO' || $room->status === 'RESIDENCIA' || $room->status === 'DISPONIBLE') {

                $rent = HotelRent::where('hotel_room_id', $room->id)
                    ->where(function($query) use ($currentDateTime) {
                        // Check if the current datetime is within any booking time range
                        $query->whereRaw('CONCAT(input_date, " ", IFNULL(input_time, "00:00:00")) <= ?', [$currentDateTime]);
                    })
                    ->where('status', '!=', 'FINALIZADO')
                    ->where('status', '!=', 'ELIMINADO')
                    ->where(function ($query) {
                        $query->where('status', 'INICIADO')
                            ->orWhere('status', 'RESERVADO');
                    })
                    ->orderBy('id', 'DESC')
                    ->first();
                
                    if($room->status === 'DISPONIBLE'){
                        $room->status = 'OCUPADO';
                        $room->save();
                    }
                if ($rent) {
                    $rent->is_current_booking = $rent->is_booking; // Indicar si es una reserva o check-in normal
                    $room->rent = $rent;
                    $bookings = HotelRent::where('hotel_room_id', $room->id)
                    ->where('status', '!=', 'FINALIZADO')
                    ->where('status', '!=', 'ELIMINADO')
                    ->where('is_booking', true)
                    ->orderBy('id', 'DESC')
                    ->get();
                    $room->rents = $bookings;
                } else {
                    $room->status = 'DISPONIBLE';
                    $room->save();
                    $room->rent = [];
                    $bookings = HotelRent::where('hotel_room_id', $room->id)
                        ->where('status', '!=', 'FINALIZADO')
                        ->where('status', '!=', 'ELIMINADO')
                        ->where('is_booking', true)
                        ->orderBy('id', 'DESC')
                        ->get();
                    $room->rents = $bookings;
                }
            } else {
                $room->rent = [];
            }
            
            // Verificar si hay reservas próximas (en la próxima semana)
            $upcomingBooking = HotelRent::where('hotel_room_id', $room->id)
                ->where('is_booking', true)
                ->where(function($query) use ($currentDateTime, $oneWeekLater) {
                    // Check for bookings that start after the current time
                    $query->whereRaw('CONCAT(input_date, " ", IFNULL(input_time, "00:00:00")) > ?', [$currentDateTime])
                          // And within the next week
                          ->where('input_date', '<=', $oneWeekLater->format('Y-m-d'));
                })
                ->where(function ($query) {
                    $query->where('status', 'INICIADO')
                        ->orWhere('status', 'RESERVADO');
                })
                ->orderBy('input_date', 'ASC')
                ->orderBy('input_time', 'ASC')
                ->first();
                
            $room->has_upcoming_booking = (bool)$upcomingBooking;
            if ($upcomingBooking) {
                $room->next_booking_date = $upcomingBooking->input_date;
            }

            $total = 0;
            if(isset($room->rent->history)){
                foreach(json_decode($room->rent->history) as $history){
                    if(isset($history->total)){
                        $total += $history->total;
                    }
                }
            }
            if(isset($room->rent->payment_history)){
                foreach(json_decode($room->rent->payment_history) as $payment){
                    if(isset($payment->amount)){
                        $total -= $payment->amount;
                    }
                }
            }
            if ($total > 0) {
                $room->debt = $total;
            }
            return $room;
        });

        return response()->json([
            'success' => true,
            'rooms'   => $rooms,
        ], 200);
    }
    /**
     * Devuelve informacion de cuartos disponibles
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|\Modules\Hotel\Models\HotelRoom[]
     */
    private function getRooms()
    {
        $rooms = HotelRoom::with('category', 'floor', 'rates', 'establishment')
            ->where('establishment_id', auth()->user()->establishment_id);

        if (request('hotel_floor_id')) {
            $rooms->where('hotel_floor_id', request('hotel_floor_id'));
        }
        if (request('status')) {
            $rooms->where('status', request('status'));
        }

        $now = now();
        $currentDateTime = now()->format('Y-m-d H:i');
        $oneWeekLater = now()->addWeek();

        $rooms->orderBy('name');
        return $rooms->get()->each(function ($room) use ($now, $currentDateTime, $oneWeekLater) {
            if ($room->status === 'OCUPADO') {
                $rent = HotelRent::where('hotel_room_id', $room->id)
                    ->where(function($query) use ($currentDateTime) {
                        // Check if the current datetime is within any booking time range
                        $query->whereRaw('CONCAT(input_date, " ", IFNULL(input_time, "00:00:00")) <= ?', [$currentDateTime])
                              ->whereRaw('CONCAT(output_date, " ", IFNULL(output_time, "23:59:59")) >= ?', [$currentDateTime]);
                    })
                    ->where(function ($query) {
                        $query->where('status', 'INICIADO')
                            ->orWhere('status', 'RESERVADO');
                    })
                    ->orderBy('id', 'DESC')
                    ->first();
                $room->rent = $rent;
            } else {
                $room->rent = [];
            }
            
            // Verificar si hay reservas próximas (en la próxima semana)
            $upcomingBooking = HotelRent::where('hotel_room_id', $room->id)
                ->where('is_booking', true)
                ->where(function($query) use ($now, $oneWeekLater) {
                    $query->where('input_date', '>=', $now->format('Y-m-d'))
                          ->where('input_date', '<=', $oneWeekLater->format('Y-m-d'));
                })
                ->where(function ($query) {
                    $query->where('status', 'INICIADO')
                        ->orWhere('status', 'RESERVADO');
                })
                ->orderBy('input_date', 'ASC')
                ->orderBy('input_time', 'ASC')
                ->first();
                
            $room->has_upcoming_booking = (bool)$upcomingBooking;
            if ($upcomingBooking) {
                $room->next_booking_date = $upcomingBooking->input_date;
                if (!empty($upcomingBooking->input_time)) {
                    $room->next_booking_time = $upcomingBooking->input_time;
                }
            }
            
            return $room;
        });
    }

    public function getItem($id)
    {
        $rent = HotelRent::findOrFail($id);

        $item = $rent->items->where('type', 'HAB')->where('payment_status', 'PAID')->first();
        $item_debt = $rent->items->where('type', 'HAB')->where('payment_status', 'DEBT')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $item,
                'item_debt' => $item_debt
            ],
            'message'   => "Datos encontrados",
        ], 200);
    }

    public function changeUserEstablishment(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);
        $user->establishment_id = $request->establishment_id;
        $user->save();

        return response()->json([
            'success' => true,
            'message'   => "Establecimiento actualizado con éxito",
        ], 200);
    }
}
