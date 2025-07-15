<?php

namespace Modules\Hotel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Company;
use Modules\Hotel\Models\HotelRoom;
use App\Models\Tenant\SaleNote;
use Modules\Hotel\Models\HotelRent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelBookingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = Company::first();
        $establishments = [];
        
        if ($user->type === 'admin') {
            $establishments = Establishment::all();
        } else {
            $establishments = Establishment::where('id', $user->establishment_id)->get();
        }

        // Obtener habitaciones reales desde la base de datos
        $rooms = HotelRoom::with('category', 'floor')
            ->where('establishment_id', $user->establishment_id)
            ->get();

        // Obtener reservas reales desde la base de datos
        $rents = HotelRent::with(['room'])
            ->whereHas('room', function($query) use ($user) {
                $query->where('establishment_id', $user->establishment_id);
            })
            ->whereIn('status', ['OCUPADO', 'RESERVADO'])
            ->get();

        // Formatear reservas para la visualización del timeline
        // Obtener todas las reservas (HotelRent) con sus habitaciones
        $bookings = HotelRent::with(['room'])->where('status', '!=', 'ELIMINADO')->get();
        
        // Formatear los datos para el timeline
        $formattedBookings = [];
        foreach ($bookings as $booking) {
            // Obtener datos del cliente desde el JSON
            $customer = $booking->customer;
            
            // Determinar la clase CSS según el estado
            $className = 'orange';
            if ($booking->status === 'FINALIZADO') {
                $className = 'gray';
            }
            else if ($booking->is_booking == 0) {
                $className = 'green';
            }else if ($booking->is_booking == 1) {
                $className = 'blue';
            }

            
            $formattedBookings[] = [
                'id' => $booking->id,
                'room_id' => $booking->hotel_room_id,
                'customer_name' => $customer ? ($customer->name ?? 'Cliente sin asignar') : 'Cliente sin asignar',
                'customer_number' => $customer ? ($customer->number ?? 'N/A') : 'N/A',
                'start_date' => $booking->input_date,
                'end_date' => $booking->output_date,
                'status' => $booking->status,
                'className' => $className,
                'is_booking' => $booking->is_booking,
                'customer' => $customer  // Asegurarse de que los datos del cliente estén disponibles
            ];
        }
        return view('hotel::bookings.index', [
            'establishments' => $establishments,
            'establishmentId' => $user->establishment_id,
            'userType' => $user->type,
            'company' => $company,
            'rooms' => $rooms,
            'bookings' => $bookings,                // Modelos completos de HotelRent
            'formattedBookings' => $formattedBookings  // Datos formateados para el timeline
        ]);
    }

    
    public function calendarEvents(Request $request)
    {
        $user = auth()->user();
        
        // Obtener reservas reales desde la base de datos
        $rentsQuery = HotelRent::with(['customer', 'room'])
            ->whereHas('room', function($query) use ($user) {
                $query->where('establishment_id', $user->establishment_id);
            })
            ->where('status', '!=', 'ELIMINADO')
            ->whereIn('status', ['OCUPADO', 'RESERVADO']);
            
        // Aplicar filtros si se especifican
        if ($request->has('rooms')) {
            $roomIds = (array)$request->input('rooms');
            $rentsQuery->whereIn('room_id', $roomIds);
        }

        if ($request->has('statuses')) {
            $statuses = (array)$request->input('statuses');
            $rentsQuery->whereIn('status', $statuses);
        }
        
        $rents = $rentsQuery->get();
        
        // Formatear reservas para la respuesta JSON
        $bookings = [];
        foreach ($rents as $rent) {
            // Determinar la clase CSS según el estado
            $className = 'status-other';
            if ($rent->status === 'OCUPADO') {
                $className = 'status-confirmed';
            } else if ($rent->status === 'RESERVADO') {
                $className = 'status-pending';
            }
            
            $bookings[] = [
                'id' => $rent->id,
                'title' => ($rent->customer->name ?? 'Cliente') . ' - ' . ($rent->room->name ?? 'Habitación'),
                'start' => Carbon::parse($rent->input_date)->format('Y-m-d H:i:s'),
                'end' => Carbon::parse($rent->output_date)->format('Y-m-d H:i:s'),
                'room_id' => $rent->room_id,
                'status' => strtolower($rent->status),
                'className' => $className
            ];
        }

        return response()->json(array_values($bookings));
    }

    public function updateBooking(Request $request, $id)
    {
        try {
            $rent = HotelRent::findOrFail($id);
            
            // Actualizar fechas si se proporcionan
            if ($request->has('start')) {
                $rent->input_date = Carbon::parse($request->input('start'));
                
                // Si la fecha de inicio es después de hoy, o la fecha de fin es antes de hoy, 
                // se considera que la reserva no está figurando, y debemos revisar si la habitación 
                // tiene alguna otra renta que la haga ocupada
                if ($rent->input_date > now() || $rent->output_date < now()) {
                    $otherRents = HotelRent::where('hotel_room_id', $rent->hotel_room_id)
                        ->where('id', '!=', $rent->id)
                        ->where('status', '!=', 'FINALIZADO')
                        ->count();
                    
                    // Si no hay otras rentas que la hagan ocupada, se pone en estado DISPONIBLE
                    if (!$otherRents) {
                        $room = HotelRoom::find($rent->hotel_room_id);
                        $room->status = 'DISPONIBLE';
                        $room->save();
                    }
                }
            }
            
            if ($request->has('end')) {
                $rent->output_date = Carbon::parse($request->input('end'));
            }
            
            // Actualizar habitación si se proporciona
            if ($request->has('room_id')) {
                $newRoomId = $request->input('room_id');
                $rent->hotel_room_id = $newRoomId;
            }
            $rent->duration = diffInDays(Carbon::parse($request->end), Carbon::parse($request->start));
            $rent->save();
            
            return response()->json(['success' => true, 'data' => $rent, 'room' => $room]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Crear una nueva reserva
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'hotel_room_id' => 'required|exists:hotel_rooms,id',
                'status' => 'required|in:OCUPADO,RESERVADO',
                'input_date' => 'required|date',
                'output_date' => 'required|date|after:input_date',
                'customer' => 'required|array',
                'customer.name' => 'required|string',
                'customer.number' => 'required|string',
            ]);
            
            // Crear la nueva reserva
            $rent = new HotelRent();
            $rent->hotel_room_id = $request->input('hotel_room_id');
            $rent->status = $request->input('status');
            $rent->input_date = Carbon::parse($request->input('input_date'));
            $rent->output_date = Carbon::parse($request->input('output_date'));
            $rent->customer = $request->input('customer');
            $rent->notes = $request->input('notes');
            $rent->establishment_id = auth()->user()->establishment_id;
            
            // Guardar la reserva
            $rent->save();
            
            // Actualizar el estado de la habitación
            $room = HotelRoom::find($request->input('hotel_room_id'));
            if ($room) {
                $room->status = $request->input('status');
                $room->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva creada correctamente',
                'data' => $rent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar una reserva
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Actualizar una reserva existente
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'input_date' => 'required|date',
                'input_time' => 'required|date_format:H:i',
                'output_date' => 'required|date|after_or_equal:input_date',
                'output_time' => 'required|date_format:H:i',
                'quantity_persons' => 'required|integer|min:1',
                'towels' => 'nullable|integer|min:0',
                'observaciones' => 'nullable|string',
            ]);

            // Buscar la reserva
            $rent = HotelRent::findOrFail($id);
            
            // Actualizar los campos
            $rent->input_date = $validated['input_date'];
            $rent->input_time = $validated['input_time'];
            $rent->output_date = $validated['output_date'];
            $rent->output_time = $validated['output_time'];
            $rent->quantity_persons = $validated['quantity_persons'];
            $rent->towels = $validated['towels'] ?? 0;
            $rent->observations = $validated['observaciones'] ?? null;
            
            // Guardar los cambios
            $rent->save();
            
            // Verificar si necesitamos actualizar el estado de la habitación
            $this->updateRoomStatus($rent->hotel_room_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada correctamente',
                'data' => $rent
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar la reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualiza el estado de una habitación basado en sus reservas
     * 
     * @param int $roomId
     * @return void
     */
    protected function updateRoomStatus($roomId)
    {
        $now = now();
        $room = HotelRoom::find($roomId);
        
        if (!$room) return;
        
        // Verificar si hay reservas activas para esta habitación
        $activeRent = HotelRent::where('hotel_room_id', $roomId)
            ->where('status', '!=', 'FINALIZADO')
            ->where('status', '!=', 'ELIMINADO')
            ->where('input_date', '<=', $now->format('Y-m-d'))
            ->where('output_date', '>=', $now->format('Y-m-d'))
            ->first();
            
        if ($activeRent) {
            // Hay una reserva activa, actualizar el estado de la habitación
            $room->status = 'OCUPADO';
        } else {
            // No hay reservas activas, verificar si hay reservas futuras
            $futureRent = HotelRent::where('hotel_room_id', $roomId)
                ->where('status', '!=', 'FINALIZADO')
                ->where('status', '!=', 'ELIMINADO')
                ->where('input_date', '>', $now->format('Y-m-d'))
                ->exists();
                
            if ($futureRent) {
                $room->status = 'RESERVADO';
            } else {
                $room->status = 'DISPONIBLE';
            }
        }
        
        $room->save();
    }
    
    /**
     * Eliminar una reserva
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Obtener la reserva con sus relaciones necesarias
            $rent = HotelRent::with(['room'])->findOrFail($id);
            
            foreach (json_decode($rent->history) as $item) {
                if(isset($item->sale_note_id)){
                    $SaleNote = SaleNote::where('id', $item->sale_note_id)->first();
                    if($SaleNote){
                        $SaleNote->state_type_id = 11;
                        $SaleNote->update();
                    }
                }elseif(isset($item->sale_note_ids)){
                    foreach ($item->sale_note_ids as $sale_note_id) {
                        $SaleNote = SaleNote::where('id', $sale_note_id)->first();
                        if($SaleNote){
                            $SaleNote->state_type_id = 11;
                            $SaleNote->update();
                        }
                    }
                }
            }
            // Cambiar el estado a ELIMINADO en lugar de eliminar
            $rent->update([
                'status' => 'ELIMINADO',
                'updated_at' => now()
            ]);
            
            // Verificar si hay otras reservas activas para esta habitación
            $otherActiveRents = HotelRent::where('hotel_room_id', $rent->hotel_room_id)
                ->where('id', '!=', $id) // Excluir la reserva actual
                ->where('status', '!=', 'FINALIZADO')
                ->where('status', '!=', 'ELIMINADO')
                ->exists();
            
            // Si no hay más reservas activas para esta habitación, actualizar su estado a DISPONIBLE
            if (!$otherActiveRents && $rent->room) {
                $rent->room->update(['status' => 'DISPONIBLE']);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva marcada como eliminada correctamente.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar la reserva como eliminada: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualiza una reserva mediante drag & drop
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReservation(Request $request, $id)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'start' => 'required|date',
                'end' => 'required|date|after:start',
                'room_id' => 'required',
            ]);
            
            // Iniciar transacción
            DB::beginTransaction();
            
            // Buscar la reserva
            $booking = HotelRent::findOrFail($id);
            
            // Actualizar fechas
            $startDate = Carbon::parse($request->start);
            $endDate = Carbon::parse($request->end);
            
            $booking->input_date = $startDate->format('Y-m-d');
            $booking->output_date = $endDate->format('Y-m-d');
            $booking->duration = $endDate->diffInDays($startDate);
            $item = $booking->items->where('type', 'HAB')->where('payment_status', 'DEBT')->first();
	        if($item){
				$data_item = $item->item;
				$data_item->quantity = $endDate->diffInDays($startDate);
				$data_item->total = $data_item->quantity * $data_item->unit_price;
                $item->item = $data_item;
                $item->save();
			}
			
            // Actualizar habitación si es necesario
            if ($booking->hotel_room_id != $request->room_id) {
                $booking->hotel_room_id = $request->room_id;
            }
            
            $booking->save();
            
            // Confirmar transacción
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada correctamente',
                'data' => $item->item
            ]);
            
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }
}



