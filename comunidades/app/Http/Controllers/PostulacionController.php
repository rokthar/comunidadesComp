<?php
namespace App\Http\Controllers;
use App\Models\Postulacion;
use App\Models\Comunidad;
use App\Models\Estudiante;
use App\Models\DetallePostulacion;
use App\Models\Usuario;
use App\Models\Docente;
use App\Http\Controllers\MailController;

use Illuminate\Http\Request;

//estado 0 Inactivo | 1 Activado | 2 En espera
class PostulacionController extends Controller{

    public function RegistrarPostulacion($external_estudiante, $external_comunidad){
        $enviar = new MailController();
        $comunidadObj = Comunidad::where("external_comunidad", $external_comunidad)->first();
        $docente = Docente::where("id",$comunidad->tutor)->first();
        $estudianteObj = Estudiante::where("external_es", $external_estudiante)->first();
        $usuarioEst = Usuario::where("id", $estudianteObj->fk_usuario)->first();
        $usuarioDo = Usuario::where("id", $docente->fk_usuario)->first();
        if($comunidadObj){
            if($estudianteObj){
                $postulacion = new Postulacion();
                $postulacion->fk_estudiante = $estudianteObj->id;
                $postulacion->fk_comunidad = $comunidadObj->id;
                $postulacion->estado = 2;
                $external = "Post".Utilidades\UUID::v4();
                $postulacion->external_postulacion = $external;
                $postulacion->save();
                
                $enviar->enviarMail("Tutor","Postulación","El estudiante ".$estudianteObj->nombres." ".$estudianteObj->apellidos." del ciclo ".$estudianteObj->ciclo." paralelo ".$estudianteObj->paralelo." ha enviado una postulación a la comunidad",$usuarioDo->correo);
                $enviar->enviarMail($estudianteObj->nombres." ".$estudianteObj->apellidos,"Postulación","Su postulación a la comunidad ".$comunidadObj->nombre_comunidad." ha sido enviada correctamente, debera esperar un aproximado de 3-8 dias para su respuesta",$usuarioEst->correo);
            
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE","external_postulacion"=>$external],200);
            }else{
                return response()->json(["mensaje"=>"El estudiante no esta registrado","siglas"=>"ENR"],200);
            }
        }else{
            return response()->json(["mensaje"=>"La comunidad no esta registrada","siglas"=>"CNR"],200);
        }
    }

    public function RegistrarDetallePostulacion(Request $request, $external_postulacion){
        if ($request->json()){
            $data = $request->json()->all();
            
            $postulacionObj = Postulacion::where("external_postulacion", $external_postulacion)->first();
            if($postulacionObj){
                for($i=0; $i < count($data) ; $i++){
                    $detallePostulacion = new DetallePostulacion();
                    $detallePostulacion->fk_postulacion = $postulacionObj->id;
                    $detallePostulacion->habilidad = $data[$i]["habilidad"];
                    $detallePostulacion->nivel = $data[$i]["nivel"];
                    $detallePostulacion->estado =2;
                    $external = "DetPost".Utilidades\UUID::v4();
                    $detallePostulacion->external_det_postulacion = $external;
                    $detallePostulacion->save();
                }
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La Postulación no esta registrada","siglas"=>"PNR"],200);
            }
        }
    }

