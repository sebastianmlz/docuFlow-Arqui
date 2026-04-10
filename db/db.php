<?php
class Conectar {
    public static function conexion() {
        $host = "ep-rough-mountain-an5e41dz-pooler.c-6.us-east-1.aws.neon.tech";
        $db = "neondb";
        $user = "neondb_owner";
        $pass = "npg_k7dBUYXwl6Kz";
        
        // Extraemos el ID del endpoint (la primera parte del host)
        $endpoint_id = "ep-rough-mountain-an5e41dz-pooler";
        
        // Agregamos el parámetro 'options' al final de la cadena
        $conexion = pg_connect("host=$host dbname=$db user=$user password=$pass sslmode=require options='endpoint=$endpoint_id'");
        
        if (!$conexion) {
            die("Error al conectar con NeonDB");
        }
        
        return $conexion;
    }
}
?>