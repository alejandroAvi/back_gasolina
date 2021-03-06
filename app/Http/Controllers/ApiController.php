<?php

namespace App\Http\Controllers;

use App\Models\ZipCode;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    function getPrices()
    {
        $url = "https://api.datos.gob.mx/v1/precio.gasolina.publico";
         
        $curl = curl_init($url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return json_decode($response, true);
    }

    function getResult(Request $request)
    {
        $prices = $this->getPrices();
        $zip_code = ZipCode::select('postal_code','city','state');
        if($request->municipio != null){
            $zip_code = $zip_code->where('city',$request->municipio);
        }
        if($request->estado!=null){
            $zip_code = $zip_code->where('state',$request->estado);
        }
        $zip_code = $zip_code->distinct()->get();
        $result = [];
        foreach($zip_code as $item){
            foreach($prices['results'] as $value){
                if($value['codigopostal'] == $item->postal_code){
                    // $value['colonia'] = $item->suburb;
                    $value['municipio'] = $item->city;
                    $value['state'] = $item->state;
                    array_push($result,$value);
                }
            }
        } 
        // ordenar
        array_multisort(array_column($result, 'regular'),$request->ordenar == 0 ?SORT_ASC :SORT_DESC, $result);

        if(sizeof($result)>0){
            return response()->json(array('success' => true, 'results' => $result), 200);
        }else{
            return response()->json(array('success' => false, 'results' => $result));
        }
        
    }
}
