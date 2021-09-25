<?php
namespace App\Http\Controllers;
use App\Models\Comunidad;
use App\Models\Docente;
use App\Models\Miembros;
use App\Models\Estudiante;
use App\Models\Actividades;
use App\Models\DetalleActividad;
use App\Models\Resultado;
use App\Models\Vinculacion;
use App\Models\Usuario;
use App\Http\Controllers\MailController;

use Illuminate\Http\Request;


//estado 0 Inactivo | 1 Activado | 2 revision Gestor | 3 revision secretaria | 4 en espera
class ComunidadController extends Controller{

    public function RegistrarComuidad(Request $request, $external_docente){
            $enviar = new MailController();
            $docenteS = Docente::where("tipoDocente","3")->first();
            $secretaria = Usuario::where("id",$docenteS->fk_usuario)->first();
            if ($request->json()){
                $data = $request->json()->all();

                $docenteObj = Docente::where("external_do", $external_docente)->first();
                $docente = Usuario::where("id",$docenteObj->fk_usuario)->first();
                if($docenteObj){
                    
                    $comunidad = new Comunidad();
                    $comunidad->nombre_comunidad = $data["nombre_comunidad"];
                    $comunidad->tutor = $docenteObj->id;
                    $comunidad->descripcion = $data["descripcion"];
                    $comunidad->mision = $data["mision"];
                    $comunidad->vision = $data["vision"];
                    $comunidad->ruta_logo = "default.png";
                    $comunidad->estado = 4;
                    $external = "Com".Utilidades\UUID::v4();
                    $comunidad->external_comunidad = $external;
        
                    $comunidad->save();
                    
                    ////$enviar->enviarMail("Secretaria ".$secretaria->nombres." ".$secretaria->apellidos,"Solicitud para la creación de una Comunidad","Ha sido enviada una nueva solicitud para la creacion de la comunidad ".$data["nombre_comunidad"], $secretaria->correo);
                    ////$enviar->enviarMail("Docente ".$docenteObj->nombres." ".$docenteObj->apellidos,"Solicitud para la creación de una Comunidad","Su solicitud para la creacion de la comunidad ".$data["nombre_comunidad"]. " ha sido enviada correctamente debe esperar un aproximado de 3-24 dias para su respuesta", $docente->correo);
                    
                    return response()->json(["mensaje"=>"Operación Exitosa","external_comunidad"=>$external ,"siglas"=>"OE"],200);
                }else{
                    return response()->json(["mensaje"=>"Docente no enconrado", "siglas"=>"DNE"],200);
                } 
            }
    }

