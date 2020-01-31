<?php

namespace App\Http\Controllers;

use App\Repositories\DSpace;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use DSpace;
    public function retrivdrupal()
    {
        $data = [
            "dspace_asset_title"=> "Ante el cambio climático Soluciones Prácticas",
            "dspace_asset_status"=> "publish",
        ];

        $sembrar = $this->Sembrar($data);
        return $sembrar;
    }
    public function cosecha($cantidad)
    {
        $this->Cosechar($cantidad);
        echo 'Listo';
    }
}
