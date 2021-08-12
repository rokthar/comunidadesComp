<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\comunidad;
use App\Http\Controllers\MailController;



class CancelarComunidad extends Command{
    protected $signature = 'command:cancelarcomunidad';
    protected $description = 'Command Description';
    
    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $enviar = new MailController();
        
        // echo "hola mundo \n";
        $comunidad = comunidad::where('created_at','<',Carbon::now()->subDays(7))->get();
        // for ($i=0; $i < count($comunidad) ; $i++) { 
        //     echo $comunidad[$i]->id."\n";
        //     // $comunidad[$i]->estado=0;
        //     // $comunidad[$i]->save();
        //     // llamar a la funcion para enviar correo
        // }
        $enviar->enviarMail("Tutor","Solicitud para la creacion de una Comunidad","Ha pasado el tiempo de espera, por lo que su solicitud para la creación de una comunidad ha sido rechazada automaticamente");

    }
}
?>