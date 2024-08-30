# Symfony Security

![Iniciar el proyecto](/assets/images/01-iniciar-proyecto.png)

Quick setup — if you’ve done this kind of thing before

## HTTPS

`https://github.com/juan-belda-hgue/symfony-security.git`

or

## SSH

`git@github.com:juan-belda-hgue/symfony-security.git`

Get started by creating a new file or uploading an existing file. We recommend every repository include a README, LICENSE, and .gitignore.

## …or create a new repository on the command line

```shell
echo "# symfony-security" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin git@github.com:juan-belda-hgue/symfony-security.git
git push -u origin main
```

## …or push an existing repository from the command line

```shell
git remote add origin git@github.com:juan-belda-hgue/symfony-security.git
git branch -M main
git push -u origin main
```

## Instalando seguridad

Instala el paquete de seguridad

```Shell
composer require symfony/security-bundle
```

Además de las cosas normales, ha añadido dos nuevos archivos de configuración:

- config/packages/security.yaml
- config/routes/security.yaml

Independientemente de cómo se autentifiquen tus usuarios -un formulario de inicio de sesión, una autenticación social o una clave de la API-, tu sistema de seguridad necesita algún concepto de usuario: alguna clase que describa la "cosa" que ha iniciado la sesión.

Sí, el paso 1 de la autenticación es crear una clase User. ¡Y hay un comando que puede ayudarnos! Busca tu terminal y ejecuta:

```Shell
symfony console make:user
```

o

```Shell
php bin/console make:user
```

Si no nos funciona necesitamos instalar:

```Shell
composer require symfony/maker-bundle --dev
```

```Shell
C:\Users\33484234y\Documents\Proyectos\symfony\symfony-security>php bin/console make:user

 The name of the security user class (e.g. User) [User]:
 > Profesional

 Do you want to store user data in the database (via Doctrine)? (yes/no) [no]:
 > yes


 [ERROR] Missing package: Doctrine must be installed to store user data in the database, run:

         composer require symfony/orm-pack
```

Instrucciones después de la instalación de doctrine/doctrine-bundle:

- Modifica la configuración de **DATABASE_URL** en `.env`
- Configura el **driver** (postgresql) y la versión **server_version** (16) en `config/packages/doctrine.yaml`

## Reglas para los nombres

1. Los nombre de tabla son en singular, por ejemplo, **profesional** en lugar de profesionales.
2. Los nombres de las entidades son Clases y por eso empiezan en mayúscula, por ejemplo, **Profesional**.
3. Los nombres de los campos de las entidades/tablas son siempre en minúsculas y si se necesita dos palabras se usa el guión bajo **_**, por ejemplo, **fecha_nacimiento**. Los métodos en PHP que se generan para estas propiedades, entiende esta nomenglatura y serán `getFechaNacimiento()` o `setFechaNacimiento()`.
4. Evitar abreviaturas, por ejemplo, **localidad_nacimiento** en lugar de localidad_nac.

## symfony console make:user

Nos sale un asistente para crear la Entidad de tipo *user*, que se usará para la identificación.

## symfony console make:entity

Para añadir más campos a la Entidad anterior que hemos creado tipo **user**, ahora llamamos a este *maker* y en el asistente introducimos el mismo nombre de Entidad con lo que añadirá los campos que necesitemos.

## symfony console make:migration --formatted

Generar la migración es crear las sentencias SQL sin ejecutarlas.

Se crea la carpeta **migrations** y dentro el archivo php.

## symfony console doctrine:migrations:migrate

Ejecutamos la migración pendiente.

## make:security:form-login ~~symfony console make:auth~~

Este asistente agiliza la creación de lo necesario para la seguridad.

```Shell
[ERROR] Missing package: to use the make:security:form-login command, run:

   composer require twig
```

## composer require twig

Instalamos el sistema de plantillas Twig.

## symfony console make:security:custom

Cada vez que queramos autentificar al usuario -como cuando enviamos un formulario de acceso- necesitamos un autentificador. Hay algunas clases de autentificadores principales que podemos utilizar, incluida una para los formularios de inicio de sesión... Pero para empezar, vamos a construir nuestra propia clase de autentificador desde cero.

Creamos la clase `LoginFormAuthenticator.php` y actualizamos `security.yaml`.

El autentificador se ha activado al añadir `custom_authenticator: App\Security\LoginFormAuthenticator` en security.yaml. Puedes tener varios autentificadores personalizados si quieres.

Si vas a cualquier URL, se encontrará con nuestro `supports()`. En cada petición, antes del controlador, Symfony pregunta ahora a nuestro autentificador si soporta la autentificación en esta petición.

## La seguridad de Symfony no ocurre en un controlador

Lo raro del sistema de seguridad de Symfony es que... no vamos a escribir esta lógica en el controlador. No. Cuando hagamos un POST a /login, nuestro autentificador va a interceptar esa petición y hará todo el trabajo por sí mismo. Sí, cuando enviemos el formulario de inicio de sesión, nuestro controlador en realidad nunca se ejecutará.

### El método supports()

