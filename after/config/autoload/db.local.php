<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
return array(
    'db' => array(
        'driver'         => 'pdo',
        'dsn'            => 'mysql:dbname=registrator_doctrine;host=localhost',
        'username'       => 'zend',
        'password'       => 'password',
        'driver_options' => array(
        		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        		// NOTE: change to PDO::ERRMODE_SILENT for production! 
        		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION),
    ),
	'service_manager' => array(
		'factories' => array(
			'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
		),
	),
    'doctrine' => array(
        'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'driver'         => 'pdo_mysql',
                    'host'           => 'localhost',
                    'dbname'         => 'registrator_doctrine',
                    'user'           => 'zend',
                    'password'       => 'password',
                    'driver_options' => array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                        // NOTE: change to PDO::ERRMODE_SILENT for production!
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
                    ),
                )
            )
        )
    )
);
