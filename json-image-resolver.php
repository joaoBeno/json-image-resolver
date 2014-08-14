<?php
// Access control
// TODO Put it where it belongs!
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Allow-Origin, Access-Control-Allow-Methods');

/**
 * Plugin Name: JSON image resolver
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Get one or more images from wordpress media library.
 * Version: 0.0.1
 * Author: João Beno Schreiner Junior
 * Author URI: http://joao.beno.net.br
 * License: Open Source
 */

class Pugs_API_Endpoint{
    
    /** Hook WordPress
     * @return void
     */
    public function __construct(){
        add_filter('query_vars', array($this, 'add_query_vars'), 0);
        add_action('parse_request', array($this, 'receive_requests'), 0);
        add_action('init', array($this, 'add_endpoint'), 0);
    }
    /** Add public query vars
     * @param array $vars List of current public query vars
     * @return array $vars
     */
    public function add_query_vars($vars){
        $vars[] = 'jir';
        $vars[] = 'image';
        $vars[] = 'images';
        return $vars;
    }
    /** Add API Endpoint
     * @return void
     */
    public function add_endpoint(){
        add_rewrite_rule('^api/pugs/?([0-9]+)?/?','index.php?jir=1&images=$matches[1]','top');
        add_rewrite_rule('^api/pugs/?([0-9]+)?/?','index.php?jir=1&image=$matches[1]','top');
    }

    /** Receive Requests
     * If $_GET['jir'] is set, we reply
     * @return die if API request
     */
    public function receive_requests(){
        global $wp;
        if(isset($wp->query_vars['jir'])){
            $this->handle_request();
            exit;
        }
    }
    /** Handle Requests
     * @return void
     */
    protected function handle_request(){
        global $wp;

        $query_vars = $wp->query_vars;

        $imgs = $query_vars['images'];
        $img = $query_vars['image'];

        //If you want everything
        if ($imgs) {
            $imgs = $this->display_images_from_media_library();

            if($imgs)
                $this->send_response('200 OK', $imgs);
            else
                $this->send_response('Something went wrong with the all images function');
        }

        //If you want just THE one...
        if ($img) {
            $img = $this->get_image_from_media_library($img);

            if($img)
                $this->send_response('200 OK', $img);
            else
                $this->send_response('Something went wrong with the single image function');
        }
    }
    /** Response Handler
     * This sends a JSON response to the browser
     */
    protected function send_response($msg, $pugs = ''){
        $response['message'] = $msg;
        if($pugs)
            $response['images'] = $pugs;
        header('content-type: application/json; charset=utf-8');
        echo json_encode($response)."\n";
        exit;
    }

    protected function get_images_from_media_library() {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' =>'image',
            'post_status' => 'inherit',
            'posts_per_page' => 5,
            'orderby' => 'rand'
        );
        $query_images = new WP_Query( $args );
        $images = array();
        foreach ( $query_images->posts as $image) {
            $l_image = array();

            $l_image[]=$image->ID;
            $l_image[]=$image->guid;

            $images[]= $l_image;
        }
        return $images;
    }

    protected function get_image_from_media_library($image_id) {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' =>'image',
            'post_status' => 'inherit',
            'posts_per_page' => 5,
            'orderby' => 'rand'
        );
        $query_images = new WP_Query( $args );
        $image_return = "";
        foreach ( $query_images->posts as $image) {
            $image_id = intval($image->ID);
            if($image_id == intval($image_id)) {
                $image_return = $image->guid;
            } else {
                $image_return = "Image not found!".intval($image_id);
            }
        }
        return $image_return;
    }

    protected function display_images_from_media_library() {

        $imgs = $this->get_images_from_media_library();
        $html = array();

        foreach($imgs as $img) {
            $html[]= $img;
        }

        return $html;
    }
}
new Pugs_API_Endpoint();