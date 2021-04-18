<?php
 
/*
 
Plugin Name: Clip-Table
 
Plugin URI: 
 
Description: Plugin to show custom table with a field being added to a copy to clipboard button. 
Admin Page allows create, delete and update and copy. Short Code just shows table with copy button.
 
Version: 1.0
 
Author: Rowan Evenstar
 
Author URI: https://www.linkedin.com/in/rowanevenstar/
 
License: GPLv2 or later
 
Text Domain: cliptable
 
*/


// Set up JS and CSS files

function enqueue_my_scripts() {
  error_log( 'CLIPTABLE PLUGIN  - Enqueue Scripts ');

  $src = plugin_dir_url(__FILE__) . 'js/copy.js'; //TODO replace in query so not hard coded below

  error_log( 'CLIPTABLE PLUGIN  - Enqueue gScripts '.$src);
  wp_enqueue_script( 'jquery' );

  //wp_enqueue_script('copy_js', plugin_dir_url(__FILE__) . 'js/copy.js'); //standard way to enqueue

  //Loads in footer - for Event listeners
  //Loads latest file - always runs latest version 
  wp_enqueue_script(
    'copy_js',
    plugin_dir_url(__FILE__) . 'js/copy.js',
    array('jquery'), // this script depends on jQuery
    filemtime(plugin_dir_url(__FILE__) . 'js/copy.js'), // uses file modified date 
    true // true = in Footer - load after page - e.g. for eventlistners
  );
}
add_action('admin_enqueue_scripts', 'enqueue_my_scripts');  //wp_enqueue_scripts for front end


// Create Table in Database on Plugin Activation

register_activation_hook( __FILE__, 'crudOperationsTable');

function crudOperationsTable() {
  
  //CREATE TABLE ON ACTIVATION
    global $wpdb; //The wordpress Database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //makes dbDelta work


    //Table Settings - TABLE NAME
    $charset_collate = $wpdb->get_charset_collate();
    //$table_name = $wpdb->prefix . 'cliptable'; //5a_ prefix failed. Not using prefix. //BUG TODO
    $table_name ='cliptable';

    $sql = "CREATE TABLE ". $table_name." (
        id int(11) NOT NULL AUTO_INCREMENT,
        Title varchar(255)  NOT NULL,
        Description varchar(255)  NOT NULL,
        Details varchar(255)  NULL,
        PRIMARY KEY  (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
        ";

    error_log( 'CLIPTABLE PLUGIN - Creation SQL ' );
    error_log( print_r( $sql, true ) );
  
    //Run Create Statement, If Table Doesn't exist 
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
     {
      
      dbDelta($sql);
      error_log( 'CLIPTABLE PLUGIN  - Database Created ' );
         
     }
}

// Admin Page Creation
add_action('admin_menu', 'addAdminPageContent');

// Add Admin Page to Admin Menu List
function addAdminPageContent() {
  add_menu_page('Clip Table', 'Clip Table', 'manage_options', __FILE__, 'crudAdminPage', 'dashicons-wordpress');
}

//Admin Page - Note TABLE NAME
function crudAdminPage() {
  global $wpdb;
  $table_name ='cliptable';

  //INSERT New Entry

  if (isset($_POST['newsubmit'])) {
    $title = $_POST['newtitle'];
    $descr = $_POST['newdesc'];
    $details = $_POST['newdetails'];

    error_log( 'CLIPTABLE PLUGIN  - Insert New Entry ' );

    //SQL- no fallback or confirmation
    $wpdb->query("INSERT INTO $table_name(Title,Description,Details) VALUES('$title','$descr','$details')");
    
    //refresh page - TODO Make Global string
    echo "<script>location.replace('admin.php?page=clip-table%2Fclip-table.php');</script>";
  }

  //UPDATE Entry 

  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $title = $_POST['uptTitle'];
    $desc = $_POST['uptDesc'];
    $details = $_POST['uptDetails'];

    error_log( 'CLIPTABLE PLUGIN  - UPDATE Entry: '.$id);

    $wpdb->query("UPDATE $table_name SET Title='$title',Description='$desc',Details='$details'  WHERE id='$id'");
    
    echo "<script>location.replace('admin.php?page=clip-table%2Fclip-table.php');</script>";
  }

  //DELETE Entry

  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];

    error_log('CLIPTABLE PLUGIN  - Delete Entry: '+$del_id) ;

    $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");

    echo "<script>location.replace('admin.php?page=clip-table%2Fclip-table.php');</script>";
  }
  ?>

  <!--- Main Table -->
  <div class="wrap">
    <h2>Clip Table</h2>
    <table class="wp-list-table widefat striped" id='mainTable'>
      <? tableHeaders(); ?>
      <tbody>
        <!-- Insert New Record - Table Row -->
        <? insertNewRecordForm();?>

        <!-- Show Database Records with admin controls-->
        <? showAllRecordsAdmin($table_name);?>
      </tbody>
    </table>
  </div>
  <!-- UPDATE TABLE -->
  <!-- If Updating - Show editable row for item -->
  <?php
  if (isset($_GET['upt'])) {
    updateRecord($table_name);
  }
}

