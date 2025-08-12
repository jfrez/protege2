# protege

## Docker

Construye y levanta los contenedores ejecutando:

```
docker-compose up --build
```

La aplicación PHP se sirve mediante Apache en `http://localhost:8080` y la base de datos SQL Server escucha en el puerto `1433`.

La base de datos se crea automáticamente al iniciar los contenedores. El script de arranque ejecuta los archivos SQL de `db-init/` solo si la base `protege` aún no existe.

Las credenciales por defecto de la base de datos son:

- Usuario: `sa`
- Contraseña: `YourStrong@Passw0rd`
- Base de datos: `protege`

Para ejecutar el script de predicción dentro del contenedor web:

```
docker-compose exec web python predecir.py <evaluation_id>
```

