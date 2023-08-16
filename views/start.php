<?php
//phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.
?>
<div id="fdestadisticas-plugin-container">
  <div class="fdestadisticas-lower">
    <div class="fdestadisticas-boxes">
      <div class="wrap">
        <h2><?php _e('Archivos cargados') ?></h2>
          <?php
          $wp_list_table = new StatsFile();
          $wp_list_table->prepare_items();
          $wp_list_table->display();
          ?>
      </div>

      <div class="wrap">
        <h2><?php _e('Importar archivo') ?></h2>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" enctype='multipart/form-data'>

          <table class="form-table">
            <tr valign="top">
              <th scope="row">Cargar archivo</th>
              <td><input type='file' name='file' onchange="ValidateSingleInput(this)"></td>
            </tr>
          </table>
          <p class="submit">
            <?php submit_button(__('Upload', 'ds8stats'), '', 'uploadds8', false); ?>
          </p>
        </form>
      </div>
    </div>
  </div>