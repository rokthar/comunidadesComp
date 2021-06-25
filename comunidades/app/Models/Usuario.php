<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
* 
*/
class usuario extends Model
{
	//nombre de la tabla
	protected $table = 'usuario';
    
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['correo', 'clave', 'tipoUsuario','estado','external_us','created_at','updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
    

    

}
?>