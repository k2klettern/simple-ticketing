<?php

if (!is_user_logged_in() && !current_user_can('administrator')) {
    wp_safe_redirect(wp_login_url());
    die;
}

get_header();

$userid = get_current_user_id();

if (current_user_can('caae_client')) {
    query_posts($query_string . "&order=DESC&posts_per_page=-1&author=$user_id");
} else {
    query_posts($query_string . '&order=DESC&posts_per_page=-1');
}

?>

<div class="col-xs-12 col-lg-12 content-area" id="page-wrapper">
    <div class="container">
        <div class="row">
                        <div class="container">
                            <div class="row">
                                <div class="col-xs-12 col-lg-9">
                                    <form id="new-st" method="POST" action="">
                                        <?php
                                        $action = get_query_var('st-action');
                                        if (is_user_logged_in()) :
                                        if (!$action) { ?>
                                            <?php if (have_posts()) : ?>
                                                <?php wp_nonce_field('delete-st-action', 'delete-st-nonce-field'); ?>
                                                <h1 class="page-header">Mensajes</h1>
                                                <div id="tickets">
                                                    <p><?php $counter = StHelpers::getInstance()->get_main_message_status_for_user(get_current_user_id());
                                                        if($counter) {
                                                            echo $counter ? "Tienes " . $counter . " mensajes nuevos " : "";
                                                        } else {
                                                            echo "No tienes mensajes nuevos";
                                                        }
                                                        ?></p>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover table-striped">
                                                            <thead>
                                                            <tr>
                                                                <th><span class="sort"
                                                                          data-sort="postname"><?php _e('Mensaje', 'st_plugin'); ?></span>
                                                                </th>
                                                                <th><span class="sort"
                                                                          data-sort="status"><?php _e('Estado', 'st_plugin'); ?></span>
                                                                </th>
                                                                <th><span class="sort"
                                                                          data-sort="reference"><?php _e('Referencia', 'st_plugin'); ?></span>
                                                                </th>
                                                                <th><span class="sort"
                                                                          data-sort="date"><?php _e('Fecha', 'st_plugin'); ?></span>
                                                                </th>
                                                                <th><span class="sort"
                                                                          data-sort="moddate"><?php _e('&Uacute;ltima Respuesta', 'st_plugin'); ?></span>
                                                                </th>
                                                                <th><?php _e('Acciones', 'st_plugin'); ?></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody class="list">
                                                            <?php
                                                            // Start the Loop.
                                                            while (have_posts()) : the_post();
                                                                $reference = get_post_meta($post->ID, '_reference', true);
                                                                $permalink = get_the_permalink($reference);
                                                                echo "<tr>";
                                                                echo "<td class=\"postname\"><a href=\"" . get_permalink($post->ID) . "\">" . get_the_title($post->ID) . "</a></td>";
                                                                echo "<td class=\"status\">" . StHelpers::getInstance()->get_the_status($post->ID) . "</td>";
                                                                echo "<td class=\"reference\"><a href=\"" . $permalink . "\" alt=\"referencia\">" . get_the_title($reference) . "</a></h3>";
                                                                echo "<td class=\"date\">" . get_the_date() . "</td>";
                                                                echo "<td class=\"moddate\">" . $post->post_modified_gmt . "</td>";
                                                                echo "<td>" . StHelpers::getInstance()->build_action_buttons($post->ID);
                                                                $isnew = StHelpers::getInstance()->get_message_status($post->ID);
                                                                if ($isnew == 1)
                                                                    echo "<i class=\"fa fa-star\" aria-hidden=\"true\" style=\"color: #ffa500;\"></i> Nuevo</td>";
                                                                elseif ($isnew == 2)
                                                                    echo "<i class=\"fa fa-reply\" aria-hidden=\"true\" style=\"color: #ffa500;\"></i> Nueva Respuesta</td>";
                                                                else
                                                                    echo"</td>";
                                                                echo "</tr>";
                                                                // End the loop.
                                                            endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <?php
                                                // Previous/next page navigation.
                                                the_posts_pagination(array(
                                                    'prev_text' => __('P&aacute;gina Anterior', 'twentysixteen'),
                                                    'next_text' => __('P&aacute;gina Siguiente', 'twentysixteen'),
                                                    'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'twentysixteen') . ' </span>',
                                                ));
                                                // echo StHelpers::getInstance()->build_footer_buttons();
                                            else : ?>
                                                <h1 class="page-header">Mensajes</h1>
                                                <div id="tickets">
                                                <?php
                                                _e('A&uacute;n no existen Mensajes en tu Perfil xxx', 'st_plugin'); ?>
                                                </div>
                                                <?php
                                            endif;

                                        } elseif ($action == "new") {
                                            ?>
                                            <h1 class="page-header"><?php _e('Crear un Nuevo Mensaje', 'st_plugin'); ?></h1>
                                            <?php
                                            $input = isset($_POST['input']) ? $_POST['input'] : "";
                                            if (isset($GLOBALS['answer'])) {
                                                echo $GLOBALS['answer'];
                                            } ?>

                                            <?php wp_nonce_field('front-st-action', 'front-st-nonce-field'); ?>
                                            <div clas="form-group">
                                                <label for="title"><?php _e('Asunto', 'st_plugin'); ?></label>
                                                <input type="text" class="form-control" id="title" name="title"
                                                       value="<?php echo isset($_POST['title']) ? $_POST['title'] : ""; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="reference"><?php _e('Referencia', 'st_plugin'); ?></label>
                                                <select name="reference" id="referencia" class="form-control">
                                                    <option value="">Selecciona una Solicitud</option>
                                                    <?php $posts = get_posts(array(
                                                        'post_type' => "solicitudes",
                                                        'post_status' => array('publish', 'pendiente', 'aprobada', 'revision', 'cerrada'),
                                                        'post_author' => get_current_user_id(),
                                                        'order' => 'DESC'
                                                    ));

                                                    if ($posts) {
                                                        foreach ($posts as $post) { ?>
                                                            <option
                                                                value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
                                                        <?php }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group your-request">
                                                <label for="stn-editor"><?php _e('Tu Mensaje', 'st_plugin'); ?></label><br/>
                                                <?php $content = "";
                                                $editor_id = "stn-editor";
                                                wp_editor($content, $editor_id, array('textarea_name' => "input[0][message]", 'teeny' => true, 'media_buttons' => false)); ?>
                                            </div>
                                            <input type="hidden" name="input[0][userid]" value="<?php echo get_current_user_id(); ?>">
                                            <input type="hidden" name="input[0][type]" value="mainmessage">
                                            <button class="btn btn-md btn-submit" id="volver" onclick="history.back()" type="button"><i class="fa fa-chevron-left"></i> Volver</button>
                                            <input type="submit" class="btn btn-success btn-send-msg" value"<?php _e('Enviar', 'st_plugin'); ?>">
                                            <?php
                                        } ?>
                                    </form>
                                    <?php else : ?>
                                        <p><a href='<?php echo wp_login_url(); ?>'
                                              class='kp-button blue'><?php _e('Debes Ingresar para ver el contenido', 'st_plugin'); ?></a>
                                        </p>
                                    <?php endif; //isuserloggedin ?>
                                </div>
                            </div>
                        </div>
        </div>
    </div>
</div><!-- .content-area -->

<?php if(is_active_sidebar('sidebar')) get_sidebar(); ?>
<?php get_footer(); ?>
