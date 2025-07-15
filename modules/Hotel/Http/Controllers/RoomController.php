<?php

namespace Modules\Hotel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Modules\Hotel\Models\HotelRoom;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = HotelRoom::with(['rent', 'cleaner'])->get();
        return response()->json(['rooms' => $rooms]);
    }

    public function assignCleaner(Request $request, $roomId)
    {
        $room = HotelRoom::findOrFail($roomId);
        
        // Verificar si la habitación ya está en limpieza
        if ($room->is_clean) {
            return response()->json([
                'success' => false,
                'message' => 'La habitación ya está en proceso de limpieza'
            ], 400);
        }

        // Actualizar la habitación con el limpiador
        $room->update([
            'is_clean' => true,
            'cleaner_id' => $request->cleaner_id
        ]);

        // Actualizar el estado de la habitación

        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Limpieza asignada correctamente'
        ]);
    }

    public function getCleaners()
    {
        $cleaners = User::where('type', 'cleaner')->get();
        return response()->json(['users' => $cleaners]);
    }

    public function completeClean(Request $request, $roomId)
    {
        $room = HotelRoom::findOrFail($roomId);
        
        if (!$room->is_clean) {
            return response()->json([
                'success' => false,
                'message' => 'La habitación no está en proceso de limpieza'
            ], 400);
        }

        $room->update([
            'is_clean' => false,
            'cleaner_id' => null,
            'status' => 'DISPONIBLE'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Limpieza completada'
        ]);
    }
}
