<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

class StatsFile extends WP_List_Table {

  function __construct() {
    parent::__construct(array(
        'singular' => 'wp_list_text_link', //Singular label
        'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
        'ajax' => false //We won't support Ajax for this table
    ));
  }

  function extra_tablenav($which) {
    if ($which == "top") {
      //The code that goes before the table is here
      echo "Se toma en cuenta luego del caracter underscore para llenar el filtro de año. Ejemplo: <nombre>_2019.csv";
    }
    /*if ($which == "bottom") {
      //The code that goes after the table is there
      echo"Hi, I'm after the table";
    }*/
  }

  function get_columns() {
    $columns['col_link_name'] = __('Name');
    $columns['col_link_date'] = __('Date');
    $columns['col_link_action'] = __('Action');
    return $columns;
  }
  
  function column_default( $item, $column_name ) {
    switch( $column_name ) { 
      case 'col_link_name':
      case 'col_link_date':
      case 'col_link_action':
        return isset($item[ $column_name ]) ? $item[ $column_name ] : '';
      default:
        return print_r( $item, true );
    }
  }

  function prepare_items() {
    global $wpdb, $_wp_column_headers;
    $screen = get_current_screen();
    
    $columns = $this -> get_columns(); 
    $hidden = array(); 
    $sortable = array(); 
    $this->_column_headers = array( $columns ,$hidden , $sortable ); 
    
    $php_files = glob(DS8STATS_PLUGIN_DIR . '_tablas/*.csv');
    $items = array();
    
    foreach($php_files as $file) {
      //$file_info = pathinfo($file);
      //$file_extension = $file_info['extension'];
      
      $str = basename($file);
      preg_match_all('/\d{4}\+?/', $str, $matches);

      if ($matches[0] != null || $matches[0] != false) {
        $list[] = $matches[0][0];
        $items[]= array('col_link_name'   => $str, 
                        'col_link_date'   => wp_date ("F d Y H:i:s.", filemtime($file)),
                        'col_link_action' => '<a class="remover" href="'.admin_url( 'admin.php?page=ds8stats-key-config&fname='.$str, 'http' ).'"><i class="dashicons-before dashicons-remove"></i></a>'
            );
      }
      
    }
    $totalitems = sizeof($php_files);//$wpdb->query($query);
    $this->items = $items; //$wpdb->get_results($query);
  }
  
}