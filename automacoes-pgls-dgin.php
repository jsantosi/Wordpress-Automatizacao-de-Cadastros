<?php
// Função para registrar o menu de Cadastrar no painel administrativo
function registrar_menu_cadastrar() {
    add_menu_page(
        'Cadastrar Processo Seletivo',   // Título da página
        'Cadastrar',                    // Título do menu
        'manage_options',               // Capacidade necessária para acessar o menu
        'cadastrar-processo-seletivo',  // Slug do menu
        'exibir_formulario_cadastro',   // Função que irá exibir o conteúdo da página
        'dashicons-welcome-write-blog', // Ícone do menu
        -1                              // Posição do menu
    );
}
add_action( 'admin_menu', 'registrar_menu_cadastrar' );

// Função para exibir o formulário de cadastro de processo seletivo
function exibir_formulario_cadastro() {
    ?>
    <div class="wrap">
        <div class="health-check-body container-autpgls">
            <h1 class="titulo-autpgls">Cadastrar Novo Processo Seletivo</h1>
            <form method="post">
                <label for="nome_processo">Nome do Processo Seletivo</label><br/>
                <input type="text" id="nome_processo" name="nome_processo" required><br><br>
                <label for="cpfs_candidatos">Digite aqui os CPFs dos candidatos que terão acesso</label><br>
                <textarea id="cpfs_candidatos" name="cpfs_candidatos" placeholder="Os CPFs devem ser separados apenas por espaços ou quebra de linha." required></textarea><br><br>
                <div class="form-row">
                    <div class="form-group">
                        <label for="encerramento_previsto">Encerramento Previsto</label><br>
                        <input type="number" id="encerramento_previsto" name="encerramento_previsto" min="1" required>
                        <select id="encerramento_periodo" name="encerramento_periodo" required>
                            <option value="">-- Selecione --</option>
                            <option value="Hour">Hora(s)</option>
                            <option value="Day">Dia(s)</option>
                            <option value="Week">Semana(s)</option>
                            <option value="Month">Mês(es)</option>
                            <option value="Year">Ano(s)</option>                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="instituicao_id">Selecione a Instituição</label>
                        <select id="instituicao_id" name="instituicao_id" required>
                        <option value="">-- Selecione --</option>
                            <?php
                             // Obter opções da tabela wpad_pmpro_groups
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'pmpro_groups';
                            $results = $wpdb->get_results( "SELECT id, name FROM $table_name" );
                            foreach ( $results as $result ) {
                                echo '<option value="' . $result->id . '">' . $result->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <br><br>
                <input type="submit" name="submit_processo_seletivo" class="button button-primary" value="Salvar Processo Seletivo">                
            </form>
        </div>    
    </div>
    <?php
}

// Função para processar o formulário
function processar_formulario_processo_seletivo() {
    if ( isset( $_POST['submit_processo_seletivo'] ) ) {
        // Sanitize e obter valores do formulário
        $nome_processo = sanitize_text_field( $_POST['nome_processo'] );
        $encerramento_previsto = intval( $_POST['encerramento_previsto'] );
        $encerramento_periodo = sanitize_text_field( $_POST['encerramento_periodo'] );
        $instituicao_id = intval( $_POST['instituicao_id'] );
        $cpfs_candidatos = sanitize_textarea_field( $_POST['cpfs_candidatos'] );

        // Salvar na tabela wpad_pmpro_membership_levels
        global $wpdb;
        $table_name = $wpdb->prefix . 'pmpro_membership_levels';

        $data = array(
            'name' => $nome_processo,
            'expiration_number' => $encerramento_previsto,
            'expiration_period' => $encerramento_periodo, 
            'cycle_period' => '0',
            'allow_signups' => $instituicao_id,
            'description' => '', // Será preenchido posteriormente
        );

        $format = array(
            '%s',
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
        );

        // Inserir dados na tabela
        $wpdb->insert( $table_name, $data, $format );

        // Verificar por erros SQL
        if ( !empty( $wpdb->last_error ) ) {
            die($wpdb->last_error); // Mostra o erro SQL, se houver
        }

        // Pegar o ID inserido
        $membership_level_id = $wpdb->insert_id;

        // Inserir meta_key na tabela wpad_pmpro_membership_levelmeta
        $table_meta_name = $wpdb->prefix . 'pmpro_membership_levelmeta';
        $meta_data = array(
            'pmpro_membership_level_id' => $membership_level_id,
            'meta_key' => 'confirmation_in_email',
            'meta_value' => '0' // ou o valor que deseja
        );

        $meta_format = array(
            '%d',
            '%s',
            '%s'
        );

        $wpdb->insert( $table_meta_name, $meta_data, $meta_format );

        // Salvar o instituicao_id na tabela wpad_pmpro_membership_levels_groups
        $table_levels_groups_name = $wpdb->prefix . 'pmpro_membership_levels_groups';
        $levels_groups_data = array(
            'level' => $membership_level_id,
            'group' => $instituicao_id,
        );

        $levels_groups_format = array(
            '%d',
            '%d'
        );

        $wpdb->insert( $table_levels_groups_name, $levels_groups_data, $levels_groups_format );

        
        // Criar e publicar página
        $pagina_id = criar_publicar_pagina( $nome_processo, $membership_level_id );

        // Proteger página com senha
        proteger_pagina_com_senha( $pagina_id, $cpfs_candidatos );

        // Preencher a coluna description
        preencher_coluna_description( $membership_level_id, $pagina_id );

        // Mostrar link da página criada em um popup
        $pagina_url = get_permalink( $pagina_id );
        echo '<div id="success-popup">
                <h2>Cadastro finalizado com sucesso!</h2>
                <p>O processo seletivo e a página de acesso foram registrados com sucesso. Copie o link abaixo e envie-o aos candidatos para que eles possam se cadastrar no processo seletivo:</p>
                <h1> ' . esc_url( $pagina_url ) . '</h1>
                <div class="button-container">
                    <button class="acessar-pagina" onclick="window.open(\'' . esc_url( $pagina_url ) . '\', \'_blank\')">Acessar Página</button>
                    <button onclick="document.getElementById(\'success-popup\').style.display=\'none\'">FECHAR</button>
                </div>
            </div>';
        echo '<script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    var popup = document.getElementById("success-popup");
                    popup.style.display = "block";
                });
            </script>';
        
    }
}

