<?php

namespace Modules\Hotel\Http\Controllers;

use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Hotel\Models\HotelRent;
use Modules\Hotel\Models\HotelRoom;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use Modules\Hotel\Models\HotelRentOrder;
use Modules\Hotel\Models\HotelRentItem;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\Catalogs\DocumentType;
use Modules\Hotel\Http\Requests\HotelRentRequest;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use Modules\Hotel\Http\Requests\HotelRentItemRequest;
use Modules\Hotel\Models\HotelRentItemPayment;
use Modules\Hotel\Exports\HotelRentExport;
use Carbon\Carbon;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Document;
use Modules\Hotel\Models\HotelRoomRate;
use Illuminate\Support\Facades\Validator;

class HotelRentController extends Controller
{
    use FinanceTrait;
    
    /**
     * Update the dates for a rent
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDates(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $rent = HotelRent::findOrFail($id);
            
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'input_date' => 'required|date',
                'input_time' => 'required|date_format:H:i',
                'output_date' => 'required|date|after_or_equal:input_date',
                'output_time' => 'required|date_format:H:i',
                'notes' => 'nullable|string|max:500',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Update the rent with new dates
            $rent->input_date = $request->input_date;
            $rent->input_time = $request->input_time;
            $rent->output_date = $request->output_date;
            $rent->output_time = $request->output_time;
			$duration = 0;
			switch ($rent->rate_type) {
				case 'DAY':
					$duration = (int) Carbon::parse($rent->input_date)->diffInDays(Carbon::parse($rent->output_date)) + 1;
					$typeRate = 'noche(s)';
					break;
				case 'MONTH':
					$duration = (int) Carbon::parse($rent->input_date)->diffInMonths(Carbon::parse($rent->output_date)) + 1;
					$typeRate = 'mes(es)';
					break;
				case 'HOUR':
					$duration = (int) Carbon::parse($rent->input_date . ' ' . $rent->input_time)
						->diffInHours(Carbon::parse($rent->output_date . ' ' . $rent->output_time)) + 1;
					$typeRate = 'hora(s)';
					break;
			}
			$rent->duration = $duration;

            $rent->save();
			$history = json_decode($rent->history, true);
			if(count($history) == 1){
				$history[0]['quantity'] = $duration;
				$history[0]['total'] = $duration * $history[0]['unit_price'];
				$history[0]['total_igv'] = $history[0]['total'] - $history[0]['total']/(($history[0]['percentage_igv'] +100)/ 100);
				$history[0]['unit_value'] = $history[0]['unit_price']/(($history[0]['percentage_igv'] +100)/ 100);
				$history[0]['total_taxes'] = $history[0]['total_igv'];
				$history[0]['total_value'] = $history[0]['unit_value'] * $duration;
				$history[0]['total_base_igv'] = $history[0]['total_value'];
				$history[0]['total_igv_without_rounding'] = $history[0]['total_igv'];
				$history[0]['total_taxes_without_rounding'] = $history[0]['total_taxes'];
				$history[0]['total_value_without_rounding'] = $history[0]['total_value'];
				$history[0]['total_base_igv_without_rounding'] = $history[0]['total_base_igv'];
				$history[0]['total_igv'] = round($history[0]['total_igv'], 2);
				$history[0]['unit_value'] = round($history[0]['unit_value'], 2);
				$history[0]['total_taxes'] = round($history[0]['total_taxes'], 2);
				$history[0]['total_value'] = round($history[0]['total_value'], 2);
				$history[0]['total_base_igv'] = round($history[0]['total_base_igv'], 2);
				$history[0]['name_product_pdf'] = 'Habitación '.$rent->room->name.' x '.$duration.' '.$typeRate;
				$history[0]['item']['name_product_pdf'] = 'Habitación '.$rent->room->name.' x '.$duration.' '.$typeRate;
				$history[0]['item']['description'] = 'Habitación '.$rent->room->name.' x '.$duration.' '.$typeRate;
				$history[0]['item']['full_description'] = 'Habitación '.$rent->room->name.' x '.$duration.' '.$typeRate;
				$history[0]['item']['name'] = 'Habitación '.$rent->room->name.' x '.$duration.' '.$typeRate;
				$rent->history = json_encode($history);
				$historial = json_decode($rent->historial, true);$uniqueId = 0;
				foreach($historial as $itemHis){
					if(isset($itemHis['unique_id']) && (int)$itemHis['unique_id'] > $uniqueId){
						$uniqueId = (int)$itemHis['unique_id'];
					}
				}
				$uniqueId++;
				$historial[] = [
					'name' => 'Cambio de fecha: Habitación '.$rent->room->name.' x '.$duration.' '.$typeRate,
					'quantity' => $duration,
					'unit_price' => $history[0]['unit_price'],
					'total' => $duration * $history[0]['unit_price'],
					'date' => now()->format('Y-m-d H:i:s'),
					'id' => null,
					'is_product' => false,
					'unique_id' => $uniqueId,
					'delete' => true,
				];
				$rent->historial = json_encode($historial);
				$rent->update();
			}
			if (isset($history[0]['sale_note_id'])){
				$salenote = SaleNote::findOrFail($history[0]['sale_note_id']);
				if($salenote){
					$salenote->state_type_id = 11;
					$salenote->update();
				}
			}
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Fechas actualizadas correctamente',
                'data' => $rent
            ]);
            
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las fechas: ' . $e->getMessage()
            ], 500);
        }
    }

	public function rent($roomId)
	{
		if(request()->get('mode') == 'edit'){
			$rent = HotelRent::findOrFail($roomId);
			$room = HotelRoom::with('category', 'rates.rate')
				->findOrFail($rent->hotel_room_id);
			$room->status = 'RESERVADO';
			$affectation_igv_types = AffectationIgvType::whereActive()->get();
			$series = Series::where('establishment_id',  auth()->user()->establishment_id)->get();
			return view('hotel::rooms.rent', compact('room', 'affectation_igv_types', 'series', 'rent'));
		}
		$room = HotelRoom::with('category', 'rates.rate')
			->findOrFail($roomId);
		$rent = HotelRent::where('hotel_room_id', $roomId)
			->where('status', '!=', 'FINALIZADO')
			->where('status', '!=', 'ELIMINADO')
			->get();
		$room->rents = $rent;

		if($room->status == 'OCUPADO'){
			$rent = HotelRent::where('hotel_room_id', $roomId)
				->where('is_booking', 1)
				->where('output_date', '>=', Carbon::now())
				->first();

			if($rent){
				$room->status = 'RESERVADO';
			}
		}

		$affectation_igv_types = AffectationIgvType::whereActive()->get();
		$series = Series::where('establishment_id',  auth()->user()->establishment_id)->get();

		return view('hotel::rooms.rent', compact('room', 'affectation_igv_types','series'));
	}
	public function update(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            $rent = HotelRent::findOrFail($id);
            
            // Check if this is a check-in
            $isCheckin = $request->has('is_checkin') && $request->is_checkin == 'true';
            
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|numeric',
                'hotel_rate_id' => 'required|numeric',
                'duration' => 'required|numeric|min:1',
                'quantity_persons' => 'required|numeric|min:1',
                'payment_status' => 'required|in:PAID,PENDING,DEBT',
                'input_date' => 'required|date',
                'input_time' => 'required|date_format:H:i',
				'travel_purpose' => 'nullable|string',
                'output_date' => 'required|date|after_or_equal:input_date',
                'output_time' => 'required|date_format:H:i',
                'data_persons' => 'nullable|array',
                'notes' => 'nullable|string',
				'rate_type' => 'required|string',
                'rent_payment' => 'required|array',
                'rent_payment.payment_method_type_id' => 'required|string',
                'rent_payment.payment_destination_id' => 'nullable|string',
                'rent_payment.reference' => 'nullable|string|max:50',
                'rent_payment.payment' => 'required|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Format dates for MySQL
            $inputDate = \Carbon\Carbon::parse($request->input_date)->format('Y-m-d');
            $outputDate = \Carbon\Carbon::parse($request->output_date)->format('Y-m-d');
            
            // Prepare the update data
            $updateData = [
                'customer_id' => $request->customer_id,
                'hotel_rate_id' => $request->hotel_rate_id,
                'duration' => $request->duration,
                'quantity_persons' => $request->quantity_persons,
                'payment_status' => $request->payment_status,
                'input_date' => $inputDate,
                'input_time' => $request->input_time,
				'travel_purpose' => $request->travel_purpose,
                'output_date' => $outputDate,
                'output_time' => $request->output_time,
                'data_persons' => $request->data_persons,
                'notes' => $request->notes,
				'towels' => $request->towels,
				'matricula' => $request->matricula,
                'total_to_pay' => $request->total_to_pay,
				'rate_type' => $request->rate_type,
            ];
            
            // If this is a check-in, update status and is_booking
            if ($isCheckin) {
                $updateData['status'] = 'INICIADO';
                $updateData['is_booking'] = 0;
                
                // Update room status to OCUPADO
                $room = $rent->room;
                if ($room) {
                    $room->status = 'OCUPADO';
                    $room->save();
                }
            }
            
            // Update the rent data
            $rent->update($updateData);
			
			$item = $rent->items->where('type', 'HAB')->where('payment_status', 'DEBT')->first();
			switch ($rent->rate_type) {
				case 'HOUR':
					$typeRate = 'hora(s)';
					break;
				case 'DAY':
					$typeRate = 'dia(s)';
					break;
				case 'MONTH':
					$typeRate = 'mes(es)';
					break;
			}
			if($item){
				if(isset(json_decode($rent->history)->sale_note_id)){
					$salenote = SaleNote::findOrFail(json_decode($rent->history)->sale_note_id);
					$salenote->state_type_id = 11;
					$salenote->update();
				}
				$data_item = $item->item;
				$data_item->quantity = $request->duration;
				$data_item->total = $data_item->quantity * $data_item->unit_price;
				$item->item = $data_item;
				$item->save();
				$product = $request->product;
				$product['payment_status'] = $request->payment_status;
				$product['name_product_pdf'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
				$product['item']['name_product_pdf'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
				$product['item']['description'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
				$product['item']['full_description'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
				$product['item']['name'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
				$product['sale_note_id'] = $request->sale_note_id;
				$product['unit_value'] = 100 * $product['unit_price'] / ($product['percentage_igv']+100);
				$product['id'] = 1;
				$history = json_decode('[]', true);
				$history[] = $product;
				$historial = json_decode('[]', true);
				$historial[] = [
					'name' => $product['item']['name'],
					'quantity' => $product['quantity'],
					'unit_price' => $product['unit_price'],
					'total' => $product['total'],
					'date' => now()->format('Y-m-d H:i:s'),
					'id' => 1,
					'is_product' => false,
					'unique_id' => 1
				];
				$rent->history = json_encode($history);
				$rent->historial = json_encode($historial);
				$rent->save();
			}
            
            // Update or create payment
			if (isset($request->rent_payment) && $request->payment_status != 'DEBT' ) {
				// Obtener el historial de pagos existente o inicializar como array vacío
				
				if(isset($request->rent_payment['amount']) ){
					$total = $request->rent_payment['amount'];
					$type = 'advancePayment';
				}else{
					$total = $request->total_to_pay;
					$type = 'payment';
				}
				// Crear registro del pago
				$paymentData = [
					'date' => now()->format('Y-m-d H:i:s'),
					'amount' => (float)$total,
					'payment_method_type_id' => $request->rent_payment['payment_method_type_id'],
					'payment_destination_id' => $request->rent_payment['payment_destination_id'],
					'type' => $type,
					'description' => 'Pago de habitación',
					'reference' => ''
				];
				
				// Agregar el nuevo pago al historial
				$paymentHistory[] = $paymentData;
				$rent->payment_history = json_encode($paymentHistory);
				$rent->save();
			}
            
            
            DB::connection('tenant')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Recepción actualizada correctamente',
                'data' => $rent->fresh() // Return the updated rent data without trying to load relationships
            ]);
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la recepción: ' . $e->getMessage()
            ], 500);
        }
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la recepción: ' . $e->getMessage()
        ], 500);
    }
	
    public function store(HotelRentRequest $request, $roomId)
	{
		DB::connection('tenant')->beginTransaction();
		try {
			$room = HotelRoom::findOrFail($roomId);
			if ($room->status !== 'DISPONIBLE' && !$request->is_booking ) {
				return response()->json([
					'success' => true,
					'message' => 'La habitación seleccionada no esta disponible',
				], 500);
			}

			$request->merge(['hotel_room_id' => $roomId]);
			$request->merge(['establishment_id' => $room->establishment_id]);
			$request->merge(['is_booking' => $request->is_booking? 1 : 0]);
			$request->merge(['status' => $request->is_booking? 'RESERVADO' : 'INICIADO']);
			$now = Carbon::parse($request->input_date);
			$request->merge(['input_date' => $now->format('Y-m-d')]);
			$request->merge(['towels' => (int)$request->towels]);
			$rent = HotelRent::create($request->only('travel_purpose','customer_id','rate_type', 'customer', 'notes', 'towels','matricula', 'hotel_room_id', 'hotel_rate_id', 'duration', 'quantity_persons', 'payment_status', 'output_date', 'output_time', 'input_date', 'input_time','data_persons','establishment_id', 'is_booking', 'status'));

			if(!$request->is_booking && ($request->rate_type === 'DAY' || $request->rate_type === 'HOUR')){
			  $room->status = 'OCUPADO';
			  $room->save();
			}else if(!$request->is_booking && $request->rate_type === 'MONTH'){
			  $room->status = 'RESIDENCIA';
			  $room->save();
			}

			$order = new HotelRentOrder();
			$order->hotel_rent_id = $rent->id;
			$order->order_number = 1;
			$order->order_status = $request->payment_status;
			$order->sale_note_id = $request->sale_note_id;
			$order->establishment_id = $room->establishment_id;
			$order->save();


			// Agregando la habitación a la lista de productos
			$item = new HotelRentItem();
			$item->type = 'HAB';
			$item->hotel_rent_id = $rent->id;
			$item->item_id = $request->product['item_id'];
			$item->item = $request->product;
			$item->payment_status = 'DEBT';
			$item->hotel_rent_order_id = $order->id;
			$item->save();

			switch ($rent->rate_type) {
				case 'DAY':
					$typeRate = 'noche(s)';
					break;
				case 'MONTH':
					$typeRate = 'mes(es)';
					break;
				case 'HOUR':
					$typeRate = 'hora(s)';
					break;
			}

			$product = $request->product;
			$product['payment_status'] = $request->payment_status;
			$product['name_product_pdf'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
			$product['item']['name_product_pdf'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
			$product['item']['description'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
			$product['item']['full_description'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
			$product['item']['name'] = 'Habitación '.$rent->room->name.' x '.$rent->duration.' '.$typeRate;
			$product['sale_note_id'] = $request->sale_note_id;
			$product['unit_value'] = 100 * $product['unit_price'] / ($product['percentage_igv']+100);
			$product['id'] = 1;
			$history = json_decode('[]', true);
			$history[] = $product;
			$historial = json_decode('[]', true);
			$historial[] = [
				'name' => $product['item']['name'],
				'quantity' => $product['quantity'],
				'unit_price' => $product['unit_price'],
				'total' => $product['total'],
				'date' => now()->format('Y-m-d H:i:s'),
				'id' => 1,
				'is_product' => false,
				'unique_id' => 1
			];
			$rent->history = json_encode($history);
			$rent->historial = json_encode($historial);
			$rent->save();
			if (isset($request->rent_payment) && (($request->payment_status == 'DEBT' && $request->rent_payment['amount'] > 0) || $request->payment_status == 'PAID')) {
				// Obtener el historial de pagos existente o inicializar como array vacío
				
				$paymentHistory = json_decode($rent->payment_history ?? '[]', true);
				if(isset($request->rent_payment['amount']) && $request->payment_status == 'DEBT' && $request->rent_payment['amount'] > 0){
					$total = $request->rent_payment['amount'];
					$type = 'advancePayment';
				}else if($request->payment_status == 'PAID'){
					$total = $request->total_to_pay;
					$type = 'payment';
				}

				// Crear registro del pago
				$paymentData = [
					'date' => now()->format('d/m/Y, H:i:s'),
					'amount' => (float)$total,
					'payment_method_type_id' => $request->rent_payment['payment_method_type_id'],
					'payment_destination_id' => $request->rent_payment['payment_destination_id'],
					'type' => $type,
					'description' => 'Pago de habitación',
					'reference' => ''
				];
				
				// Agregar el nuevo pago al historial
				$paymentHistory[] = $paymentData;
				$rent->payment_history = json_encode($paymentHistory);
				$rent->save();
			}
			
			//registrar pago
			$this->saveHotelRentItemPayment($request->rent_payment, $item);

			DB::connection('tenant')->commit();

			return response()->json([
				'success' => true,
				'message' => 'Habitación rentada de forma correcta.',
			], 200);
		} catch (\Throwable $th) {
			DB::connection('tenant')->rollBack();

			return response()->json([
				'success' => true,
				'message' => 'No se puede procesar su transacción. Detalles: ' . $th->getMessage(),
			], 500);
		}
	}

	private function getOrderNumberHotelRent($establishment_id)
	{
		
	}

	/**
	 *
	 * Registrar pago si la habitacion/producto fueron pagados
	 *
	 * @param  array $rent_payment
	 * @param  HotelRentItem $item
	 * @return void
	 */
	public function saveHotelRentItemPayment($rent_payment, HotelRentItem $item)
	{
		if($item->isPaid())
		{
			$record = $item->payments()->create([
				'date_of_payment' => date('Y-m-d'),
				'payment_method_type_id' => $rent_payment['payment_method_type_id'],
				'reference' => $rent_payment['reference'],
				'payment' => $rent_payment['payment'],
			]);

		}
	}


