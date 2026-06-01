<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dsn']      The full DSN string describe a connection to the database.
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database driver. e.g.: mysqli.
|			Currently supported:
|				 cubrid, ibase, mssql, mysql, mysqli, oci8,
|				 odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['encrypt']  Whether or not to use an encrypted connection.
|
|			'mysql' (deprecated), 'sqlsrv' and 'pdo/sqlsrv' drivers accept TRUE/FALSE
|			'mysqli' and 'pdo/mysql' drivers accept an array with the following options:
|
|				'ssl_key'    - Path to the private key file
|				'ssl_cert'   - Path to the public key certificate file
|				'ssl_ca'     - Path to the certificate authority file
|				'ssl_capath' - Path to a directory containing trusted CA certificats in PEM format
|				'ssl_cipher' - List of *allowed* ciphers to be used for the encryption, separated by colons (':')
|				'ssl_verify' - TRUE/FALSE; Whether verify the server certificate or not ('mysqli' only)
|
|	['compress'] Whether or not to use client compression (MySQL only)
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|	['ssl_options']	Used to set various SSL options that can be used when making SSL connections.
|	['failover'] array - A array with 0 or more data for connections if the main should fail.
|	['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
| 				NOTE: Disabling this will also effectively disable both
| 				$this->db->last_query() and profiling of DB queries.
| 				When you run a query, with this setting set to TRUE (default),
| 				CodeIgniter will store the SQL statement for debugging purposes.
| 				However, this may cause high memory usage, especially if you run
| 				a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/

$active_group = 'sqlserver';
$query_builder = TRUE;

$db_host = getenv('DB_HOST') ?: '192.168.1.8\SQLEXPRESS';
$db_port = getenv('DB_PORT') ?: '';
$db_user = getenv('DB_USER') ?: 'sa';
$db_pass = getenv('DB_PASS') ?: 'minda1234';
$db_name = getenv('DB_NAME') ?: 'treportdb';
$db_driver = getenv('DB_DRIVER') ?: 'mssql';

// Setup 'sqlserver' config
$sqlserver_dsn = '';
$sqlserver_hostname = $db_host;
if ($db_driver === 'pdo') {
	$host_with_port = $db_host;
	if (!empty($db_port) && strpos($db_host, ':') === false && strpos($db_host, ',') === false) {
		$host_with_port .= ',' . $db_port;
	}
	$sqlserver_dsn = "dblib:host={$host_with_port};dbname={$db_name};charset=utf8";
	$sqlserver_hostname = '';
} else {
	if (!empty($db_port) && strpos($db_host, ':') === false && strpos($db_host, ',') === false) {
		$sqlserver_hostname .= ',' . $db_port;
	}
}

$db['sqlserver'] = array(
	'dsn'	   => $sqlserver_dsn,
	'hostname' => $sqlserver_hostname,
	'username' => $db_user,
	'password' => $db_pass,
	'database' => $db_name,
	'dbdriver' => $db_driver,
	'dbprefix' => '',
	'pconnect' => TRUE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',	
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'autoinit' => TRUE,
	'save_queries' => TRUE
);


/* STAGING DATABASE */

$db_name_staging = getenv('DB_NAME_STAGING') ?: 'treport_db_staging';
$staging_dsn = '';
$staging_hostname = $db_host;
if ($db_driver === 'pdo') {
	$host_with_port = $db_host;
	if (!empty($db_port) && strpos($db_host, ':') === false && strpos($db_host, ',') === false) {
		$host_with_port .= ',' . $db_port;
	}
	$staging_dsn = "dblib:host={$host_with_port};dbname={$db_name_staging};charset=utf8";
	$staging_hostname = '';
} else {
	if (!empty($db_port) && strpos($db_host, ':') === false && strpos($db_host, ',') === false) {
		$staging_hostname .= ',' . $db_port;
	}
}

$db['sqlserver_staging'] = array(
	'dsn'			=> $staging_dsn,
	'hostname' 		=> $staging_hostname,
	'username' 		=> $db_user,
	'password' 		=> $db_pass,
	'database' 		=> $db_name_staging,
	'dbdriver' 		=> $db_driver,
	'dbprefix' 		=> '',
	'pconnect' 		=> TRUE,
	'db_debug' 		=> FALSE,
	'cache_on' 		=> FALSE,
	'cachedir' 		=> '',
	'char_set' 		=> 'utf8',	
	'dbcollat' 		=> 'utf8_general_ci',
	'swap_pre' 		=> '',
	'encrypt'  		=> TRUE,
	'compress' 		=> FALSE,
	'stricton' 		=> FALSE,
	'failover' 		=> array(),
	'autoinit' 		=> TRUE,
	'save_queries'  => TRUE
);

$db_name_pds = getenv('DB_NAME_PDS') ?: 'pdsdb';
$pds_dsn = '';
$pds_hostname = $db_host;
if ($db_driver === 'pdo') {
	$host_with_port = $db_host;
	if (!empty($db_port) && strpos($db_host, ':') === false && strpos($db_host, ',') === false) {
		$host_with_port .= ',' . $db_port;
	}
	$pds_dsn = "dblib:host={$host_with_port};dbname={$db_name_pds};charset=utf8";
	$pds_hostname = '';
} else {
	if (!empty($db_port) && strpos($db_host, ':') === false && strpos($db_host, ',') === false) {
		$pds_hostname .= ',' . $db_port;
	}
}

$db['pdsdb'] = array(
	'dsn'	   => $pds_dsn,
	'hostname' => $pds_hostname,
	'username' => $db_user,
	'password' => $db_pass,
	'database' => $db_name_pds,
	'dbdriver' => $db_driver,
	'dbprefix' => '',
	'pconnect' => TRUE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',	
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'autoinit' => TRUE,
	'save_queries' => TRUE
);

/* LOCAL Database */

/*$db['sqlserver'] = array(
	'dsn'	=> '',
	'hostname' => '192.168.1.57:1433\SQLEXPRESS',
	'username' => 'anjo',
	'password' => 'Password123$',
	'database' => 'treportdb',
	'dbdriver' => 'mssql',
	'dbprefix' => '',
	'pconnect' => TRUE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',	
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'autoinit' => TRUE,
	'save_queries' => TRUE
);*/




/*
$db['sqlserver'] = array(
	'dsn'	=> '',
	'hostname' => 'Driver={SQL Server};Server=RBORJA\SQLEXPRESS;Database=treportdb;',
	'port' => '1433',
	'username' => 'anjo',
	'password' => 'Password123$',
	'database' => 'treportdb',
	'dbdriver' => 'odbc',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',	
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

*/