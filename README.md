# Symfony Security

## 01. composer require seguridad

### Configuración del Proyecto

![Iniciar el proyecto](/assets/images/01-iniciar-proyecto.png)

Quick setup — if you’ve done this kind of thing before

### HTTPS

`https://github.com/juan-belda-hgue/symfony-security.git`

or

### SSH

`git@github.com:juan-belda-hgue/symfony-security.git`

Get started by creating a new file or uploading an existing file. We recommend every repository include a README, LICENSE, and .gitignore.

### …or create a new repository on the command line

```shell
echo "# symfony-security" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin git@github.com:juan-belda-hgue/symfony-security.git
git push -u origin main
```

### …or push an existing repository from the command line

```shell
git remote add origin git@github.com:juan-belda-hgue/symfony-security.git
git branch -M main
git push -u origin main
```

### Instalando seguridad

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

### Reglas para los nombres

1. Los nombre de tabla son en singular, por ejemplo, **profesional** en lugar de profesionales.
2. Los nombres de las entidades son Clases y por eso empiezan en mayúscula, por ejemplo, **Profesional**.
3. Los nombres de los campos de las entidades/tablas son siempre en minúsculas y si se necesita dos palabras se usa el guión bajo **_**, por ejemplo, **fecha_nacimiento**. Los métodos en PHP que se generan para estas propiedades, entiende esta nomenglatura y serán `getFechaNacimiento()` o `setFechaNacimiento()`.
4. Evitar abreviaturas, por ejemplo, **localidad_nacimiento** en lugar de localidad_nac.

### Autenticación y Autorización

De todos modos, cuando se habla de seguridad, hay dos grandes partes: la **autenticación** y la **autorización**. La autenticación plantea la pregunta "¿quién eres? Y "¿puedes demostrarlo?" Los usuarios, los formularios de inicio de sesión, las cookies "recuérdame", las contraseñas, las claves API..., todo eso está relacionado con la autenticación.

La autorización plantea una pregunta diferente: "¿Deberías tener acceso a este recurso?" A la autorización no le importa mucho quién eres..., se trata de permitir o denegar el acceso a diferentes cosas, como diferentes URLs o controladores.

## 02. make:user

### symfony console make:user

Nos sale un asistente para crear la Entidad de tipo *user*, que se usará para la identificación.

## 03. Personalizar la clase de usuario

### symfony console make:entity

Para añadir más campos a la Entidad anterior que hemos creado tipo **user**, ahora llamamos a este *maker* y en el asistente introducimos el mismo nombre de Entidad con lo que añadirá los campos que necesitemos.

### symfony console make:migration --formatted

Generar la migración es crear las sentencias SQL sin ejecutarlas.

Se crea la carpeta **migrations** y dentro el archivo php.

### symfony console doctrine:migrations:migrate

Ejecutamos la migración pendiente.

## 04. Construir un formulario de inicio de sesión

### make:security:form-login ~~symfony console make:auth~~

Este asistente agiliza la creación de lo necesario para la seguridad.

```Shell
[ERROR] Missing package: to use the make:security:form-login command, run:

   composer require twig
```

### composer require twig

Instalamos el sistema de plantillas Twig.

### symfony console make:security:custom

Cada vez que queramos autentificar al usuario -como cuando enviamos un formulario de acceso- necesitamos un autentificador. Hay algunas clases de autentificadores principales que podemos utilizar, incluida una para los formularios de inicio de sesión... Pero para empezar, vamos a construir nuestra propia clase de autentificador desde cero.

Creamos la clase `LoginFormAuthenticator.php` y actualizamos `security.yaml`.

El autentificador se ha activado al añadir `custom_authenticator: App\Security\LoginFormAuthenticator` en security.yaml. Puedes tener varios autentificadores personalizados si quieres.

Si vas a cualquier URL, se encontrará con nuestro `supports()`. En cada petición, antes del controlador, Symfony pregunta ahora a nuestro autentificador si soporta la autentificación en esta petición.

## 05. Cortafuegos y autenticadores

## 06. El autentificador y el pasaporte

### La seguridad de Symfony no ocurre en un controlador

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

### El método authenticate()

