<?php
/**
 * amazon.api-user.keys.php
 * This file contains the credentials for the DF api-user created to make API calls to AWS
 */
if ( !class_exists( '\\CFRuntime' ) )
{
    die( 'No direct access allowed.' );
}

return
    array(
        // Credentials for the development environment.
        'development'            => array(

            //	Amazon Web Services Key. Found in the AWS Security Credentials. You can also pass this value as the first parameter to a service constructor.
            'key'                   => 'AKIAI53GZF6JTBZVUKYA',
            // Amazon Web Services Secret Key. Found in the AWS Security Credentials. You can also pass this value as the second parameter to a service constructor.
            'secret'                => 'b5o88Fcg53Zv69QZjHPbtdJLqCankjPyLavZtP4D',
            /**
             * This option allows you to configure a preferred storage type to use for caching by default. This can be changed later using the set_cache_config() method.
             * Valid values are: `apc`, `xcache`, or a file system path such as `./cache` or `/tmp/cache/`.
             */
            'default_cache_config'  => '',
            //	Use default system CA
            'certificate_authority' => false,
        ),
        // Credentials for the production environment.
        'production'             => array(

            //	Amazon Web Services Key. Found in the AWS Security Credentials. You can also pass this value as the first parameter to a service constructor.
            'key'                   => 'AKIAI53GZF6JTBZVUKYA',
            // Amazon Web Services Secret Key. Found in the AWS Security Credentials. You can also pass this value as the second parameter to a service constructor.
            'secret'                => 'b5o88Fcg53Zv69QZjHPbtdJLqCankjPyLavZtP4D',
            /**
             * This option allows you to configure a preferred storage type to use for caching by default. This can be changed later using the set_cache_config() method.
             * Valid values are: `apc`, `xcache`, or a file system path such as `./cache` or `/tmp/cache/`.
             */
            'default_cache_config'  => '',
            //	Use default system CA
            'certificate_authority' => false,
        ),
        // Specify a default credential set to use if there are more than one.
        '@default'               => 'production',
        //	Our Route53 hosted zones
        'route53.hosted_zones'   => array(
            'cloud'  => array('id' => 'Z34Q387I3ENIM2'),
            'ec2'    => array('id' => 'Z1UUTPNXPP2GV2'),
            'fabric' => array('id' => 'Z1JRVOK5UH1C1L'),
        ),
        //.........................................................................
        //. Other
        //.........................................................................
        'default_security_group' => 'dsp-basic',
        'default_key_name'       => 'dfadmin',
        'default_instance_type'  => 't1.micro',

    );