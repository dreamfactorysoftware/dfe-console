<?php namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Common\Config\ClusterManifest;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Lumberjack;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Services\Commands\ManifestJob;
use Illuminate\Http\Response;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Processes queued environment manifest generation requests
 */
class ManifestHandler
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, Lumberjack;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle a request
     *
     * @param ManifestJob $command
     *
     *
     * @return bool
     * @throws \Exception
     */
    public function handle( ManifestJob $command )
    {
        \Log::debug( '[dfe:manifest] begin' );

        if ( $command->showManifest() )
        {
            $_manifest = ClusterManifest::createFromFile( base_path() . DIRECTORY_SEPARATOR . ConsoleDefaults::CLUSTER_MANIFEST_FILE );
            $_result = !$_manifest->existed() ? ErrorPacket::create() : SuccessPacket::make( $_manifest->toArray() );

            //  And then show it...
            if ( $_manifest->existed() )
            {
                \Log::debug( '  * Manifest found: ' . print_r( $_manifest->all(), true ) );
            }
            else
            {
                \Log::info( '  * No manifest file found. Nothing to show.' );
            }
        }
        else
        {
            try
            {
                $_key = $command->noKeys() ? false : AppKey::createKey( $command->getOwnerId(), $command->getOwnerType() );

                if ( $_key )
                {
                    $command->getOutput()->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE &&
                    $command->getOutput()->writeln( ' - generated client-id and secret: ' . $_key->client_id );
                }

                if ( $command->createManifest() )
                {
                    //  Create a new manifest...
                    $_manifest = ClusterManifest::make(
                        base_path(),
                        [
                            'cluster-id'       => config( 'dfe.provisioning.default-cluster-id' ),
                            'default-domain'   => config( 'dfe.provisioning.default-domain' ),
                            'signature-method' => config( 'dfe.signature-method' ),
                            'storage-root'     => config( 'dfe.provisioning.storage-root' ),
                            'console-api-url'  => config( 'dfe.security.console-api-url' ),
                            'console-api-key'  => config( 'dfe.security.console-api-key' ),
                            'client-id'        => !$_key ? null : $_key->client_id,
                            'client-secret'    => !$_key ? null : $_key->client_secret,
                        ]
                    );

                    $_result = SuccessPacket::make( $_manifest->toArray(), Response::HTTP_CREATED );
                }
            }
            catch
            ( \Exception $_ex )
            {
                $_result = ErrorPacket::create( Response::HTTP_BAD_REQUEST );
            }
        }

        $command->setResult( $_result );

        \Log::debug( '[dfe:manifest] end' );

        return $command;
    }
}
