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

Para acceder a este listado habria que ira a ``

http://eata:8888/app_dev.php/order/api/verify?code=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIiwib3JkZXJMaW5lIjoyLCJ0aWNrZXQiOjMsImlhdCI6MTU0MjI4MzkwMCwiZXhwIjoxNTczODE5OTAwfQ.ONHmwSPOSfTfWTPxyfu64Q6FYg0jGRueHwWb95O_zV8
