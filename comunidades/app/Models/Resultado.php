<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
* 
*/
class Resultado extends Model
{
	//nombre de la tabla
	protected $table = 'resultado';
    
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['fk_det_actividad', 'descripcion_resultado', 'fecha_fin', 'estado', 'external_resultado', 'created_at', 'updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
    

    

}
?>