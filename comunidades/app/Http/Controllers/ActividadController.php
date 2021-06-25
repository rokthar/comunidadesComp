<?php
namespace App\Http\Controllers;
use App\Models\actividades;
use App\Models\comunidad;
use App\Models\docente;
use App\Models\detalleActividad;
use App\Models\usuario;
use App\Http\Controllers\MailController;

use Illuminate\Http\Request;

//estado 0 Inactivo | 1 Activado | 2 En espera
class ActividadController extends Controller{

    public function PlanificarActividades ($external_docente){
        $docenteG = docente::where("tipoDocente","2")->first();
        $gestor = usuario::where("id",$docenteG->fk_usuario)->first();
            $enviar = new MailController();

            $docente=docente::where("external_do",$external_docente)->first();
                if($docente){
                    $comunidadObj=comunidad::where("tutor",$docente->id)->first();
                if($comunidadObj){
                    $actividades = new actividades();
                    $actividades->fk_comunidad = $comunidadObj->id;
                    $actividades->estado=3;
                    $external = "Act".Utilidades\UUID::v4();
                    $actividades->external_actividades = $external;
                    $actividades->save();
                    $enviar->enviarMail($docenteG->nombres." ".$docenteG->apellidos." Gestor de la carrera","Planificacion de Actividades","La Comunidad ".$comunidadObj->nombre_comunidad." ha envida su planificacion de actividades, esta debera ser revisada en un perdiodo de 3-8 dias", $gestor->correo);

                    return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE","external_actividades"=>$external],200);
                }else{
                    return response()->json(["mensaje"=>"El Docente no es tutor de una comunidad", "siglas"=>"DNT"],200);
                }
            }else{
                return response()->json(["mensaje"=>"El Docente no esta registrado", "siglas"=>"DNE"],200);
            }
    }

    public function RegistrarDetalleActividad(Request $request, $external_actividades){
        $enviar = new MailController();

        if ($request->json()){
            $data = $request->json()->all();
            
            $actividadesObj = actividades::where("external_actividades", $external_actividades)->first();
            if($actividadesObj){
                for($i=0; $i < count($data) ; $i++){
                    $detalleActividad = new detalleActividad();
                    $detalleActividad->fk_actividades = $actividadesObj->id;
                    $detalleActividad->nombre_actividad = $data[$i]["nombre_actividad"];
                    $detalleActividad->descripcion_actividad = $data[$i]["descripcion_actividad"];
                    $detalleActividad->fecha_inicio = $data[$i]["fecha_inicio"];
                    $detalleActividad->estado =3;
                    $external = "DetPost".Utilidades\UUID::v4();
                    $detalleActividad->external_detact = $external;
                    $detalleActividad->save();
                }

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La Planificación no ha sido registrada","siglas"=>"DI"],200);
            }
        }
    }

