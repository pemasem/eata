# Pere Mataix Sempere

[![N|Solid](https://www.entradasatualcance.com/theme-images/logo.png?1542223829583iwiq6f1omlf5stt9)](https://www.entradasatualcance.com/)




  - Crear un proyecto que lea de un api para desargar información de eventos
  - Interactuar con el api para crear un pedido de tickets y crear códigos de acceso
  - Crear una propia api para verificar los códigos de acceso

# Planteamiento del problema:

  - Crear un bundle de Symfony 3 para gestionar el aplicativo
  - Desarrollar una interfaz de usuario para acceder a las funcionalidades
  - Crear un sistema de códigos de tickets facilmente validables, seguros y robustos
  - Crear un webservice REST para la validación de los códigos.
  - Realizar pruebas de testeo
  - Documentar

### Que se ha utilizado

Para ello se ha utilizado:

* [Symfony] - PHP framework!
* [Bootstrap] - para la interfaz gráfica
* [Redis] - base de datos no relacional
* [git] - control de versiones
* [gitHub] - Almacenamiento externo de repositorio
* [jQuery] - Presentación, animación y funcionalidad de la aplicación


### Instalacion

Para ver el funcionamiento hay que montar el proyecto symfony en un servidor web, ya sea local o remoto, en el que también esté funcionando un servidor REDIS.

Para ello hay que descargar el código e instalar las dependencias.

```sh
$ composer install
```


### Plugins

Se han requerido los siguientes pluguins.

| Plugin |  | |
| ------ | ------ | ------- |
| Twig | [twig/twig] | Motor de plantilla
| Swiftmailer | [symfony/swiftmailer-bundle] | Envio de correo electrónico
| Bootstrap | [twitter/bootstrap] | Diseño de sitios y aplicaciones web |
| Predis | [predis/predis] | Acceso a base de datos |
| Jwt | [firebase/php-jwt] | Estándar abierto basado en JSON propuesto por IETF para la creación de tokens
| Unirest | [mashape/unirest-php] | Framework para realizar peticiones a webservice RESTfull
|phpUnit|symfony/phpunit-bridge|Realización de tests
|Nelmioapidocbundle|nelmio/api-doc-bundle|Documentación y banco de prueba del API


### Desarrollo

Para el desarrollo de la prueba he creado un bundle llamado OrderBundle donde he intentado concentrar todo el código para mayor facilidad de lectura e independencia.

##### Controladores

He separado el código en varios controadores:
 - Default - Se encargara de toda la capa de negocio para ofrecer y gestionar la venta de tickets
 - Api - Se encargará de ofrecer funcionalidad externa

Ambos controadores usaran un mismo servicio llamado `ApiClient`
La ruta de acceso a estos controladores se ha definido dentro del fichero general routing.yml.
```sh
order:
    resource: "@OrderBundle/Resources/config/routing.yml"
    prefix:   /order
```
Por lo que se accederá a los mismos con el prefijo `/order/`

## WORKFLOW DE LA APLICACIÓN

Para probar toda la funcionalidad desarrollada haria que seguir los siguintes pasos:
### Consumir una API:
En este apartado, habrá que solicitar un listado de eventos a una API y permitir que el usuario seleccione las entradas que desea.
#### 1.- Acceder al listado de eventos

Para acceder a este listado habria que ira a `/order/events`
![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/events.png?raw=true)
En el se puede ver el listado de eventos disponibles así como la fecha en los que se realizan dichos eventos.
#### 2.- El usuario puede seleccionar uno de los Event.
Para que el usuario puede seleccionar uno de los `Event` hay que pulsar en el botón que pone `Show Tickets`.
#### 3.- Obtener listado de Ticket del Event.
Se realiza una llamada `ajax` a la ruta `/order/tickets` que obtiene los tickets de cada correspondiente enento.
#### 4.- Mostrar un formulario con los distintos tipos de Ticket para que el usuario elija la cantidad de cada uno de ellos que desee.
La información se presenta en pantalla mediante un listado de elementos con la información de cada ticket, su precio i un selector de cantidad.
El mismo botón nos sirve ahora para mostrar/ocultar la información de los tickets sin necesidad de cargarlos de nuevo.
![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/tickets.png?raw=true)
Cada vez que se modifica la cantidad de los tickets deseado se recalcula el precio final tanto del ticket, como del evento, como del pedido final.
También se añade un pequeño elemento de validación para asegurar-se que el valor introducido sea válido.
######  * Un punto de mejora sería actualizar el pedido en el servidor a través de `ajax` para no perder la información en caso de que se refrescase la pantalla.

![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/order.png?raw=true)
#### 5.- Crear un pedido mediante la API con la selección del usuario.
Una vez se ha seleccionado los ticket deseados se pasaria a formalizar el pedido.
Para ello si pulsamos en el botón `ORDER` se desplegará el panel del pedido donde podremos introducir los valores requeridos para realizar el pedido.
Si no estamos seguros el panel se puede volver a reducir o bién podemos finalizar el pedido.
######  * Un punto de mejora para el sitio sería crear un validador para los campos introducidos tanto a nivel de cliente `javascript` como a nivel de servidor.
![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/client.png?raw=true)

#### 6.- Si la petición es correcta, la API devolverá un objeto de tipo Order, con un conjunto de lineas que corresponden a los Ticket seleccionados.
Al pulsar el botón de `CONFIRM` se hace un `POST` a la url `/order/confirm`.
Esta acción obtiene la información recibida y la formatea para crear el pedido a través del API.
Lamentablemente no he sido capaz de formatear correctamente los datos requeridos por el API puesto, y pese que me devuelve un código `200` como que no ha habido problema no me devuelve ningún pedido.
#### 7.- El Order y sus OrderLine, tienen una propiedad uuid. Se deberá generar tantos códigos aleatorios como entradas haya comprado el usuario.
Una vez obtengamos el orden el servicio genera un código `JWT` con toda la información requerida y con un tiempo de caducidad de un año.
El pedido es guardado en una variable de sesión para ser recuperado desde las siguientes secciones.
#### 8.- Enviar un email al usuario con los códigos generados.
Esta información se le envía al cliente mediante `swifmailer`, se ha configurado para salir a través del protocomo `smtp` de gmail con una cuenta de correo creada expresamente.
Una vez realizada esta acción el sitio se redirije a `/order/confirmed` para evitar realizar otro post al recargar la pantalla y para ofrecer la información resumida del pedido al cliente.
Esta pantalla hereda de la misma plantilla que se envia en el email para reutilizar código.
![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/email.png?raw=true)

### Crear una API
Hay que desarrollar una pequeña API para la comprobación de los códigos de entradas que se han emitido previamente.

#### 1.- La API tendrá un web service que recibirá un código de entrada (por ejemplo: 1234567890asdfghjkl).

La `url` de entrada del `api` tiene el prefijo `api`, precedido del prefijo anterior `order`.
Para la llamada al método requerido se ha utilizado la ruta `/order/api/verify` que espera un parámetro `code` para ser validado.

Un ejemplo de llamada podría ser:
```sh
http://eata.com/order/api/verify?code=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIiwib3JkZXJMaW5lIjoyLCJ0aWNrZXQiOjMsImlhdCI6MTU0MjI4MzkwMCwiZXhwIjoxNTczODE5OTAwfQ.ONHmwSPOSfTfWTPxyfu64Q6FYg0jGRueHwWb95O_zV8
```
#### 2.- Se comprueba si el código es correcto, y si ha sido usado previamente o no.
De nuevo mediante `JWT` se verifica si el código introducido es correcto o no.
Este método es capaz de obtener la información requerida directamente del código sin necesidad de acceder a base de datos, otorgando así un plus de rendimiento y velocidad.
Además ofrece garantias de cifrado y expiración de código que también son verificadas.
Para saber si el código ha sido utilizado o no se consulta a la base de datos no relacional `REDIS` porque no se requiere mas información que la fecha de uso y porque la velocidad que ofrece este sistema es sustancialmente mayor a otros.
En el caso de que el código sea correcto se extrae la información del mismo y se presenta mediante un `JSON` y se introduce el valor del código en la base de datos con la fecha de uso para que no pueda ser utilizada de nuevo.
#### 3.- En caso de una entrada ya utilizada o incorrecta, se devuelve un error con el motivo y el número de pedido, así como la hora de uso, si corresponde.
El servicio creado incorpora varias clases que heredan de `Exception` para personalizar los distintos casos de error.

| class | Description | Error Msg |
| ------ | ------ | ----- |
| ApiCodeValidationError | Excepción que se lanza si se ha prducido un error de validación de código o este está caducado | Invalid Code |
|ApiCodeUsedError| Excepción producida por un código en uso| Used Code |

Estas excepciones son lanzadas y capturadas para presentar al consumidor del `API` un mensaje de error acorde a lo sucedido.
```sh
{"error":true,"msg":"Invalid Code"}
```
En el caso de que se produzca cualquier otro tipo de error también se presentará debidamente.

```sh
{"error":true,"msg":"Unexpected Error"}
```

#### 4.- Si la entrada es correcta, devolverá el uuid del Order y el de su correspondiente OrderLine, así como el tipo de Ticket al que pertenece el código.
En el caso de que el código sea correcto se ofrece la información obtenida.
```sh
{"error":false,"msg":{"order":"1","line":2,"ticket":3}}
```

### Tests
Los tests se encuentran en `tests/OrderBundle`.
Para los test se han creado tanto Unitarios como funcionales en el propio proyecto de symfony.
Se realiza el testeo mediante phpUnit con el comando `./vendor/bin/simple-phpunit` y se testea tanto el funcionamiento del API creada como el consumo del API de eventos, así como que todo aparece correctamente al usuario.
Por ejemplo se testea que el proceso que enera los códigos lo realiza correctamente.
También nos sirve para generar códigos de prueba.

![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/phpUnit.png?raw=true)

Para el API se ha creado la ruta `api/doc` donde hay un sandbox de prueba y la documentación
![N|Solid](https://github.com/pemasem/eata/blob/master/symfony/src/OrderBundle/Resources/doc/api.png?raw=true)
### Puntos de mejora de la prueba
- Mejor diseño css, presentación mas elaborada, navegación
- Mas test unitarios y funcionales
- Mas control de errores y pantallas de error personalizadas
- Validación de los campos introducidos así como formateadores y placeholders
- Uso de entidades para el pedido y sus lineas así como eventos y tickets.
- Quitar el area de DEFAUL de la documentación del API.
- Poner algunas constantes en los ficheros de configuración. Por ejemplo los datos de acceso de REDIS
- Sacar Informacion del usuario en el email enviado
