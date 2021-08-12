<?php
namespace App\Http\Controllers;
use App\Models\Miembros;
use App\Models\Postulacion;
use App\Models\Estudiante;
use Illuminate\Http\Request;

//estado 0 Inactivo | 1 Activado
class MiembroController extends Controller{

    public function AñadirMiembro($external_postulacion){
        $postulacionObj = Postulacion::where("external_postulacion", $external_postulacion)->first();
        
        if($postulacionObj){
            if($postulacionObj->estado == 1){
                $miembros = new Miembros();
                $miembros->fk_estudiante = $postulacionObj->fk_estudiante;
                $miembros->fk_comunidad = $postulacionObj->fk_comunidad;
                $miembros->estado = 1;
                $external = "Mmbs".Utilidades\UUID::v4();
                $miembros->external_miembro = $external;
                $miembros->save();
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La postulación no ha sido aceptada","siglas"=>"PNA"],200);
            }
        }else{
            return response()->json(["mensaje"=>"La postulación no ha sido registrada","siglas"=>"PNR"],200);
        }
    }

    public function listarMiembrosComunidad($id_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Miembros::where("fk_comunidad",intval($id_comunidad))->get();
        $data = array();
        foreach ($listas as $lista) {
            $estudiante = Estudiante::where("id",$lista->fk_estudiante)->first();
           
            $datos['data'][] = [
                "estudiante"=>$estudiante->nombres." ". $estudiante->apellidos
            ];
        }
        
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