    public function ActivarPlanificacion(Request $request,$external_actividades){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();

            $actividadObj = actividades::where("external_actividades", $external_actividades)->first();
            $comunidad = comunidad::where("id",$actividadObj->fk_comunidad)->first();
            $tutor = docente::where("id", $comunidad->tutor)->first();
            $usuarioT = usuario::where("id", $tutor->fk_usuario)->first();
            
            if($actividadObj){
                $detalleactividadObj = detalleActividad::where("fk_actividades", $actividadObj->id)->get();
                foreach ($detalleactividadObj as $lista) {
                    $lista->estado = 2;
                    $lista->save();    
                }
                $actividad = actividades::where("id", $actividadObj->id)->first(); //veo si el usuario tiene una persona y obtengo todo el reglon
                $actividad->estado = 2;
                $actividad->save();
                $enviar->enviarMail("Tutor ".$tutor->nombres." ".$tutor->apellidos,"Planificacion de Actividades Aprobada","Su planificacion de actividades ha sido aprobada por el Gestor de la Carrera <br>".$data["comentario"], $usuarioT->correo);
                        
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La planificación no ha sido registrada","siglas"=>"PNR"],200);
            }
        }
    }

    public function RechazarPlanificacion(Request $request,$external_actividades){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();

            $actividadObj = actividades::where("external_actividades", $external_actividades)->first();
            $comunidad = comunidad::where("id",$actividad_fk_comunidad)->first();
            $tutor = docente::where("id", $comunidad->tutor)->first();
            $usuarioT = usuario::where("id", $tutor->fk_usuario)->first();
            if($actividadObj){
                $detalleactividadObj = detalleActividad::where("fk_actividades", $actividadObj->id)->get();
                foreach ($detalleactividadObj as $lista) {
                    $lista->estado = 0;
                    $lista->save();    
                }
                $actividad = actividades::where("id", $actividadObj->id)->first(); //veo si el usuario tiene una persona y obtengo todo el reglon
                $actividad->estado = 0;
                $actividad->save();
                $enviar->enviarMail("Tutor ".$tutor->nombres." ". $tutor->apellidos,"Planificacion de Actividades Rechazada","Su planificacion de actividades ha sido rechazada por el Gestor de la Carrera, podra generar otra planificacion de actividades y volver a enviarla para su revision. <br>".$data["comentario"], $usuarioT->correo);
                        
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La planificación no ha sido registrada","siglas"=>"PNR"],200);
            }
        }
    }

    public function TermianrPlanificacion($external_comunidad){
        $actividad = actividad::where("estado",2)->where("fk_comunidad",$external_comunidad)->first();
        $actividad->estado=1;
        $actividad->save();
    }


    public function ListarPlanificacionEspera (){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = actividades::where("estado",3)->get();

        $data = array();
        foreach ($listas as $lista) {
            $dataAct = null;
            $actividades = detalleActividad::where("fk_actividades",$lista->id)->get();
            $comunidad = comunidad::where("id",$lista->fk_comunidad)->first();
            $tutor = docente::where("id", $comunidad->tutor)->first();
            foreach ($actividades as $act) {
                $dataAct[] =[
                    "nombre_actividad"=>$act->nombre_actividad,
                    "descripcion_actividad"=>$act->descripcion_actividad,
                    "fecha_inicio"=>$act->fecha_inicio
                ];
            }
            $datos['data'][] = [
                "comunidad" => $comunidad->nombre_comunidad,
                "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                "actividades"=>$dataAct,
                "external_actividades"=>$lista->external_actividades,
                "logo_comunidad"=>$comunidad->ruta_logo
            ];
        }
        
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function ListarPlanificacionActivada (){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = actividades::where("estado",2)->get();

        $data = array();
        foreach ($listas as $lista) {
            $actividades = detalleActividad::where("fk_actividades",$lista->id)->get();
            $comunidad = comunidad::where("id",$lista->fk_comunidad)->first();
            $tutor = docente::where("id", $comunidad->tutor)->first();
            foreach ($actividades as $act) {
                $dataAct[] =[
                    "nombre_actividad"=>$act->nombre_actividad,
                    "descripcion_actividad"=>$act->descripcion_actividad,
                    "fecha_inicio"=>$act->fecha_inicio
                ];
            }
            $datos['data'][] = [
                "comunidad" => $comunidad->nombre_comunidad,
                "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                "actividades"=>$dataAct
            ];
        }
        
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function ListarPlanificacionByComunidad($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $comunidad = comunidad::where("external_comunidad",$external_comunidad)->first();
        if($comunidad){
            $listas = actividades::where("fk_comunidad",$comunidad->id)->get();
            if($listas != null){
                $data = array();
                foreach ($listas as $lista) {
                    $actividades = detalleActividad::where("fk_actividades",$lista->id)->get();
        
                    foreach ($actividades as $act) {
                        if($act->estado == 1){
                            $estado = "Completada";
                        }else if($act->estado == 2){
                            $estado = "Por Completar";
                        }
                        $datos['data'][] = [
                            "nombre_actividad"=>$act->nombre_actividad,
                            "descripcion_actividad"=>$act->descripcion_actividad,
                            "fecha_inicio"=>$act->fecha_inicio,
                            "external_det_actividad"=>$act->external_detact,
                            "estado"=>$estado
                        ];
                    }
                }
                self::estadoJson(200, true, '');
            }else{
                self::estadoJson(200, false, 'La comunidad no tiene ninguna planificación');
            }
        }else{
            self::estadoJson(200, false, 'La comunidad no esta registrada');
        }
       
        return response()->json($datos, $estado);
    }

    public function ListarPlanificacionResultados($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $comunidad = comunidad::where("external_comunidad",$external_comunidad)->first();
        if($comunidad){
            $listas = actividades::where("fk_comunidad",$comunidad->id)->get();
            $data = array();
            foreach ($listas as $lista) {
                $actividades = detalleActividad::where("fk_actividades",$lista->id)->where("estado",2)->get();

                foreach ($actividades as $act) {
                    $datos['data'][] = [
                        "nombre_actividad"=>$act->nombre_actividad,
                        "descripcion_actividad"=>$act->descripcion_actividad,
                        "fecha_inicio"=>$act->fecha_inicio,
                        "external_det_actividad"=>$act->external_detact
                    ];
                }
            }
            
            self::estadoJson(200, true, '');
        }else{
            self::estadoJson(200, false, 'La comunidad no esta registrada');
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