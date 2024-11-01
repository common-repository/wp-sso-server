# [WP-SSO](https://gitlab.com/wp-sso)

## wp-sso-server

Servidor de sso, este proyecto se trabajo en el cliente del usuario. Por lo que no es necesario, compartir servidor, ni cokkies, ni base de datos


### Instalacion

Se instala como cualquier plugin, no conlleva ningun tipo de configuracion extra

### Filtros

#### wp_sso_server_response_is_login

Puedes modificar el response de is_login *parcialmente*
    
asi se aplica el filtro
```php
    $data = apply_filters( 'wp_sso_server_response_is_login', $data, $current_user );
```
    
Añadiendo campos

```php
function my_wp_sso_server_response_is_login($data, $user) {

if ($data['is_login'] == true) {
$data['data_user'] = array(
'display_name' => $user->display_name,
);
}

return $data;
}

add_filter( 'wp_sso_server_response_is_login', 'my_wp_sso_server_response_is_login', 10, 2 );    
```

Response

```json
{"data":{"is_login":true,"is_suscription":true,"data_user":{"display_name":"mferro"},"user_token":{"token":"ODNjOTEwOGI1OTE0YzU2Zjk5ZTExNzU0NTIzMTIwOGRfMV8xNTE5Mjc0MDU1","nonce":1519274055}},"_":1519274055}
```

#### wp_sso_server_response_get_data

Puedes modificar el response de get_data *total* o *parcialmente*

asi se aplica el filtro
```php
$ar_user_data = apply_filters( 'wp_sso_server_response_get_data', $ar_user_data, $user );
```

Añadiendo campos

```php
function my_wp_sso_server_response_get_data($ar_user_data, $user) {
$ar_user_data['valor_rand'] = md5(time() . rand(0, 3));
return $ar_user_data;
}

add_filter( 'wp_sso_server_response_get_data', 'my_wp_sso_server_response_get_data', 10, 2 );    
```

Response

```json
{"data":{"user_login":"mferro","user_nicename":"mferro","user_email":"ferro.mariano@gmail.com","user_url":"","user_registered":"2018-02-03 03:35:04","display_name":"mferro","valor_rand":"96e8bbe7f0ba7259dfa391f5c25678c3"},"_":1519274669}
```

#### wp_sso_server_response_%

Para definir otras consultas remplasando % por el nombre que quieras

EJ: mas_datos_de_usuario

la url para llamar a este metodo seria **http://example.com/sso/mas_datos_de_usuario**

```php
$data = apply_filters( 'wp_sso_server_response_'.$action, null );
if ($data != null) {
$this->__responce_json($data);
}
```

Añadiendo campos

```php
function my_wp_sso_server_response_mas_datos_de_usuario($tmp) {
$response = array();
$response['data_extra'] = md5(time() . rand(0, 3));
return $response;
}

add_filter( 'wp_sso_server_response_mas_datos_de_usuario', 'my_wp_sso_server_response_mas_datos_de_usuario', 10, 2 );
```

Response

```json
{"data":{"data_extra":"c7ede399e60b1a54656329b596a7e846"},"_":1519275168}
```

    
    
    
    
    