    public function subirImagenComunidad(Request $request, $external_comunidad){
  
        $file = $request->file('file');
        $ruta= '../imagenes/comunidad';
        $image_name = time().$file->getClientOriginalName();
        $file->move($ruta, $image_name);
        $comunidades = Comunidad::where("external_comunidad",$external_comunidad)->first();
        if($comunidades){
            $comunidades->ruta_logo = $image_name;
            $comunidades->save();
            return response()->json(["mensaje"=>"Operacion existosa","nombre_imagen" => $image_name, "siglas"=>"OE"], 200);
        }else{
            return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"CNR"], 200);
        }
        
    }

    public function ActivarComunidad ($external_comunidad){
        $enviar = new MailController();
        $comunidadObj = Comunidad::where("external_comunidad", $external_comunidad)->first();
        if($comunidadObj){
            $docenteObj = Docente::where("id", $comunidadObj->tutor)->first();
            $usuario = Usuario::where("id",$docenteObj->fk_usuario)->first();
            $comunidad = Comunidad::where("id", $comunidadObj->id)->first(); //veo si el usuario tiene una persona y obtengo todo el reglon
            $comunidad->estado = 1;
            $comunidad->save();
            //tipoDocente: 1 docente | 2 gestor | 3 secretaria | 4 Decano | 5 Tutor
            $docenteObj->tipoDocente = 5;
            $docenteObj->save();
            //$enviar->enviarMail("Docente ".$docenteObj->nombres." ".$docenteObj->apellidos,"Aprobación de Solicitud","La comunidad ".$comunidadObj["nombre_comunidad"]." ha sido aprobada", $usuario->correo);

            return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
        }else{
            return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"OE"],200);
        }
    }

    public function RechazarComunidad (Request $request, $external_comunidad){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();
            $comunidadObj = Comunidad::where("external_comunidad", $external_comunidad)->first();
            if($comunidadObj){
                $docenteObj = Docente::where("id", $comunidadObj->tutor)->first();
                $usuario = Usuario::where("id",$docenteObj->fk_usuario)->first();
                $comunidad = Comunidad::where("id", $comunidadObj->id)->first(); //veo si el usuario tiene una persona y obtengo todo el reglon
                $comunidad->estado = 0;
                $comunidad->save();

                $docenteObj->tipoDocente = 1;
                $docenteObj->save();
                //$enviar->enviarMail("Tutor ".$docenteObj->nombres." ".$docenteObj->apellidos,"Aprobacion de Solicitud","La comunidad ".$comunidadObj["nombre_comunidad"]." ha sido rechazada. <br> ".$data["comentario"],$usuario->correo);
                
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"OE"],200);
            }
        }else{
            return response()->json(["mensaje"=>"Datos Incorrectos", "siglas"=>"DI"],200);
        }
    }

    public function RevisionInformacion (Request $request,$external_comunidad){
        $docenteG = Docente::where("tipoDocente","2")->first();
        $gestor = Usuario::where("id",$docenteG->fk_usuario)->first();

        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();
            $comunidadObj = Comunidad::where("external_comunidad", $external_comunidad)->first();
            if($comunidadObj){
                $comunidad = Comunidad::where("id", $comunidadObj->id)->first(); //veo si el usuario tiene una persona y obtengo todo el reglon
                $comunidad->estado = 3;
                $comunidad->save();
                //$enviar->enviarMail("Gestor/a ".$docenteG->nombres." ".$docenteG->apellidos,"Solicitud de Comunidad","La solicitud de la comunidad ".$comunidadObj["nombre_comunidad"]." ha sido verificada por la Secretaria <br>".$data["comentario"], $gestor->correo);

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"OE"],200);
            }
        }
    }

    public function RevisionGestor (Request $request,$external_comunidad){
        $decano = Docente::where("tipoDocente","4")->first();
        $usuario = Usuario::where("id",$decano->fk_usuario)->first();
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();
            $comunidadObj = Comunidad::where("external_comunidad", $external_comunidad)->first();
            if($comunidadObj){
                $comunidad = Comunidad::where("id", $comunidadObj->id)->first(); //veo si el usuario tiene una persona y obtengo todo el reglon
                $comunidad->estado = 2;
                $comunidad->save();
                //$enviar->enviarMail("Decano/a ".$decano->nombres." ".$decano->apellidos,"Solicitud de Comunidad","La solicitud de la comunidad ".$comunidadObj["nombre_comunidad"]." ha sido validada por el Gestor de la Carrera <br> ".$data["comentario"], $usuario->correo);

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"OE"],200);
            }
        }
    }

    public function EditarComunidad (Request $request, $external_comunidad){
        if ($request->json()){
            $data = $request->json()->all();

            $comunidad = Comunidad::where("external_comunidad", $external_comunidad)->first();
            if($comunidad){
                $comunidad->nombre_comunidad = $data["nombre_comunidad"];
                $comunidad->descripcion = $data["descripcion"];
                $comunidad->mision = $data["mision"];
                $comunidad->vision = $data["vision"];
                $comunidad->save();
                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"CNR"],200);
            }
        }else{
            return response()->json(["mensaje"=>"Datos Incorrectos", "siglas"=>"DI"],200);
        }
        
    }

    public function ListarComunidadesActivadas (){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Comunidad::where("estado",1)->get();
        
        $data = array();
        foreach ($listas as $lista) {
            $tutor = Docente::where("id", $lista->tutor)->first();

            $datos['data'][] = [
                "nombres" => $lista->nombre_comunidad,
                "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                "descripcion"=>$lista->descripcion,
                "mision"=>$lista->mision,
                "vision"=>$lista->vision,
                "external_comunidad"=>$lista->external_comunidad,
                "ruta_logo"=>$lista->ruta_logo
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }
    public function ListarComunidadesVinculacion ($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Comunidad::where("estado",1)->get();
        
        $data = array();
        foreach ($listas as $lista) {
            $tutor = Docente::where("id", $lista->tutor)->first();
            if($lista->external_comunidad == $external_comunidad){
            }else{
                $datos['data'][] = [
                    "nombres" => $lista->nombre_comunidad,
                    "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                    "descripcion"=>$lista->descripcion,
                    "mision"=>$lista->mision,
                    "vision"=>$lista->vision,
                    "external_comunidad"=>$lista->external_comunidad,
                    "ruta_logo"=>$lista->ruta_logo
                ];
                self::estadoJson(200, true, '');
            }
        }
        
        return response()->json($datos, $estado);
    }

    public function ListarComunidadesSecretaria(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Comunidad::where("estado",4)->get();
        
        $data = array();
        foreach ($listas as $lista) {
            $tutor = Docente::where("id", $lista->tutor)->first();

            $datos['data'][] = [
                "nombres" => $lista->nombre_comunidad,
                "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                "descripcion"=>$lista->descripcion,
                "mision"=>$lista->mision,
                "vision"=>$lista->vision,
                "external_comunidad"=>$lista->external_comunidad,
                "ruta_logo"=>$lista->ruta_logo
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function ListarComunidadesGestor(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Comunidad::where("estado",3)->get();
        
        $data = array();
        foreach ($listas as $lista) {
            $tutor = Docente::where("id", $lista->tutor)->first();

            $datos['data'][] = [
                "nombres" => $lista->nombre_comunidad,
                "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                "descripcion"=>$lista->descripcion,
                "mision"=>$lista->mision,
                "vision"=>$lista->vision,
                "external_comunidad"=>$lista->external_comunidad,
                "ruta_logo"=>$lista->ruta_logo
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function ListarComunidadesDecano(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Comunidad::where("estado",2)->get();
        
        $data = array();
        foreach ($listas as $lista) {
            $tutor = Docente::where("id", $lista->tutor)->first();

            $datos['data'][] = [
                "nombres" => $lista->nombre_comunidad,
                "tutor"=>$tutor->nombres." ". $tutor->apellidos,
                "descripcion"=>$lista->descripcion,
                "mision"=>$lista->mision,
                "vision"=>$lista->vision,
                "external_comunidad"=>$lista->external_comunidad,
                "ruta_logo"=>$lista->ruta_logo
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function BuscarComunidad($external_docente){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $docente = Docente::where("external_do",$external_docente)->first();
        if($docente){
        $comunidad = Comunidad::where("tutor",$docente->id)->where ("estado","!=",0)->first();
            if($comunidad){
                $datos['data'] = [
                    "nombre_comunidad" => $comunidad->nombre_comunidad,
                    "external_comunidad"=>$comunidad->external_comunidad,
                    "ruta_logo"=>$comunidad->ruta_logo,
                    "descripcion"=>$comunidad->descripcion,
                    "mision"=>$comunidad->mision,
                    "vision"=>$comunidad->vision
                ];
                self::estadoJson(200, true, '');
            }else{
                self::estadoJson(200, false, 'El docente no es tutor de una comunidad');
            }
        }else{
            self::estadoJson(200, false, 'El docente no esta registrado');
        }
        return response()->json($datos, $estado);
    }

    public function BuscarComunidadExternal($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $comunidad = Comunidad::where("external_comunidad",$external_comunidad)->first();
        if($comunidad){
            $docente = Docente::where("id",$comunidad->tutor)->first();
            $datos['data'] = [
                "nombre_comunidad" => $comunidad->nombre_comunidad,
                "external_comunidad"=>$comunidad->external_comunidad,
                "tutor"=>$docente->nombres." ".$docente->apellidos,
                "descripcion"=>$comunidad->descripcion,
                "mision"=>$comunidad->mision,
                "vision"=>$comunidad->vision
            ];
            self::estadoJson(200, true, '');
        }else{
            self::estadoJson(200, false, 'La comunidad no esta registrada');
        }
        
        return response()->json($datos, $estado);
    }

    public function BuscarComunidadByMiembro($external_estudiante){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $estudiante = Estudiante::where("external_es",$external_estudiante)->first();
        if($estudiante){
            $miembro = Miembros::where("fk_estudiante",$estudiante->id)->first();
            if($miembro){
                $comunidad = Comunidad::where("id",$miembro->fk_comunidad)->first();
                    $datos['data'] = [
                        "nombre_comunidad" => $comunidad->nombre_comunidad,
                        "external_comunidad"=>$comunidad->external_comunidad,
                        "ruta_logo"=>$comunidad->ruta_logo
                    ];
                    self::estadoJson(200, true, '');
            }else{
                self::estadoJson(200, false, 'El estudiante no es miembro de una comunidad');
            }
        }else{
            self::estadoJson(200, false, 'El estudiante no esta registrado');
        }
        return response()->json($datos, $estado);
    }

    public function historialComunidad($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $data=null;
        $dataRes=null;
        $dataAct=null;
        $dataVinc=null;
        $comunidad = Comunidad::where("external_comunidad",$external_comunidad)->first();
        if($comunidad){
            $miembro = Miembros::where("fk_comunidad",$comunidad->id)->get();
            $listas = Actividades::where("fk_comunidad",$comunidad->id)->get();
            $vinculaciones = Vinculacion::where("fk_comunidad_solicitada",$comunidad->id)->get();
            foreach ($miembro as $item) {
                $data=null;
                $estudiante = Estudiante::where("id",$item->fk_estudiante)->first();
                $data[] = [
                    "estudiante"=>$estudiante->nombres." ".$estudiante->apellidos,
                    "ciclo"=>$estudiante->ciclo,
                    "paralelo"=>$estudiante->paralelo
                ];
            }
            foreach($vinculaciones as $vinc){
                $comunidad = Comunidad::where("id",$vinc->fk_comunidad_solicitante)->first();
                $dataVinc[]=[
                    "fecha_solicitud"=>$vinc->fecha_inicio,
                    "comunidad_solicitante"=>$comunidad->nombre_comunidad
                ];
            }
            foreach ($listas as $act) {
                // $dataAct = null;
                $actividades = DetalleActividad::where("fk_actividades",$act->id)->get();
                foreach ($actividades as $item) {
                    $dataAct[] =[
                        "nombre_actividad"=>$item->nombre_actividad,
                        "descripcion_actividad"=>$item->descripcion_actividad,
                        "fecha_inicio"=>$item->fecha_inicio
                    ];
                    $resultados = Resultado::where("fk_det_actividad",$item->id)->get();
                    foreach ($resultados as $res) {
                        if($resultados != null){
                            $dataRes[] = [
                                "resumen_resultado"=>$res->resumen_resultado,
                                "fecha_fin"=>$res->fecha_fin
                            ];
                        }else{
                            $dataRes=null;
                        }
                        
                    }
                }
                
            }
            
            $datos['data'] = [
                "miembros" => $data,
                "actividades"=>$dataAct,
                "resultados"=>$dataRes,
                "vinculaciones"=>$dataVinc
            ];
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
?>