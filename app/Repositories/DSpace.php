<?php

namespace App\Repositories;

use App\Recursos;
use Carbon\Carbon;
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
        foreach ($urls as $key => $url) {
            $xmlObj = simplexml_load_file($url);
            $xmlNode = $xmlObj->ListRecords;
            $this->InsertarData($xmlNode);
        }

        return 'maiz';
    }
    public function InsertarData($data)
    {
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
                    break;
                }else{
                    $identifier = '';
                }
            }
            Recursos::create([
                'title'=>$titulo,
                'contributor'=>json_encode($data->contributor),
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
        $urls = [];
        for ($i=0; $i <= $ciclos; $i++) { 
            $urls[$i] = "{$url}oai/request?verb={$verb}&resumptionToken=oai_dc///{$comunidad}/{$cont}";
            $cont +=100;
        }
        return $urls;
    }

}