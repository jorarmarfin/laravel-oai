<?php

namespace App;

use Carbon\Carbon;
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
        foreach (json_decode($this->contributor) as $key => $value) {
            $data[$key]= ['tag'=>$value];
        }
        return $data;
    }
    public function getColeccionesAttribute()
    {
        $data = [];
        foreach (json_decode($this->collections) as $key => $value) {
            $data[$key]= ['tag'=>$value];
        }
        return $data;
    }
    public function getPublicadoresAttribute()
    {
        $p = '';
        foreach (json_decode($this->contributor) as $key => $value) {
            $p .= $value.',';
        }
        $p = substr($p, 0, -1);

        return $p;
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
}