Así que si supports() devuelve true, Symfony llama a authenticate(). Este es el corazón de nuestro autentificador... y su trabajo es comunicar dos cosas importantes. En primer lugar, quién es el usuario que está intentando iniciar sesión -en concreto, qué objeto **User** es- y, en segundo lugar, alguna prueba de que es ese usuario. En el caso de un formulario de acceso, eso sería una contraseña. Como nuestros usuarios aún no tienen contraseña, la falsificaremos temporalmente.

### El objeto Pasaporte: UserBadge y Credenciales

Comunicamos estas dos cosas devolviendo un objeto Passport: return newPassport():

Este simple objeto es básicamente un contenedor de cosas llamadas "insignias"..., donde una insignia es un pequeño trozo de información que va en el pasaporte. Las dos insignias más importantes son `UserBadge` y una especie de "insignia de credenciales" que ayuda a demostrar que este usuario es quien dice ser.

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

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   // ...
   public function authenticate(Request $request): PassportInterface
   {
      // ...
      return new Passport(
         // ...
         new CustomCredentials(function($credentials, Profesional $profesional) {
            // ...
         }, $password)
      );
   }
   // ...
}
```

¡Imaginemos que todos los usuarios tienen la misma contraseña `Aa_123456`! Para validarlo, devuelve true si `$credentials === 'Aa_123456'`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   // ...
   public function authenticate(Request $request): PassportInterface
   {
      // ...
      return new Passport(
         // ...
         new CustomCredentials(function($credentials, Profesional $profesional) {
            return $credentials === 'Aa_123456';
         }, $password)
      );
   }
   // ...
}
```

¡Seguridad hermética!

### Fallo y éxito de la autenticación

Si devolvemos `true` desde esta función, ¡la autenticación ha sido un éxito! ¡Vaya! Si devolvemos `false`, la autenticación falla. Para comprobarlo, baja a `onAuthenticationSuccess()` y `dd('success')`. Haz lo mismo dentro de `onAuthenticationFailure()`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   // ...
   public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
   {
      dd('success');
   }
   public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
   {
      dd('failure');
   }
}
```

Pronto pondremos código real en estos métodos... pero su propósito se explica por sí mismo: si la autenticación tiene éxito, Symfony llamará a `onAuthenticationSuccess()`. Si la autenticación falla por cualquier motivo - como un nif o una contraseña no válidos - Symfony llamará a `onAuthenticationFailure()`.

¡Vamos a probarlo! Vuelve directamente a `/login`. Utiliza de nuevo el **nif** real con la contraseña correcta: **Aa_123456**. Envía y... ¡sí! llamó a `onAuthenticationSuccess()`. ¡La autenticación se ha completado!

Lo sé, todavía no parece gran cosa... así que a continuación, vamos a hacer algo en caso de éxito, como redirigir a otra página. También vamos a conocer el otro trabajo crítico de un proveedor de usuarios: refrescar el usuario de la sesión al principio de cada petición para mantenernos conectados.

## 08. Éxito de la autenticación y actualización del usuario

Hagamos un rápido repaso de cómo funciona nuestro autentificador. Después de activarlo en `security.yaml`:

```yaml
security:
   firewalls:
      main:
         custom_authenticator: App\Security\LoginFormAuthenticator
```

Symfony llama a nuestro método `supports()` en cada petición antes del controlador:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   public function supports(Request $request): ?bool
   {
      return ($request->getPathInfo() === '/login' && $request->isMethod('POST'));
   }
}
```

Como nuestro autentificador sabe cómo manejar el envío del formulario de inicio de sesión, devolvemos `true` si la petición actual es un `POST` a `/login`. Una vez que devolvemos `true,` Symfony llama a `authenticate()` y básicamente pregunta:

Bien, dime quién está intentando iniciar sesión y qué prueba tiene.