Ahora que nuestro autentificador está activado, al inicio de cada petición, Symfony llamará al método `supports()` de nuestra clase. Nuestro trabajo es devolver **true** si esta petición "contiene información de autenticación que sabemos procesar". Si no, devolvemos false. Si devolvemos false, no fallamos en la autenticación: sólo significa que nuestro autenticador no sabe cómo autenticar esta petición... y la petición continúa procesándose con normalidad... ejecutando cualquier controlador con el que coincida.

Así que pensemos: ¿cuándo queremos que nuestro autenticador "haga su trabajo"? ¿Qué peticiones "contienen información de autenticación que sabemos procesar"? La respuesta es: siempre que el usuario envíe el formulario de inicio de sesión.

Dentro de `supports()` devuelve true si `$request->getPathInfo()` -es un método elegante para obtener la URL actual- es igual a `/login` y si `$request->isMethod('POST')`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return ($request->getPathInfo() === '/login' && $request->isMethod('POST'));
    }
```

Así que si la petición actual es un POST a /login, queremos intentar autentificar al usuario. Si no, queremos permitir que la petición continúe de forma normal.

Para ver lo que ocurre a continuación, baja en `authenticate()`, `dd('authenticate')`:

### composer require --dev symfony/profiler-pack

Instalamos el paquete para poder ver la barra de depuración.

## El método authenticate()

Así que si supports() devuelve true, Symfony llama a authenticate(). Este es el corazón de nuestro autentificador... y su trabajo es comunicar dos cosas importantes. En primer lugar, quién es el usuario que está intentando iniciar sesión -en concreto, qué objeto **User** es- y, en segundo lugar, alguna prueba de que es ese usuario. En el caso de un formulario de acceso, eso sería una contraseña. Como nuestros usuarios aún no tienen contraseña, la falsificaremos temporalmente.

### El objeto Pasaporte: UserBadge y Credenciales

Comunicamos estas dos cosas devolviendo un objeto Passport: return newPassport():

Este simple objeto es básicamente un contenedor de cosas llamadas "insignias"... donde una insignia es un pequeño trozo de información que va en el pasaporte. Las dos insignias más importantes son UserBadge y una especie de "insignia de credenciales" que ayuda a demostrar que este usuario es quien dice ser.

Empieza por coger el nif y la contraseña que te han enviado: `$nif = $request->request->get('_username')`. Si no lo has visto antes, `$request->request->get()` es la forma de leer los datos de POST en Symfony. En la plantilla de inicio de sesión, el nombre del campo es `_username`... así que leemos el campo POST _username. Copia y pega esta línea para crear una variable $password que lea el campo `_password` del formulario:

```php
    public function authenticate(Request $request): Passport
    {
        $nif = $request->request->get('_username');
        $password = $request->request->get('_password');
```

A continuación, dentro del `Passport`, el primer argumento es siempre el `UserBadge`. Di `new UserBadge()` y pásale nuestro "identificador de usuario". Para nosotros, ese es el $nif:

```php
    public function authenticate(Request $request): Passport
    {
        $nif = $request->request->get('_username');
        $password = $request->request->get('_password');
        return new Passport(
            new UserBadge($nif),
```

El segundo argumento de `Passport` es una especie de "credencial". Eventualmente le pasaremos un `PasswordCredentials()`..., pero como nuestros usuarios aún no tienen contraseñas, utiliza un nuevo `CustomCredentials()`. Pásale una devolución de llamada con un argumento `$credentials` y un argumento `$user` de tipo-indicado con nuestra clase Profesional:

```php
    public function authenticate(Request $request): Passport
    {
        $nif = $request->request->get('_username');
        $password = $request->request->get('_password');
        return new Passport(
            new UserBadge($nif),
            new CustomCredentials(function($credentials, Profesional $profesional) {
                   dd($credentials, $profesional);
            }, $password)
        );
    }
```

Symfony ejecutará nuestra llamada de retorno y nos permitirá "comprobar las credenciales" de este usuario de forma manual..., sea lo que sea que eso signifique en nuestra aplicación. Para empezar, `dd($credentials, $profesional)`. Ah, y `CustomCredentials` necesita un segundo argumento, que es cualquiera de nuestras "credenciales". Para nosotros, eso es $password.

Si esto de `CustomCredentials` es un poco confuso, no te preocupes: realmente tenemos que ver esto en acción.

Pero en un nivel alto..., es algo genial. Devolvemos un objeto `Passport`, que dice quién es el usuario -identificado por su nif - y una especie de "proceso de credenciales" que probará que el usuario es quien dice ser.

Bien: con sólo esto, vamos a probarlo. Vuelve al formulario de acceso y vuelve a enviarlo. Recuerda: hemos rellenado el formulario con un NIF que sí existe en nuestra base de datos.

Y... ¡impresionante! *1234* es lo que envié para mi contraseña y también está volcando el objeto de entidad Profesional correcto de la base de datos! Así que... ¡oh! De alguna manera, supo consultar el objeto Profesional utilizando ese nif. ¿Cómo funciona eso?

La respuesta es **el proveedor de usuarios**. Vamos a sumergirnos en eso a continuación, para saber cómo podemos hacer una consulta personalizada para nuestro usuario y terminar el proceso de autenticación.

## 07. Consulta de usuario personalizada y credenciales

### UserBadge y el proveedor de usuarios

Así es como funciona esto. Después de que devolvamos el objeto `Passport`, el sistema de seguridad intenta encontrar el objeto `Profesional` a partir de `UserBadge`. Si sólo le pasas un argumento a `UserBadge` -como es nuestro caso-, lo hace aprovechando nuestro **proveedor de usuarios**. ¿Recuerdas esa cosa de `security.yaml` llamada `providers`?

```yaml
security:
    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: App\Entity\Profesional
                property: nif
```

Como nuestra clase Profesional es una entidad, estamos utilizando el proveedor **entity** que sabe cómo cargar usuarios utilizando la propiedad **nif**. Así que, básicamente, se trata de un objeto que es muy bueno para consultar la tabla de profesionales a través de la propiedad nif. Así que cuando pasamos sólo el nif a **UserBadge**, el proveedor de usuarios lo utiliza para consultar **Profesional**.

Si se encuentra un objeto **Profesional**, Symfony intenta entonces "comprobar las credenciales" de nuestro pasaporte. Como estamos utilizando **CustomCredentials**, esto significa que ejecuta esta llamada de retorno..., en la que volcamos algunos datos. Si no se encuentra un **Profesional** - porque hemos introducido un **nif** que no está en la base de datos - la autenticación falla. Pronto veremos más sobre estas dos situaciones.

### Consulta de usuario personalizada

En cualquier caso, la cuestión es la siguiente: si sólo pasas un argumento a **UserBadge**, el proveedor de usuarios carga el usuario automáticamente. Eso es lo más fácil de hacer. E incluso puedes personalizar un poco esta consulta si lo necesitas - busca "[Usar una consulta personalizada para cargar el usuario](https://bit.ly/sf-entity-provider-query)" en los documentos de Symfony para ver cómo hacerlo.

O..., puedes escribir tu propia lógica personalizada para cargar el usuario aquí mismo. Para ello, vamos a necesitar el **ProfesionalRepository**. En la parte superior de la clase, añade `public function __construct()`... y autoconduce un argumento `ProfesionalRepository`. Pulsaré `Alt+Enter` y seleccionaré "Inicializar propiedades" para crear esa propiedad y establecerla:

```php
use App\Repository\ProfesionalRepository;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    private ProfesionalRepository $profesionalRepository;
    
    public function __construct(ProfesionalRepository $profesionalRepository)
    {
        $this->profesionalRepository = $profesionalRepository;
    }
```

En `authenticate()`, `UserBadge` tiene un segundo argumento opcional llamado cargador de usuario. Pásale una llamada de retorno con un argumento: `$userIdentifier`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   // ...
   public function authenticate(Request $request): PassportInterface
   {
      // ...
      return new Passport(
         new UserBadge($nif, function($userIdentifier) {
         // ...
         }),
      // ...
      );
   }
   // ...
}
```

Es bastante sencillo: si le pasas un callable, cuando Symfony cargue tu Profesional, llamará a esta función en lugar de a tu proveedor de usuario. Nuestro trabajo aquí es cargar el usuario y devolverlo. El `$userIdentifier` será lo que hayamos pasado al primer argumento de `UserBadge`... así que el **nif** en nuestro caso.

Digamos que $profesional = $this->profesionalRepository->findOneBy() para consultar nif se ajusta a $userIdentifier:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   // ...
   public function authenticate(Request $request): PassportInterface
   {
      // ...
      return new Passport(
         new UserBadge($nif, function($userIdentifier) {
            // Opcionalmente, pase una devolución de llamada para cargar el profesional manualmente
            $profesional = $this->profesionalRepository->findOneBy(['nif' => $userIdentifier]);
            if (!$profesional) {
               throw new UserNotFoundException();
            }
            return $profesional;
         }),
      // ...
      );
   }
   // ...
}
```

Esto... es básicamente idéntico a lo que hacía nuestro proveedor de usuarios hace un minuto... así que no cambiará nada. Pero puedes ver que tenemos el poder de cargar el Profesional como queramos.

Actualicemos. Sí el mismo volcado que antes.

## Validación de las credenciales

Bien, si se encuentra un objeto Profesional - ya sea desde nuestro callback personalizado o desde el proveedor de usuarios - Symfony comprueba a continuación nuestras credenciales, lo que significa algo diferente dependiendo del objeto de credenciales que pases. Hay 3 principales:

- **PasswordCredentials** - lo veremos más adelante.
- **SelfValidatingPassport**, que sirve para la autenticación de la API y no necesita ninguna credencial.
- **CustomCredentials**.

Si usas **CustomCredentials**, Symfony ejecuta la llamada de retorno..., y nuestro trabajo es "comprobar sus credenciales"... sea lo que sea que eso signifique en nuestra aplicación. El argumento `$credentials` coincidirá con lo que hayamos pasado al segundo argumento de `CustomCredentials`. Para nosotros, eso es la contraseña enviada:
