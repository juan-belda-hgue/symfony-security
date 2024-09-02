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

Fíjate que no ha generado un método `getPassword()`... porque ya teníamos uno. Pero tenemos que actualizarlo para que devuelva `$this->password`.

Algo muy importante sobre esta propiedad `$password`: **no va a almacenar la contraseña en texto plano**. ¡Nunca almacenes la contraseña en texto plano! Esa es la forma más rápida de tener una brecha de seguridad..., y de perder amigos.

En su lugar, vamos a almacenar una versión cifrada de la contraseña..., y veremos cómo generar esa contraseña cifrada en un minuto. Pero antes, vamos a hacer la migración para la nueva propiedad:

```Shell
symfony console make:migration --formatted

symfony console doctrine:migrations:migrate
```

### La configuración de password_hashers

¡Perfecto! Ahora que nuestros usuarios tienen una nueva columna de contraseña en la base de datos, vamos a rellenarla en nuestros accesorios. Abre `src/Factory/UserFactory.php` y busca `getDefaults()`.

De nuevo, lo que no vamos a hacer es poner en password la contraseña en texto plano. No, esa propiedad password tiene que almacenar la versión hash de la contraseña.

Abre `config/packages/security.yaml`. Este tiene un poco de configuración en la parte superior llamada `password_hashers`, que le dice a Symfony qué algoritmo de hash debe utilizar para el hash de las contraseñas de los usuarios:

```yaml
security:
   # https://symfony.com/doc/current/security.html#registering-the-user-hashing-passwords
   password_hashers:
      Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: "auto"
```

Esta configuración dice que cualquier clase de User que implemente `PasswordAuthenticatedUserInterface` - lo que nuestra clase, por supuesto, hace - utilizará el algoritmo auto donde Symfony elige el último y mejor algoritmo automáticamente.

### El servicio de aseado de contraseñas

Gracias a esta configuración, tenemos acceso a un servicio "hasher" que es capaz de convertir una contraseña de texto plano en una versión hash utilizando este algoritmo auto. De vuelta a `UserFactory`, podemos utilizarlo para establecer la propiedad password.

En el constructor, añade un nuevo argumento: `UserPasswordHasherInterface $passwordHasher`. Yo le doy a `Alt+Enter` y voy a "Inicializar propiedades" para crear esa propiedad y establecerla:

```php
// src/Factory/UserFactory.php
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// ... lines 8 - 29
final class UserFactory extends ModelFactory
{
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }
// ... lines 40 - 67
}
```

A continuación, podemos establecer password a `$this->passwordHasher->hashPassword()` y luego pasarle alguna cadena de texto plano.

Bueno..., para ser sincero..., aunque espero que esto tenga sentido a alto nivel..., esto no funcionará del todo porque el primer argumento de `hashPassword()` es el objeto Profesional..., que aún no tenemos dentro de `getDefaults()`.

No pasa nada porque, de todas formas, me gusta crear una propiedad `plainPassword` en `Profesional` para facilitar todo esto. Añadamos eso a continuación, terminemos las fijaciones y actualicemos nuestro autentificador para validar la contraseña. Ah, pero no te preocupes: esa nueva propiedad `plainPassword` no se almacenará en la base de datos.

## 12. Hash de contraseñas en texto plano y PasswordCredentials

El proceso de guardar la contraseña de un usuario siempre es el siguiente:

1. Empieza con una contraseña en texto plano.
2. Haz un hash de la misma.
3. Y luego guarda la versión hash en el Profesional.

Esto es algo que vamos a hacer en los accesorios,... pero también lo haremos en un formulario de registro más adelante,... y también lo necesitarías en un formulario de cambio de contraseña.

### Añadir un campo plainPassword

Para facilitar esto, voy a hacer algo opcional. En `Profesional`, arriba, añade una nueva propiedad `private $plainPassword`.

La clave es que esta propiedad no se persistirá en la base de datos: es sólo una propiedad temporal que podemos utilizar durante, por ejemplo, el registro, para almacenar la contraseña simple.

A continuación, iré a "Código"->"Generar" -o Command+N en un Mac- para generar el getter y el setter para esto. El getter devolverá un string nulo:

```php
class Profesional implements UserInterface, PasswordAuthenticatedUserInterface
{
   public function getPlainPassword(): ?string
   {
      return $this->plainPassword;
   }
   
   public function setPlainPassword(string $plainPassword): self
   {
      $this->plainPassword = $plainPassword;
      return $this;
   }
}
```

Ahora, si tienes una propiedad `plainPassword`, querrás encontrar `eraseCredentials()` y poner `$this->plainPassword` en `null`:

```php
class Profesional implements UserInterface, PasswordAuthenticatedUserInterface
{
   public function eraseCredentials()
   {
      // Si almacena algún dato temporal y confidencial sobre el usuario, bórrelo aquí
      $this->plainPassword = null;
   }
}
```

