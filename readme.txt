=== wp-sso-server ===
Tags: sso, Single Sign-on, authentication, one login, my sso
Donate link: https://paypal.me/ferromariano
Requires at least: 4.9.2
Tested up to: 4.9.4
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Servidor de sso para wordpress, este plugin convierte a tu wordpress en el servidor de login de otro wordpress, sin DBs, sin cookies. [Cliente wp-sso-client](https://wordpress.org/plugins/wp-sso-client/)

== Description ==
Servidor de sso para wordpress, este plugin convierte a tu wordpress en el servidor de login de otro wordpress, sin DBs, sin cookies. [Cliente wp-sso-client](https://wordpress.org/plugins/wp-sso-client/)

== Installation ==
= From your WordPress dashboard =
1. Visit `Plugins > Add New`.
2. Search for `wp-sso-server`. Find and Install `wp-sso-server`.
3. Activate the plugin from your Plugins page.
4. Visit `Users > WP SSO Server`.
5. Agregar los sitios habilitados

Este plugin solo funciona con permalinks configurado, no importa como esté configurado.


== Frequently Asked Questions ==
= ¿ Afectas a las URL ? =
Algunas url no las podras usar, El plugin usa el sufijo de /sso/ EJ: example.com/sso/is_login solo

= ¿ Requiere compartir servidor ? =
NO

= ¿ Requiere compartir DBS ? =
NO

= ¿ como lo hace ? =
El cliente incluye un jsonp el cual le entrega la sobre el usuario, el login y un token. Si el usuario no esta registrado en el WP cliente pero esta logueado en el WP servidor, este pide información al servidor, servidor a servidor, enviando el token. Con la información devuelta comprueba si el usuario esta registrado un usuario en el WP cliente, ( si no está lo registra ) y lo loguea


== Changelog ==
= 1.0 =
* Inicio del plugin