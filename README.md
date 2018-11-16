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
| Twig | [twig/twig] | motor de plantilla
| Swiftmailer | [symfony/swiftmailer-bundle] | envio de correo electrónico
| Bootstrap | [twitter/bootstrap] | diseño de sitios y aplicaciones web |
| Predis | [predis/predis] | acceso a base de datos |
| Jwt | [firebase/php-jwt] | estándar abierto basado en JSON propuesto por IETF para la creación de tokens
| Unirest | [mashape/unirest-php] | Framework para realizar peticiones a webservice RESTfull


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

### WORKFLOW DE LA APLICACIÓN

Para probar toda la funcionalidad desarrollada haria que seguir los siguintes pasos:

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
Esta acción obtiene la información recibida y la ormatea para crear el pedido a través del API.
Lamentablemente no he sido c


http://eata:8888/app_dev.php/order/api/verify?code=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIiwib3JkZXJMaW5lIjoyLCJ0aWNrZXQiOjMsImlhdCI6MTU0MjI4MzkwMCwiZXhwIjoxNTczODE5OTAwfQ.ONHmwSPOSfTfWTPxyfu64Q6FYg0jGRueHwWb95O_zV8
