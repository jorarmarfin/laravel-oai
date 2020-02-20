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
            // $url = $this->curl_get_contents($url);
            $xmlObj = simplexml_load_file($url);
            if ((string)$xmlObj->error=='No matches for the query') {
                echo 'No matches for the query :'.$url."\n";
            } else {
                $xmlNode = $xmlObj->ListRecords;
                $this->InsertarData($xmlNode,'m');
            }
        }
        foreach ($urls['detalle'] as $key => $url) {
            if ($key==0)DB::table('recursos_detalles')->truncate();
            $xmlObj = simplexml_load_file($url);
            if ((string)$xmlObj->error=='No matches for the query') {
                echo 'No matches for the query :'.$url."\n";
            } else {
                $xmlNode = $xmlObj->ListRecords;
                $this->InsertarData($xmlNode,'d');
            }
        }

        return 'maiz';
    }
    public function InsertarData($data,$tipo)
    {
        if ($tipo=='m') {
            foreach ($data->record as $key => $node) {
                $data = $node->metadata->children('oai_dc', 1)->dc->children('dc', 1);
                $setSpec = $node->header->setSpec;

                #Variables
                $now = new Carbon();
                Recursos::create([
                    'title'=>json_encode($data->title),
                    'dspace_id'=>$node->header->identifier,
                    'contributor'=>$data,
                    'communities'=>json_encode($setSpec),
                    'collections'=>json_encode($setSpec),
                    'subject'=>$data->subject[0],
                    'description'=>$data->description[0],
                    'date'=>$data->date,
                    'identifier'=>json_encode($data->identifier),
                    'language'=>$data->language[0],
                    'rights'=>json_encode($data->rights),
                    'format'=>json_encode($data->format),
                    'publisher'=>$data->publisher[0],
                    'relation'=>$data->relation[0],
                    'created_at'=>$now,
                    'updated_at'=>$now,
                    ]);
            }
        
        } else {
            foreach ($data->record as $key => $node) {
                $data = $node->metadata->children('atom', 1)->entry->children('atom', 1);

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
                        $file_link = '';
                        $file_name = '';
                        $file_size = 0;
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
                    'title'=>json_encode($data->title),
                    'dspace_id'=>$node->header->identifier,
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
    function curl_get_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $html = curl_exec($ch);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}