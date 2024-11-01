<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
  return;
}

global $rcsplay_error_msg;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if($_POST['rcsplay_api_key']){
    // Sanitize
    $key = sanitize_text_field( $_POST['rcsplay_api_key'] );
    // Validation
    if(strlen($key) < 10) {
      $rcsplay_error_msg = 'Chave de API inválida';
    }else{
      rcsplay_new_token($key);
    };
  };
};
?>
<div id="rcsplay-admin" class="wrap rcsplay-settings">

  <p class="breadcrumbs">
    <span class="prefix">Você está aqui: </span>
    <span class="current-crumb"><strong>YOUMBRELLA</strong></span>
  </p>


  <div class="row">
    <!-- Main Content -->
    <div class="main-content col col-4">
      <div class="logo-top">
        <img src="<?php echo SPLAY_PLUGIN_URL; ?>admin/images/youmbrella-logo-p.jpg" alt="Youmbrella" title="Youmbrella" class=""/>
      </div>
      <h1 class="page-title">
        Configurações
      </h1>
      <h2 style="display: none;"></h2>
      <?php if($rcsplay->key){ ?>
        <div class="row">
          <div class="col col-6">
            <p>
              <span class="status positive">CONECTADO</span>
            </p>
            <p>
              Nome: <?php echo $rcsplay->name; ?>
            </p>
            <p>
              E-mail: <?php echo $rcsplay->email; ?>
            </p>
            <p>
              Empresa: <?php echo $rcsplay->company; ?>
            </p>
            <p>
              Conectado em: <?php echo $rcsplay->start; ?>
            </p>
          </div>
        </div>
        <?php }else{ ?>

          <?php if($rcsplay_error_msg){ ?>
            <div class="notice notice-error">
              <p>
                <?php echo $rcsplay_error_msg; ?>
              </p>
            </div>
            <?php } ?>

            <form method="post">
              <div class="row">
                <div class="col col-6">
                  <textarea placeholder="Sua chave API Youmbrella" id="rcsplay_api_key" name="rcsplay_api_key" rows="10" cols="50" class="large-text code"></textarea>
                </div>
                <div class="col col-6">
                  <input type="submit" value="Salvar e conectar" class="button button-primary">
                </div>
                <div class="col col-6">
                  <p class="help">
                    Digite sua Chave API para conectar com a sua conta no Youmbrella. <a target="_blank" href="https://admin.youmbrella.com/plugins/wordpress">Pegue sua chave aqui.</a>
                  </p>
                </div>
              </div>
            </form>
            <?php }; ?>
          </div>
          <!-- Sidebar -->
          <div class="sidebar col col-2">
            <?php include dirname( __FILE__ ) . '/partials/sidebar.php'; ?>
          </div>


        </div>

      </div>