function updateRecord($table_name){
  $table_name = $tablename;

  //Get details for item to update
  $upt_id = $_GET['upt'];
    $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$upt_id'");
    foreach($result as $print) {
      $title = $print->Title;
      $descr = $print->Description;
      $details = $print->Details;
      error_log( 'CLIPTABLE PLUGIN - Update New Entry '. $title.' '.$descr.' '.$details);
    }
   ?>
    <br/><br/>

    <!-- Display row to edit -->
    <h2> Update Record</h2>
    <table class='wp-list-table widefat striped'>
      tableHeaders();
      <tbody>
        <form action='' method='post'>
          <tr>
            <td width=5%'>$print->id <input type='hidden' id='uptid' name='uptid' value='$print->id'></td>
            <td width='25%'><input type='text' id='uptTitle' name='uptTitle' value='$print->Title'></td>
            <td width='25%'><input type='text' id='uptDesc' name='uptDesc' value='$print->Description'></td>
            <td width='25%'><input type='text' id='uptDetails' name='uptDetails' value='$print->Details'></td>
            <td width='15%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=clip-table%2Fclip-table.php'><button type='button'>CANCEL</button></a></td>
          </tr>
        </form>
      </tbody>
    </table>
    <?
}
function tableHeaders(){
  ?>
  <thead>
      <tr>
        <th width='5%'>ID</th>
        <th width='25%'>Title</th>
        <th width='25%'>Description </th>
        <th width='25%'>Details</th>
        <th width='15%'>Actions</th>
      </tr>
    </thead>
  <?
}

function insertNewRecordForm(){
?>
  <!-- Insert New Record - Table Row -->
  <form action="" method="post">
    <tr>
      <td><input type="text" value="" disabled></td>
      <td><input type="text" id="newtitle" name="newtitle"></td>
      <td><input type="text" id="newdesc" name="newdesc"></td>
      <td><input type="text" id="newdetails" name="newdetails"></td>
      <td><button id="newsubmit" name="newsubmit" type="submit">Add</button></td>
    </tr>
  </form>
  <?
}
function showAllRecordsAdmin($table_name)
{
  global $wpdb;
  $table_name = $table_name;
  $result = $wpdb->get_results("SELECT * FROM $table_name");
          
  foreach ($result as $print) {
    echo "
      <tr>
        <td width='5%'>$print->id</td>
        <td width='25%'>$print->Title</td>
        <td width='25%'>$print->Description</td>
        <td width='25%'id='copyItem-$print->id'>$print->Details</td>

        <!--Button Actions -->

        <td width='15%'>
          <!-- UPDATE / Edit calls form -->
          <a href='admin.php?page=clip-table%2Fclip-table.php&upt=$print->id'>
            <button class='edit-button btn-lg' data-id='$print->id'><i class='fas fa-pencil-alt'></i></button></a> 
          
          <!-- DELETE -->
          <a href='admin.php?page=clip-table%2Fclip-table.php&del=$print->id'>
            <button class='delete-button btn-lg' data-id='$print->id' id='$print->id'><i class='fas fa-trash-alt'></i></button></a>

          <!-- COPY field to clipboard - listner done by copy.js-->
          <button class='copyBtn btn-lg'data-id='$print->id' id='$print->id'><i class='far fa-clipboard'></i></button>

        </td>
      </tr>
    ";
  }
}

function showAllRecords($table_name)
{
  //TABLE WITH ONLY COPY BUTTON
  global $wpdb;
  $table_name = $table_name;
  $result = $wpdb->get_results("SELECT * FROM $table_name");
          
  foreach ($result as $print) {
    echo "
      <tr>
        <td width='5%'>$print->id</td>
        <td width='25%'>$print->Title</td>
        <td width='25%'>$print->Description</td>
        <td width='25%'id='copyItem-$print->id'>$print->Details</td>
        <td width='15%'>
          <!-- COPY field to clipboard-->
          <button class='copyBtn btn-lg'data-id='$print->id' id='$print->id'><i class='far fa-clipboard'></i></button>
        </td>
      </tr>
    ";
  }
}

//Short Code 
function cliptable_show_table($atts) {

  //Output full table 
  global $wpdb;
  $table_name = "cliptable";
  $result = $wpdb->get_results("SELECT * FROM $table_name");
 
  ob_start(); //all echo statements put in array 

    echo "
      <div class='wrap'>
        <table class='p-list-table widefat striped' id='mainTable'>";

          tableHeaders();
          echo "<tbody>";
          showAllRecords($table_name);

    echo "</tbody></table></div>";
 			
 	  $final_table = ob_get_clean(); //gets all echo values since start

   //load copy script to activate copy button listners
   wp_enqueue_script(
    'copy_js',
    plugin_dir_url(__FILE__) . 'js/copy.js',
    array('jquery'), // this script depends on jQuery
    filemtime(plugin_dir_url(__FILE__) . 'js/copy.js'), // uses file modified date 
    true // true = in Footer - load after page - e.g. for eventlistners
  );
 	
 	return $final_table ;
}

add_shortcode('cliptable-show-table', 'cliptable_show_table');



