<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
* 
*/
class detalleActividad extends Model
{
	//nombre de la tabla
	protected $table = 'detalle_actividad';
    
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['fk_actividades', 'nombre_actividad', 'descripcion_actividad', 'fecha_inicio', 'estado', 'external_detact', 'created_at', 'updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
    

    

}
?>