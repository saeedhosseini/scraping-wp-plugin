<?php
use Illuminate\Database\Capsule\Manager as Capsule;
/*
Plugin Name: Scrap plugin
Description: A test plugin to demonstrate wordpress functionality
Author: Asadollahi
Version: 0.1
*/

add_action('wp_enqueue_scripts', 'load_CSS_JS');
function load_CSS_JS()
{
    wp_enqueue_style('style.css', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('script', plugin_dir_url(__FILE__) . 'js/action-script.js', array(), '1.0.0', true);
}

function load_ORM(){
    $capsule = new Capsule;
    $capsule->addConnection([
        "driver" => "mysql",
        "host" =>"127.0.0.1",
        "database" => "turkal",
        "username" => "root",
        "password" => ""
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
}

add_action('wp_ajax_my_special_action', 'implement_ajax');
add_action('wp_ajax_nopriv_my_special_action', 'implement_ajax');
add_action('wp_ajax_my_special_ajax_call', 'implement_ajax');
add_action('wp_ajax_nopriv_my_special_ajax_call', 'implement_ajax');

add_action('wp_ajax_sub_cat_action', 'LoadData');
add_action('wp_ajax_nopriv_sub_cat_action', 'LoadData');

add_action('wp_ajax_sub_cat_ajax_call', 'LoadData');
add_action('wp_ajax_nopriv_sub_cat_ajax_call', 'LoadData');

add_action('admin_menu', 'scrap_plugin_setup_menu');

//load packages
include __DIR__ . '/vendor/autoload.php';


function scrap_plugin_setup_menu()
{
    add_menu_page('Scraping Product', 'Products Scrap', 'manage_options', 'scrap-plugin', 'scrap_init');
}

function scrap_init()
{
    load_ORM();

    load_CSS_JS();
    include(__DIR__ . '/admin/views/product_table.php');
}


function LoadData()
{
    if (isset($_POST['sub_catid'])) {
        global $wpdb;
        $attributes = $wpdb->get_results("SELECT attr.id, attr.attr_name,property FROM `tk_site_category_product_attributes` AS attr JOIN `tk_site_categories` WHERE attr.site_cat_id=`tk_site_categories`.`id` AND `tk_site_categories`.`woo_category_id`=" . $_POST['sub_catid']);
        //1 باید با ای دی دسته بندی انتخاب شده از ووکامرس جایگزین شود
        $products = $wpdb->get_results("SELECT * FROM `tk_products`");
        $productAttributes = $wpdb->get_results("SELECT * FROM `tk_product_attributes`");
        $productAttributesByGroup = group_by("product_id", $productAttributes);
        if (count($attributes) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'><tr><th></th> ";
            foreach ($attributes as $attribute):
                echo '<th>' . str_replace("_", " ", $attribute->attr_name) . '</th>';
            endforeach;
            echo "<th>Action</th></tr>";
            $i = 0;
            foreach ($products as $product):
                echo "<tr><td>" . ++$i . "</td> ";
                foreach ($attributes as $attribute):
                    echo '<td>';
                    echo search_by($attribute->id, $attribute->property, $productAttributesByGroup[$product->id]);
                    echo '</td>';
                endforeach;
                echo "<td>
	   <a class='confirm' style='cursor: pointer;' data-param='" . $product->id . "'>";
                echo $product->product_woocommerce_id ? 'Update' : ' Publish';
                echo " </a>   <a href='" . $product->link . "' target='_blank'>Link</a>";
                echo "</td></tr>";
            endforeach;
            echo '</table>';
        } else echo false;
        die();
    }
}

function group_by($key, $data)
{
    $result = array();
    foreach ($data as $val) {
        if (array_key_exists($key, $val)) {
            $result[$val->$key][] = $val;
        } else {
            $result[""][] = $val;
        }
    }
    return $result;
}

function search_by($key, $property, $data)
{
    foreach ($data as $element) {
        if ($key == $element->attr_id)
            return modify_value($element->attr_value, $property);
    }
    return '-';
}

function modify_value($value, $property)
{
    switch ($property) {
        case 'price':
            return $value . ' TL';
        case 'boolean':
            return $value == 1 ? 'Yes' : 'NO'; //filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'text':
            return $value;
        case 'image':
            return '<img src="' . $value . '" class="thumbnail"/>';
    }
}

function implement_ajax()
{

    if (isset($_POST['main_catid'])) {
        $categories = get_categories('child_of=' . $_POST['main_catid'] . '&hide_empty=0&taxonomy=product_cat');
        if (count($categories) > 0) {
            foreach ($categories as $cat) {
                $option .= '<option value="' . $cat->term_id . '">';
                $option .= $cat->cat_name;
                //$option .= ' ('.$cat->category_count.')';
                $option .= '</option>';
            }

            echo '<option value="-1" selected="selected" disabled>select sub category</option>' . $option;

        } else echo false;
        die();
    } // end if
}

?>