Esto..., no es realmente tan importante. Después de que la autenticación sea exitosa, Symfony llama a `eraseCredentials()`. Es..., sólo una forma de "borrar cualquier información sensible" de tu objeto Profesional una vez que se ha realizado la autenticación. Técnicamente nunca estableceremos `plainPassword` durante la autenticación..., así que no importa. Pero, de nuevo, es algo seguro.

### Hacer un hash de la contraseña en los accesorios

De vuelta a `UserFactory`, en lugar de establecer la propiedad password, establece `plainPassword` como "Aa_123456".

Si nos detuviéramos ahora, se establecería esta propiedad..., pero entonces la propiedad `password` seguiría siendo `null`..., y explotaría en la base de datos porque esa columna es necesaria.

Así que, después de que `Foundry` haya terminado de instanciar el objeto, tenemos que ejecutar algún código adicional que lea el `plainPassword` y lo someta a **hash**. Podemos hacerlo aquí abajo, en el método `initialize()`..., mediante un gancho "después de la instanciación":

```php
final class UserFactory extends ModelFactory
{
// ...
   protected function initialize(): self
   {
      // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
      return $this
         // ->afterInstantiate(function(User $user) {})
      ;
   }
// ...
}
```

Esto está muy bien: llama a `$this->afterInstantiate()`, pásale una llamada de retorno y, dentro de digamos si `$user->getPlainPassword()` -por si acaso lo anulamos a `null` -, entonces `$user->setPassword()`. Genera el hash con `$this->passwordHasher->hashPassword()` pasándole el usuario al que estamos tratando de hacer el hash - así que `$user` - y luego lo que sea la contraseña simple: `$user->getPlainPassword()`:

```php
final class UserFactory extends ModelFactory
{
// ...
   protected function initialize(): self
   {
      // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
      return $this
         ->afterInstantiate(function(Profesional $profesional) {
                if ($profesional->getPlainPassword()) {
                    $profesional->setPassword(
                        $this->passwordHasher->hashPassword($profesional, $profesional->getPlainPassword())
                    );
                }
            })
      ;
   }
// ...
}
```

¡Hecho! Vamos a probar esto. Busca tu terminal y ejecuta:

> `symfony console doctrine:fixtures:load`

Esto te llevará un poco más de tiempo que antes, porque hacer el hash de las contraseñas requiere un uso intensivo de la CPU. Pero... ¡funciona! Comprueba la tabla user:

> `symfony console doctrine:query:sql "SELECT * FROM user"`

Y... ¡lo tengo! ¡Cada usuario tiene una versión con hash de la contraseña!

### Validación de la contraseña: PasswordCredentials

Por último, estamos preparados para comprobar la contraseña del usuario dentro de nuestro autentificador. Para ello, tenemos que hacer un hash de la contraseña simple enviada y luego compararla de forma segura con el hash de la base de datos.

Bueno, no necesitamos hacerlo..., porque Symfony lo hará automáticamente. Compruébalo: sustituye `CustomCredentials` por un nuevo `PasswordCredentials` y pásale la contraseña en texto plano enviada:

```php
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
// ...
class LoginFormAuthenticator extends AbstractAuthenticator
{
// ...
    public function authenticate(Request $request): PassportInterface
    {
// ...
        return new Passport(
// ...
            new PasswordCredentials($password)
        );
    }
// ...
}
```

¡Ya está! Pruébalo. Accede con nuestro usuario real - 12345678Z - y copia eso, y luego una contraseña errónea. ¡Muy bien! ¡Contraseña no válida! Ahora introduce la contraseña real Aa_123456. ¡Funciona!

¡Es increíble! Cuando pones un `PasswordCredentials` dentro de tu `Passport`, Symfony lo utiliza automáticamente para comparar la contraseña enviada con la contraseña con hash del usuario en la base de datos. Eso me encanta.

Todo esto es posible gracias a un potente sistema de escucha de eventos dentro de la seguridad. Vamos a aprender más sobre eso a continuación y veremos cómo podemos aprovecharlo para añadir protección CSRF a nuestro formulario de acceso..., con unas dos líneas de código.

## 13. Sistema de eventos de seguridad y protección Csrf

