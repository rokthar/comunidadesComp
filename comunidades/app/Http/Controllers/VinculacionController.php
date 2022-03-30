<?php
namespace App\Http\Controllers;
use App\Models\Vinculacion;
use App\Models\Comunidad;
use App\Http\Controllers\MailController;
use App\Models\Usuario;
use App\Models\Docente;

use Illuminate\Http\Request;

//estado 0 Inactivo | 1 Activado | 2 En espera
class VinculacionController extends Controller{
    public function RegistrarVinculacion(Request $request, $ext_comunidad,$ext_comunidad_solic){
        $enviar = new MailController();

        if ($request->json()){
            $data = $request->json()->all();
            
            $comunidadSolicitante=Comunidad::where("external_comunidad",$ext_comunidad)->first();
            $comunidadSolicitada=Comunidad::where("external_comunidad",$ext_comunidad_solic)->first();

            if($comunidadSolicitante && $comunidadSolicitada){

                $docenteSolicitante=Docente::where("id",$comunidadSolicitante->tutor)->first();
                $docenteSolicitado=Docente::where("id",$comunidadSolicitada->tutor)->first();

                $usuarioSolicitante=Usuario::where("id",$docenteSolicitante->fk_usuario)->first();
                $usuarioSoliitado=Usuario::where("id",$docenteSolicitado->fk_usuario)->first();

                if($data["descripcion"] == "" || $data["fecha_inicio"] == ""){
                    return response()->json(["mensaje"=>"Datos Faltantes", "siglas"=>"DF"],200);
                }else{
                    $vinculacion = new Vinculacion();
                    $vinculacion->fk_comunidad_solicitante = $comunidadSolicitante->id;
                    $vinculacion->fk_comunidad_solicitada = $comunidadSolicitada->id;
                    $vinculacion->descripcion = $data["descripcion"];
                    $vinculacion->fecha_inicio = $data["fecha_inicio"];
                    $vinculacion->estado = 2;
                    $external = "Vinc".Utilidades\UUID::v4();
                    $vinculacion->external_vinculacion = $external;
    
                    $vinculacion->save();
    
                    $enviar->enviarMail("Tutor ".$docenteSolicitante->nombres." ".$docenteSolicitante->apellidos,"Solicitud de Vinculación","Su solicitud de vinculación con la comunidad ".$comunidadSolicitada->nombre_comunidad." ha sido enviada correctamente, debera esperar un aproximado de 3-8 dias para su respuesta", $usuarioSolicitante->correo);
                    $enviar->enviarMail("Tutor ".$docenteSolicitado->nombres." ".$docenteSolicitado->apellidos,"Solicitud de Vinculación","Ha sido enviada una nueva solicitud para vincularse con la comunidad ".$comunidadSolicitante->nombre_comunidad.", dispone de 3-8 dias para dar su respuesta", $usuarioSoliitado->correo);
                    return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE","external_vinculacion"=>$external],200);
                
                }
            }else{
                return response()->json(["mensaje"=>"La comunidad no esta registrada", "siglas"=>"CNR"],200);
            }

        }
    }

    public function AceptarVinculacion(Request $request,$external_vinculacion){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();

            $vinculacionObj = Vinculacion::where("external_vinculacion",$external_vinculacion)->first();
            
            if($vinculacionObj){
                $comunidad=Comunidad::where("id",$vinculacionObj->fk_comunidad_solicitada)->first();
                $docente=Docente::where("id",$comunidad->tutor)->first();
                $usuario=Usuario::where("id",$docente->fk_usuario)->first();

                $vinculacionObj->estado = 1;
                $vinculacionObj->save();
                $enviar->enviarMail("Tutor ".$docente->nombres." ".$docente->apellidos,"Solicitud de Vinculación Aceptada","Su solicitud de vinculación con la comunidad ".$comunidad->nombre_comunidad." ha sido aceptada.<br>".$data["comentario"], $usuario->correo);

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La Vinculación no esta registrada","siglas"=>"VNR"],200);
            }
        }
    }

    public function RechazarVinculacion(Request $request, $external_vinculacion){
        if ($request->json()){
            $data = $request->json()->all();
            $enviar = new MailController();
            
            $vinculacionObj = Vinculacion::where("external_vinculacion",$external_vinculacion)->first();

            if($vinculacionObj){
                $comunidad=Comunidad::where("id",$vinculacionObj->fk_comunidad_solicitada)->first();
                $docente=Docente::where("id",$comunidad->tutor)->first();
                $usuario=Usuario::where("id",$docente->fk_usuario)->first();

                $vinculacionObj->estado = 0;
                $vinculacionObj->save();
                $enviar->enviarMail("Tutor ".$docente->nombres." ".$docente->apellidos,"Solicitud de Vinculación Rechazada","Su solicitud de vinculación con la comunidad ".$comunidad->nombre_comunidad." ha sido rechazada <br>".$data["comentario"], $usuario->correo);

                return response()->json(["mensaje"=>"Operación Exitosa", "siglas"=>"OE"],200);
            }else{
                return response()->json(["mensaje"=>"La vinculación no esta registrada","siglas"=>"DI"],200);
            }
        }else{
            return response()->json(["mensaje"=>"Datos Incorrectos","siglas"=>"DI"],200);
        }
    }

    public function ListarVinculacionComunidad($external_comunidad){
        global $estado, $datos;
        self::iniciarObjetoJSon();
           
            $comunidad=Comunidad::where("external_comunidad",$external_comunidad)->where("estado",1)->first();
            if($comunidad){
                $vinculacionObj = Vinculacion::where("fk_comunidad_solicitada",$comunidad->id)
                ->where("estado",2)->get();
    
                foreach ($vinculacionObj as $lista) {
                    $comunidadSolicitante=Comunidad::where("id",$lista->fk_comunidad_solicitante)->first();
    
                    $datos['data'][] = [
                        "comunidad_solicitante" => $comunidadSolicitante->nombre_comunidad,
                        "comunidad_solicitada"=>$comunidad->nombre_comunidad,
                        "descripcion"=>$lista->descripcion,
                        "fecha_inicio"=>$lista->fecha_inicio,
                        "external_vinculacion"=>$lista->external_vinculacion,
                        "ruta_logo"=>$comunidadSolicitante->ruta_logo
                    ];
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