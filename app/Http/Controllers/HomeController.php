<?php

namespace App\Http\Controllers;

use App\Recursos;
use App\Repositories\DSpace;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use DSpace;
    public function sembrando($id=null,$fecha=null)
    {
        if (isset($id)) {
            $recursos = Recursos::where('id',$id)->get();
        }elseif (isset($fecha)) {
            $recursos = Recursos::whereDate('date',$fecha)->where('procesar',1)->get();
        } else {
            $recursos = Recursos::where('procesar',1)->get();
        }
        if (count($recursos)) {
            foreach ($recursos as $key => $recurso) {
                $data = [
                    'dspace_asset_title'=> $recurso->title,
                    'dspace_asset_status'=> "publish",
                    'dspace_asset_abstract'=> $recurso->description,
                    'dspace_asset_uri'=> $recurso->identifier,
                    'dspace_asset_authors'=> $recurso->autores,
                    'dspace_asset_issue_date'=> $recurso->fecha,
                    'dspace_asset_oai_identifier'=> $recurso->identifier,
                    'dspace_asset_publisher'=> $recurso->publisher,
                    'dspace_asset_relation'=> $recurso->relation,
                    'dspace_asset_downloads'=>[
                        [
                        'name'=>$recurso->detalles->file_name,
                        'image'=>$recurso->detalles->img,
                        'size'=>$recurso->detalles->file_size,
                        'mimetype'=>$recurso->format,
                        'description'=>$recurso->detalles->file_name,
                        'download'=>$recurso->detalles->file_link,
                        ]
                    ],
                    'dspace_asset_rights'=>$recurso->Derechos,
                    "dspace_asset_externallinks"=>[
                        ['link'=>$recurso->identifier]
                    ],
                    'dspace_asset_urls'=>[],
                    'dspace_asset_collections'=>$recurso->colecciones,
                    'dspace_asset_subjects'=>[
                        ['tag'=>$recurso->subject]
                    ],
                    'dspace_asset_format'=>[
                        ['tag'=>$recurso->format]
                    ],
                    'dspace_asset_language'=>[
                        ['tag'=>$recurso->language]
                    ],
                    'dspace_asset_year'=>[
                        ['tag'=>$recurso->year]
                    ]
                ];
                $sembrar = $this->Sembrar($data);
                echo 'Sembrado '.$key.'-'.$recurso->title."\n";
            }
        } else {
            echo "No hay registros por sembrar \n";
        }
        
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
            $this->sembrando(null,$fecha);
            echo 'Procesado';
        } else {
            $fecha = date('Y-m-d');
            $this->Cosechar($fecha,'f');
            $this->sembrando(null,$fecha);
            echo 'Procesado';

        }
    }
}