Después de devolver el objeto `Passport`, sabemos que ocurren dos cosas. En primer lugar, el `UserBadge` se utiliza para obtener el objeto `Profesional`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
// ...
    public function authenticate(Request $request): PassportInterface
    {
// ...
        return new Passport(
            new UserBadge($nif, function($profesionalIdentifier) {
                // optionally pass a callback to load the User manually
                $profesional = $this->profesionalRepository->findOneBy(['nif' => $profesionalIdentifier]);
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

En nuestro caso, como le pasamos un segundo argumento, sólo llama a nuestra función, y nosotros hacemos el trabajo. Pero si sólo pasas un argumento, entonces el proveedor del usuario hace el trabajo.

Lo segundo que ocurre es que se "resuelve" la "placa de credenciales":

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
// ...
   public function authenticate(Request $request): PassportInterface
   {
// ...
      return new Passport(
// ...
         new PasswordCredentials($password)
      );
   }
// ...
}
```

Originalmente lo hacía ejecutando nuestra llamada de retorno. Ahora comprueba la contraseña del usuario en la base de datos.

### El sistema de eventos en acción

Todo esto está impulsado por un sistema de eventos realmente genial. Después de nuestro método `authenticate()`, el sistema de seguridad envía varios eventos..., y hay un conjunto de oyentes de estos eventos que hacen diferentes trabajos. Más adelante veremos una lista completa de estos oyentes..., e incluso añadiremos nuestros propios oyentes al sistema.

### UserProviderListener

Pero veamos algunos de ellos. Pulsa `Shift+Shift` para que podamos cargar algunos archivos del núcleo de Symfony. El primero se llama `UserProviderListener`. Asegúrate de "Incluir elementos que no sean del proyecto"..., y ábrelo.

Se llama después de que devolvamos nuestro `Passport`. Primero comprueba que el `Passport` tiene un `UserBadge` -siempre lo tendrá en cualquier situación normal- y luego coge ese objeto. A continuación, comprueba si la placa tiene un **"cargador de usuario": es la función que pasamos al segundo argumento de nuestro `UserBadge`**. Si la placa ya tiene un cargador de usuario, como en nuestro caso, no hace nada. Pero si no lo tiene, establece el cargador de usuarios en el método `loadUserByIdentifier()` de nuestro proveedor de usuarios.

Es..., un poco técnico..., pero esto es lo que hace que nuestro proveedor de usuario en `security.yaml` se encargue de cargar el usuario si sólo pasamos un argumento a `UserBadge`.

### CheckCredentialsListener

Vamos a comprobar otra clase. Cierra ésta y pulsa `Shift+Shift` para abrir `CheckCredentialsListener`. Como su nombre indica, se encarga de comprobar las "credenciales" del usuario. Primero comprueba si el `Passport` tiene una credencial `PasswordCredentials`. Aunque su nombre no lo parezca, los objetos "credenciales" son sólo insignias..., como cualquier otra insignia. Así que esto comprueba si el `Passport` tiene esa insignia y, si la tiene, coge la insignia, lee la contraseña en texto plano de ella y, finalmente aquí abajo, utiliza el hasher de contraseñas para verificar que la contraseña es correcta. Así que **esto contiene toda la lógica del hash de la contraseña**. Más abajo, este oyente también se encarga de la insignia `CustomCredentials`.

### Las insignias deben ser resueltas

Así que tu `Passport` siempre tiene al menos estas dos insignias: la `UserBadge` y también algún tipo de "insignia de credenciales". Una propiedad importante de las insignias es que cada una debe estar "resuelta". Puedes ver esto en `CheckCredentialsListener`. Cuando termina de comprobar la contraseña, llama a `$badge->markResolved()`. Si, por alguna razón, no se llamara a este `CheckCredentialsListener` debido a alguna configuración errónea..., la insignia quedaría "sin resolver" y eso haría que la autenticación fallara. Sí, después de llamar a los listeners, Symfony comprueba que todas las insignias se han resuelto. Esto significa que puedes devolver con confianza `PasswordCredentials` y no tener que preguntarte si algo ha verificado realmente esa contraseña.

### Añadir protección CSRF

Y aquí es donde las cosas empiezan a ponerse más interesantes. Además de estas dos insignias, podemos añadir más insignias a nuestro `Passport` para activar más superpoderes. Por ejemplo, una cosa buena para tener en un formulario de inicio de sesión es la protección CSRF. Básicamente, añades un campo oculto a tu formulario que contenga un token CSRF..., y luego, al enviar, validas ese token.

Hagamos esto. En cualquier lugar dentro de tu formulario, añade una entrada `type="hidden"`, `name="_csrf_token"` - este nombre podría ser cualquier cosa, pero es un nombre estándar - y luego `value="{{ csrf_token() }}"`. Pásale la cadena `authenticate`:

```php
{% block body %}
<div class="container">
    <div class="row">
        <div class="login-form bg-light mt-4 p-4">
            <form method="post" class="row g-3">
// ...
                <input type="hidden" name="_csrf_token"
                    value="{{ csrf_token('authenticate') }}"
                >
// ...
            </form>
        </div>
    </div>
</div>
{% endblock %}
```

Ese `authenticate` también podría ser cualquier cosa..., es como un nombre único para este formulario.

Ahora que tenemos el campo, copia su nombre y dirígete a `LoginFormAuthenticator`. Aquí, tenemos que leer ese campo de los datos POST y luego preguntar a Symfony:

¿Es válido este token CSRF?

Bueno, en realidad, esa segunda parte ocurrirá automáticamente.

¿Cómo? El objeto `Passport` tiene un tercer argumento: un array de otras fichas que queramos añadir. Añade una: una nueva `CsrfTokenBadge()`.

Esto necesita dos cosas. La primera es el identificador del token CSRF. Digamos `authenticate`.

Esto sólo tiene que coincidir con lo que hayamos utilizado en el formulario. El segundo argumento es el valor enviado, que es `$request->request->get()` y el nombre de nuestro campo: `_csrf_token`:

```php
class LoginFormAuthenticator extends AbstractAuthenticator
{
// ...
    public function authenticate(Request $request): PassportInterface
    {
// ...
        return new Passport(
// ...
            [
                new CsrfTokenBadge(
                    'authenticate',
                    $request->request->get('_csrf_token')
                )
            ]
        );
    }
// ...
}
```

Y..., ¡ya hemos terminado! Internamente, un oyente se dará cuenta de esta insignia, validará el token CSRF y resolverá la insignia.

¡Vamos a probarlo! Ve a `/login`, inspecciona el formulario..., y encuentra el campo oculto. Ahí está. Introduce cualquier nif, cualquier contraseña..., pero lía el valor del token CSRF. Pulsa "Iniciar sesión" y..., ¡sí! ¡Token CSRF inválido! Ahora bien, si no nos metemos con el token..., y utilizamos cualquier nif y contraseña..., ¡bien! El token CSRF era válido..., así que continuó con el error del nif.

A continuación: vamos a aprovechar el sistema "recuérdame" de Symfony para que los usuarios puedan permanecer conectados durante mucho tiempo. Esta función también aprovecha el sistema de oyentes y una insignia.

## 14. Sistema de recordarme

Otra buena característica de un formulario de acceso es la casilla "recuérdame". Aquí almacenamos una cookie "recuérdame" de larga duración en el navegador del usuario, de modo que cuando cierre su navegador -y por tanto, pierda su sesión- esa cookie le mantendrá conectado... durante una semana... o un año... o lo que configuremos. Añadamos esto.

### Habilitar el sistema **remember_me**

El primer paso es ir a `config/packages/security.yaml` y activar el sistema. Lo hacemos diciendo `remember_me:` y, a continuación, estableciendo una pieza de configuración necesaria: `secret:` establecer en `%kernel.secret%`:

```yaml
security:
// ...
    firewalls:
// ...
        main:
// ...
            remember_me:
                secret: '%kernel.secret%'
```

Esto se utiliza para "firmar" el valor de la cookie remember me... y el parámetro `kernel.secret` viene en realidad de nuestro archivo `.env`:

```yaml
// Archivo .env
// ...
###> symfony/framework-bundle ###
// ...
APP_SECRET=c28f3d37eba278748f3c0427b313e86a
###< symfony/framework-bundle ###
// ...
```

Sí, este `APP_SECRET` acaba convirtiéndose en el parámetro `kernel.secret`..., al que podemos hacer referencia aquí.

Como es normal, hay un montón de otras opciones que puedes poner en `remember_me`... y puedes ver muchas de ellas ejecutando:

`symfony console debug:config security`

Busca la sección `remember_me:`. Una importante es `lifetime:`, que es el tiempo de validez de la cookie "Recuérdame".

Antes he dicho que la mayor parte de la configuración que ponemos bajo nuestro cortafuegos sirve para activar diferentes autentificadores. Por ejemplo, `custom_authenticator`: activa nuestro `LoginFormAuthenticator`:

Lo que significa que ahora se llama a nuestra clase al inicio de cada petición y se busca el envío de un formulario de acceso. La configuración de `remember_me` también activa un autentificador: un autentificador central llamado `RememberMeAuthenticator`. En cada petición, éste busca una cookie "recuérdame" -que crearemos en un segundo- y, si está ahí, la utiliza para autenticar al usuario.

### Añadir la casilla de verificación "Recuérdame

Ahora que esto está en su sitio, nuestro siguiente trabajo es establecer esa **cookie** en el navegador del usuario después de que se conecte. Abre `login.html.twig`. En lugar de añadir siempre la cookie, dejemos que el usuario elija. Justo después de la contraseña, añade un `div` con algunas clases, una etiqueta y una entrada `type="checkbox"`, `name="_remember_me"`:

```php
{% block body %}
<div class="container">
    <div class="row">
        <div class="login-form bg-light mt-4 p-4">
            <form method="post" class="row g-3">
// ...
                <div class="form-check mb-3">
                    <label>
                        <input type="checkbox" name="_remember_me" class="form-check-input"> Recuérdame
                    </label>
                </div>
// ...
            </form>
        </div>
    </div>
</div>
{% endblock %}
```

El nombre - `_remember_me` - es importante y tiene que ser ese valor. Como veremos en un minuto, el sistema busca una casilla de verificación con este nombre exacto.

Bien, actualiza el formulario. Genial, ¡tenemos una casilla de verificación!

### Optar por la Cookie Remember Me

Si marcáramos la casilla y la enviáramos... no pasaría absolutamente nada diferente: Symfony no establecería una cookie "Recuérdame".

Esto se debe a que nuestro autentificador necesita anunciar que admite el establecimiento de cookies remember me. Esto es un poco raro, pero piénsalo: el hecho de que hayamos activado el sistema `remember_me` en `security.yaml` no significa que queramos que SIEMPRE se establezcan cookies remember me. En un formulario de inicio de sesión, definitivamente. Pero si tuviéramos algún tipo de autenticación con token de la API..., entonces no querríamos que Symfony intentara establecer una cookie remember me en esa petición de la API.

En cualquier caso, todo lo que tenemos que añadir es una pequeña bandera que diga que este mecanismo de autenticación sí admite añadir cookies remember me. Hazlo con una insignia: `new RememberMeBadge()`:

```php
\\ Archivo src/Security/LoginFormAuthenticator.php

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
// ...
class LoginFormAuthenticator extends AbstractAuthenticator
{
// ...
    public function authenticate(Request $request): PassportInterface
    {
// ...
        return new Passport(
            new UserBadge($email, function($userIdentifier) {
// ...
            new PasswordCredentials($password),
            [
// ...
                new RememberMeBadge(),
            ]
        );
    }
// ...
}
```

¡Eso es todo! Pero hay una cosa rara. Con el `CsrfTokenBadge`, leemos el token **POST** y se lo pasamos a la insignia. Pero con `RememberMeBadge`..., no hacemos eso. En su lugar, internamente, el sistema "recuérdame" sabe que debe buscar una casilla llamada, exactamente, `_remember_me`.

Todo el proceso funciona así. Después de que nos autentifiquemos con éxito, el sistema "recuérdame" buscará esta insignia y mirará si esta casilla está marcada. Si ambas cosas son ciertas, añadirá la cookie "recuérdame".

Veamos esto en acción. Actualiza la página..., e introduce nuestro nif normal, la contraseña "Aa_123456", haz clic en la casilla "Recuérdame"..., y pulsa "Iniciar sesión". La autenticación se ha realizado con éxito. No es ninguna sorpresa. Pero ahora abre las herramientas de tu navegador, ve a "Aplicación", busca "Cookies" y..., ¡sí! Tenemos una nueva cookie `REMEMBERME`..., que caduca dentro de mucho tiempo: ¡es decir, dentro de 1 año!

### Ver cómo nos autentifica la cookie RememberMe

Para demostrar que el sistema funciona, elimina la cookie de sesión que normalmente nos mantiene conectados. Observa lo que ocurre cuando actualizamos. ¡Seguimos conectados! Eso es gracias al autentificador `remember_me`.

Cuando te autentificas, internamente, tu objeto `Profesional` se envuelve en un objeto "token"..., que normalmente no es demasiado importante. Pero ese token muestra cómo te has autentificado. Ahora dice `RememberMeToken`..., lo que demuestra que la cookie "Recuérdame" fue la que nos autenticó.

Ah, y si te preguntas por qué Symfony no ha añadido una nueva cookie de sesión..., eso es sólo porque **la sesión de Symfony es perezosa**. No lo verás hasta que vayas a una página que utilice la sesión - como la página de inicio de sesión. Ahora está de vuelta.

Y..., ¡eso es todo! Además de nuestro `LoginFormAuthenticator`, ahora hay un segundo autentificador que busca información de autentificación en una cookie de `REMEMBERME`.

Sin embargo, podemos hacer todo esto un poco más elegante. A continuación, vamos a ver cómo podríamos añadir una cookie "Recuérdame" para todos los usuarios cuando se conecten, sin necesidad de una casilla de verificación. También vamos a explorar una nueva opción del sistema "recuérdame" que permite invalidar todas las cookies "recuérdame" existentes si el usuario cambia su contraseña.

## 15. Recordarme siempre y "signature_properties"

Ahora que tenemos el sistema "recuérdame" funcionando, ¡juguemos con él! En lugar de dar al usuario la opción de activar "recuérdame", ¿podríamos..., activarlo siempre?

En este caso, ya no necesitamos la casilla "Recuérdame", así que la eliminamos por completo.

### always_remember_me: true

Hay dos formas de "forzar" al sistema remember me a establecer siempre una cookie aunque no esté la casilla de verificación.

- La primera es en security.yaml: establecer always_remember_me: en true:

   ```yaml
   security:
   // ... lines 2 - 16
       firewalls:
   // ... lines 18 - 20
         main:
   // ... lines 22 - 27
               remember_me:
   // ... line 29
                always_remember_me: true
   ```

   Con esto, nuestro autentificador sigue necesitando añadir un `RememberMeBadge`. Pero el sistema ya no buscará esa casilla. Mientras vea esta insignia, añadirá la cookie.

### Habilitación en el RememberMeBadge

- La otra forma de habilitar la cookie "Recuérdame" en todas las situaciones es a través de la propia insignia. Comenta la nueva opción:

   ```yaml
   security:
   // ... lines 2 - 16
       firewalls:
   // ... lines 18 - 20
         main:
   // ... lines 22 - 27
               remember_me:
   // ... line 29
                #always_remember_me: true
   ```

Dentro de `LoginFormAuthenticator`, en la propia insignia, puedes llamar a `->enable()`..., que devuelve la instancia de la insignia:

```php
\\ Archivo src/Security/LoginFormAuthenticator.php

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
// ...
class LoginFormAuthenticator extends AbstractAuthenticator
{
// ...
    public function authenticate(Request $request): PassportInterface
    {
// ...
        return new Passport(
            new UserBadge($email, function($userIdentifier) {
// ...
            new PasswordCredentials($password),
            [
// ...
                new RememberMeBadge()->enable(),
            ]
        );
    }
// ...
}
```

Esto dice:

> No me interesa ninguna otra configuración ni la casilla de verificación: Definitivamente quiero que el sistema remember me añada una cookie.

¡Vamos a probarlo! Borra la sesión y la cookie `REMEMBERME`. Esta vez, cuando iniciemos la sesión..., ¡oh, token CSRF no válido! Eso es porque acabo de matar mi sesión sin refrescar - Refresca e inténtalo de nuevo.

¡Muy bien! ¡Tenemos la cookie REMEMBERME!

### Asegurar las cookies Remember Me: Invalidar al cambiar los datos del usuario

Hay una cosa con la que debes tener cuidado cuando se trata de las cookies "Recuérdame". Si un usuario malintencionado consiguiera de algún modo acceder a mi cuenta -por ejemplo, si robara mi contraseña-, podría, por supuesto, iniciar la sesión. Normalmente, eso es un asco..., pero en cuanto lo descubra, podría cambiar mi contraseña, lo que les desconectaría.

Pero..., si ese mal usuario tiene una cookie de REMEMBERME..., entonces, aunque cambie mi contraseña, seguirá conectado hasta que esa cookie caduque..., lo que podría ser dentro de mucho tiempo. Estas cookies son casi tan buenas como las reales: actúan como "billetes de autentificación gratuitos". Y siguen funcionando -independientemente de lo que hagamos- hasta que caducan.

Afortunadamente, en el nuevo sistema de autenticación, hay una forma muy interesante de evitar esto. En `security.yaml`, debajo de `remember_me`, añade una nueva opción llamada `signature_properties` configurada en un array con password dentro:

   ```yaml
   security:
   // ...
       firewalls:
   // ...
         main:
   // ...
            remember_me:
   // ...
               #always_remember_me: true
   // ...
               signature_properties: [password]
   ```

Me explico. Cuando Symfony crea la cookie remember me, crea una "firma" que demuestra que esta cookie es válida. Gracias a esta configuración, ahora obtendrá la propiedad `password` de nuestro **Profesional** y la incluirá en la firma. Luego, cuando esa cookie se utilice para autenticarse, Symfony volverá a crear la firma utilizando el password del Profesional que está actualmente en la base de datos y se asegurará de que las dos firmas coincidan. Así que si el password de la base de datos es diferente a la contraseña que se utilizó para crear originalmente la cookie... ¡la coincidencia de la firma fallará!

En otras palabras, para cualquier propiedad de esta lista, si incluso una de estas cambia en la base de datos en ese Profesional, todas las cookies "recuérdame" para ese usuario serán invalidadas instantáneamente.

Así que si un usuario malo me roba la cuenta, todo lo que tengo que hacer es cambiar mi contraseña y ese usuario malo será expulsado.

Esto es superguay verlo en acción. Actualiza la página. Si modificas la configuración de `signature_properties`, se invalidarán todas las cookies de REMEMBERME en todo el sistema: así que asegúrate de que la configuración es correcta cuando lo configures por primera vez. Observa: si borro la cookie de sesión y actualizo..., ¡sí! No estoy autentificado: la cookie de REMEMBERME no ha funcionado. Sigue ahí..., pero no es funcional.

Iniciemos la sesión - con nuestro NIF normal..., y la contraseña..., para que obtengamos una nueva cookie remember me que se crea con la contraseña con hash.

¡Genial! Y ahora, en condiciones normales, las cosas funcionarán como siempre. Puedo borrar la cookie de sesión, actualizarla y seguiré conectado.

Pero ahora, vamos a cambiar la contraseña del usuario en la base de datos. Podemos hacer trampa y hacer esto en la línea de comandos:

`symfony console doctrine:query:sql "UPDATE profesional SET password='foo' WHERE nif='12345678Z'"`

Poner la contraseña en `foo` es una auténtica tontería..., ya que esta columna debe contener una contraseña con hash..., pero estará bien para nuestros propósitos. Pulsa y..., ¡fantástico! Esto imita lo que ocurriría si cambiara la contraseña de mi cuenta.

Ahora, si somos el usuario malo, la próxima vez que volvamos al sitio..., ¡de repente habremos cerrado la sesión! ¡Una barbaridad! ¡Y yo también me habría salido con la mía si no fuera por vosotros, niños entrometidos! La cookie "recuérdame" está ahí..., pero no funciona. Me encanta esta función.

A continuación: ¡es hora de tener un viaje de poder y empezar a negar el acceso! Veamos `access_control`: la forma más sencilla de bloquear el acceso a secciones enteras de tu sitio.

## 16. Denegación de acceso, access_control y roles

Ya hemos hablado mucho de la autenticación: el proceso de inicio de sesión. Y..., incluso ya hemos iniciado la sesión. Así que vamos a echar nuestro primer vistazo a la **autorización**, que es la parte divertida en la que podemos ir de un lado a otro y denegar el acceso a diferentes partes de nuestro sitio.

### Hola control_de_acceso

La forma más fácil de expulsar a alguien de tu fiesta es en realidad dentro de `config/packages/security.yaml`. Es a través de `access_control`:

```yaml
security:
// ...
   # Una forma sencilla de controlar el acceso a grandes secciones de su sitio
   # Nota: Solo se utilizará el *primer* control de acceso que coincida
   access_control:
      # - { path: ^/admin, roles: ROLE_ADMIN }
      # - { path: ^/profile, roles: ROLE_USER }
```

Descomenta la primera entrada.

El `path` es una expresión regular. Así que esto dice básicamente

Si una URL empieza por `/admin` -por tanto, `/admin` o `/admin*` -, entonces **denegaré** el acceso a menos que el usuario tenga `ROLE_ADMIN`.

Hablaremos más sobre los roles en un minuto..., pero puedo decirte que nuestro usuario no tiene ese rol. Así que..., vamos a intentar ir a una URL que coincida con esta ruta. En realidad tenemos una pequeña sección de administración en nuestro sitio. Asegúrate de que estás conectado..., y luego ve a `/admin`. ¡Acceso denegado! Se nos expulsa con un **error 403**.

En producción, puedes personalizar el aspecto de esta página de error 403..., además de personalizar la página de error 404 o 422.

### ¡Roles! Usuario::getRoles()

Hablemos de estos "**roles**". Abre la clase `Profesional:src/Entity/Profesional.php`. Así es como funciona. En el momento en que nos conectamos, Symfony llama a este método `getRoles()`, que forma parte de `UserInterface`.

Devolvemos un array con los roles que debe tener este usuario. El comando `make:user` generó esto para que siempre tengamos un rol llamado `ROLE_USER`..., más cualquier rol extra almacenado en la propiedad `$this->roles`. Esa propiedad contiene una matriz de cadenas..., que se almacenan en la base de datos como **JSON** (o no):

```php
/**
 * @var list<string> Los roles de usuario
   */
#[ORM\Column]
private array $roles = [];
```

Esto significa que podemos dar a cada usuario tantos roles como queramos. Hasta ahora, cuando hemos creado nuestros usuarios, no les hemos dado ningún rol..., por lo que nuestra propiedad roles está vacía. Pero gracias a cómo está escrito el método `getRoles()`, cada usuario tiene al menos `ROLE_USER`. El comando `make:user` generó el código así porque **todos los usuarios necesitan tener al menos un rol**..., de lo contrario vagan por nuestro sitio como usuarios zombis medio muertos. No es..., bonito.

Así que, por convención, siempre damos a un usuario al menos `ROLE_USER`. Ah, y la única regla sobre los roles -eso es un trabalenguas- es que deben empezar por `ROLE_`. Más adelante en el tutorial, aprenderemos por qué.

En cualquier caso, en el momento en que nos conectamos, Symfony llama a `getRoles()`, nos devuelve el array de roles, y los almacena. De hecho, podemos ver esto si hacemos clic en el icono de seguridad de la barra de herramientas de depuración de la web. ¡Sí! Roles: `ROLE_USER`.

Entonces, cuando vamos a `/admin`, esto coincide con nuestra primera entrada `access_control`, comprueba si tenemos `ROLE_ADMIN`, no lo tenemos, y deniega el acceso.

### Sólo coincide UN control_de_acceso

Ah, pero hay un detalle importante que hay que saber sobre `access_control`: sólo se encontrará una coincidencia en una petición.

Por ejemplo, supón que tienes dos controles de acceso como éste:

```yaml
security:
    # ...
    access_control:
      - { path: ^/admin, roles: ROLE_ADMIN }
      - { path: ^/admin/foo, roles: ROLE_USER }
```

Si fuéramos a `/admin`, eso **coincidiría con la primera regla y sólo utilizaría la primera regla**. Funciona como el enrutamiento: recorre la lista de control de acceso de uno en uno y, en cuanto encuentra la primera coincidencia, se detiene y utiliza sólo esa entrada.

Esto nos ayudará más adelante, cuando neguemos el acceso a toda una sección excepto a una URL. Pero por ahora, ¡sólo tenlo en cuenta!

Y..., eso es todo. Los controles de acceso nos proporcionan una forma realmente sencilla de asegurar secciones enteras de nuestro sitio. Pero es sólo una forma de denegar el acceso. Pronto hablaremos de cómo podemos denegar el acceso controlador por controlador, algo que me gusta mucho.

Pero antes de hacerlo, sé que si intento acceder a esta página sin `ROLE_ADMIN`, obtengo el error 403 prohibido. ¿Pero qué pasa si intento acceder a esta página como usuario anónimo? Ve a `/logout`. Ahora no estamos conectados.

Vuelve a `/admin` y..., ¡whoa! ¡Un error!

Se requiere una autentificación completa para acceder a este recurso.

A continuación, vamos a hablar del "punto de entrada" de tu cortafuegos: la forma en que ayudas a los usuarios anónimos a iniciar el proceso de acceso.

## 17. El punto de entrada: invitar a los usuarios a conectarse

Vuelve a entrar utilizando 12345678Z y la contraseña Aa_123456. Cuando vamos a `/admin`, como hemos visto antes, obtenemos "Acceso denegado". Esto se debe a `access_control`..., y al hecho de que nuestro usuario no tiene `ROLE_ADMIN`.

Pero si lo cambiamos por `ROLE_USER` -un rol que sí tenemos-, el acceso está garantizado.

Y conseguimos ver unos gráficos impresionantes.

Probemos una cosa más. Cerremos la sesión, es decir, vayamos manualmente a `/logout`. Ahora que no hemos iniciado la sesión, si vamos directamente a `/admin`: ¿qué debería ocurrir?

Bueno, en este momento, obtenemos una gran página de error con un código de estado 401. Pero..., ¡eso no es lo que queremos! Si un usuario anónimo intenta acceder a una página protegida de nuestro sitio, en lugar de un error, queremos ser súper amables e invitarle a iniciar la sesión. Como tenemos un formulario de entrada, significa que queremos redirigir al usuario a la página de entrada.

### ¡Hola punto de entrada!

Para saber qué hacer cuando un usuario anónimo accede a una página protegida, cada cortafuegos define algo llamado "punto de entrada". El punto de entrada de un cortafuegos es literalmente una función que dice:

> ¡Esto es lo que debemos hacer cuando un usuario anónimo intenta acceder a una página protegida!

Cada autentificador de nuestro cortafuegos puede o no "proporcionar" un punto de entrada. Ahora mismo, tenemos dos autentificadores: nuestro `LoginFormAuthenticator` personalizado y también el autentificador `remember_me`.

Pero ninguno de ellos proporciona un punto de entrada, por lo que, en lugar de redirigir al usuario a una página..., o algo diferente, obtenemos este error genérico 401. Algunos autenticadores incorporados -como `form_login`, del que hablaremos pronto- sí proporcionan un punto de entrada..., y lo veremos.

### Hacer de nuestro autentificador un punto de entrada

Pero, de todos modos, ninguno de nuestros autenticadores proporciona un punto de entrada..., ¡así que vamos a añadir uno!

Abre nuestro autentificador: `src/Security/LoginFormAuthenticator.php`. Si quieres que tu autentificador proporcione un punto de entrada, todo lo que tienes que hacer es implementar una nueva interfaz: `AuthenticationEntryPointInterface`:

```php
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class LoginFormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
// ...
}
```

Esto requiere que la clase tenga un nuevo método..., que en realidad ya tenemos aquí abajo. Se llama `start()`. Descomenta el método. Luego, dentro, muy simplemente, vamos a redirigir a la página de inicio de sesión. Voy a robar el código de arriba:

```php
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class LoginFormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
   public function start(Request $request, AuthenticationException $authException = null): Response
   {
        /*
         * If you would like this class to control what happens when an anonymous user accesses a
         * protected page (e.g. redirect to /login), uncomment this method and make this class
         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
         *
         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
         */
   }
}
```
