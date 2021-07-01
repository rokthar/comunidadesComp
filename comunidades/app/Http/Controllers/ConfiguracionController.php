<?php
namespace App\Http\Controllers;
use App\Models\Configuracion;
use Illuminate\Http\Request;
require 'Utilidades/PHPMailer/vendor/autoload.php';

//estado 0 Inactivo | 1 Activado | 2 revision Gestor | 3 revision secretaria | 4 en espera
class ConfiguracionController extends Controller{
    public function editarMail(Request $request){
        if ($request->json()){
            $data = $request->json()->all();
            if($data["host"] != "" && $data["correo"] != "" && $data["dias"] != ""){
                $configuracion=Configuracion::where("id",1)->first();
                $configuracion->host = $data["host"];
                $configuracion->correo = $data["correo"];
                $configuracion->dias = $data["dias"];
                $configuracion->save();
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"Datos Faltantes", "siglas"=>"DF"],200);
            }
        }
    }

    public function editarClave(Request $request){
        if ($request->json()){
            $data = $request->json()->all();
            if($data["clave"] != ""){
                $configuracion=Configuracion::where("id",1)->first();
                $configuracion->clave = $data["clave"];
                $configuracion->save();
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"Datos Faltantes", "siglas"=>"DF"],200);
            }
        }else{
            return response()->json(["mensaje"=>"Error en los datos", "siglas"=>"ED"],200);
        }
    }

    public function listarConf(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $configuracion=Configuracion::where("id",1)->first();
        $datos['data'] = [
            "host" => $configuracion->host,
            "correo"=>$configuracion->correo,
            "dias"=>$configuracion->dias
        ];
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    private static function estadoJson($estadoPeticion, $satisfactorio, $mensaje)
    {
        global $estado, $datos;
        $estado = $estadoPeticion;
        $datos['sucess'] = $satisfactorio;
        $datos['mensaje'] = $mensaje;
    }

    private static function iniciarObjetoJSon(){
        global $estado, $datos;
        $datos['data'] = null;
        $datos['sucess'] = 'false';
        $datos['mensaje'] = '';
    }
}   
?>