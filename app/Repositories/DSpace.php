<?php

namespace App\Repositories;

use App\Recursos;
use Carbon\Carbon;
use App\RecursosDetalles;
use Illuminate\Support\Str;
use GuzzleHttp\Client as GuzzleClient;
use Orchestra\Parser\Xml\Facade as XmlParser;
/**
* Clase de conexion con drupal
*/
trait DSpace
{
	private $dspace;
	private $portal;
	private $client;

	public function __construct()
	{
        $this->client = new GuzzleClient();
        $this->portal = env("PORTAL_URL");
    }
    public function Cosechar($cantidad)
    {

        $urls = $this->UrlDSpaceOAI($cantidad);
        foreach ($urls['maestro'] as $key => $url) {
            $xmlObj = simplexml_load_file($url);
            $xmlNode = $xmlObj->ListRecords;
            $this->InsertarData($xmlNode,'m');
        }
        foreach ($urls['detalle'] as $key => $url) {
            $xmlObj = simplexml_load_file($url);
            $xmlNode = $xmlObj->ListRecords;
            $this->InsertarData($xmlNode,'d');
        }

        return 'maiz';
    }
    public function InsertarData($data,$tipo)
    {
        if ($tipo=='m') {
            foreach ($data->record as $key => $node) {
                $data = $node->metadata->children('oai_dc', 1)->dc->children('dc', 1);
                $date = $data->date[0];
                $date = str_replace('T',' ', $date);
                $date = str_replace('Z','', $date);
                $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $date)->toDateTimeString();
                $now = new Carbon();
                
                $titulo = ($data->title[1]=='') ? $data->title[0] : $data->title[1] ;
                
                foreach ($data->identifier as $key => $item) {
                    $texto = (string)$item;
                    $sw = Str::contains($texto, 'http://hdl.handle.net');
                    if ($sw) {
                        $identifier = $item;
                        $tmp = explode('/',$item);
                        $dspace_id = $tmp[4];
                    break;
                }else{
                    $identifier = '';
                }
            }
            if ($data->contributor) {
                $contributor = $data->contributor;
            }elseif($data->creator){
                $contributor = $data->creator;
            } else {
                $contributor = '';
            }
            
            Recursos::create([
                'title'=>$titulo,
                'dspace_id'=>$dspace_id,
                'contributor'=>json_encode($contributor),
                'subject'=>$data->subject[0],
                'description'=>$data->description[0],
                'date'=>$carbon,
                'year'=>substr($carbon,0,4),
                'identifier'=>$identifier,
                'language'=>$data->language[0],
                'rights'=>$data->rights[1],
                'format'=>$data->format[2],
                'publisher'=>$data->publisher[0],
                'created_at'=>$now,
                'updated_at'=>$now,
                ]);
            }
        } else {
            foreach ($data->record as $key => $node) {
                $data = $node->metadata->children('atom', 1)->entry->children('atom', 1);
                $titulo = ($data->title[1]=='') ? $data->title[0] : $data->title[1] ;

                $tmp = explode('/',(string)$data->id[0]);
                $dspace_id = $tmp[4];
                $triples = $node->metadata->children('atom', 1)
                ->entry->children('oreatom', 1)
                ->triples->children('rdf', 1);

                foreach ($triples as $key => $item)  {
                        $texto = (string)$item['about'];
                        $extension = substr($texto,-3);
                        $tipo = ['pdf','PDF','doc','DOC','docx','DOCX'];
                        if (Str::contains($extension, $tipo)) {
                            $file_link = (string)$data->link[3]->attributes()[1];
                            $file_name = (string)$data->link[3]->attributes()[2];
                            $file_size = (string)$data->link[3]->attributes()[4];
                        break;
                    } else {
                        $file = '';
                    }
                }
                foreach ($triples as $key => $item) {
                    $texto = (string)$item['about'];
                    $tipo = ['jpg','JPG','jpeg','JPEG'];
                    if (Str::contains($texto, $tipo)) {
                        $img = $texto;
                    break;
                } else {
                    $img = '';
                }
            }
            $now = new Carbon();
            $peso = $file_size/1048576;
            $peso = round($peso,2);
            RecursosDetalles::create([
                'title'=>$titulo,
                'dspace_id'=>$dspace_id,
                'file_name'=>$file_name,
                'file_link'=>$file_link,
                'file_size'=>"{$peso} Mb",
                'img'=>$img,
                'created_at'=>$now,
                'updated_at'=>$now,
                ]);
            }
        }

    }
    public function Sembrar($data)
    {
        $client = $this->client;
        $response = $client->request('POST',$this->portal,[
            'json' => $data
        ]);
        $status = $response->getStatusCode();
        return $status;
    }
    public function UrlDSpaceOAI($cantidad)
    {
        $ciclos = intval($cantidad/100);
        $cont = 0;
        $url            = env("DSPACE_URL");
        $verb           = 'ListRecords';
        $metadataPrefix = 'oai_dc';
        $metadataPrefix2 = 'ore';
        $set            = 'com_11283_';
        $comunidad      = $set.'320273';
        $urls = []; $ore = [];
        for ($i=0; $i <= $ciclos; $i++) { 
            $urls[$i] = "{$url}oai/request?verb={$verb}&resumptionToken={$metadataPrefix}///{$comunidad}/{$cont}";
            $ore[$i] = "{$url}oai/request?verb={$verb}&resumptionToken={$metadataPrefix2}///{$comunidad}/{$cont}";
            $cont +=100;
        }
        return [
            'maestro' => $urls,
            'detalle' => $ore,
        ];
    }

}