Respondemos a estas preguntas devolviendo un `Passport`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   public function authenticate(Request $request): PassportInterface
   {
      return new Passport(
         new UserBadge($nif, function($profesionalIdentifier) {
            // Opcionalmente, pase una devolución de llamada para cargar el profesional manualmente
            $profesional = $this->userRepository->findOneBy(['nif' => $profesionalIdentifier]);
            if (!$profesional) {
               throw new UserNotFoundException();
            }
            return $profesional;
         }),
         new CustomCredentials(function($credentials, Profesional $profesional) {
            return $credentials === 'Aa_123456';
         }, $password)
      );
   }
}
```

El primer argumento identifica al usuario y el segundo argumento identifica alguna prueba..., en este caso, sólo una devolución de llamada que comprueba que la contraseña enviada es **Aa_123456**. Si somos capaces de encontrar un profesional y las credenciales son correctas..., ¡entonces estamos autentificados!

Cuando iniciamos la sesión utilizando el **nif** de un usuario real en nuestra base de datos y la contraseña **Aa_123456**..., ejecutamos esta declaración `dd('success')`:

### onAuthenticationSuccess

Si la autenticación tiene éxito, Symfony llama a `onAuthenticationSuccess()` y pregunta:

¡Felicidades por la autenticación! ¡Estamos súper orgullosos! Pero... ¿qué debemos hacer ahora?

En nuestra situación, después del éxito, probablemente queramos redirigir al usuario a alguna otra página. Pero para otros tipos de autenticación podrías hacer algo diferente. Por ejemplo, si te estás autenticando mediante un token de la API, devolverías `null` desde este método para permitir que la petición continúe hacia el controlador normal.

En cualquier caso, ese es nuestro trabajo aquí: decidir qué hacer "a continuación"..., que será "no hacer nada" - `null` - o devolver algún tipo de objeto `Response`. Vamos a redirigir.

Dirígete a la parte superior de esta clase `LoginFormAuthenticator`. Añade un segundo argumento - `RouterInterface $router`  e inicializa la propiedad para crear esa propiedad y establecerla:

```php
use Symfony\Component\Routing\RouterInterface;

class LoginFormAuthenticator extends AbstractAuthenticator
{
   private RouterInterface $router;

   public function __construct(ProfesionalRepository $profesionalRepository, RouterInterface $router)
   {
      $this->router = $router;
   }
}
```

De vuelta a `onAuthenticationSuccess()`, necesitamos devolver `null` o un `Response`. Devuelve un nuevo `RedirectResponse()` y, para la URL, di `$this->router->generate()` y pasa `app_homepage`:

```php
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginFormAuthenticator extends AbstractAuthenticator
{
   // ...
   public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
   {
      return new RedirectResponse(
         $this->router->generate('app_homepage')
      );
   }
   // ...
}
```

Déjame ir..., vuelve a comprobar que el nombre de la ruta..., debe estar dentro de `QuestionController`. Sí, `app_homepage` es correcta.

vamos a entrar desde cero. Vamos directamente a `/login`, introducimos el nif - un nif real en nuestra base de datos - y la contraseña "**Aa_123456**". Cuando enviamos... ¡funciona! ¡Somos redirigidos! ¡Y estamos conectados! Lo sé gracias a la barra de herramientas de depuración de la web: conectado como **12345678Z**, autentificado: Sí.

Si haces clic en este icono para entrar en el perfil, hay un montón de información jugosa sobre la seguridad. Vamos a hablar de las partes más importantes de esto a medida que avancemos.

### Información sobre la autenticación y la sesión

Vuelve a la página de inicio. Fíjate en que, si navegamos por el sitio, seguimos conectados..., que es lo que queremos. Esto funciona porque los cortafuegos de Symfony son, por defecto, "**stateful**". Es una forma elegante de decir que, al final de cada petición, el objeto `Profesional` se guarda en la sesión. Luego, al inicio de la siguiente petición, ese objeto `Profesional` se carga desde la sesión..., y seguimos conectados.

### Actualizar el usuario

¡Esto funciona muy bien! Pero..., hay un problema potencial. Imagina que nos conectamos en el ordenador del trabajo. Luego, nos vamos a casa, iniciamos la sesión en un ordenador totalmente diferente y cambiamos algunos de nuestros datos de usuario, como por ejemplo, cambiamos nuestro apellido en la base de datos a través de una sección de "edición de perfil". Cuando volvamos al trabajo al día siguiente y actualicemos el sitio, Symfony cargará, por supuesto, el objeto Profesional de la sesión. Pero... ¡ese objeto Profesional tendrá ahora el apellido equivocado! Sus datos ya no coincidirán con lo que hay en la base de datos... porque estamos recargando un objeto "**viejo**" de la sesión.

Afortunadamente..., esto no es un problema real. ¿Por qué? Porque **al principio de cada petición, Symfony también refresca el usuario**. Bueno, en realidad nuestro "proveedor de usuarios" hace esto. Volviendo a `security.yaml`, ¿recuerdas esa cosa del proveedor de usuarios?

```yaml
security:
    providers:
        # Se utiliza para recargar al usuario desde la sesión y otras funciones (por ejemplo, switch_user)
        app_user_provider:
            entity:
                class: App\Entity\Profesional
                property: nif
    firewalls:
        main:
            provider: app_user_provider
