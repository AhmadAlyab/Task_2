<?php

namespace App\Http\Traits;

trait ApiresponseTrait
{
    public function ApiResponse($data,$code=500){
        return  response()->json($data, $code);
    }
}
