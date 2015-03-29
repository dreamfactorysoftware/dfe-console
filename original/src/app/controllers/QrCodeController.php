<?php
use Kisma\Core\Enums\OutputFormat;

/**
 * QrCodeController.php
 */
Yii::import( 'application.components.QrCodeGenerator' );
/**
 * QrCodeController
 */
class QrCodeController extends BaseServiceController
{
	//*************************************************************************
	//* Public Actions
	//*************************************************************************

	/**
	 * Initializes the controller. By the time we get to this method, the header
	 * and request have been scraped for parameters defined in $headerParameters
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();

		//	Set our access rules..
		$this->addUserActions(
			self::Any,
			array(
				 //	Creates a QR code and returns file name
				 'create',
				 //	Creates a QR code and dumps to stream
				 'show',
				 //	Displays a bookmarklet for generating QR codes
				 'bookmarklet',
			)
		);
	}

	/**
	 * @param string $url
	 * @param int    $size
	 * @param string $encoding
	 * @param int    $margin
	 *
	 * @return string
	 */
	public function getCreate( $url, $size = 150, $encoding = 'L', $margin = 0 )
	{
		if ( false !== ( $_result = QrCodeGenerator::create( urldecode( $url ), $size, $encoding, $margin ) ) )
		{
			return $this->_createResponse( $_result );
		}

		//	Return results
		return $this->_createErrorResponse( 'Unable to create QR code.' );
	}

	/**
	 * @param string $url
	 * @param int    $size
	 * @param string $encoding
	 * @param int    $margin
	 *
	 * @return null|string
	 */
	public function getShow( $url, $size = 150, $encoding = 'L', $margin = 0 )
	{
		if ( false !== ( $_result = \QrCodeGenerator::create( urldecode( $url ), $size, $encoding, $margin ) ) )
		{
			$this->setOutputFormat( OutputFormat::Raw );

			header( 'content-type: image/png' );
			readfile( $_result );
			@unlink( $_result );

			return null;
		}

		//	Return results
		return $this->_createErrorResponse( 'Unable to create QR code.' );
	}

	/**
	 * Shows a page with a bookmarklet for generating QR codes
	 *
	 * @return void
	 */
	public function getBookmarklet()
	{
		$this->setOutputFormat( PS::OF_RAW );
		header( 'Content-type: text/html' );

		echo <<<HTML
		<html><body>
		<h1>Easy QR Generator</h1>
		<p>This bookmarklet will create a QR code for the current URL in your browser's address bar.</p>
		<p>Simply drag this to your bookmarks toolbar and when you want to create a QR code, just click it.</p>
		<p>
		<a href="javascript:(function(){window.location='http://chart.apis.google.com/chart?cht=qr&chl='+encodeURIComponent(window.location)+'&chs=150x150'})();">QR Code From Url</a>
		</p>
		</body></html>
HTML;

		return null;
	}
}