```

Sí, **tiene dos funciones**. En primer lugar, si le damos un `nif`, sabe cómo encontrar a ese usuario. Si sólo le pasamos un único argumento a `UserBadge`, el proveedor de usuarios hace el trabajo duro de cargar el `Profesional` desde la base de datos:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
    public function authenticate(Request $request): PassportInterface
    {
        return new Passport(
            new UserBadge($nif, function($profesionalIdentifier) {
            }),
        );
    }
}
```

Pero el **proveedor de usuarios** también tiene un segundo trabajo. **Al comienzo de cada petición, refresca el `Profesional` consultando la base de datos para obtener datos nuevos**. Todo esto ocurre automáticamente en segundo plano..., ¡lo cual es genial! Es un proceso aburrido, pero crítico, del que tú, al menos, deberías ser consciente.

### Cambio de usuario === Cierre de sesión

Ah, y por cierto: después de consultar los datos frescos de `Profesional`, si algunos datos importantes del usuario han cambiado -como los de nif, password o roles - se te cerrará la sesión. Se trata de una función de seguridad: permite que un usuario, por ejemplo, cambie su contraseña y haga que se cierre la sesión de cualquier usuario "malo" que haya podido acceder a su cuenta. Si quieres saber más sobre esto, busca `EquatableInterface`: es una interfaz que te permite controlar este proceso.

Averigüemos qué ocurre cuando falla la autenticación. ¿Dónde va el usuario? ¿Cómo se muestran los errores? ¿Cómo vamos a tratar la carga emocional del fracaso? La mayor parte de eso es lo siguiente.

## 09. Cuando falla la autenticación

Vuelve al formulario de inicio de sesión. ¿Qué ocurre si falla el inicio de sesión? En este momento, hay dos formas de fallar:

- si no podemos encontrar un Profesional para el nif
- o si la contraseña es incorrecta. Probemos primero con una contraseña incorrecta.

### onAuthenticationFailure & AuthenticationException

Introduce un nif real de la base de datos..., y luego cualquier contraseña que no sea "Aa_123456". Y..., ¡sí! Nos encontramos con el `dd()`..., que viene de `onAuthenticationFailure()`:

Así que, independientemente de cómo fallemos la autenticación, acabamos aquí, y se nos pasa un argumento `$exception`. También vamos a mostrar eso:

```php
 public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
{
   dd('failure', $exception);
}
```

Vuelve... y actualiza. ¡Genial! Es un `BadCredentialsException`.

Esto es genial. Si la autenticación falla -no importa cómo falle- vamos a acabar aquí con algún tipo de `AuthenticationException`. `BadCredentialsException` es una subclase de ese..., al igual que el `UserNotFoundException` que estamos lanzando desde nuestro callback del cargador de usuarios.

Todas estas clases de excepción tienen algo importante en común. Mantén pulsado Command o Ctrl para abrir `UserNotFoundException` y verlo. Todas estas excepciones de autenticación tienen un método especial `getMessageKey()` que contiene una explicación segura de por qué ha fallado la autenticación. Podemos utilizarlo para informar al usuario de lo que ha ido mal.

### hide_user_not_found: Mostrar errores de nombre de usuario/nif no válidos

Así que esto es lo más importante: cuando la autenticación falla, es porque algo ha lanzado un `AuthenticationException` o una de sus subclases. Y así, como estamos lanzando un `UserNotFoundException` cuando se introduce un nif..., si intentamos iniciar la sesión con un nif incorrecto, esa excepción debería pasarse a `onAuthenticationFailure()`.

Vamos a probar esa teoría. En el formulario de inicio de sesión, introduce un nif y envía. ¡Ah! Seguimos obteniendo un `BadCredentialsException`! Esperaba que ésta fuera la verdadera excepción lanzada: la `UserNotFoundException`.

En la mayoría de los casos..., así es como funciona. Si lanzas un `AuthenticationException` durante el proceso de autentificación, esa excepción se te pasa a `onAuthenticationFailure()`. Entonces puedes utilizarla para averiguar qué ha ido mal. Sin embargo, `UserNotFoundException` es un caso especial. En algunos sitios, cuando el usuario introduce un **nif** válido pero una contraseña incorrecta, puede que no quieras decirle al usuario que, de hecho, se encontró el **nif**. Así que dices "Credenciales no válidas" tanto si no se encontró el nif como si la contraseña era incorrecta.

