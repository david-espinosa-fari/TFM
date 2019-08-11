# MPWAR (Trabajo de fin del master)

## Instalación

- [Instalar Docker](https://docs.docker.com/install/)
- [Instalar docker-compose](https://docs.docker.com/compose/install/)

- Ejecutar `docker-compose up -d`

## Servicios

- Mysql: 3306
- Nginx: 80
- Redis: 6379
- RabbitMQ: 15672
- Elasticsearch: 9200
- Kibana: 5601
- Blackfire

Las predicciones las saco pidiendole a marc el identificador le paso el 
 
codigo postal

get /predictions/idPoblacion
la respuesta de esto lo meto en cache

Para obtener todas las estaciones del resto de apis llamo a este endpoint de marc.
get /stations/

Endpoints a crear

[11:18, 10/8/2019] David: resumiendo endpoints
[11:18, 10/8/2019] David: ===============estaciones===============
Listo [11:20, 10/8/2019] David: Post /apiv1/stations/ valores (uuid, codigo postal, latitud, longitud, temp, humedad, presion, timestamp), crea una estacion
Listo [11:26, 10/8/2019] David: GET /apiv1/stations/uuidStation devuelve una estacion


[11:21, 10/8/2019] David: put /apiv1/stations/uuidStations? campo=valor a actualizar actualiza un dato para una estacion

[11:22, 10/8/2019] David: GET / apiv1/stations/ devuelve todas las estaciones con el formato que me pediste
[11:23, 10/8/2019] David: delete /apiv1/stations/uuidStations elimina la estacion
[11:24, 10/8/2019] David: ========================================
[11:26, 10/8/2019] David: es eso??
[11:32, 10/8/2019] David: ==================Para Usuarios==============
[11:33, 10/8/2019] David: Post /apiv1/users/ valores (los valores que tu digas), crea un usuario
[11:33, 10/8/2019] David: put /apiv1/users/uuidUsers? campo=valor a actualizar actualiza un dato para un usuario

[11:35, 10/8/2019] David: delete /apiv1/users/uuidUser elimina un usurio
[11:35, 10/8/2019] David: GET / apiv1/users/uuidUser devuelve un usuario
[11:35, 10/8/2019] David: =================================

Post /apiv1/stations/history/uuidStations
id estación, temperatura, humedad, presión

Las estaciones no tendran datos de usuario, sera el usuario que contendra un identificador de estacion


los datos de usuarios

![datos de estaciones](datos%20de%20estaciones.jpeg)


![datos de usuarios](datos%20de%20usuarios.jpeg)


