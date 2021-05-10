<?php
 
 /**  The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linkedin.com/in/rowanevenstar/
 * @since             1.0.0
 * @package           Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:       Clip Table
 * Plugin URI:        https://github.com/mm0hgw/clip-table
 * Description:       Show table with details field linked to copy to clip-board button. Add, Update, Delete entries.
 * Version:           1.0.0
 * Author:            Rowan Evenstar
 * Author URI:        https://www.linkedin.com/in/rowanevenstar/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       clip-table
 * Domain Path:       /languages
 */

/**
 * Currently plugin version.
*/
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-clip-table-activator.php
 */
function activate_clip_table() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clip-table-activator.php';
	Clip_Table_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_clip_table' );


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-clip-table-deactivator.php
 */
function deactivate_clip_table() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clip-table-deactivator.php';
	Clip_Table_Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_clip_table' );


// Set up JS and CSS files

function enqueue_my_scripts() {
  error_log( 'CLIPTABLE PLUGIN  - Enqueue Scripts ');

  wp_enqueue_script( 'jquery' );
  // wp_enqueue_script( 'typedJS', 'https://pro.crunchify.com/typed.min.js', array('jquery') );

  //Font Awesome Style to show Action Button images
  wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.5.0/css/all.css' );
  
  //Bootstrap
  wp_enqueue_style( 'Bootstrap_css','https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css' );
  wp_enqueue_script( 'Bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js', array(), '1.0.0', true );

  //Admin Stylesheet 
  wp_enqueue_style('admin_css', plugin_dir_url(__FILE__).'admin/css/admin.css');

  //wp_enqueue_script('copy_js', plugin_dir_url(__FILE__) . 'js/copy.js'); //standard way to enqueue

  //Copy script for Copy to Clipboard Button Loads in footer - for Event listeners. Loads latest file - always runs latest version 
  wp_enqueue_script(
    'copy_js',
    plugin_dir_url(__FILE__) . 'js/copy.js',
    array('jquery'), // this script depends on jQuery
    filemtime(plugin_dir_url(__FILE__) . 'js/copy.js'), // uses file modified date 
    true // true = in Footer - load after page - e.g. for eventlistners
  );
}
add_action('admin_enqueue_scripts', 'enqueue_my_scripts'); 

//Admin Page
function crudAdminPage() {
  global $wpdb;
  $table_name ='cliptable';

  //CRUD based on isset($_GETn..

  performInsert();
  performUpdate();
  performDelete();  
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
  <!-- If Updating - Show editable row for item (for non Modal) -->
  <?php
  if (isset($_GET['upt'])) {
    updateRecordForm();
  }
  assignItemModal();
}

// Admin Page Creation
add_action('admin_menu', 'addAdminPageContent');

// Add Admin Page to Admin Menu List
function addAdminPageContent() {
  add_menu_page('Clip Table', 'Clip Table', 'manage_options', __FILE__, 'crudAdminPage', 'dashicons-wordpress');
}

function performUpdate(){
  //UPDATE Entry in DB from POST and reload page

  //Add OR Request from Modal 
  global $wpdb;
  $table_name = "cliptable";

  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $title = $_POST['uptTitle'];
    $desc = $_POST['uptDesc'];
    $details = $_POST['uptDetails'];

    error_log( 'CLIPTABLE PLUGIN  - UPDATE Entry: '.$id);

    $wpdb->query("UPDATE $table_name SET Title='$title',Description='$desc',Details='$details'  WHERE id='$id'");
    
    echo "<script>location.replace('admin.php?page=clip-table%2Fclip-table.php');</script>";
     /* Go Back to URL you came from*/
      /* This keeps all URL post criteria for filters*/
      //   if (wp_get_referer()) {
      //     wp_safe_redirect(wp_get_referer());
      // } else {
      //     wp_safe_redirect(get_home_url());
      // }
  }
}

function performInsert()
{
  global $wpdb;
  $table_name = "cliptable";
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
}

function performDelete()
{
  if (isset($_GET['del'])) {

    global $wpdb;
    $table_name = "cliptable";
  
    $del_id = $_GET['del'];
  
    error_log('CLIPTABLE PLUGIN  - Delete Entry: '+$del_id) ;
  
    $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");
  
    echo "<script>location.replace('admin.php?page=clip-table%2Fclip-table.php');</script>";
  }
}

function updateRecordForm(){

  //Show form based on POST/GET inline on page

  //Get details for item to update
  $id = $_GET['upt'];
  $row = getItemByID($id);
  ?>
  <!-- Display table of row to edit -->
  <br/><br/>
  <h2> Update Record</h2>
  <table class='wp-list-table widefat striped'>
    <? 
      tableHeaders(); 
      updateForm($row);
    ?>
  </table>
  <?
}

function getItemByID($id)
{
  global $wpdb;
  $table_name = "cliptable";
  $id = $id;
  console.log("getItemByID id: "+$id );

  $results = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");

  foreach($results as $row) {
    $title = $row->Title;
    $descr = $row->Description;
    $details = $row->Details;
  }
  return $row;
}

function updateForm($row){
  $row = $row;
  echo "
    <tbody>
      <form action='' method='post'>
        <tr>
          <td>$row->id<input type='hidden' id='uptid' name='uptid' value='$row->id'></td><!--uneditable-->
          <td><input type='text' id='uptTitle' name='uptTitle' value='$row->Title'></td>
          <td><input type='text' id='uptDesc' name='uptDesc' value='$row->Description'></td>
          <td><input type='text' id='uptDetails' name='uptDetails' value='$row->Details'></td>
          <td><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=clip-table%2Fclip-table.php'><button type='button'>CANCEL</button></a></td>
        </tr>
      </form>
    </tbody>
 ";
}

