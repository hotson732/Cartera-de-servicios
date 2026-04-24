<?php

namespace App\Http\Middleware;

use Closure; //es para terminar el proceso del middleware y pasar al siguiente
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
//herramientas necesarias para que exista el funcionamiento

//fucion handle ejecuta tres parametros, el primero es la petición, el segundo es el siguiente middleware y el tercero es el rol que se espera verificar dependiendo lo que exista en posgreSQL de forma dinamica
class CheckRole
{  
    public function handle(Request $request, Closure $next, string $role): Response
    {
      $user = $request->user(); //para saber quien esta ingresando

        // esto es para verificar si hay un usuario autenticado haciendo la petición
        if (!$user) {
            return response()->json([ //Devolver una respueesta JSON para detener lo y no mostrar la pagina
                'success' => false,
                'message' => 'No estás autenticado usuario.'
            ], 401);
        }

        //Necesario que verifica si el usuario tiene un rol y si el nombre de ese rol coincide en posgreSQL
        if (!$user->role || $user->role->name !== $role) {
            return response()->json([  //Estos se definen para saber si ingreso y si tiene el rol
                'success' => false,
                'message' => 'Acceso denegado, aun no tienes los permisos necesarios.'
            ], 403);
        }

        //Cuando es correcto dejamos que la petición continúe y paso los dos filtros anteriores
        return $next($request);  
    }
}
