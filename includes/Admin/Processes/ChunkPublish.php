<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Abstracts_Batch_Process
 */
class NF_Admin_Processes_ChunkPublish extends NF_Abstracts_BatchProcess
{
//	header( 'Content-Type: application/json' );
    private $data;
    private $form_id;
    private $response = array(
    	'last_request' => 'failure',
        'batch_complete' => false,
    );



    /**
     * Constructor
     */
    public function __construct( $data = array() )
    {
        //Bail if we aren't in the admin.
        if ( ! is_admin() )
            return false;
        // Record our data if we have any.
        $this->data = $data[ 'data' ];
        $this->form_id = $this->data[ 'form_id' ];
        // Run process.
        $this->process();
    }


    /**
     * Function to loop over the batch.
     * 
     * @return JSON
     *  Str last_response = success/failure
     *  Bool batch_complete = true/false
     *  Int requesting = x
     */
    public function process()
    {
        // Fetch our option to see what step we're on.
        $batch = get_option( 'nf_chunk_publish_' . $this->form_id );
        // If we don't have an option to see what step we're on...
        if ( ! $batch ) {
            // Run startup.
            $this->startup();
            // Fetch our option now that it's created.
            $batch = get_option( 'nf_chunk_publish_' . $this->form_id );
        }
        $batch = explode( ',', $batch );
        // If we already have a chunk for this step...
        if ( get_option( 'nf_form_' . $this->form_id . '_' . $batch[ 0 ] ) ) {
            // Update it.
            update_option( 'nf_form_' . $this->form_id . '_' . $batch[ 0 ], $this->data[ 'chunk' ] );
        } // Otherwise... (No chunk was found.)
        else {
            // Add it.
            add_option( 'nf_form_' . $this->form_id . '_' . $batch[ 0 ], $this->data[ 'chunk' ], '', 'no' );
        }
        // Increment our step.
        $batch[ 0 ]++;
        // If this was our last step...
        if ( $batch[ 0 ] == $batch[ 1 ] ) {
            // Run cleanup.
            $this->cleanup();
        } // Otherwise... (We have more steps.)
        else {
            // Update our step option.
            update_option( 'nf_chunk_publish_' . $this->form_id, implode( ',', $batch ) );
            // Request our next chunk.
            $this->response[ 'requesting' ] = $batch[ 0 ];
        }
        $this->response[ 'last_request' ] = 'success';
        echo wp_json_encode( $this->response );
        wp_die();
    }


    /**
     * Function to run any setup steps necessary to begin processing.
     */
    public function startup()
    {
        $value = '0,' . $this->data[ 'chunk_total' ];
        // Write our option to manage the process.
        add_option( 'nf_chunk_publish_' . $this->form_id, $value, '', 'no' );
        // Process the first item.
        $this->process();
    }


    /**
     * Function to cleanup any lingering temporary elements of a batch process after completion.
     */
    public function cleanup()
    {
        // Update the chunked cache option.
        $build = array();
        $batch = get_option( 'nf_chunk_publish_' . $this->form_id );
        $batch = explode( ',', $batch );
        // Add all of our chunks onto an array.
        for ( $i = 0; $i < $batch[ 1 ]; $i++ ) {
            $build[] = 'nf_form_' . $this->form_id . '_' . $i;
        }
        // Convert them to a string.
        $build = implode( ',', $build );
        // If we already have a chunked option...
        if ( get_option( 'nf_form_' . $this->form_id . '_chunks' ) ) {
            // Update it.
            update_option( 'nf_form_' . $this->form_id . '_chunks', $build );
        } // Otherwise... (If we don't already have one.)
        else {
            // Create it.
            add_option( 'nf_form_' . $this->form_id, $build, '', 'no' );
        }
        // Remove our option to manage the process.
        delete_option( 'nf_chunk_publish_' . $this->form_id );
        $this->response[ 'batch_complete' ] = true;
    }

}