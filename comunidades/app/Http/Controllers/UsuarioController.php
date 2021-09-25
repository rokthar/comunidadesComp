<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\Docente;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    private $estado = 200;
    private $datos = [];

    //REGISTRO DE USUARIO
    //1 docente | 2 estudiante
    public function RegistrarUsuario(Request $request)
    {
        if ($request->json()) {
            $data = $request->json()->all();
            $user = Usuario::where("correo",$data["correo"])->first();
            if($user == ""){
                if($data["correo"] == "" || $data["clave"] == "" || $data["tipo"] == ""){
                    return response()->json(["mensaje" => "Datos Faltantes", "siglas" => "DF"], 200);
                }else{
                    $usuario = new Usuario();
                    $usuario->correo = $data["correo"];
                    $clave = sha1($data["clave"] . "unl.");
                    $usuario->clave = $clave;
                    $usuario->tipoUsuario = $data["tipo"];
                    $usuario->estado = 1;
                    $external_usuario = "UuA" . Utilidades\UUID::v4();
                    $usuario->external_us = $external_usuario;

                    $usuario->save();
                    return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE","external_us"=>$external_usuario], 200);
                }
            }else{
                return response()->json(["mensaje" => "El usuario ya existe", "siglas" => "UE"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }

    //REGISTRO DE ESTUDIANTE

    public function RegistrarEstudiante(Request $request, $external_id){
        if ($request->json()) {
            $data = $request->json()->all();
            $usuario = Usuario::where("external_us", $external_id)->first();
            
            if ($usuario->tipoUsuario == 2) {
                if($data["nombres"] == "" || $data["apellidos"] == "" || $data["ciclo"] == "" || $data["paralelo"] == ""){
                    return response()->json(["mensaje" => "Datos Faltantes", "siglas" => "DF"], 200);
                }else{
                    $est = Estudiante::where("fk_usuario",$usuario->id)->first();
                    if($est == ""){
                        $persona = new Estudiante();
                        $persona->nombres = $data["nombres"];
                        $persona->apellidos = $data["apellidos"];
                        $persona->ciclo = $data["ciclo"];
                        $persona->paralelo = $data["paralelo"];
                        $persona->estado = 1;
                        $persona->fk_usuario = $usuario->id;
                        $persona->external_es = "Es" . Utilidades\UUID::v4();
                        $persona->save();
                        return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
                    }else{
                        return response()->json(["mensaje" => "El estudiante ya esta registrado", "siglas" => "ER"], 200);
                    }
                }
            }else{
                return response()->json(["mensaje" => "El usuario no es de tipo estudiante", "siglas" => "UNE"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }

    public function EditarEstudiante(Request $request, $external_estudiante){
        if ($request->json()) {
            $data = $request->json()->all();
            $estudiante = Estudiante::where("external_es",$external_estudiante)->first();
            
            if ($estudiante) {
                $usuario = Usuario::where("id", $estudiante->fk_usuario)->first();
                if($usuario->tipoUsuario == 2){
                    $usuario->correo = $data["correo"];

                    $estudiante->nombres = $data["nombres"];
                    $estudiante->apellidos = $data["apellidos"];
                    $estudiante->ciclo = $data["ciclo"];
                    $estudiante->paralelo = $data["paralelo"];
                    $usuario->save();
                    $estudiante->save();
                    return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
                }else{
                    return response()->json(["mensaje" => "El usuario no es de tipo estudiante", "siglas" => "UNE"], 200);
                }
            }else{
                return response()->json(["mensaje" => "El usuario no es de tipo estudiante", "siglas" => "UNE"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }
    public function EditarEstudianteClave(Request $request, $external_estudiante){
        if ($request->json()) {
            $data = $request->json()->all();
            $estudiante = Estudiante::where("external_es",$external_estudiante)->first();
            if ($estudiante) {
                $usuario = Usuario::where("id", $estudiante->fk_usuario)->first();
                if($data["clave"] == ""){
                    return response()->json(["mensaje" => "Datos Faltantes", "siglas" => "DF"], 200);
                }else{
                    $clave = sha1($data["clave"] . "unl.");
                    $usuario->clave = $clave;
                    $usuario->save();
                    return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
                }
            }else{
                return response()->json(["mensaje" => "El usuario no es de tipo estudiante", "siglas" => "UNE"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }

    //REGISTRO DE DOCENTE
    //0 inactivo, 1 docente, 2 gestor, 3 secretaria, 4 Decano, 5 tutor
    public function RegistrarDocente(Request $request, $external_id){
        if ($request->json()) {
            $data = $request->json()->all();
            $usuario = Usuario::where("external_us", $external_id)->first();
            if ($usuario->tipoUsuario == 1) {
                if($data["nombres"] == "" || $data["apellidos"] == "" || $data['tipo_docente']==""){
                    return response()->json(["mensaje" => "Datos Faltantes", "siglas" => "DF"], 200);
                }else{
                    $doc = Docente::where("fk_usuario",$usuario->id)->first();
                    if($doc){
                        return response()->json(["mensaje" => "El docente ya esta registrado", "siglas" => "DR"], 200);
                    }else{
                        $docente = new Docente();
                        $docente->nombres = $data["nombres"];
                        $docente->apellidos = $data["apellidos"];
                        $docente->tipoDocente = $data['tipo_docente'];
                        $docente->estado = 1;
                        $docente->fk_usuario = $usuario->id;
                        $docente->external_do = "Doc" . Utilidades\UUID::v4();
                        $docente->save();
                        return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
                    }
                }
            }else{
                return response()->json(["mensaje" => "El usuario no es de tipo Docente", "siglas" => "UND"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }

    public function EditarDocente(Request $request, $external_docente){
        if ($request->json()) {
            $data = $request->json()->all();
            $docente = Docente::where("external_do",$external_docente)->first();
            
            if ($docente) {
                $usuario = Usuario::where("id", $docente->fk_usuario)->first();

                $usuario->correo = $data["correo"];

                $docente->nombres = $data["nombres"];
                $docente->apellidos = $data["apellidos"];
                $usuario->save();
                $docente->save();
                return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
            }else{
                return response()->json(["mensaje" => "El usuario no es de tipo Docente", "siglas" => "UND"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }
    public function EditarDocenteClave(Request $request, $external_docente){
        if ($request->json()) {
            $data = $request->json()->all();
            $docente = Docente::where("external_do",$external_docente)->first();
            
            if ($docente) {
                if($data["clave"] == ""){
                    return response()->json(["mensaje" => "Datos Faltantes", "siglas" => "DF"], 200);
                }else{
                    $usuario = Usuario::where("id", $docente->fk_usuario)->first();
                    $clave = sha1($data["clave"] . "unl.");
                    $usuario->clave = $clave;
                    $usuario->save();
                    return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
                }
            }else{
                return response()->json(["mensaje" => "El usuario no es de tipo Docente", "siglas" => "UND"], 200);
            }
        } else {
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
    }

    public function ActivarUsuario($external_usuario){
        $usuario = Usuario::where("external_us",$external_usuario)->first();
        if($usuario){
            $usuario->estado=1;
            $usuario->save();
            return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
        }else{
            return response()->json(["mensaje" => "El usuario no existe", "siglas" => "UNE"], 200);
        }
    }

    public function DesactivarUsuario($external_usuario){
        $usuario = Usuario::where("external_us",$external_usuario)->first();
        if($usuario){
            $usuario->estado=0;
            $usuario->save();
            return response()->json(["mensaje" => "Operacion existosa", "siglas" => "OE"], 200);
        }else{
            return response()->json(["mensaje" => "El usuario no existe", "siglas" => "UNE"], 200);
        }
    }

    //REGISTRO DE LOGIN
    public function login(Request $request)
    {
        global $estado, $datos;
        $datos['data'] = null;
        $datos['sucess'] = 'false';
        $datos['mensaje'] = '';
        if ($request->json()) {
            try {
                $data = $request->json()->all();
                if($data["correo"] != "" || $data["clave"] != ""){
                    $clave = sha1($data["clave"] . "unl.");

                    $usuario = Usuario::where("correo", "=", $data["correo"])
                        ->where("clave", "=", $clave)
                        ->where("estado", 1)->first();
                    if ($usuario) {
                        
                            $datos['data'] = [
                                "correo" => $usuario->correo,
                                "tipoUsuario" => $usuario->tipoUsuario,
                                "external_us" => $usuario->external_us,
                                "siglas"=>"OE"
                            ];
                            self::estadoJson(200, true, '');
                    }else{
                        $datos['data'] = [
                            "siglas"=>"DI"
                        ];
                        self::estadoJson(200, true, 'Datos Incorrectos');
                    }
                }else{
                    self::estadoJson(200, false, 'Datos Faltantes');
                }
                
            } catch (\Exception $e) {
                $datos['data'] = [
                    "siglas"=>"Error"
                ];
                self::estadoJson(200, false, 'Error Inesperado');
                
            }
            return response()->json($datos, $estado);
        }
    }

    //DATOS PERFIL ESTUDIANTE
    public function datosEstudiante($external_id)
    {
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $estudianteObj = Usuario::where("external_us", $external_id)->where("tipoUsuario",2)->first();

        if ($estudianteObj) {
            $estudiante = Estudiante::where("fk_usuario", $estudianteObj->id)->first();
            if($estudiante){
                $datos['data']= [
                   "correo"=>$estudianteObj->correo, 
                    "nombres" => $estudiante->nombres,
                    "apellidos" => $estudiante->apellidos,
                    "ciclo" => $estudiante->ciclo,
                    "paralelo" => $estudiante->paralelo,
                    "estado"=>$estudiante->estado,
                    "external_estudiante" => $estudiante->external_es,
                    "external_usuario" => $estudianteObj->external_us
                ];
                self::estadoJson(200, true, '');
            }else{
                self::estadoJson(200, false, 'El usuario no esta registrado');
            }
            }else{
                self::estadoJson(200, false, 'El usuario no es de tipo Estudiante');
            } 
        return response()->json($datos, $estado);
    }

    public function datosDocente($external_id)
    {
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $docenteObj = Usuario::where("external_us", $external_id)->where("tipoUsuario",1)->first();

        if ($docenteObj) {
            $docente = Docente::where("fk_usuario", $docenteObj->id)->first();
            
            $data = array();
            if ($docente) {
                $datos['data']= [
                    "correo"=>$docenteObj->correo,
                    "nombres" => $docente->nombres,
                    "apellidos" => $docente->apellidos,
                    "tipo_docente" => $docente->tipoDocente, 
                    "external_docente" => $docente->external_do,
                    "external_usuario" => $docenteObj->external_us
                ];
                self::estadoJson(200, true, '');
            }else{
                self::estadoJson(200, false, 'El usuario no esta registrado');
            }
        }else{
            self::estadoJson(200, false, 'El usuario no es de tipo Docente');
        }
        return response()->json($datos, $estado);
    }

    public function listarDocentes(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Docente::where("estado",1)->get();

        $data = array();
        foreach ($listas as $lista) {
            $usuario = Usuario::where("id", $lista->fk_usuario)->first();
            $datos['data'][] = [
                "nombres" => $lista->nombres,
                "apellidos" => $lista->apellidos,
                "tipo_docente" => $lista->tipoDocente,  //1 docente, 2 gestor
                "external_docente" => $lista->external_do,
                "external_usuario" => $usuario->external_us
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function listarDocentesConf(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Docente::where("estado","<", 2)->get();
        $data = array();
        foreach ($listas as $lista) {
            $usuario = Usuario::where("id", $lista->fk_usuario)->first();
            $datos['data'][] = [
                "nombres" => $lista->nombres,
                "apellidos" => $lista->apellidos,
                "tipo_docente" => $lista->tipoDocente,  //1 docente, 2 gestor
                "external_docente" => $lista->external_do,
                "external_usuario" => $usuario->external_us,   
                "estado"=> $usuario->estado
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function listarEstudiantesConf(){
        global $estado, $datos;
        self::iniciarObjetoJSon();
        $listas = Estudiante::where("estado","<", 3)->get();
        $data = array();
        foreach ($listas as $lista) {
            $usuario = Usuario::where("id", $lista->fk_usuario)->first();
            $datos['data'][] = [
                "nombres" => $lista->nombres,
                "apellidos" => $lista->apellidos,
                "ciclo" => $lista->ciclo, 
                "paralelo"=>$lista->paralelo,
                "external_estudiante" => $lista->external_es,
                "external_usuario" => $usuario->external_us,
                "estado"=>$usuario->estado
            ];
        }
        self::estadoJson(200, true, '');
        return response()->json($datos, $estado);
    }

    public function recuperarClave(Request $request){
        $enviar = new MailController();
        if ($request->json()) {
            $data = $request->json()->all();
            if($data["correo"] != ""){
                $usuario = Usuario::where("correo",$data["correo"])->first();
                if($usuario){
                    $auxClave = random_int(2,5)."unl";
                    $usuario->clave = sha1($auxClave);
                    $usuario->save();
                    //$enviar->enviarMail("Usuario","Recuperacion de Contraseña","Su solicitud ha sido eviada correctamente <br> Pofavor usar la siguiente contraseña generada automaticamente <strong>".$auxClave."</strong>, recuerde cambiar su contraeña cuando ingrese al sistrema.", $usuario->correo);
                    return response()->json(["mensaje" => "Operación Exitosa", "siglas" => "OE"], 200);
                }else{
                    return response()->json(["mensaje" => "El usuario no existe", "siglas" => "UNE"], 200);
                }
            }   else{
                return response()->json(["mensaje" => "No hay Datos", "siglas" => "NHD"], 200);
            }
        }else{
            return response()->json(["mensaje" => "La data no tiene el formato deseado", "siglas" => "DNF"], 200);
        }
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