Este problema se llama enumeración de usuarios: es cuando alguien puede probar los nif en tu formulario de acceso para averiguar qué personas tienen cuentas y cuáles no. Para algunos sitios, definitivamente no quieres exponer esa información.

Por eso, para estar seguros, Symfony convierte `UserNotFoundException` en un `BadCredentialsException` para que introducir un nif o una contraseña no válidos dé el mismo mensaje de error. Sin embargo, si quieres poder decir "nif no válido" -lo que es mucho más útil para tus usuarios- puedes hacer lo siguiente

Abre `config/packages/security.yaml`. Y, en cualquier lugar bajo la clave raíz `security`, añade una opción `hide_user_not_found` establecida como `false`:

```yaml
security:
   hide_user_not_found: false
```

Esto le dice a Symfony que no convierta `UserNotFoundException` en un `BadCredentialsException`.

Si refrescamos ahora... ¡boom! Nuestro `UserNotFoundException` se pasa ahora directamente a `onAuthenticationFailure()`.

### Almacenamiento del error de autenticación en la sesión

Bien, pensemos. En `onAuthenticationFailure()`..., ¿qué queremos hacer? Nuestro trabajo en este método es, como puedes ver, devolver un objeto `Response`. Para un formulario de inicio de sesión, lo que probablemente queramos hacer es redirigir al usuario de vuelta a la página de inicio de sesión, pero mostrando un error.

Para poder hacerlo, vamos a guardar esta excepción -que contiene el mensaje de error- en la sesión. Digamos `$request->getSession()->set()`. En realidad podemos utilizar la clave que queramos..., pero hay una clave estándar que se utiliza para almacenar los errores de autenticación. Puedes leerla desde una constante: - la del componente de seguridad de Symfony - `SecurityRequestAttributes::AUTHENTICATION_ERROR`. Y pasa `$exception` al segundo argumento.

Ahora que el error está en la sesión, vamos a redirigirnos a la página de inicio de sesión. Haré trampa y copiaré el `RedirectResponse` de antes... y cambiaré la ruta a `app_login`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
   public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
   {
      // DEPRECATED 6.4 $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
      $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
      
      return new RedirectResponse(
         $this->router->generate('app_login')
      );
   }
}
```

### AuthenticationUtils: Renderizando el error

¡Genial! A continuación, dentro del controlador `login()`, tenemos que leer ese error y renderizarlo. La forma más directa de hacerlo sería coger la sesión y leer esta clave. Pero... ¡es incluso más fácil que eso! Symfony proporciona un servicio que tomará la clave de la sesión automáticamente. Añade un nuevo argumento de tipo `AuthenticationUtils`:

```php
class SecurityController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
```

Eso es sólo un atajo para leer esa clave de la sesión.

Esto significa que la variable `error` va a ser literalmente un objeto `AuthenticationException`. Y recuerda, para averiguar qué ha ido mal, todos los objetos `AuthenticationException` tienen un método `getMessageKey()` que devuelve una explicación.

En `templates/security/login.html.twig`, vamos a devolver eso. Justo después del `h1`, digamos que si error, entonces añade un `div` con `alert alert-danger`. Dentro renderiza `error.messageKey`:

```php
{% block body %}
<div class="container">
    <div class="row">
        <div class="login-form bg-light mt-4 p-4">
            <form method="post" class="row g-3">
                <h1 class="h3 mb-3 font-weight-normal">Por favor, inicia sesión</h1>
                {% if error %}
                    <div class="alert alert-danger">{{ error.messageKey }}</div>
                {% endif %}
            </form>
        </div>
    </div>