function assignItemModal()
{
    echo '
  <div id="modal" class="modal" role="dialog" aria-labelledby="Edit Window" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-header">
      <h1 class="modal-title"></h1>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
      <div id="modal_target" class="modal-content">

      </div>
    </div>
  </div>';
    itemModalListen();
}

function itemModalListen()
{
    ?>
	<script>

jQuery(document).ready(function($) {
  console.log("itemModelListner")


    //Show edit Modal Window
    var $edit_item = jQuery('.edit-button');
    var $modal = $('#modal');
    var $modal_target = $('#modal_target');
    var $modal_title = $('.modal-title');

    $edit_item.click(function() {
      var id = $(this).data('id');
      var title = "Edit Form";

      $.ajax({
        url: ajaxurl,
        data: {
          'action' : 'fetch_edit_item_modal_content',
          'id' : id,
          },
        success:function(data) {
          $modal_target.html(data);
          $modal_title.text(title);

          $modal.modal('show');
        }
      });
    });
  });

  //submit the form
  function edit_activity_form_submit() {
      document.getElementById("edit_form").submit();
    }

  </script>
  <?php
}

function fetch_edit_item_modal_content()
{
  if (isset($_REQUEST)) {
    global $wpdb;
    $id = $_REQUEST['id'];
    $result = getItemByID($id);
  ?>
    <div class="modal-body">
      <div class="bootstrap-iso">
        <form id="edit_form" action="" method="post"><!--TODO HERE - tie in action for submit-->
          <input type="hidden" name="itemID" id="itemID" value="<?php echo $id; ?>">
          <?
          updateForm($result);
          ?>
          <div>
            <!--TODO Buttons already in updateForm() but need styled like these -->
            <button type="button" class="btn btn-lg btn-warning" data-dismiss="modal" style="width:45%;margin-top:1em;margin-left:0.5em">
            Cancel</button>
            <!-- <a href='admin.php?page=clip-table%2Fclip-table.php&upt=$row->id'> -->
            <button class="btn btn-lg btn-success" name="submit" type="submit" style="width:49%;margin-top:1em" onclick="edit_activity_form_submit()">
            Submit
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php
    //die();
  }
}
    
add_action('wp_ajax_fetch_edit_item_modal_content', 'fetch_edit_item_modal_content');
add_action('wp_ajax_nopriv_fetch_edit_item_modal_content', 'fetch_edit_item_modal_content');

function tableHeaders(){
  ?>
  <thead>
      <tr>
        <!-- <th class='ct_column-id'>ID</th> -->
        <th class='ct_column-title'>Title</th>
        <th class='ct_column-desc'>Description </th>
        <th class='ct_column-copy'>Details</th>
        <th>Actions</th>
      </tr>
    </thead>
  <?
}

function insertNewRecordForm(){
?>
  <!-- Insert New Record - Table Row -->
  <form action="" method="post">
    <tr>
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
          
  foreach ($result as $row) {
    echo "
      <tr>
      <!-- ID in TD for item to be put in clipboard -->
        <td>$row->id  -  $row->Title</td>
        <td>$row->Description</td>
        <td id='copyItem-$row->id'>$row->Details</td> 

        <!--Button Actions -->

        <td>
          <!-- UPDATE / Edit calls form -->

          <!-- Update using Post page refresh -->
          <a href='admin.php?page=clip-table%2Fclip-table.php&upt=$row->id'>
          <button class='edit-button btn-sm' data-id='$row->id'><i class='fas fa-pencil-alt'></i></button></a> 
            
          <!-- Update using Modal -->
          <button class='edit-button btn-sm' data-id='$row->id' data-toggle='modal' data-target='#model'><i class='fas fa-pencil-alt'></i></button>
          
          <!-- DELETE -->
          <a href='admin.php?page=clip-table%2Fclip-table.php&del=$row->id'>
            <button class='delete-button btn-sm' data-id='$row->id' id='$row->id'><i class='fas fa-trash-alt'></i></button></a>

          <!-- COPY field to clipboard - listner done by copy.js-->
          <button class='copyBtn btn-sm'data-id='$row->id' id='$row->id'><i class='far fa-clipboard'></i></button>

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
          
  foreach ($result as $row) {
    echo "
      <tr>
        <!--<td width='5%'>$row->id</td>-->
        <td width='25%'>$row->Title</td>
        <td width='25%'>$row->Description</td>
        <td width='25%'id='copyItem-$row->id'>$row->Details</td>
        <td width='15%'>
          <!-- COPY field to clipboard-->
          <button class='copyBtn btn-lg'data-id='$row->id' id='$row->id'><i class='far fa-clipboard'></i></button>
        </td>
      </tr>
    ";
  }
}
function showAllRecordItems($table_name)
{
  //TABLE WITH ONLY COPY BUTTON
  global $wpdb;
  $table_name = $table_name;
  $result = $wpdb->get_results("SELECT * FROM $table_name");
          
  foreach ($result as $row) {
    echo "
      <p>$row->Title</p>
      <p>$row->Description</p>
        <p id='copyItem-$row->id'>$row->Details</p>
          <button class='copyBtn btn-lg'data-id='$row->id' id='$row->id'><i class='far fa-clipboard'></i></button>
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
        <table class='table widefat table-striped table-dark table-hover' id='mainTable'>";

          tableHeaders();
          echo "<tbody>";
          showAllRecords($table_name);
          showAllRecordItems($table_name);

    echo "</tbody></table></div>";
 			
  $final_table = ob_get_clean(); //gets all echo values since start

  enqueue_my_scripts();
 	
 	return $final_table ;
}

add_shortcode('cliptable-show-table', 'cliptable_show_table');



