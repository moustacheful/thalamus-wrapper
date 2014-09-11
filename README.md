# Thalamus OOP wrapper for PHP

##Nueva instancia
	
	$thalamus = new Thalamus(array(
    	'touchpoint' => 'TOUCHPOINT',
    	'country' => 1044 // set a default country
	));

##Configurar después de inicializar:
	$thalamus->setConfig(array(
	    'api_url' => 'API_URL',
	     'token' => 'TOKEN'
	));

##Variables

##### config
El array de configuración.

##### lastResponse
Devuelve la información sobre el último llamado para debug.

##Métodos

----------

### Básicos
#####	configureMode($config:array)
Permite configurar la instancia
	
	$config = array(
		touchpoint => 'TOUCHPOINT',
		token => 'TOKEN'
		api_url => 'API_URL',
		country => 1044	
	);

##### api($api_method:string,$data:array,$method="POST",$version = 'v3') 
Hace una llamada en nivel bajo y devuelve la respuesta como objeto

----------

### Usuarios

##### register($data:array)
Recibe un objeto 'person' de Thalamus (array) ver documentación de thalamus para estructura.

##### updateUser($data:array)
Recibe un objeto 'person' de Thalamus. actualiza los datos. Debe estar logeado.

##### login($main,$password)
Logea un usuario, devuelve su información, y la setea en la sesión como THALAMUS_USER. También se puede obtener como .getUser()


##### logout()
Elimina las variables de sesión asociadas a Thalamus

##### getUser($force = FALSE)
Devuelve el usuario en la sesión, o la va a buscar de thalamus si se puede. $force para forzar el último comportamiento mencionado.

##### resetPassword($main)
Genera un pedido de reseteo de password a Thalamus a la cuenta asociada a $main

----------

### Información

##### getCountries()

Devuelve un listado con países ('/referencedata/countries')

##### getStates($country_id:int)

Devuelve un listado con regiones o estados. Si no se da el $country_id, usa el de la configuración.

##### getCities($state_id:int)
Devuelve un listado con las ciudades de la región/
