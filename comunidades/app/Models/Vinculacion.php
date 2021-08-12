<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
* 
*/
class Vinculacion extends Model
{
	//nombre de la tabla
	protected $table = 'vinculacion';
    
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['fk_comunidad_solicitante','fk_comunidad', 'descripcion', 'fecha_inicio', 'estado','external_vinculacion', 'created_at', 'updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
    

    

}
?>