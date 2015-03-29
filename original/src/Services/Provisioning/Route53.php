<?php
/**
 * Route53.php
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @filesource
 */
namespace Cerberus\Services\Provisioning;

use DreamFactory\Services\DreamService;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Curl;
use Kisma\Core\Utility\Hasher;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

/**
 * Route53
 * Simple Route53 API client
 */
class Route53 extends DreamService
{
	//**************************************************************************
	//* Methods
	//**************************************************************************

	/**
	 * @param \Kisma\Core\Interfaces\ConsumerLike $consumer
	 * @param array                               $settings
	 *
	 * @throws \RuntimeException
	 */
	public function __construct( ConsumerLike $consumer, $settings = array() )
	{
		parent::__construct( $consumer, array( 'settings' => $settings ) );

		if ( null === ( $_keysFile = Pii::getParam( 'app.amazon_aws_credentials' ) ) && empty( $this->_settings ) )
		{
			throw new \RuntimeException( 'No credentials found for AWS.' );
		}

		if ( empty( $this->_settings ) && $_keysFile )
		{
			/** @noinspection PhpIncludeInspection */
			$this->_settings = require( $_keysFile );
		}

		$this->_endpoint = 'https://route53.amazonaws.com/2012-02-29/hostedzone';
	}

	/**
	 * @param string $zone
	 * @param string $entry
	 * @param string $value
	 * @param string $type   A | AAAA | CNAME | MX | NS | PTR | SOA | SPF | SRV | TXT
	 * @param int    $ttl
	 * @param string $comment
	 * @param bool   $delete If true, record will be deleted
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function changeResourceRecordsets( $zone, $entry, $value, $type = 'CNAME', $ttl = 3600, $comment = null, $delete = false )
	{
		if ( !isset( $this->_settings['route53.hosted_zones'][$zone] ) )
		{
			throw new \InvalidArgumentException( 'The zone "' . $zone . '" is not valid.' );
		}

		$_action = ( true === $delete ? 'DELETE' : 'CREATE' );

		$_curlOptions = array(
			CURLOPT_HTTPHEADER => $this->_signRequest(
				$this->_settings['production']['key'],
				$this->_settings['production']['secret']
			),
		);

		//	POST /zone ID/rrset
		$_url = $this->_endpoint . '/' . $this->_settings['route53.hosted_zones'][$zone]['id'] . '/rrset';

		$_xml
			= <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<ChangeResourceRecordSetsRequest xmlns="https://route53.amazonaws.com/doc/2012-02-29/">
   <ChangeBatch>
      <Comment>
      		{$comment}
      </Comment>
      <Changes>
         <Change>
            <Action>{$_action}</Action>
            <ResourceRecordSet>
               <Name>{$entry}</Name>
               <Type>{$type}</Type>
               <TTL>{$ttl}</TTL>
               <ResourceRecords>
                  <ResourceRecord>
                     <Value>{$value}</Value>
                  </ResourceRecord>
               </ResourceRecords>
            </ResourceRecordSet>
         </Change>
      </Changes>
   </ChangeBatch>
</ChangeResourceRecordSetsRequest>
XML;

		//	Now post it ...
		if ( false === ( $_response = Curl::post( $_url, $_xml, $_curlOptions ) ) )
		{
			$this->logError( 'Transport error requesting ' . $_action . ' of R53. The logs may have more info.' );

			return false;
		}

		$_result = simplexml_load_string( $_response );

		//	Ignore already existing entry errors
		if ( isset( $_result->Error ) && false === stripos( (string)$_result->Error->Message, 'already exist' ) )
		{
			$this->logError( 'Error requesting ' . $_action . ' of R53: ' . (string)$_result->Error->Message );

			return false;
		}

		if ( isset( $_result->Error ) && false !== stripos( (string)$_result->Error->Message, 'already exist' ) )
		{
			$this->logInfo( 'Duplicate DNS entry request squashed.' );

			return true;
		}

		$this->logInfo(
			'R53 request ID#' . $_result->ChangeInfo->Id . '. Status is now "' .
			$_result->ChangeInfo->Status . '"'
		);

		return true;
	}

	/**
	 * @param string $key
	 * @param string $secret
	 * @param string $when
	 *
	 * @return array
	 */
	protected function _signRequest( $key, $secret, $when = null )
	{
		$_date = $when;

		//	Get the date from the Amazon server and use it for the request
		if ( null === $_date )
		{
			Curl::get( 'https://route53.amazonaws.com/date' );
            $_headers = Curl::getLastResponseHeaders();
			$_date = Option::get( $_headers, 'Date' );
		}

		$_signature = base64_encode( hash_hmac( 'sha256', $_date, $secret, true ) );

		return array(
			'X-Amz-Date: ' . $_date,
			'X-Amzn-Authorization: AWS3-HTTPS AWSAccessKeyId=' . $key . ',Algorithm=HmacSHA256,Signature=' . $_signature,
			'Content-type: text/xml',
		);
	}
}