    public function ActivarPostulacion(Request $request, $external_postulacion){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();

            $postulacionObj = Postulacion::where("external_postulacion", $external_postulacion)->first();
            $estudianteObj = Estudiante::where("id",$postulacionObj->id)->first();
            $usuarioEst = Usuario::where("id", $estudianteObj->fk_usuario)->first();
            if($postulacionObj){
                $detallePostulacionObj = DetallePostulacion::where("fk_postulacion", $postulacionObj->id)->get();
                $postulacionObj->estado = 1;
                $postulacionObj->save();
                foreach ($detallePostulacionObj as $lista) {
                    $lista->estado = 1;
                    $lista->save();    
                }

                $estudiante = estudiante::where("id", $postulacionObj->fk_estudiante)->first();
                $estudiante->estado = 2; //estado del estudiante en 2 indica que es miembro de comunidad
                $estudiante->save();

                $enviar->enviarMail($estudiante->nombres." ".$estudiante->apellidos,"Postulación Aceptada","Su postulación ha sido aceptada. <br>".$data["comentario"],$usuarioEst->correo);

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La postulación no esta registrada","siglas"=>"PNR"],200);
            }
        }
    }

    public function RechazarPostulacion(Request $request, $external_postulacion){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();

            $postulacionObj = Postulacion::where("external_postulacion", $external_postulacion)->first();
            
            if($postulacionObj){
                $detallePostulacionObj = DetallePostulacion::where("fk_postulacion", $postulacionObj->id)->get();
                $postulacionObj->estado = 0;
                $postulacionObj->save();
                foreach ($detallePostulacionObj as $lista) {
                    $lista->estado = 0;
                    $lista->save();    
                }
                $estudiante = Estudiante::where("id", $postulacionObj->fk_estudiante)->first();
                $usuarioEst = Usuario::where("id", $estudiante->fk_usuario)->first();

                $estudiante->estado = 1; //estado del estudiante en 1 indica que es un estudiante normal
                $estudiante->save();

                $enviar->enviarMail($estudiante->nombres." ".$estudiante->apellidos,"Postulación Rechazada","Su postulación ha sido rechazada <br>".$data["comentario"], $usuarioEst->correo);

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La postulación no esta registrada","siglas"=>"PNR"],200);
            }
        }
    }

    public function CancelarPostulacion($external_estudiante){
            $enviar = new MailController();
            $estudiante = Estudiante::where("external_es",$external_estudiante)->first();
            $usuarioEst = UsuarioEst::where("id",$estudiante->fk_usuario)->first();
            
            if($estudiante){
                $postulacion = Postulacion::where("estado",2)->where("fk_estudiante",$estudiante->id)->first();
                if($postulacion){
                    $detallePost = DetallePostulacion::where("fk_postulacion",$postulacion->id)->get();
                    $postulacion->estado = 0;
                    $postulacion->save();
                    foreach ($detallePost as $lista) {
                        $lista->estado = 0;
                        $lista->save();    
                    }
                    $estudiante->estado=1;
                    $estudiante->save();
                    
                    $enviar->enviarMail($estudiante->nombres." ".$estudiante->apellidos,"Postulación Cancelada","Su postulación ha sido cancelada <br>", $usuarioEst->correo);
    
                    return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
                }else{
                    return response()->json(["mensaje"=>"El estudiante no ha realizado ninguna postulación","siglas"=>"ENRP"],200);
                }
            }else{
                return response()->json(["mensaje"=>"El estudiante no esta registrado","siglas"=>"ENR"],200);
            }
    }
    
    
    public function listarPostulacionesEspera(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Postulacion::where("estado",2)->get();

        $data = array();
        foreach ($listas as $lista) {
            $datadetpos=null;
            $detallepostulacion = DetallePostulacion::where("fk_postulacion",$lista->id)->get();
            $estudiante = Estudiante::where("id",$lista->fk_estudiante)->first();
            $comunidad = Comunidad::where("id",$lista->fk_comunidad)->first();
            foreach ($detallepostulacion as $detpos) {
                //$datadetpos[]="";
                $datadetpos[] =[
                    "habilidad"=>$detpos->habilidad,
                    "nivel"=>$detpos->nivel
                ];
            }
            $datos['data'][] = [
                "comunidad" => $comunidad->nombre_comunidad,
                "estudiante"=>$estudiante->nombres." ". $estudiante->apellidos,
                "habilidades"=>$datadetpos,
                "external_postulacion"=>$lista->external_postulacion,
                "ciclo"=>$estudiante->ciclo." ".$estudiante->paralelo
            ];
            
        }
        
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function listarPostulacionesEsperaByComunidad($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $comunidad = Comunidad::where("external_comunidad",$external_comunidad)->first();
        if($comunidad){
            $listas = Postulacion::where("estado",2)->where("fk_comunidad",$comunidad->id)->get();
            $data = array();
            foreach ($listas as $lista) {
                $datadetpos=null;
                $detallepostulacion = DetallePostulacion::where("fk_postulacion",$lista->id)->get();
                $estudiante = Estudiante::where("id",$lista->fk_estudiante)->first();
                // $comunidad = comunidad::where("id",$lista->fk_comunidad)->first();
                foreach ($detallepostulacion as $detpos) {
                    //$datadetpos[]="";
                    $datadetpos[] =[
                        "habilidad"=>$detpos->habilidad,
                        "nivel"=>$detpos->nivel
                    ];
                }
                $datos['data'][] = [
                    "comunidad" => $comunidad->nombre_comunidad,
                    "estudiante"=>$estudiante->nombres." ". $estudiante->apellidos,
                    "habilidades"=>$datadetpos,
                    "external_postulacion"=>$lista->external_postulacion,
                    "ciclo"=>$estudiante->ciclo." ".$estudiante->paralelo
                ];
            }
            self::estadoJson(200, true, '');
        }else{
            self::estadoJson(200, false, 'La comunidad no esta registrada');
        }
        return response()->json($datos, $estado);
    }

    public function listarPostulacionesAceptadas(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Postulacion::where("estado",1)->get();

        $data = array();
        foreach ($listas as $lista) {
            $datadetpos=null;
            $detallepostulacion = DetallePostulacion::where("fk_postulacion",$lista->id)->get();
            $estudiante = Estudiante::where("id",$lista->fk_estudiante)->first();
            $comunidad = Comunidad::where("id",$lista->fk_comunidad)->first();
            foreach ($detallepostulacion as $detpos) {
                $datadetpos[] =[
                    "habilidad"=>$detpos->habilidad,
                    "nivel"=>$detpos->nivel
                ];
            }
            $datos['data'][] = [
                "comunidad" => $comunidad->nombre_comunidad,
                "estudiante"=>$estudiante->nombres." ". $estudiante->apellidos,
                "habilidades"=>$datadetpos
            ];
            
        }
        
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function buscarPostulacion($external_estudiante){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $datadetpos=[];
        $estudiante = Estudiante::where("external_es",$external_estudiante)->first();
        if($estudiante){
            $postulacion = Postulacion::where("estado",2)->where("fk_estudiante",$estudiante->id)->first();
            if($postulacion){
                $detallePost = DetallePostulacion::where("fk_postulacion",$postulacion->id)->get();
                $comunidad = Comunidad::where("id",$postulacion->fk_comunidad)->first();
                foreach ($detallePost as $detpos) {
                    $datadetpos[] =[
                        "habilidad"=>$detpos->habilidad,
                        "nivel"=>$detpos->nivel
                    ];
                }
                $datos['data'] = [
                    "comunidad" => $comunidad->nombre_comunidad,
                    "habilidades"=>$datadetpos,
                    "siglas"=>"OE"
                ];
                self::estadoJson(200, true, '');
            }else{
                $datos['data'] = [
                    "siglas"=>"ENTP"
                ];
                self::estadoJson(200, false, 'El estudiante no tiene ninguna postulación');
            }
        }else{
            $datos['data'] = [
                "siglas"=>"ENP"
            ];
            self::estadoJson(200, false, 'El estudiante no esta postulado');
        }
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