	/**
	 *
	 * Eliminar pago
	 *
	 * @param  HotelRentItem $item
	 * @return void
	 */
	public function deleteHotelRentItemPayment(HotelRentItem $item)
	{
		if(!is_null($item->payments))
		{
			$item->payments->delete();
		}
	}

	public function extendTime(Request $request, $rentId)
	{
		DB::beginTransaction();
		
		try {
			$rent = HotelRent::findOrFail($rentId);
	
			// Actualizar o crear el ítem de la habitación
			$item = $rent->items->where('type', 'HAB')->where('payment_status', 'DEBT')->first();
	
			if($item) {
				$item->item = $request->item["item"];
				$item->save();
			} else {
				$item = new HotelRentItem();
				$item->type = 'HAB';
				$item->hotel_rent_id = $rent->id;
				$item->item_id = $request->item["item_id"];
				$item->item = $request->item["item"];
				$item->payment_status = 'DEBT';
				$item->hotel_rent_order_id = $request->item["hotel_rent_order_id"] ?? null;
				$item->save();
			}
			switch ($rent->rate_type) {
				case 'DAY':
					$typeRate = 'noche(s)';
					break;
				case 'MONTH':
					$typeRate = 'mes(es)';
					break;
				case 'HOUR':
					$typeRate = 'hora(s)';
					break;
			}
			$product = $request->item['item'];
			$product['payment_status'] = 'DEBT';
			$product['sale_note_id'] = '';
			$product['quantity'] = $request->duration - $rent->duration;
			$product['unit_price'] = $request->custom_price;
			$product['total'] = $request->custom_price * ($request->duration - $rent->duration);
			$product['total_base_igv'] = round($product['total'] / (1 + $product['percentage_igv'] / 100), 2);
			$product['total_value'] = $product['total_base_igv'];
			$product['total_igv'] = $product['total'] - $product['total_base_igv'];
			$product['total_taxes'] = $product['total_igv'];
			$product['total_value_without_rounding'] = $product['total_base_igv'];
			$product['total_base_igv_without_rounding'] = $product['total_base_igv'];
			$product['total_igv_without_rounding'] = $product['total_igv'];
			$product['total_taxes_without_rounding'] = $product['total_igv'];
			$product['total_without_rounding'] = $product['total'];
			$product['item']['name'] =  'Habitacion '.$rent->room->name.' x '.$product['quantity'].' '.$typeRate;
			$product['item']['full_description'] = 'Habitacion '.$rent->room->name.' x '.$product['quantity'].' '.$typeRate;
			$product['item']['description'] = 'Habitacion '.$rent->room->name.' x '.$product['quantity'].' '.$typeRate;
			$product['name_product_pdf'] = 'Habitacion '.$rent->room->name.' x '.$product['quantity'].' '.$typeRate;
			$history = json_decode($rent->history ?? '[]', true);
			$lastProduct = null;
			$duration = $request->duration;
			$output_date = $request->output_date;
			$output_time = $request->output_time;
			foreach(array_reverse($history) as $itemHis){
				if((int)$itemHis['item_id'] == (int)$product['item_id']){
					$lastProduct = $itemHis;
					$lastProductKey = array_search($itemHis, $history);
					break;
				}
			}
			if($lastProduct && (int)$lastProduct['unit_price'] == (int)$product['unit_price']){
				$lastProduct['quantity'] += $product['quantity'];
				$lastProduct['extended'] = true;
				$lastProduct['total'] += $product['total'];
				$lastProduct['total_base_igv'] += $product['total_base_igv'];
				$lastProduct['total_value'] += $product['total_value'];
				$lastProduct['total_igv'] += $product['total_igv'];
				$lastProduct['total_taxes'] += $product['total_taxes'];
				$lastProduct['total_value_without_rounding'] += $product['total_value_without_rounding'];
				$lastProduct['total_base_igv_without_rounding'] += $product['total_base_igv_without_rounding'];
				$lastProduct['total_igv_without_rounding'] += $product['total_igv_without_rounding'];
				$lastProduct['total_taxes_without_rounding'] += $product['total_taxes_without_rounding'];
				$lastProduct['total_without_rounding'] += $product['total_without_rounding'];
				$lastProduct['item']['name'] = 'Habitacion '.$rent->room->name.' x '.$lastProduct['quantity'].' '.$typeRate;
				$lastProduct['item']['full_description'] = 'Habitacion '.$rent->room->name.' x '.$lastProduct['quantity'].' '.$typeRate;
				$lastProduct['item']['description'] = 'Habitacion '.$rent->room->name.' x '.$lastProduct['quantity'].' '.$typeRate;
				$lastProduct['item']['name_product_pdf'] = 'Habitacion '.$rent->room->name.' x '.$lastProduct['quantity'].' '.$typeRate;
				$lastProduct['name_product_pdf'] = 'Habitacion '.$rent->room->name.' x '.$lastProduct['quantity'].' '.$typeRate;
				array_splice($history, $lastProductKey, 1, [$lastProduct]);
				if ($lastProduct['quantity'] == 0) {
					unset($history[$lastProductKey]);
					$history = array_values($history);
				}
				$productId = $lastProduct['id'];
			}else{
				$maxId = 0;
				foreach($history as $itemHis){
					if((int)$itemHis['id'] > $maxId){
						$maxId = (int)$itemHis['id'];
					}
				}
				$product['id'] = $maxId + 1;
				$productId = $product['id'];
				$history[] = $product;

			}
			$uniqueId = 0;
			$historial = json_decode($rent->historial ?? '[]', true);
			foreach($historial as $itemHis){
				if(isset($itemHis['unique_id']) && (int)$itemHis['unique_id']  > $uniqueId){
					$uniqueId = (int)$itemHis['unique_id'];
				}
			}
			$uniqueId++;
			$historial[] = [
				'name' => 'Extension de estadia',
				'quantity' => $product['quantity'],
				'unit_price' => $product['unit_price'],
				'total' => $product['total'],
				'date' => now()->format('Y-m-d H:i:s'),
				'id' => $productId,
				'unique_id' => $uniqueId,
				'is_product' => false,
				'total_base_igv' => $product['total_base_igv'],
				'total_igv' => $product['total_igv'],
				'total_taxes' => $product['total_taxes'],
				'total_value' => $product['total_value'],
				'total_value_without_rounding' => $product['total_value_without_rounding'],
				'total_base_igv_without_rounding' => $product['total_base_igv_without_rounding'],
				'total_igv_without_rounding' => $product['total_igv_without_rounding'],
				'total_taxes_without_rounding' => $product['total_taxes_without_rounding'],
				'total_without_rounding' => $product['total_without_rounding'],
			];
			$rent->history = json_encode($history);
			$rent->historial = json_encode($historial);

			$rent->save();

			// Actualizar detalles de la renta
			$rent->duration = $duration;
			$rent->output_date = $output_date;
			$rent->output_time = $output_time;
			$rent->save();

			
			// Manejar pago adelantado si se proporciona
			if (isset($request->advance_amount) && $request->advance_amount > 0) {
				// Obtener el historial de pagos existente o inicializar como array vacío
				
				$paymentHistory = json_decode($rent->payment_history ?? '[]', true);
				
				// Crear registro del pago
				$paymentData = [
					'date' => now()->format('Y-m-d H:i:s'),
					'amount' => (float)$request->advance_amount,
					'payment_method_type_id' => $request->payment_method_type_id,
					'payment_destination_id' => $request->payment_destination_id,
					'type' => 'advancePayment',
					'description' => 'Pago adelantado por extensión de tiempo',
					'reference' => ''
				];
				
				// Agregar el nuevo pago al historial
				$paymentHistory[] = $paymentData;
				$rent->payment_history = json_encode($paymentHistory);
				$rent->save();
				
			}
			
	
			// Guardar los cambios en la renta
			$rent->save();
			
			DB::commit();
			
			return response()->json([
				'success' => true,
				'message' => 'Tiempo de estadía extendido correctamente',
				'data' => $item
			]);
			
		} catch (\Exception $e) {
			DB::rollBack();
			\Log::error('Error al extender el tiempo de estadía: ' . $e->getMessage());
			\Log::error($e->getTraceAsString());
			
			return response()->json([
				'success' => false,
				'message' => 'Error al extender el tiempo de estadía: ' . $e->getMessage()
			], 500);
		}
	}


