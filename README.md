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