// Função para criar e publicar página
function criar_publicar_pagina( $nome_processo, $membership_level_id ) {
    $post_data = array(
        'post_title'   => $nome_processo,
        'post_name'    => sanitize_title( $nome_processo ),
        'post_content' => gerar_conteudo_pagina( $membership_level_id ),
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );

    $pagina_id = wp_insert_post( $post_data );

    // Atualizar a página com o ID de associação
    update_post_meta( $pagina_id, 'membership_level_id', $membership_level_id );

    return $pagina_id;
}

// Função para gerar o conteúdo da página
function gerar_conteudo_pagina( $membership_level_id ) {
    $conteudo = '
    <!-- wp:uagb/container {"block_id":"7c6a713e","variationSelected":true,"isBlockRootParent":true} -->
    <div class="wp-block-uagb-container uagb-block-7c6a713e alignfull uagb-is-root-container">
        <div class="uagb-container-inner-blocks-wrap">
            <!-- wp:progress-bars/progress-bar-block {"blockId":"eb-progressbar-euvvdva","blockMeta":{"desktop":".eb-progressbar-wrapper.eb-progressbar-euvvdva { margin-top:5%; margin-right:0%; margin-left:0%; margin-bottom:8% }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-line { height:25px; background-color:#eaeaea }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle { width:200px; height:200px }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle-inner-content { padding:12px }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle-half { border-width:12px; border-color:rgba(211,139,74,1) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle-fill .eb-progressbar-circle-half,.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-half-circle-fill .eb-progressbar-circle-half { background-color:rgba(211,139,74,1) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-line-fill { height:25px; background-color:rgba(211,139,74,1) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-title { font-size:0.7em; font-style:italic; text-transform:uppercase; margin-left:7px; color:rgba(255,254,254,1) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-count-wrap { font-family:\u0022Cabin\u0022, sans-serif; font-size:1em; font-weight:400; font-style:normal; color:rgba(27,84,123,1) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle-inner { border-color:#eaeaea; border-width:12px }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-half-circle-after { width:200px; color:var(\u002d\u002deb-global-text-color) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-half-circle { width:200px; height:calc(100 * 1px) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-box { height:200px; width:140px; border-width:12px; border-color:#eaeaea }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-box-fill { background-color:rgba(211,139,74,1) }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-line-container { position:relative }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-line-container .eb-progressbar-title { position:absolute; top:50%; left:0; transform:translateY(-50%); z-index:9 }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-line-container .eb-progressbar-line .eb-progressbar-count-wrap { bottom:50% !important; transform:translateY(50%) !important }","tab":".eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle-inner-content { padding:px }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-half-circle { height:calc(100 * 1px) }","mobile":".eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-circle-inner-content { padding:px }.eb-progressbar-wrapper.eb-progressbar-euvvdva .eb-progressbar-half-circle { height:calc(100 * 1px) }"},"title":"Inscrição iniciada","animationDuration":3800,"titleColor":"rgba(255,254,254,1)","counterColor":"rgba(27,84,123,1)","progressColor":"rgba(211,139,74,1)","progressGradient":"linear-gradient(45deg, rgba(0,0,0,0.8) 0% , rgba(0,0,0,0.4) 100%)","showInline":true,"stripeAnimation":"normal","counterTextFontFamily":"Cabin","counterTextSizeUnit":"em","counterTextFontSize":1,"counterTextFontWeight":"400","counterTextFontStyle":"normal","titleTextSizeUnit":"em","titleTextFontSize":0.7,"titleTextFontStyle":"italic","titleTextTextTransform":"uppercase","wrpHeightRange":25,"wrpMarginUnit":"%","wrpMarginTop":"5","wrpMarginBottom":"8","ttlSpaceRight":"","ttlSpaceLeft":"7","commonStyles":{"desktop":".wp-admin .eb-parent-eb-progressbar-euvvdva { display:block; opacity:1 }.eb-parent-eb-progressbar-euvvdva { display:block }","tab":".editor-styles-wrapper.wp-embed-responsive .eb-parent-eb-progressbar-euvvdva { display:block; opacity:1 }.eb-parent-eb-progressbar-euvvdva { display:block }","mobile":".editor-styles-wrapper.wp-embed-responsive .eb-parent-eb-progressbar-euvvdva { display:block; opacity:1 }.eb-parent-eb-progressbar-euvvdva { display:block }"}}>
                <div class="wp-block-progress-bars-progress-bar-block"><div class="eb-parent-wrapper eb-parent-eb-progressbar-euvvdva "><div class="eb-progressbar-wrapper eb-progressbar-euvvdva"><div class="eb-progressbar-line-container center"><div class="eb-progressbar-title">Inscrição iniciada</div><div class="eb-progressbar eb-progressbar-line" data-layout="line" data-count="50" data-duration="3800"><span class="eb-progressbar-count-wrap"><span class="eb-progressbar-count">50</span><span class="postfix">%</span></span><span class="eb-progressbar-line-fill"></span></div></div></div></div></div>
            <!-- /wp:progress-bars/progress-bar-block -->

            <!-- wp:uagb/advanced-heading {"block_id":"95be249b","classMigrate":true,"headingColor":"#334a78"} -->
            <div class="wp-block-uagb-advanced-heading uagb-block-95be249b"><h2 class="uagb-heading-text" style="
    color: #334a78;">Selecione abaixo o processo que você está participando:</h2></div>
            <!-- /wp:uagb/advanced-heading -->

            <!-- wp:shortcode -->
            [pmpro_advanced_levels levels="' . $membership_level_id . '" layout="table" back_link="false" description="false" expiration="false" price="hide"]
            <!-- /wp:shortcode -->
        </div>
    </div>
    <!-- /wp:uagb/container -->';

    return $conteudo;
}

