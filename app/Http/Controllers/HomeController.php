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
            'dspace_asset_authors'=> $recursos->autores,
            'dspace_asset_issue_date'=> $recursos->fecha,
            'dspace_asset_oai_identifier'=> $recursos->identifier,
            'dspace_asset_publisher'=> $recursos->publisher,
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
            'dspace_asset_rights'=>$recursos->Derechos,
            "dspace_asset_externallinks"=>[
                ['link'=>$recursos->identifier]
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

        $sembrar = $this->Sembrar($data);
        return $sembrar;
    }
    public function cosecha($cantidad,$tipo='all')
    {
        $this->Cosechar($cantidad,$tipo);
        echo 'Listo';
    }
    public function Diario($fecha=null)
    {
        if (isset($fecha)) {
            $this->Cosechar($fecha,'f');
            //$this->sembrando();
            echo 'Procesado';
        } else {
            $fecha = date('Y-m-d');
            $this->Cosechar($fecha,'f');
            //$this->sembrando();
            echo 'Procesado';

        }
    }
}
