<?php

namespace App\Http\Controllers;

use App\Recursos;
use App\Repositories\DSpace;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use DSpace;
    public function sembrando()
    {
        $recursos = Recursos::first();
        $data = [
            "dspace_asset_title"=> $recursos->title,
            "dspace_asset_status"=> "publish",
            "dspace_asset_abstract"=> $recursos->description,
            "dspace_asset_uri"=> $recursos->identifier,
            "dspace_asset_authors"=> $recursos->autores,
            "dspace_asset_issue_date"=> $recursos->fecha,
            "dspace_asset_oai_identifier"=> $recursos->identifier,
            "dspace_asset_publisher"=> $recursos->publicadores,
            'dspace_asset_downloads'=>[
                'name'=>$recursos->detalles->file_name,
                'image'=>$recursos->detalles->file_name,
                'size'=>$recursos->detalles->file_size,
                'mimetype'=>$recursos->detalles->file_name,
                'description'=>$recursos->detalles->file_name,
                'download'=>$recursos->detalles->file_link,
            ]
        ];
        dd($data);

        $sembrar = $this->Sembrar($data);
        return $sembrar;
    }
    public function cosecha($cantidad)
    {
        $this->Cosechar($cantidad);
        echo 'Listo';
    }
}
