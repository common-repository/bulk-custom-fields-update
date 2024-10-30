<?php

/*
Plugin Name: Zedna Bulk Custom Fields Update
Plugin URI: https://profiles.wordpress.org/zedna#content-plugins
Description: Change your post custom fields by bulk update.
Version: 1.1
Author: Radek Mezulanik
Author URI: https://www.mezulanik.cz
License: GPL2
*/


$styles = "<style>
    body { background-color: #007db8 !important; }
    #wpwrap { background-color: #007db8 !important; }
    #wpcontent { background-color: #007db8; }
    #wpfooter { background-color: #23282d; }
    body, * { font-family: 'Noto Sans', sans-serif; font-size: 14px; }
    input[type=text], input[type=submit] { border: 1px solid #CCC; padding: 3px; border-radius: 3px; }
    input[type=submit]:hover { cursor: pointer; border: 1px solid #272d32; background-color: #272d32; color: #fff; }
    #wrapper { color: #fff; }
    .form-table th { color: #fff; }
    .form-table td { color: #fff; }
    #wrapper h1 { font-size: 20px; color: #fff; }
    #wrapper h2 { color: #fff; }
    #results {  margin-top: 50px; }
    .orange { color: #ff6310; display: initial;}
    .green { color: #4fff30; display: initial;}
    .code { background-color: cadetblue; display: initial;}
    .ui-menu-item-wrapper:hover{background-color:#007db8;border:1px solid #007db8;}
    #previewfields { cursor:pointer; }
</style>";


//Add admin page and jQuery UI
add_action('admin_menu', 'zbcfu_zedna_bulk_custom_fields_update');

function zbcfu_zedna_bulk_custom_fields_update(){
    
    global $zbcfu_settings;
    
    $zbcfu_settings = add_menu_page( 'Custom fields update', 'Custom fields update', 'manage_options', 'zbcfu', 'zbcfu_zedna_settings_init', 'dashicons-admin-page', 6 );
    
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    wp_enqueue_style( 'zbcfu-zedna-jquery-css', plugins_url( '/jquery-ui.css', __FILE__ ) );
    
}

//Include JS with hooks
function zbcfu_zedna_load_scripts(){
        
        wp_enqueue_script( 'zbcfu-zedna-ajax', plugin_dir_url(__FILE__) . '/bulk-custom-fields-update.js', array('jquery-ui-autocomplete') );
        
    return false;
    
}

add_action( 'admin_enqueue_scripts', 'zbcfu_zedna_load_scripts');

//Ajax callback - get data from JS and print them back
function zbcfu_zedna_process_ajax(){
    
    /* Get data from DB */
    
    $ajaxresult = sanitize_text_field($_POST['fieldvariable']);
    
    $pos = get_unique_post_meta_values($ajaxresult);
    
    echo json_encode($pos);
    
    wp_die();
    
}

add_action( 'wp_ajax_zbcfu_zedna_get_results', 'zbcfu_zedna_process_ajax');

//Ajax callback 2 - get data from JS and print them back
function zbcfu_zedna_process_ajax2(){
    
    
    /* Get data from DB */
    
    $ajaxresult = sanitize_text_field($_POST['fieldvariable2']);
    
    $pos = get_unique_post_meta_values($ajaxresult);
    
    echo json_encode($pos);
    
    wp_die();
    
}

add_action( 'wp_ajax_zbcfu_zedna_get_results2', 'zbcfu_zedna_process_ajax2');


//Open plugin page
function zbcfu_zedna_settings_init(){
    
    global $styles;
    
    ?>
    <?php print $styles;
?>
        <div id="wrapper">
            <H1><?php print _('Bulk Custom Fields Update');?></H1>
            <form action="<?php print admin_url( 'admin.php?page=zbcfu');
?>" method="post" name="random_string_generator">
                <?php wp_nonce_field( 'zbcfu_zedna_nonce_action', 'zbcfu_zedna_nonce_field' );
?>
                <H4><?php print _('Filter posts');?></H4>
                <p><?php print _('Select custom field and value that will select posts you want update.');?></p>
                <select id="customfield" name="customfield">
                    <option value=""><?php print _('select custom field to filter posts');?></option>
                        <?php
//Get all custom fields in database
global $wpdb;

$sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta.' ORDER BY meta_key';

$fields = $wpdb->get_results($sql, ARRAY_N);

foreach($fields as $customfield){
    
    print '<option value="'.$customfield['0'].'">';
    
    print $customfield['0'];
    
    print '</option>';
    
}

?>
                </select>
                <input type="text" name="customvalue" id="customvalue" size="100%"  placeholder="<?php print _('insert value that match, separate multiple values by semicolon. e.g. value1;value2');?>">
                <div id="key"></div>
                <br><br>
                <H4><?php print _('Update posts');?></H4>
                <p><?php print _('Select custom field with updated value.');?></p>
                    <select name="customfieldupdate">
                    <option value=""><?php print _('select custom field to be updated');?></option>
                        <?php
//Get all custom fields in database
global $wpdb;

$sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta.' ORDER BY meta_key';

$fields = $wpdb->get_results($sql, ARRAY_N);

foreach($fields as $customfield){
    
    print '<option value="'.$customfield['0'].'">';
    
    print $customfield['0'];
    
    print '</option>';
    
}

?>
                </select>
                <input type="text" name="customvalueupdate" id="customvalueupdate" size="100%" placeholder="<?php print _('insert updated value');?>">
                <br>
                <br>
                <input type="submit" value="<?php print _('Bulk Update');?>" name="bulkupdate">
            </form>
<h4 id="previewfields"><?php print _('Click for preview fields');?> <span></span></h4>
<p class="previewfields"></p>
            <div id="results">
                <?php
global $wpdb;

if ( ! isset( $_POST['zbcfu_zedna_nonce_field'] ) || ! wp_verify_nonce( $_POST['zbcfu_zedna_nonce_field'], 'zbcfu_zedna_nonce_action' ) ) {
    
    print "<script>console.log('Your nonce haven´t been verified.');</script>";
    
    exit;
    
}
else {
    
    if( isset($_POST['customfield']) and $_POST['customfield'] != '' ){
        
        if( isset($_POST['customvalue']) and $_POST['customvalue'] != '' ){
            
            if( isset($_POST['customfieldupdate']) and $_POST['customfieldupdate'] != '' ){
                
                if( isset($_POST['customvalueupdate']) and $_POST['customvalueupdate'] != '' ){
                    
                    $customfield = sanitize_text_field($_POST['customfield']);
                    
                    $customvalue = explode(';', sanitize_text_field($_POST['customvalue']));
                    
                    $customfieldupdate = sanitize_text_field($_POST['customfieldupdate']);
                    
                    $customvalueupdate = sanitize_text_field($_POST['customvalueupdate']);

                    //Go through all inserted custom values
                    foreach($customvalue as $singlevaluekey => $singlevalue){

                        $result = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_value = '$singlevalue' AND meta_key = '$customfield'", ARRAY_A );

                        $results = count($result);
                        print "<b>" . $results . " " . _('posts have been updated.') . "</b>";
                        print "<br><br>";
                        
                        //Go through all touched posts
                        foreach($result as $resultid){

                            print "Post ID: ";
                            print $postid = $resultid['post_id'];
                            print "<br>";
                            print "Updated field: ";
                            print $customfieldupdate;
                            print "<br>";
                            print "New value: ";
                            print $customvalueupdate;
                            print "<br><br>";

                            update_post_meta($postid, $customfieldupdate, $customvalueupdate);
                        
                        }

                    }                    
                    
                }
                else{
                    print "<span class=''>"._('Insert updated value for custom field! You can separate multiple values by semicolon. e.g. value1;value2');
                }
                
            }
            else{
                print _('Select custom field to be updated!');
            }
            
        }
        else{
            print _('Insert value for custom field that match!');
        }
        
    }
    else{
        print _('Select custom field to filter posts!');
    }
    
    ?>
</div>
<?php
}

}

//Get all meta values according to selected meta key
function get_unique_post_meta_values( $key ) {

global $wpdb;


if( empty( $key ) )
return;


$res = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '%s'", $key) );


return $res;

}

?>