</div>
{% endblock %}
```

No quieres usar `error.message` porque si tuvieras algún tipo de error interno -como un error de conexión a la base de datos- ese mensaje podría contener detalles sensibles. Pero `error.messageKey` está garantizado que es seguro.

¡Hora de probar! ¡Refrescar! ¡Sí! Somos redirigidos de nuevo a `/login` y vemos:

**No se ha podido encontrar el nombre de usuario.**

Ese es el mensaje si no se puede cargar el objeto Profesional: el error que viene de `UserNotFoundException`. No es un gran mensaje..., ya que nuestros usuarios se conectan con un nif, no con un nombre de usuario.

Así que, a continuación, vamos a aprender a personalizar estos mensajes de error y a añadir una forma de cerrar la sesión.

## 10. Personalizar los mensajes de error y añadir el cierre de sesión

1. Cuando falla el inicio de sesión, almacenamos el `AuthenticationException` en la sesión -que explica lo que ha ido mal- y luego redirigimos a la página de inicio de sesión.
2. En esa página, leemos esa excepción de la sesión utilizando este bonito servicio `AuthenticationUtils`.
3. Y finalmente, en la plantilla, llamamos al método `getMessageKey()` para mostrar un mensaje seguro que describa por qué ha fallado la autenticación.

Por ejemplo, si introducimos un nif que no existe, veremos

**No se pudo encontrar el nombre de usuario.**

A nivel técnico, esto significa que no se ha podido encontrar el objeto Profesional. Genial..., pero para nosotros no es un gran mensaje porque nos estamos conectando a través de un nif. Además, si introducimos un usuario válido - 12345678Z - con una contraseña no válida, vemos

**Credenciales no válidas.**

Este es un mensaje mejor... pero no es súper amigable.

### ¿Traducción de los mensajes de error?

Entonces, ¿cómo podemos personalizarlos? La respuesta es sencilla y quizá un poco sorprendente: los traducimos. Compruébalo: en la plantilla, después de `messageKey`, añade `|trans` para traducirlo. Pásale dos argumentos. El primero es `error.messageData`. No es demasiado importante pero en el mundo de la traducción, a veces tus traducciones pueden tener valores "comodín" y aquí pasas los valores de esos comodines. El segundo argumento se llama "dominio de traducción" que es casi como una categoría de traducción. Pasa security:

```php
{% block body %}
<div class="container">
    <div class="row">
        <div class="login-form bg-light mt-4 p-4">
            <form method="post" class="row g-3">
// ... lines 10 - 11
                {% if error %}
                    <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
                {% endif %}
// ... lines 15 - 29
            </form>
        </div>
    </div>
</div>
{% endblock %}
```

Si tienes un sitio multilingüe, todos los mensajes centrales de autentificación ya han sido traducidos a otros idiomas..., y esas traducciones están disponibles en un dominio llamado `security`. Así que al utilizar el dominio `security` aquí, si cambiamos el sitio al español, obtendríamos instantáneamente mensajes de autenticación en español.

Si nos detuviéramos ahora..., ¡no cambiaría absolutamente nada! Pero como estamos pasando por el traductor, tenemos la oportunidad de "traducir" estas cadenas del inglés a..., ¡un inglés diferente!

En el directorio `translations/` -que deberías tener automáticamente porque el componente de traducción ya está instalado- crea un nuevo archivo llamado `security.en.yaml`: `security` porque estamos utilizando el dominio de traducción `security` y `en` para el inglés. También puedes crear archivos de traducción `.xlf` - YAML es simplemente más fácil para lo que necesitamos hacer.

Ahora, copia el mensaje de error exacto, incluyendo el punto, pégalo -lo envolveré entre comillas para estar seguro- y pon algo diferente como

¡Contraseña introducida no válida!

### composer require symfony/translation

Instalando el paquete de traducción.

### Cerrar la sesión

Vamos a añadir una forma de cerrar la sesión. Así como si el usuario fuera a `/logout`, se..., ¡se cierra la sesión! Esto empieza exactamente como esperas: necesitamos una ruta y un controlador.

Dentro de `SecurityController`, copiaré el método `login()`, lo pegaré, lo cambiaré a `/logout`, `app_logout` y llamaré al método `logout`.

Para realizar el cierre de sesión propiamente dicho..., no vamos a poner absolutamente nada de código en este método. En realidad, lanzaré un nuevo `\Exception()` que diga "`logout()` nunca debe ser alcanzado".

Deja que me explique. El cierre de sesión funciona un poco como el inicio de sesión. En lugar de poner alguna lógica en el controlador, vamos a activar algo en nuestro cortafuegos que diga:

> Si el usuario va a `/logout`, intercepta esa petición, cierra la sesión del usuario y redirígelo a otro lugar.

Para activar esa magia, abre `config/packages/security.yaml`. En cualquier lugar de nuestro cortafuegos, añade `logout: true`:

```yaml
security:
   firewalls:
      main:
         logout: true
