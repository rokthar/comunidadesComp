<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comunidad extends Model
{
	//nombre de la tabla
	protected $table = 'comunidad';
	
	//para saber si en la tabla usamos created_at y updated_at
    public $timestamps = true;
    //lista blancas campos publicos
    protected $fillable = ['nombre_comunidad', 'tutor', 'descripcion', 'mision','vision',
    'ruta_logo','estado', 'external_comunidad', 'created_at', 'updated_at'];
    //lista negra campos que no quieren que se encuentren facilmente
}
?>
