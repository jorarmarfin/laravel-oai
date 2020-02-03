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
            $data[$key]= [
                'tag' => $value
            ];
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



}
