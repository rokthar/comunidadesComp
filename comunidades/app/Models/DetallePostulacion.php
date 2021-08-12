<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
* 
*/
class DetallePostulacion extends Model
{
	//nombre de la tabla
	protected $table = 'detalle_postulacion';
    
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['fk_postulacion', 'habilidad', 'nivel', 'estado', 'external_det_postulacion', 'created_at', 'updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
    

    

}
?>