	public function searchCustomers()
	{
		$customers = $this->customers();

		return response()->json([
			'customers' => $customers,
		], 200);
	}

	public function showFormAddProduct($rentId){
		$rent = HotelRent::with('room')
			->findOrFail($rentId);

		$establishment = Establishment::query()->find(auth()->user()->establishment_id);
		$configuration = Configuration::first();

		$products = HotelRentItem::select(
				'hotel_rent_items.*', 
				DB::raw("IFNULL(CONCAT(sale_notes.series, '-', sale_notes.number), '') as document")
			)
			->leftJoin('hotel_rent_orders', 'hotel_rent_items.hotel_rent_order_id', '=', 'hotel_rent_orders.id')
			->leftJoin('sale_notes', 'hotel_rent_orders.sale_note_id', '=', 'sale_notes.id')
			->where('hotel_rent_items.hotel_rent_id', $rentId)
			->where('hotel_rent_items.type', 'PRO')
			->get();
			
		$series = Series::where('establishment_id',  auth()->user()->establishment_id)->get();

		return view('hotel::rooms.add-product-to-room', compact('rent', 'configuration', 'products', 'establishment','series'));
	}


	/**
	 *
	 * Agregar productos al rentar habitacion
	 *
	 * @param  HotelRentItemRequest $request
	 * @param  int $rentId
	 * @return array
	 */
	public function addProductsToRoom(HotelRentItemRequest $request, $rentId)
	{
		$rent = HotelRent::findOrFail($rentId);

		if( isset($request->sale_note_id) && $request->sale_note_id !=null) {
			$order = new HotelRentOrder();
			$order->hotel_rent_id = $rentId;
			$order->order_number = 1;
			$order->order_status = 'PAID';
			$order->sale_note_id = $request->sale_note_id;
			$order->establishment_id = $rent->establishment_id;
			$order->save();
		}
		
		foreach ($request->products as $product) {
			$item = HotelRentItem::where('hotel_rent_id', $rentId)
				->where('id', $product['id'])
				->first();
			if (!$item) {
				$item = new HotelRentItem();
				$item->type = 'PRO';
				$item->hotel_rent_id = $rentId;
				$item->item_id = $product['item_id'];
				$item->payment_status = $product['payment_status'];
				$item->save();

				$this->saveHotelRentItemPayment($product['rent_payment'], $item);
			}
			if($product['payment_status']=='PAID'){
				$paymentHistory = json_decode($rent->payment_history ?? '[]', true);
				$paymentData = [
					'date' => now()->format('d/m/Y, H:i:s'),
					'amount' => (float)$product['total'],
					'payment_method_type_id' => '01',
					'payment_destination_id' => 'cash',
					'type' => 'advance',
					'description' => $product['item']['name'],
					'reference' => ''
				];
				
				// Agregar el nuevo pago al historial
				$paymentHistory[] = $paymentData;
				$rent->payment_history = json_encode($paymentHistory);
				$rent->save();
			}
			$item->item = $product;
			$product['sale_note_id'] = $request->sale_note_id;
			$product['hidden'] = true;
			$maxId = 0;
			foreach (json_decode($rent->historial ?? '[]', true) as $key => $value) {
				if(isset($value['id']) && $value['id'] > $maxId){
					$maxId = $value['id'];
				}
			}
			$productId = $maxId + 1;
			$product['id'] = $productId;
			$historial = json_decode($rent->historial ?? '[]', true);
			$uniqueId = 0;
			foreach($historial as $itemM){
				if(isset($itemM['unique_id']) && (int)$itemM['unique_id'] > $uniqueId){
					$uniqueId = (int)$itemM['unique_id'];
				}
			}
			$uniqueId++;
			$historial[] = [
				'name' => $product['item']['description'],
				'quantity' => $product['quantity'],
				'unit_price' => $product['unit_price'],
				'total' => $product['total'],
				'product_id' => $item->id,
				'date' => now()->format('Y-m-d H:i:s'),
				'is_product' => true,
				'id' => $productId,
				'unique_id' => $uniqueId,
				'total_taxes' => $product['total_taxes'],
				'total_igv' => $product['total_igv'],
				'total_value' => $product['total_value'],
				'total_plastic_bag_taxes' => $product['total_plastic_bag_taxes'],
				'total_base_other_taxes' => $product['total_base_other_taxes'],
				'total_other_taxes' => $product['total_other_taxes'],
				'total_base_igv' => $product['total_base_igv'],
			];
			$rent->historial = json_encode($historial);
			$history = json_decode($rent->history ?? '[]', true);
			$is = false;
			foreach ($history as $key => $value) {
				if(
					(int)$value['item_id'] == (int)$product['item_id']
				){
					$history[$key]['quantity'] += $product['quantity'];
					$history[$key]['total'] += $product['total'];
					$history[$key]['sale_note_ids'][] = $product['sale_note_id'];
					$history[$key]['total_taxes'] += $product['total_taxes'];
					$history[$key]['total_igv'] += $product['total_igv'];
					$history[$key]['total_value'] += $product['total_value'];
					$history[$key]['total_plastic_bag_taxes'] += $product['total_plastic_bag_taxes'];
					$history[$key]['total_base_other_taxes'] += $product['total_base_other_taxes'];
					$history[$key]['total_other_taxes'] += $product['total_other_taxes'];
					$history[$key]['total_base_igv'] += $product['total_base_igv'];
					$is = true;
					break;
				}
			}
			if(!$is){
				$history[] = $product;
			}
			$rent->history = json_encode($history);
			$rent->save();
			$item->payment_status = $product['payment_status'];
			$item->hotel_rent_order_id = ($product['payment_status']=='PAID')? $order->id: null;
			$item->save();
            $idInRequest[] = $item->id;

		}

		return response()->json([
			'success' => true,
			'message' => 'Información actualizada.'
		], 200);
	}

