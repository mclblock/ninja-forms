<?php if ( ! defined( 'ABSPATH' ) ) exit;

abstract class NF_Webhooks_Webhook
{
    protected $action;

    public function init()
    {
        if( ! isset( $_POST[ 'nf_webhook' ] ) ) return;
        if( $this->action != $_POST[ 'nf_webhook' ] ) return;

        $hash = $_POST[ 'nf_webhook_hash' ];
        $payload = $_POST[ 'nf_webhook_payload' ];

        $client_id = get_site_option( 'ninja_forms_client_id' );
        $client_secret = get_site_option( 'ninja_forms_client_secret' );

        $this->validate( $hash, $payload, $client_id, $client_secret );
        $this->process( json_decode( $payload ) );
    }

    public function validate( $hash, $payload, $client_id, $client_secret )
    {
        if( $hash == sha1( $payload . $client_id . $client_secret  ) ) return;
        $this->respond( array( 'error' => 'Forbidden' ), 403 );
    }

    abstract public function process( $payload );

    protected function respond( $data, $status = 200 )
    {
        status_header( $status );
        echo json_encode( $data );
        exit();
    }
}
