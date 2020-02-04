<?php

namespace App\Http\Controllers;

use App\Recursos;
use App\Repositories\DSpace;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use DSpace;
    public function sembrando($id=null)
    {
        if (isset($id)) {
            $recursos = Recursos::where('id',$id)->first();
        } else {
            $recursos = Recursos::where('procesar',1)->get();
        }
        $data = [
            'dspace_asset_title'=> $recursos->title,
            'dspace_asset_status'=> "publish",
            'dspace_asset_abstract'=> $recursos->description,
            'dspace_asset_uri'=> $recursos->identifier,
            'dspace_asset_authors'=> [
                $recursos->autores
            ],
            'dspace_asset_issue_date'=> $recursos->fecha,
            'dspace_asset_oai_identifier'=> $recursos->identifier,
            'dspace_asset_publisher'=> $recursos->publicadores,
            'dspace_asset_downloads'=>[
                [
                'name'=>$recursos->detalles->file_name,
                'image'=>$recursos->detalles->img,
                'size'=>$recursos->detalles->file_size,
                'mimetype'=>$recursos->format,
                'description'=>$recursos->detalles->file_name,
                'download'=>$recursos->detalles->file_link,
                ]
            ],
            'dspace_asset_rights'=>[
                [
                    'tag'=>$recursos->rights
                ]
            ],
            "dspace_asset_externallinks"=>[
                ['tag'=>$recursos->identifier]
            ],
            'dspace_asset_urls'=>[],
            'dspace_asset_collections'=>$recursos->colecciones,
            'dspace_asset_subjects'=>[
                ['tag'=>$recursos->subject]
            ],
            'dspace_asset_format'=>[
                ['tag'=>$recursos->format]
            ],
            'dspace_asset_language'=>[
                ['tag'=>$recursos->language]
            ]
        ];
        //dd($data);
        //echo json_encode($data);

        $sembrar = $this->Sembrar($data);
        return $sembrar;
    }
    public function cosecha($cantidad)
    {
        $this->Cosechar($cantidad);
        echo 'Listo';
    }
}