  public function showFormChekout($rentId)
  {
    $rent = HotelRent::with('room', 'room.category', 'items')
      ->findOrFail($rentId);

	$items = HotelRentItem::select(
			'hotel_rent_items.*', 
			DB::raw("IFNULL(CONCAT(sale_notes.series, '-', sale_notes.number), '') as document"),
			DB::raw("IFNULL(sale_notes.total, 0) as sale_note_total")
		)
		->leftJoin('hotel_rent_orders', 'hotel_rent_items.hotel_rent_order_id', '=', 'hotel_rent_orders.id')
		->leftJoin('sale_notes', 'hotel_rent_orders.sale_note_id', '=', 'sale_notes.id')
		->where('hotel_rent_items.hotel_rent_id', $rent->id)
		->get();
		
	
	
	$room = $items->firstWhere('type', 'HAB');

    $customer = Person::withOut('department', 'province', 'district')
      ->findOrFail($rent->customer_id);

        $payment_method_types = PaymentMethodType::getPaymentMethodTypes();
        $payment_destinations = $this->getPaymentDestinations();
        $series = Series::where('establishment_id',  auth()->user()->establishment_id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
    	$affectation_igv_types = AffectationIgvType::whereActive()->get();
		$payments = HotelRentItemPayment::whereHas('associated_record_payment', function ($query) use($rentId) {
			$query->whereHas('hotel_rent', function ($query) use ($rentId) {
				$query->where('id', $rentId);
			});
		})->get();

    return view('hotel::rooms.checkout', compact(
            'rent', 'room',
            'customer',
            'payment_method_types',
            'payment_destinations',
            'series',
            'document_types_invoice',
      		'affectation_igv_types',
			'payments',
			'items'
        ));
  }

  public function finalizeRent($rentId)
  {
    $rent = HotelRent::findOrFail($rentId);
	$rent->output_date = Carbon::now()->format('Y-m-d');
	$rent->output_time = Carbon::now()->format('H:i');
	$rent->save();
    $items = HotelRentItem::where('hotel_rent_id', $rentId)->get();
    $rent->update([
      'arrears' => request('arrears'),
      'payment_status' => 'PAID',
      'status'  => 'FINALIZADO'
    ]);
	
    foreach ($items as $item) {
      $item->update([
        'payment_status' => 'PAID',
      ]);
    }
    HotelRoom::where('id', $rent->hotel_room_id)
      ->update([
        'status' => 'LIMPIEZA'
      ]);
        $rent = HotelRent::with('room', 'room.category', 'items')->findOrFail($rentId);
    return response()->json([
      'success' => true,
      'message' => 'Información procesada de forma correcta.',
            'currentRent' => $rent
		], 200);
	}

    /**
     * Update the rent status to 'INICIADO' when checking in
     *
     * @param int $rentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkin($rentId)
    {
        DB::beginTransaction();
        try {
            $rent = HotelRent::findOrFail($rentId);
            
            // Update rent status to 'INICIADO' and set is_booking to 0
            $rent->status = 'INICIADO';
            $rent->is_booking = 0;
            $rent->save();
            
            // Update room status to 'OCUPADO' if it was a booking
            if ($rent->is_booking) {
                $room = $rent->room;
                if ($room) {
                    $room->status = 'OCUPADO';
                    $room->save();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado correctamente.',
                'redirect' => url('/hotels/reception')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el check-in: ' . $e->getMessage()
            ], 500);
        }
    }

	private function customers()
	{
		$customers = Person::with('addresses')
			->whereType('customers')
			->whereIsEnabled()
			->whereIn('identity_document_type_id', [1, 4, 6])
			->orderBy('name');

		$query = request('input');
		$search_by_barcode = (bool)request('search_by_barcode');
		if ($query && $search_by_barcode) {

			$customers = $customers->where('barcode', 'like', "%{$query}%");
		}else{
			if (is_numeric($query)) {
				$customers = $customers->where('number', 'like', "%{$query}%");
			}else {
				$customers = $customers->where('name', 'like', "%{$query}%");
			}
		}

		$customers = $customers->take(20)
			->get()
			->transform(function ($row) {
				return [
					'id'                          => $row->id,
					'description'                 => $row->number . ' - ' . $row->name,
					'name'                        => $row->name,
					'number'                      => $row->number,
					'identity_document_type_id'   => $row->identity_document_type_id,
					'identity_document_type_code' => $row->identity_document_type->code,
					'addresses'                   => $row->addresses,
					'address'                     => $row->address,
					'internal_code'               => $row->internal_code,
					'barcode'					  => $row->barcode
				];
			});

		return $customers;
	}

	public function tables()
	{
		$customers = $this->customers();
		$configuration = Configuration::select('affectation_igv_type_id')->first();

        $payment_method_types = PaymentMethodType::getTableCashPaymentMethodTypes();
        $payment_destinations = $this->getPaymentDestinations();

		return response()->json([
			'customers' => $customers,
			'configuration' => $configuration,
			'payment_method_types' => $payment_method_types,
			'payment_destinations' => $payment_destinations
		], 200);
	}


	/**
	 *
	 * Datos relacionados para agregar productos al rentar habitacion
	 *
	 * @return array
	 */
	public function rentProductsTables()
	{
        $payment_method_types = PaymentMethodType::getTableCashPaymentMethodTypes();
        $payment_destinations = $this->getPaymentDestinations();

		return [
			'payment_method_types' => $payment_method_types,
			'payment_destinations' => $payment_destinations
		];
	}

    public function report($start, $end, $establishment_id)
    {
		$user = auth()->user();
		$establishment = $user->establishment;
		$query = HotelRent::whereBetween('input_date', [$start, $end]);

		if ($establishment_id && $user->type === 'admin') {
			$query->where('establishment_id', $establishment_id);
			$establishment = Establishment::findOrFail($establishment_id);
		}

		if ($user->type != 'admin') {
			$query->where('establishment_id', $user->establishment_id);
		}
		
        $documents = $query->get();

        $records = collect($documents)->transform(function ($row) {

			$data_persons = collect((array) $row->data_persons)
				->map(function ($person) {
					$name = isset($person->name) ? $person->name : '';
					$number = isset($person->number) ? $person->number : '';
					return trim("{$name} {$number}", " ");
				})
				->implode('; ');

			$document_number = "";
			$document_date = "";
			$total = "";

			$document = Document::where('hotel_rent_id',$row->id)->first();

			if($document){
				$document_number = $document->series.'-'.$document->number;
				$document_date = $document->date_of_issue;
				$total = $document->total;
			}

			$sale_note = SaleNote::where('hotel_rent_id',$row->id)->first();

			if($sale_note){
				$document_number = $sale_note->series.'-'.$sale_note->number;
				$document_date = $sale_note->date_of_issue;
				$total = $sale_note->total;
			}
			switch ($row->rate_type) {
				case 'DAY':
					$rate_type = 'dia(s)';
					break;
				case 'HOUR':
					$rate_type = 'hora(s)';
					break;
				case 'MONTH':
					$rate_type = 'mes(es)';
					break;
			}

            return [
                'id' => $row->id,
				'room_name' => $row->room->name,
                'customer' => $row->customer->description,
                'document_number' => $document_number,
                'document_date' => $document_date,
                'total' => $total,
                'input_date' => $row->input_date,
                'input_time' => $row->input_time,
				'output_date' => $row->output_date,
				'rate_type' => $rate_type,
                'output_time' => $row->output_time,
				'travel_purpose' => $row->travel_purpose,
                'duration' => $row->duration,
                'quantity_persons' => $row->quantity_persons,
                'category' => $row->room->category->description,
				'data_persons' => $data_persons,
            ];
        });

        $filename = "Reporte_Recepción";
		$company = Company::first();

		return (new HotelRentExport)
			->records($records)
			->company($company)
            ->establishment($establishment)
			->download($filename . Carbon::now() . '.xlsx');
		
    }

    /**
     * Actualiza las observaciones de una renta
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateObservations(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'observations' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rent = HotelRent::findOrFail($id);
            
            $rent->observations = $request->input('observations');
            $rent->save();

            return response()->json([
                'success' => true,
                'message' => 'Observaciones actualizadas correctamente',
                'data' => $rent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las observaciones: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualiza el historial de pagos de una renta
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentHistory(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'payment_history' => 'required|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rent = HotelRent::findOrFail($id);
            
            // Decodificar el historial de pagos
            $paymentHistory = json_decode($request->input('payment_history'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('El formato del historial de pagos no es válido');
            }
            
            // Actualizar el historial de pagos
            $rent->payment_history = $request->input('payment_history');
            $rent->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Historial de pagos actualizado correctamente',
                'data' => [
                    'payment_history' => $paymentHistory,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar el historial de pagos: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el historial de pagos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene las habitaciones disponibles para cambio
     * 
     * @param int $currentRoomId ID de la habitación actual
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableRoomsForChange($currentRoomId)
    {
        try {
            $currentRoom = HotelRoom::findOrFail($currentRoomId);
            
            // Obtener habitaciones disponibles del mismo tipo (misma categoría)
            $availableRooms = HotelRoom::where('id', '!=', $currentRoomId)
                ->where('status', 'DISPONIBLE')
                ->with(['category', 'floor'])
                ->get();
            
            return response()->json([
                'success' => true,
                'rooms' => $availableRooms
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las habitaciones disponibles: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cambia una habitación de una renta
     * 
     * @param int $rentId ID de la renta
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeRoom($rentId, Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'new_room_id' => 'required',
                'observations' => 'nullable|string|max:1000'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            $rateid = $request->hotel_rate_id;
			$newRoom = HotelRoom::findOrFail($request->new_room_id);
			$rate = HotelRoomRate::findOrFail($rateid);
			$precio = $rate->price;
            $rent = HotelRent::findOrFail($rentId);
			$item = $rent->items()->where('hotel_rent_id', $rentId)->first();
			$dataItem = $item->item;
			$daysLeft = 0;
			switch ($rent->rate_type) {
				case 'DAY':
					$daysLeft = (int)Carbon::parse($rent->output_date)->diffInDays(Carbon::now()) + 1;
					$typeRate = 'noche(s)';
					break;
				case 'MONTH':
					$daysLeft = (int)Carbon::parse($rent->output_date)->diffInMonths(Carbon::now()) + 1;
					$typeRate = 'mes(es)';
					break;
				case 'HOUR':
					$daysLeft = (int)Carbon::parse($rent->output_date . ' ' . $rent->output_time)->diffInHours(Carbon::now()) + 1;
					$typeRate = 'hora(s)';
					break;
			}
			$quantity = $daysLeft;
			$history = array_values(json_decode($rent->history, true));
			$product = $history[0];
			foreach (array_reverse($history, true) as $key => $histor) {
				if (isset($histor['hidden']) && $histor['hidden']) {
					continue;
				}
				if($histor['quantity'] <= $daysLeft){
					unset($history[$key]);
					$daysLeft -= $histor['quantity'];
					if($histor['sale_note_id']){
						$SaleNote = SaleNote::where('id', $histor['sale_note_id'])->first();
						if($SaleNote){
							$SaleNote->state_type_id = 11;
							$SaleNote->update();
						}
					}
				}else{
					if($history[$key]['sale_note_id']){
						$SaleNote = SaleNote::where('id', $history[$key]['sale_note_id'])->first();
						if($SaleNote){
							$SaleNote->state_type_id = 11;
							$SaleNote->update();
						}
						$history[$key]['sale_note_id'] = null;
					}
					$history[$key]['quantity'] -= $daysLeft;
					$history[$key]['name_product_pdf'] = 'Habitación '.$newRoom->name.' x '.$history[$key]['quantity'].' '.$typeRate;
					$history[$key]['item']['name_product_pdf'] = 'Habitación '.$newRoom->name.' x '.$history[$key]['quantity'].' '.$typeRate;
					$history[$key]['item']['description'] = 'Habitación '.$newRoom->name.' x '.$history[$key]['quantity'].' '.$typeRate;
					$history[$key]['item']['full_description'] = 'Habitación '.$newRoom->name.' x '.$history[$key]['quantity'].' '.$typeRate;
					$history[$key]['item']['name'] = 'Habitación '.$newRoom->name.' x '.$history[$key]['quantity'].' '.$typeRate;
					$history[$key]['total'] -= $histor['unit_price'] * $daysLeft;
					$history[$key]['total_base_igv'] = round($history[$key]['total'] / (1 + $history[$key]['percentage_igv'] / 100), 2);
					$history[$key]['total_value'] = $history[$key]['total_base_igv'];
					$history[$key]['total_igv'] = $history[$key]['total'] - $history[$key]['total_base_igv'];
					$history[$key]['total_taxes'] = $history[$key]['total_igv'];
					$history[$key]['total_value_without_rounding'] = $history[$key]['total_base_igv'];
					$history[$key]['total_base_igv_without_rounding'] = $history[$key]['total_base_igv'];
					$history[$key]['total_igv_without_rounding'] = $history[$key]['total_igv'];
					$history[$key]['total_taxes_without_rounding'] = $history[$key]['total_igv'];
					$history[$key]['total_without_rounding'] = $history[$key]['total'];
					$daysLeft = 0;
				}
			}
			$product['quantity'] = $quantity;
			$product['item']['name'] = 'Habitación '.$newRoom->name.' x '.$quantity.' '.$typeRate;
			$product['name_product_pdf'] = 'Habitación '.$newRoom->name.' x '.$quantity.' '.$typeRate;
			$product['item']['name_product_pdf'] = 'Habitación '.$newRoom->name.' x '.$quantity.' '.$typeRate;
			$product['item']['description'] = 'Habitación '.$newRoom->name.' x '.$quantity.' '.$typeRate;
			$product['item']['full_description'] = 'Habitación '.$newRoom->name.' x '.$quantity.' '.$typeRate;
			$product['unit_price'] = $precio;
			$product['total'] = round($precio * $quantity, 2);
			$product['total_base_igv'] = round($product['total'] / (1 + $product['percentage_igv'] / 100), 2);
			$product['total_value'] = $product['total_base_igv'];
			$product['total_igv'] = $product['total'] - $product['total_base_igv'];
			$product['total_taxes'] = $product['total_igv'];
			$product['total_value_without_rounding'] = $product['total_base_igv'];
			$product['total_base_igv_without_rounding'] = $product['total_base_igv'];
			$product['total_igv_without_rounding'] = $product['total_igv'];
			$product['total_taxes_without_rounding'] = $product['total_igv'];
			$product['total_without_rounding'] = $product['total'];
			$maxId = 0;
			foreach ($history as $key => $value) {
				if($value['id'] > $maxId){
					$maxId = $value['id'];
				}
			}
			$product['id'] = $maxId + 1;
			$productId = $product['id'];
			$history[] = $product;
			$historial = json_decode($rent->historial ?? '[]', true);
			$uniqueId = 0;
			foreach($historial as $itemHis){
				if(isset($itemHis['unique_id']) && (int)$itemHis['unique_id'] > $uniqueId){
					$uniqueId = (int)$itemHis['unique_id'];
				}
			}
			$uniqueId++;
			$historial[] = [
				'name' => 'Cambio de Habitacion: '.$product['item']['name_product_pdf'],
				'quantity' => $quantity,
				'unit_price' => $precio,
				'total' => $product['total'],
				'date' => now()->format('Y-m-d H:i:s'),
				'id' => $productId,
				'is_product' => false,
				'unique_id' => $uniqueId,
				'delete' => true,
			];
			$rent->history = json_encode(array_values($history));
			$rent->historial = json_encode($historial);
			$rent->save();
			$precioTotal = -1*($dataItem->unit_price - $precio) * $quantity;
			$dataItem->total += $precioTotal;
			$dataItem->unit_price = $precio;
			$item->item = $dataItem;
			$item->save();
            $oldRoomId = $rent->hotel_room_id;
            $newRoomId = $request->new_room_id;
            
            // Verificar que la nueva habitación esté disponible
            $newRoom = HotelRoom::findOrFail($newRoomId);
            
            if ($newRoom->status !== 'DISPONIBLE') {
                return response()->json([
                    'success' => false,
                    'message' => 'La habitación seleccionada no está disponible'
                ], 400);
            }
            
            // Actualizar la renta con la nueva habitación
            $rent->hotel_room_id = $newRoomId;
            
            // Agregar observación si se proporcionó
            if (!empty($request->observations)) {
                $observations = $rent->observations;
				$observations = 'Cambio de habitación: '.$request->observations;
                $rent->observations = $observations;
            }
            
            $rent->save();
            
            // Actualizar el estado de las habitaciones
            $oldRoom = HotelRoom::find($oldRoomId);
            if ($oldRoom) {
                $oldRoom->status = 'DISPONIBLE';
                $oldRoom->save();
            }
            
            $newRoom->status = 'OCUPADO';
            $newRoom->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Habitación cambiada exitosamente',
                'data' => [
                    'old_room_id' => $oldRoomId,
                    'new_room_id' => $newRoomId,
					'rent' => $precioTotal,
					'item' => $item
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cambiar de habitación: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar de habitación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un registro específico del historial de una renta
     *
     * @param int $rentId
     * @param int $historyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteHistoryRecord($rentId, $historyId)
    {
        try {
            DB::beginTransaction();
            
            // Buscar la renta
            $rent = HotelRent::findOrFail($rentId);
            
            // Obtener el historial actual
            $history = json_decode($rent->historial ?? '[]', true);
            $originalHistory = json_decode($rent->historial ?? '[]', true);
            
            // Filtrar el historial para eliminar el registro con el ID especificado
            $updatedHistory = array_filter($history, function($record) use ($historyId) {
				if(($record['unique_id'] ?? null) == $historyId){
					$recordToDelete = $record;
				}
                return ($record['unique_id'] ?? null) != $historyId;
            });
			foreach($history as $key => $record){
				if(isset($record['unique_id']) && $record['unique_id'] == $historyId){
					$recordToDelete = $record;
				}
			}
            
            // Si no hubo cambios, el registro no existía
            if (count($history) === count($updatedHistory)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El registro del historial no fue encontrado.'
                ], 404);
            }
            
            // Actualizar el historial
            $rent->historial = json_encode(array_values($updatedHistory));
            
            // También actualizamos el historial principal si es necesario
            $mainHistory = json_decode($rent->history ?? '[]', true);
            $itemToDelete = null;
			$itemId = $recordToDelete['id'];
            foreach($mainHistory as $key => $record){
                if($record['id'] == $itemId){
                    $itemToDelete = $key;
                    break;
                }
            }
            if($itemToDelete !== null){
				if($recordToDelete['quantity'] < $mainHistory[$itemToDelete]['quantity']){
					if($recordToDelete['is_product']){
						$mainHistory[$itemToDelete]['quantity'] -= $recordToDelete['quantity'];
						$mainHistory[$itemToDelete]['total'] -= $recordToDelete['total'];
						$mainHistory[$itemToDelete]['total_taxes'] -= $recordToDelete['total_taxes'];
						$mainHistory[$itemToDelete]['total_igv'] -= $recordToDelete['total_igv'];
						$mainHistory[$itemToDelete]['total_value'] -= $recordToDelete['total_value'];
						$mainHistory[$itemToDelete]['total_plastic_bag_taxes'] -= $recordToDelete['total_plastic_bag_taxes'];
						$mainHistory[$itemToDelete]['total_base_other_taxes'] -= $recordToDelete['total_base_other_taxes'];
						$mainHistory[$itemToDelete]['total_other_taxes'] -= $recordToDelete['total_other_taxes'];
						$mainHistory[$itemToDelete]['total_base_igv'] -= $recordToDelete['total_base_igv'];
						if(isset($recordToDelete['product_id'])){
							$item = HotelRentItem::where('hotel_rent_id', $rentId)
								->where('id', $recordToDelete['product_id'])
								->first();
							if($item){
								$item->item = $mainHistory[$itemToDelete];
							}
						}
					}
					else{
						$rent->duration = $rent->duration - $recordToDelete['quantity'];
						switch ($rent->rate_type) {
							case 'DAY':
								$rent->output_date = Carbon::createFromFormat('Y-m-d', $rent->output_date)->subDays($recordToDelete['quantity'])->format('Y-m-d');
								$typeRate = 'noche(s)';
								break;
							case 'MONTH':
								$rent->output_date = Carbon::createFromFormat('Y-m-d', $rent->output_date)->subMonths($recordToDelete['quantity'])->format('Y-m-d');
								$typeRate = 'mes(es)';
								break;
							case 'HOUR':
								$date = Carbon::createFromFormat('Y-m-d H:i:s', $rent->output_date.' '.$rent->output_time)->subHours($recordToDelete['quantity'])->format('Y-m-d H:i:s');
								$rent->output_date = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
								$rent->output_time = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('H:i:s');
								$typeRate = 'hora(s)';
								break;
						}
						
						$mainHistory[$itemToDelete]['quantity'] -= $recordToDelete['quantity'];
						$mainHistory[$itemToDelete]['name_product_pdf'] =  'Habitacion '.$rent->room->name.' x '.$mainHistory[$itemToDelete]['quantity'].' '.$typeRate;
						$mainHistory[$itemToDelete]['item']['name'] =  'Habitacion '.$rent->room->name.' x '.$mainHistory[$itemToDelete]['quantity'].' '.$typeRate;
						$mainHistory[$itemToDelete]['item']['description'] =  'Habitacion '.$rent->room->name.' x '.$mainHistory[$itemToDelete]['quantity'].' '.$typeRate;
						$mainHistory[$itemToDelete]['item']['name_product_pdf'] = 'Habitacion '.$rent->room->name.' x '.$mainHistory[$itemToDelete]['quantity'].' '.$typeRate;
						$mainHistory[$itemToDelete]['item']['full_description'] = 'Habitacion '.$rent->room->name.' x '.$mainHistory[$itemToDelete]['quantity'].' '.$typeRate;
						$mainHistory[$itemToDelete]['total'] -= $recordToDelete['total'];
						$mainHistory[$itemToDelete]['total_base_igv'] -= $recordToDelete['total_base_igv'];
						$mainHistory[$itemToDelete]['total_igv'] -= $recordToDelete['total_igv'];
						$mainHistory[$itemToDelete]['total_taxes'] -= $recordToDelete['total_taxes'];
						$mainHistory[$itemToDelete]['total_value'] -= $recordToDelete['total_value'];
						$mainHistory[$itemToDelete]['total_value_without_rounding'] -= $recordToDelete['total_value_without_rounding'];
						$mainHistory[$itemToDelete]['total_base_igv_without_rounding'] -= $recordToDelete['total_base_igv_without_rounding'];
						$mainHistory[$itemToDelete]['total_igv_without_rounding'] -= $recordToDelete['total_igv_without_rounding'];
						$mainHistory[$itemToDelete]['total_taxes_without_rounding'] -= $recordToDelete['total_taxes_without_rounding'];
						$mainHistory[$itemToDelete]['total_without_rounding'] -= $recordToDelete['total_without_rounding'];
					}
				}else{
					if(!$recordToDelete['is_product']){
						$rent->duration = $rent->duration - $recordToDelete['quantity'];
						switch ($rent->rate_type) {
							case 'DAY':
								$rent->output_date = Carbon::createFromFormat('Y-m-d', $rent->output_date)->subDays($recordToDelete['quantity'])->format('Y-m-d');
								$typeRate = 'noche(s)';
								break;
							case 'MONTH':
								$rent->output_date = Carbon::createFromFormat('Y-m-d', $rent->output_date)->subMonths($recordToDelete['quantity'])->format('Y-m-d');
								$typeRate = 'mes(es)';
								break;
							case 'HOUR':
								$date = Carbon::createFromFormat('Y-m-d H:i:s', $rent->output_date.' '.$rent->output_time)->subHours($recordToDelete['quantity'])->format('Y-m-d H:i:s');
								$rent->output_date = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
								$rent->output_time = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('H:i:s');
								$typeRate = 'hora(s)';
								break;
						}
					}else{
						if(isset($recordToDelete['product_id'])){
							$item = HotelRentItem::where('hotel_rent_id', $rentId)
								->where('id', $recordToDelete['product_id'])
								->first();
							if($item){
								$item->forceDelete();
							}
						}
						if(isset($mainHistory[$itemToDelete]['sale_note_ids'])){
							foreach($mainHistory[$itemToDelete]['sale_note_ids'] as $sale_note_id){
								$SaleNote = SaleNote::where('id', $sale_note_id)->first();
								if($SaleNote){
									$SaleNote->state_type_id = 11;
									$SaleNote->update();
								}
							}
						}
					}
					unset($mainHistory[$itemToDelete]);
					$mainHistory = array_values($mainHistory);
				}
            }
            $rent->history = json_encode(array_values($mainHistory));

			
            $rent->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'historial' => $updatedHistory
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualiza el método de pago de un pago en el historial
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentMethod(Request $request, $id)
    {
        try {
			DB::beginTransaction();
            
            $rent = HotelRent::findOrFail($id);


            $paymentHistory = json_decode($rent->payment_history, true);
            
            // Validar que el índice del pago sea válido
            $paymentIndex = $request->input('payment_index');
            if (!isset($paymentHistory[$paymentIndex])) {
                throw new \Exception('El pago especificado no existe en el historial.');
            }
            
            // Obtener el método de pago anterior para el registro
            $oldPaymentMethod = $paymentHistory[$paymentIndex]['payment_method_type_id'];
            $newPaymentMethod = $request->input('new_payment_method_type_id');
            
            // Verificar que el nuevo método de pago sea diferente al actual
            if ($oldPaymentMethod === $newPaymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nuevo método de pago debe ser diferente al actual.'
                ], 400);
            }
            
            // Actualizar el método de pago
            $paymentHistory[$paymentIndex]['payment_method_type_id'] = $newPaymentMethod;
            
            // Actualizar el historial de pagos y cambios
            $rent->payment_history = json_encode($paymentHistory);
            $rent->save();
            
			$rent = HotelRent::findOrFail($id);
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Método de pago actualizado correctamente',
                'payment_history' => $rent->payment_history
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el método de pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
