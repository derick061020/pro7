<?php

namespace Modules\ApiPeruDev\Http\Controllers;

use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\Catalogs\District;
use Illuminate\Routing\Controller;
use Modules\ApiPeruDev\Data\ServiceData;
use Hyn\Tenancy\Environment;
use App\Models\System\Client;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;

class ServiceController extends Controller
{
    public function service($type, $number)
    {
        if ($type != 'dni') {
            return (new ServiceData)->service($type, $number);
        }
        
        $tenant = app(Environment::class);
        $website = $tenant->tenant();
        $client = Client::whereHas('hostname', function($query) use ($website) {
            $query->where('website_id', $website->id);
        })->first();
        if ($client->api_preference == 2) {
            // Usar API Perú
            // Obtener la configuración del sistema
        $configuration = \App\Models\System\Configuration::first();
        
        if (!$configuration || !$configuration->api_custom_key) {
            return response()->json(['error' => 'Clave API personalizada no configurada'], 400);
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apis.aqpfact.pe/api/$type/$number",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer ' . $configuration->api_custom_key
            ),
        ));
        } else {
           return (new ServiceData)->service($type, $number);
        }

        $response = json_decode(curl_exec($curl), true);
        if (isset($response['data']) && $response['success']) {
            $response['data']['address'] = $response['data']['direccion'];
            $ubigeo_reniec = (string)$response['data']['ubigeo_reniec'];
            // En tu método del controlador
            // Obtener IDs de ubicación
            $department_id = Department::idByDescription($response['data']['departamento']);
            $province_id = Province::idByDescription($response['data']['provincia']);
            $district_id = District::idByDescription($response['data']['distrito'], $province_id);

            // Estructura de location_id que espera el frontend
            $response['data']['location_id'] = [
                $department_id,
                $province_id,
                $district_id
            ];
            unset($response['data']['direccion']);
        }

        curl_close($curl);
        return json_encode($response);
    }

    public function exchange($date)
    {
        return (new ServiceData)->exchange($date);
    }
}
