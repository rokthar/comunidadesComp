<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
* 
*/
class actividades extends Model
{
	//nombre de la tabla
	protected $table = 'actividades';
    
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['fk_comunidad','estado', 'external_actividades', 'created_at', 'updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
    

    

}
?>