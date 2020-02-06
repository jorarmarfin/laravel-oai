<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecursosDetalles extends Model
{
    protected $table = 'recursos_detalles';
    protected $guarded = [];
    public $timestamps = false;

    public function setTitleAttribute($value)
    {
        $titulo = (array)json_decode($value);
        $titulo = (array_key_exists('1', $titulo)) ? $titulo[1] : $titulo[0] ;
        $this->attributes['title'] = $titulo;
    }
    public function setDspaceIdAttribute($value)
    {
        $id = (string)$value;
        $id = explode('/',$id);
        $this->attributes['dspace_id'] = $id[1];
    }





}
