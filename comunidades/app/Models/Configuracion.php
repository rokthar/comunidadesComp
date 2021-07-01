<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
	//nombre de la tabla
	protected $table = 'configuraciones';
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['host', 'correo', 'contraseña', 'dias'];
    //lista negra campos que no quieren que se encuentren facilmente
}
?>