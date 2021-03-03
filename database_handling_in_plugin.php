<?php

/**
 * Plugin Name:       Database Managing
 * Plugin URI:        https://sakibmd.xyz/
 * Description:       How to manage database, add/remove/update tables
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sakib Mohammed
 * Author URI:        https://sakibmd.xyz/
 * License:           GPL v2 or later
 * License URI:
 * Text Domain:       database_handling_in_plugin
 * Domain Path:       /languages
 */

// Register Custom Post Type Book
define('DBDEMO_DB_VERSION', '1.3');
function dbdemo_init()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "persons";
    $sql = "CREATE TABLE {$table_name} (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(250),
        email VARCHAR(250),
        PRIMARY KEY  (id)

    );";
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta($sql);

    add_option('dbdemo_db_version', DBDEMO_DB_VERSION);
    if (get_option('dbdemo_db_version') != DBDEMO_DB_VERSION) {
        $sql = "CREATE TABLE {$table_name} (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(250),
            email VARCHAR(250),
            age INT
            PRIMARY KEY  (id)

        );";
        dbDelta($sql);

        update_option('dbdemo_db_version', DBDEMO_DB_VERSION);
    }
}

register_activation_hook(__FILE__, 'dbdemo_init');

function dbdemo_drop_column()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    if (get_option("dbdemo_db_version") != DBDEMO_DB_VERSION) {
        $query = "ALTER TABLE {$table_name} DROP COLUMN age";
        $wpdb->query($query);
    }
    update_option("dbdemo_db_version", DBDEMO_DB_VERSION);
}

add_action("plugins_loaded", "dbdemo_drop_column"); //used for drop a column

function dbdemo_insert()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $wpdb->insert(
        $table_name,
        array(
            'name' => 'Sakib Md',
            'email' => 'sakibmd.cse@gmail.com',
        )
    );
}

register_activation_hook(__FILE__, 'dbdemo_insert'); //used for insert a data

function dbdemo_flush_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $query = "TRUNCATE TABLE {$table_name}";
    $wpdb->query($query);
}

register_deactivation_hook(__FILE__, "dbdemo_flush_data"); //used for remove data after deactivation

add_action('admin_menu', function () {
    $title = __('DB Demo', 'database_handling_in_plugin');
    add_menu_page($title, $title, 'manage_options', 'dbdemo', 'dbdemo_display_data', 'dashicons-editor-table');
});

function dbdemo_display_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    echo "<h2>DB Demo</h2>";
    $id = $_GET['pid'] ?? 0;
    $id = sanitize_key($id);
    if ($id) {
        $result = $wpdb->get_row("SELECT * from {$table_name} WHERE id='{$id}'");
        if ($result) {
            echo "Name: {$result->name} <br/>";
            echo "Email: {$result->email} <br/>";
        }

    }
    ?>
    <form action="<?php echo admin_url('admin-post.php')?>" method="POST">
    <?php wp_nonce_field('dbdemo', 'nonce')?>
    <input type="hidden" name="action" value="dbdemo_add_new">
       Name: <input type="text" name="name" value="<?php if($id) echo $result->name ?>"><br/>
       Email: <input type="email" name="email" value="<?php if($id) echo $result->email ?>"><br/>

<?php 
if($id){
    echo '<input type="hidden" name="id" value="'.$id.'" >';
    submit_button('Update Record');
}else{
    submit_button('Add Record');
}

?>
    
   </form>
   <?php
// if (isset($_POST['submit'])) {
    //     $nonce = sanitize_text_field($_POST['nonce']);
    //     if (wp_verify_nonce($nonce, 'dbdemo')) {
    //         $name = sanitize_text_field($_POST['name']);
    //         $email = sanitize_text_field($_POST['email']);
    //         $wpdb->insert($table_name, [
    //             'name' => $name,
    //             'email' => $email,
    //         ]);
    //     } else {
    //         echo "Please Try Again";
    //     }
    // }

   

}

add_action('admin_post_dbdemo_add_new', function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $nonce = sanitize_text_field($_POST['nonce']);

    if (wp_verify_nonce($nonce, 'dbdemo')) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_text_field($_POST['email']);
        $id = sanitize_text_field($_POST['id']);
        if($id){
            $wpdb->update(
                $table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                ),
                ['id' => $id], //where reference
            );
            wp_redirect(admin_url('admin.php?page=dbdemo&pid='.$id));
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                )
            );
            wp_redirect(admin_url('admin.php?page=dbdemo'));
        }
    }
    


    add_action('admin_enqueue_scripts', function($hook){
        if("toplevel_menu_dbdemo" == $hook){
            wp_enqueue_style('dbdemo-style', plugin_dir_url(__FILE__)."/assets/admin/css/main.css");
        }
    });

    
        
       
        
    
    
});