/// Função para proteger página com senha
function proteger_pagina_com_senha( $pagina_id, $cpfs_candidatos ) {
    // Limpar espaços extras e quebras de linha
    $cpfs_candidatos = preg_replace('/\s+/', ' ', trim($cpfs_candidatos));

    // Converter CPFs em um array
    $senhas = explode( ' ', $cpfs_candidatos );

    // Adicionar a senha fixa no início do array
    array_unshift( $senhas, '@12qwaszx' );

    // Serializar o array de senhas
    $senhas_serializadas = maybe_unserialize( $senhas );

    // Atualizar a meta chave da página para proteger com múltiplas senhas
    update_post_meta( $pagina_id, 'wp_protect_password_multiple_passwords', $senhas_serializadas );
}

// Função para preencher a coluna description
function preencher_coluna_description( $membership_level_id, $pagina_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pmpro_membership_levels';
    $pagina_url = get_permalink( $pagina_id );

    // Remover a barra final da URL, se houver
    $pagina_url = untrailingslashit( $pagina_url );
    
    $description = '<a href="' . esc_url( $pagina_url . '-etapas-do-processo-seletivo' ) . '">Veja as etapas e realize seu agendamento</a>';

    $wpdb->update(
        $table_name,
        array( 'description' => $description ),
        array( 'id' => $membership_level_id ),
        array( '%s' ),
        array( '%d' )
    );
}

add_action( 'admin_init', 'processar_formulario_processo_seletivo' );
?>
