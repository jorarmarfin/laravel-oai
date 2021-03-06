<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Recursos extends Model
{
    protected $table = 'recursos';
    protected $guarded = [];
    public $timestamps = false;

    public function getFechaAttribute()
    {
        $carbon = Carbon::createFromFormat('Y-m-d', $this->date);

        return $carbon->format('d/m/Y');
    }
    public function getAutoresAttribute()
    {
        $data = [];
        if ($this->contributor!='""') {
            foreach (json_decode($this->contributor) as $key => $value) {
                $data[$key]= ['name'=>$value];
            }
            return $data;
        }else{
            return $this->contributor;
        }
    }
    public function getColeccionesAttribute()
    {
        $data = [];
        foreach (json_decode($this->collections) as $key => $value) {
            $data[$key]= ['tag'=>$value];
        }
        return $data;
    }
    public function getDerechosAttribute()
    {
        $data = [];
        foreach (json_decode($this->rights) as $key => $value) {
            $data[$key]= ['right'=>$value];
        }
        return $data;
    }
    public function getDetallesAttribute()
    {
        $r = RecursosDetalles::where('dspace_id',$this->dspace_id)->first();

        return $r;
    }
    public function setLanguageAttribute($value)
    {
        $retVal = ($value=='es') ? 'Español' : 'Ingles' ;
        switch ($value) {
            case 'es':
                $retVal = 'Español';
                break;
            case 'Spanish':
                $retVal = 'Español';
                break;
            case 'en':
                $retVal = 'Ingles';
                break;
            case 'English':
                $retVal = 'Ingles';
                break;

        }
        $this->attributes['language'] = $retVal;
    }
    public function setSubjectAttribute($value)
    {
        switch ($value) {
            case 'Agriculture':
                $retVal = 'Agricultura';
                break;
            case 'Construction':
                $retVal = 'Construcción';
                break;
            case 'Disasters':
                $retVal = 'Desastres';
                break;
            case 'Energy':
                $retVal = 'Energía';
                break;
            case 'Food':
                $retVal = 'Alimentos';
                break;
            case 'Water and Sanitation':
                $retVal = 'Agua y saneamiento';
                break;
            case 'Development':
                $retVal = 'Desarrollo';
                break;
            case 'Transport':
                $retVal = 'Transporte';
                break;
            case 'Technology':
                $retVal = 'Tecnología';
                break;
            case 'Waste Management':
                $retVal = 'Gestión de residuos';
                break;
            case 'Climate Change':
                $retVal = 'Cambio climático';
                break;
            case 'Health':
                $retVal = 'Salud';
                break;
            case 'Education':
                $retVal = 'Educacion';
                break;
            case 'Forestry':
                $retVal = 'Forestal';
                break;
            case 'Industrial Development':
                $retVal = 'Desarrollo industrial';
                break;
            
            default:
                $retVal = $value;
                break;
        }
        $this->attributes['Subject'] = $retVal;
    }
    public function setFormatAttribute($value)
    {
        foreach (json_decode($value) as $key => $item) {
            if ($item == 'Article') {
                $this->attributes['format'] = 'Artículo';
                break;
            }
            if ($item == 'Book') {
                $this->attributes['format'] = 'Libro';
                break;
            }
            if ($item == 'Case Study') {
                $this->attributes['format'] = 'Caso de estudio';
                break;
            }
            if ($item == 'Fact Sheet') {
                $this->attributes['format'] = 'Ficha técnica';
                break;
            }
            if ($item == 'Infographic') {
                $this->attributes['format'] = 'Infografía';
                break;
            }
            if ($item == 'Manual') {
                $this->attributes['format'] = 'Manual';
                break;
            }
            if ($item == 'Technical Brief') {
                $this->attributes['format'] = 'Ficha técnica';
                break;
            }
            if ($item == 'Report') {
                $this->attributes['format'] = 'Reporte';
                break;
            }
            if ($item == 'Video') {
                $this->attributes['format'] = 'Vídeo';
                break;
            }
            if ($item == 'Policy Paper') {
                $this->attributes['format'] = 'Documento de política';
                break;
            }
            if ($item == 'Publicity Materials') {
                $this->attributes['format'] = 'Materiales institucionales';
                break;
            }
            if ($item == 'Magazine') {
                $this->attributes['format'] = 'Revista';
                break;
            }
            if ($item == 'Presentation') {
                $this->attributes['format'] = 'Presentación';
                break;
            }
            if ($item == 'Project') {
                $this->attributes['format'] = 'Documento de proyecto';
                break;
            }else{
                $this->attributes['format'] = 'Otros';
                break;
            }
        }
    }
    public function setCommunitiesAttribute($value)
    {
        $communities = [];
        foreach (json_decode($value) as $key => $item) {
            if (substr($item,0,3)=='com') {
                $item = $this->ComCol($item);
                if ($item) {
                    array_push($communities,$item);
                }
            }
        }
        $this->attributes['communities'] = json_encode($communities);
    }
    public function setCollectionsAttribute($value)
    {
        $collections = [];
        foreach (json_decode($value) as $key => $item) {
            if (substr($item,0,3)=='col') {
                $item = $this->ComCol($item);
                if ($item) {
                    array_push($collections,$item);
                }
            }
        }

        $retVal = (empty($collections)) ? false : true ;
        
        $this->attributes['procesar'] = $retVal;
        $this->attributes['collections'] = json_encode($collections);
    }
    public function setIdentifierAttribute($value)
    {
        $formatos = ['http://hdl.handle.net'];
        foreach (json_decode($value) as $key => $item) {
            $contains = Str::contains($item, $formatos);
            if ($contains) {
                $this->attributes['identifier'] = $item;
            break;
            }else{
                $this->attributes['identifier'] = 'http://hdl.handle.net';
            }
        }
    }
    public function setDspaceIdAttribute($value)
    {
        $id = (string)$value;
        $id = explode('/',$id);
        $this->attributes['dspace_id'] = $id[1];
    }
    public function setDateAttribute($value)
    {
        $date = (string)$value[0];
        $date = str_replace('T',' ', $date);
        $date = str_replace('Z','', $date);
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $date)->toDateTimeString();
        $this->attributes['date'] = $carbon;
        $i = count($value)-1;
        $year = (string)$value[$i];
        $pos1 = strpos($year, '/');
        if ($pos1>0) {
            $year = explode('/',$year);
            $year = end($year);
        }
        $pos2 = strpos($year, '-');
        if ($pos2>0) {
            $year = explode('-',$year);

            $year = $year[0];
        }

        $this->attributes['year'] = $year;
    }
    public function setContributorAttribute($value)
    {
        if ($value->contributor) {
            $contributor = $value->contributor;
        }elseif($value->creator){
            $contributor = $value->creator;
        } else {
            $contributor = '';
        }
        $this->attributes['contributor'] = json_encode($contributor);
    }
    public function setTitleAttribute($value)
    {
        $titulo = (array)json_decode($value);
        $titulo = $titulo[0] ;
        $this->attributes['title'] = $titulo;
    }
    public function ComCol($valor)
    {
        $agricultura = ['320274','320565','320275','333479','320290','320291','320280','320292','313788','320276','320262','320277','317695','317025'];
        $construccion = ['320281','320293','320557','320558','320559','320571','320560'];
        $desastres = ['320562','313768','320563','320575','320564'];
        $alimentos = ['320590','320585','320591','620314','320592','320606','320607','320593','320594','320608','320595','320596','320597','320598'];
        $ganaderia = ['320609','320599','320600','620315'];
        $industrias = ['320602','620316','320603','320604','320610','320605','320612'];
        $residuos = ['320630','320631','320620','620318'];
        $agua = ['320632','320633','320635','620319','320622','320623','320638'];
        $ambiente = ['320583'];
        $desarrollo = ['320640','320643'];
        $energia = ['320565','320578','320586','313770','320587','320579','320580','320581','320582','320588'];

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
            $translate = 'Artesanía y textiles';
        }elseif (Str::contains($valor, $residuos)) {
            $translate = 'Gestión de residuos';
        }elseif (Str::contains($valor, $agua)) {
            $translate = 'Agua y saneamiento';
        }elseif (Str::contains($valor, $ambiente)) {
            $translate = 'Medio ambiente y cambio climático';
        }elseif (Str::contains($valor, $desarrollo)) {
            $translate = 'Desarrollo económico y social';
        }elseif (Str::contains($valor, $energia)) {
            $translate = 'Energía';
        } else {
            $translate = false;
        }

        return $translate;
    }
}
