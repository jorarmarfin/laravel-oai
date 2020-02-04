<?php

namespace App\Repositories;

use App\Recursos;
use Carbon\Carbon;
use App\RecursosDetalles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
    public function Cosechar($cantidad,$tipo)
    {

        $urls = $this->UrlDSpaceOAI($cantidad,$tipo);
        foreach ($urls['maestro'] as $key => $url) {
            if ($key==0)DB::table('recursos')->truncate();
            $xmlObj = simplexml_load_file($url);
            $xmlNode = $xmlObj->ListRecords;
            $this->InsertarData($xmlNode,'m');
        }
        foreach ($urls['detalle'] as $key => $url) {
            if ($key==0)DB::table('recursos_detalles')->truncate();
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
                $setSpec = $node->header->setSpec;
                $procesar = true;
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
                $communities = [];
                $collections = [];
                foreach ($setSpec as $key => $set) {
                    $texto = (string)$set;
                    if (substr($texto,0,3)=='com') {
                        $texto = $this->ComCol($texto);
                        if ($texto) {
                            array_push($communities,$texto);
                        }
                    } else {
                        $texto = $this->ComCol($texto);
                        if ($texto) {
                            array_push($collections,$texto);
                        }
                    }
                }
                if (empty($collections)) {
                    $procesar = false;
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
                    'communities'=>json_encode($communities),
                    'collections'=>json_encode($collections),
                    'subject'=>$data->subject[0],
                    'description'=>$data->description[0],
                    'date'=>$carbon,
                    'year'=>substr($carbon,0,4),
                    'identifier'=>$identifier,
                    'language'=>$data->language[0],
                    'rights'=>json_encode($data->rights),
                    'format'=>json_encode($data->format),
                    'publisher'=>$data->publisher[0],
                    'created_at'=>$now,
                    'updated_at'=>$now,
                    'procesar'=>$procesar,
                    ]);
            }
        
        } else {
            DB::table('recursos_detalles')->truncate();
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
    public function UrlDSpaceOAI($cantidad,$tipo)
    {
        if ($tipo=='all') {
            $ciclos = intval($cantidad/100);
        } else {
            $ciclos = 0;
        }
        $cont = 0;
        $url            = env("DSPACE_URL");
        $verb           = 'ListRecords';
        $metadataPrefix = 'oai_dc';
        $metadataPrefix2 = 'ore';
        $set            = 'com_11283_';
        $comunidad      = $set.'320273';
        $urls = []; $ore = [];
        for ($i=0; $i <= $ciclos; $i++) {
            if ($tipo=='all') {
                $urls[$i] = "{$url}oai/request?verb={$verb}&resumptionToken={$metadataPrefix}///{$comunidad}/{$cont}";
                $ore[$i] = "{$url}oai/request?verb={$verb}&resumptionToken={$metadataPrefix2}///{$comunidad}/{$cont}";
                $cont +=100;
            } else {
                $urls[$i] = "{$url}oai/request?verb={$verb}&metadataPrefix={$metadataPrefix}&from={$cantidad}&until={$cantidad}&set={$comunidad}";
                $ore[$i] = "{$url}oai/request?verb={$verb}&metadataPrefix={$metadataPrefix2}&from={$cantidad}&until={$cantidad}&set={$comunidad}";
            }
        }

        return [
            'maestro' => $urls,
            'detalle' => $ore,
        ];
    }
    public function ComCol($valor)
    {
        $agricultura = ['320274','320565','320275','333479','320290','320291','320280','320292','313788','320276','320262','320277','320578','320586','313770','320587','320579','320580','320581','320582','320588','317695','317025'];
        $construccion = ['320281','320293','320557','320558','320559','320571','320560'];
        $desastres = ['320562','313768','320563','320575','320564'];
        $alimentos = ['320590','320585','320591','620314','320592','320606','320607','320593','320594','320608','320595','320596','320597','320598'];
        $ganaderia = ['320609','320599','320600','620315'];
        $industrias = ['320602','620316','320603','320604','320610','320605','320612'];
        $residuos = ['320630','320631','320620','620318'];
        $agua = ['320632','320633','320635','620319','320622','320623','320638'];

        if (Str::contains($valor, $agricultura)) {
            $translate = 'Agricultura y bosques';
        }elseif (Str::contains($valor, $construccion)) {
            $translate = 'Construcción';
        }elseif (Str::contains($valor, $desastres)) {
            $translate = 'Reducción de riesgo de desastres';
        }elseif (Str::contains($valor, $alimentos)) {
            $translate = 'Procesamiento de alimentos';
        }elseif (Str::contains($valor, $ganaderia)) {
            $translate = 'Ganadería';
        }elseif (Str::contains($valor, $industrias)) {
            $translate = 'Industrias de Manufactura, Artesanía y Procesos';
        }elseif (Str::contains($valor, $residuos)) {
            $translate = 'Gestión de residuos';
        }elseif (Str::contains($valor, $agua)) {
            $translate = 'Agua y saneamiento';
        } else {
            $translate = false;
        }

        return $translate;
    }

}