```

Internamente, esto activa un "oyente" que busca cualquier petición a `/logout`.

### Configurar el cierre de sesión

Y en realidad, en lugar de decir simplemente `logout: true`, puedes personalizar cómo funciona esto. Busca tu terminal y ejecuta:

> `symfony console debug:config security`

Como recordatorio, este comando te muestra toda tu configuración actual bajo la clave `security`. Así que toda nuestra configuración más los valores por defecto.

Si ejecutamos esto..., y encontramos el cortafuegos `main`..., mira la sección `logout`. Todas estas claves son los valores por defecto. Observa que hay una llamada `path: /logout`. Por eso está escuchando la URL `/logout.` Si quisieras cerrar la sesión a través de otra URL, sólo tendrías que modificar esta clave aquí.

Pero como aquí tenemos `/logout`..., y eso coincide con nuestro `/logout` de aquí, esto debería funcionar. Por cierto, quizá te preguntes por qué necesitamos crear una ruta y un controlador ¡Buena pregunta! En realidad no necesitamos un controlador, nunca será llamado. Pero sí necesitamos una ruta. Si no tuviéramos una, el sistema de rutas provocaría un error 404 antes de que el sistema de cierre de sesión pudiera hacer su magia. Además, es bueno tener una ruta, para poder generar una URL hacia ella.

Bien: ¡probemos esto! Primero inicia sesión: **12345678Z** y contraseña **Aa_123456**. Genial: estamos autentificados. Ve manualmente a `/logout` y..., ¡ya hemos cerrado la sesión! El comportamiento por defecto del sistema es cerrar la sesión y redirigirnos a la página de inicio. Si necesitas personalizarlo, hay algunas opciones. En primer lugar, en la clave `logout`, puedes cambiar `target` por alguna otra URL o nombre de ruta.

Pero también podemos engancharnos al proceso de cierre de sesión a través de un oyente de eventos, un tema del que hablaremos hacia el final del tutorial.

Siguiente: vamos a dar a cada usuario una contraseña real. Esto implicará **hacer un hash de las contraseñas**, para poder **almacenarlas de forma segura en la base de datos**, y luego comprobar esas contraseñas hash durante la autenticación. Symfony facilita ambas cosas.

## 11. Dar contraseñas a los usuarios

A Symfony no le importa realmente si los usuarios de tu sistema tienen contraseñas o no. Si estás construyendo un sistema de inicio de sesión que lee las claves de la API desde una cabecera, entonces no hay contraseñas. Lo mismo ocurre si tienes algún tipo de sistema SSO. Tus usuarios pueden tener contraseñas..., pero las introducen en algún otro sitio.

Pero para nosotros, sí queremos que cada usuario tenga una contraseña. Cuando usamos antes el comando `make:user`, en realidad nos preguntó si queríamos que nuestros usuarios tuvieran contraseñas. Respondimos que no..., para poder hacer todo esto manualmente. Pero en un proyecto real, yo respondería que sí para ahorrar tiempo.

### PasswordAuthenticatedUserInterface

Sabemos que todas las clases de usuario deben implementar `UserInterface`:

```php
use Symfony\Component\Security\Core\User\UserInterface;

class Profesional implements UserInterface
{

}
```

Entonces, si necesitas comprobar las contraseñas de los usuarios en tu aplicación, también tienes que implementar una segunda interfaz llamada `PasswordAuthenticatedUserInterface`:

```php
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Profesional implements UserInterface, PasswordAuthenticatedUserInterface
{

}
```

Esto requiere que tengas un nuevo método: `getPassword()`.

Si estás usando Symfony 6, no tendrás esto todavía, así que añádelo a tu entidad Profesional.

### Almacenamiento de una contraseña codificada para cada usuario

Bien, vamos a olvidarnos de la seguridad por un momento. En su lugar, céntrate en que necesitamos poder almacenar una contraseña única para cada usuario en la base de datos. Esto significa que nuestra entidad de usuario necesita un nuevo campo! Busca tu terminal y ejecuta:

> `symfony console make:entity`

Actualicemos la entidad Profesional, para añadir un nuevo campo llámalo `password`..., que es una cadena, 255 de longitud es exagerado pero está bien..., y luego di "`no`" a anulable. Pulsa `enter` para terminar.

De vuelta a la clase Profesional, es..., mayormente no sorprendente. Tenemos una nueva propiedad `$password`... y al final, un nuevo método `setPassword()`.
