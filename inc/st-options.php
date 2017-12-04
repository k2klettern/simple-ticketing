<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


if (!current_user_can('manage_options')) {
    wp_die(_e('You are not authorized to view this page.','clone'));
}

// Get the current tab
$current = empty( $_GET['tab'] ) ? "nots" : $_GET['tab'];

// Set the Tabs Arary
$tabs = array(  'nots'   => 'Notifications',
    'marketoapi'  => 'Marketo API'
);

if ( isset ( $_REQUEST['tab'] ) )
    $tab = $_REQUEST['tab'];
else
    $tab = 'nots';

if (isset( $_POST['stoptions'] ) && wp_verify_nonce( $_POST['stoptions'], 'stoptionsnonce' )) {

    switch ( $tab ) {
        case 'nots' :
            $option_name = '_st_options_nots';
            break;
        case 'marketoapi' :
            $option_name = '_st_options_marketoapi';
            break;
    }

    $inputs = isset($_POST['inputs']) ? $_POST['inputs'] : "";
    $users = isset($_POST['users']) ? $_POST['users'] : "";
    $inputs['users'] = $users;
    if ( get_option( $option_name ) !== false ) {
        $update = update_option($option_name, $inputs);
    } else {
        $deprecated = null;
        $autoload = 'no';
        $update = add_option($option_name, $inputs, $deprecated, $autoload);
    }

    if($update) {
        echo '<div class="updated">';
        _e('settings saved.','clone');
        echo '</strong></p></div>';
    } else {
        echo '<div class="error"><p><strong>';
        _e('Error - Url does not seems to be correct.','clone');
        echo '</strong></p></div>';
    }
}
?>
<div class="wrap" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
    <div id="welcome-panel" class="welcome-panel">
        <div class="welcome-panel-content">
            <h2><?php _e('Ticketing System','clone'); ?></h2>
            <p><?php _e('Settings Page','clone'); ?></p>
        </div>
        <div class="clear"></div>

        <h2 class="nav-tab-wrapper">
            <?php

            foreach( $tabs as $tab => $name ){
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab$class' href='?page=simple-ticketing%2Finc%2Fclass-simple-ticketing.php&tab=$tab'>$name</a>";
            }
            ?>
        </h2>

        <?php   if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
        else $tab = 'nots'; ?>

       <?php switch ( $tab ){
            case 'nots':
                ?>
                <div class="welcome-panel-column-container">
                    <form id="settings-form" name="form1" method="post" action="">
                        <div class="bootstrap-wrapper">
                            <div class="table table-stripped">
                                <table class="stoptions-general">
                                    <?php wp_nonce_field( 'stoptionsnonce', 'stoptions' );
                                    $option_name = '_st_options_nots';
                                    $inputs = get_option($option_name);
                                    ?>
                                        <tr>
                                            <td>
                                            <h1><span class="dashicons dashicons-email-alt"></span> Email Options</h1>
                                            <p><input type="checkbox" value="1" id="enabling-nots" name="inputs[enabled]" <?php if(isset($inputs['enabled'])) checked($inputs['enabled'], 1); ?>>
                                                <label for="enabling-nots">Enable e-mail Notifications</label></p>
                                            <p><label for="select-users"><?php _e('Select Users to notify', 'stoptions'); ?></label><br/>
                                                <select id="select-users" class="widefat" name="users[]" multiple>
                                                <?php $users = get_users(array('role' => 'caae_admin'));
                                                foreach($users as $user) {?>
                                                     <option value="<?php echo $user->ID;?>" <?php if(isset($inputs['users']) && is_array($inputs['users'])) { if (in_array($user->ID, $inputs['users'])) echo "selected"; } ?>><?php echo $user->nickname; ?></option>
                                                <?php } ?>
                                                </select><br/>
                                                <sup><?php _e('Hold down the Ctrl (windows) / Command (Mac) button to select multiple options.', 'st_plugin'); ?></sup>
                                            </p>
                                            </td>
                                            <td>
                                                <h1><span class="dashicons dashicons-format-status"></span> Slack Options</h1>
                                                <p><input type="checkbox" value="1" id="enabling-slacknots" name="inputs[enabledslack]" <?php if(isset($inputs['enabledslack'])) checked($inputs['enabledslack'], 1); ?>>
                                                    <label for="enabling-nots">Enable Slack Notifications</label></p>
                                                <p>
                                                    <label for="slack-url">Slack URL</label>
                                                    <input type="text" id="slack-url" class="widefat" name="inputs[slackurl]" value="<?php echo (isset($inputs['slackurl'])) ? $inputs['slackurl'] : ""; ?>">
                                                    <sup><?php _e('To enabling your Url/Channel on Slack visit http://{yourteam}.slack.com/apps<br> then click on Manage and Custom Integrations and create a new incoming WebHooks<br> then Add a New Configuration, select wherever you want the notifications to show<br> And click on Add Incoming WebHooks Integration button<br> Fill the needed fields, and copy the URL, paste the URL here in the integrations field.<br> You are done!', 'st_plugin'); ?></sup>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h1><span class="dashicons dashicons-email"></span> User Notify</h1>
                                                <p><input type="checkbox" value="1" id="enabling-usern" name="inputs[enabledusern]" <?php if(isset($inputs['enabledusern'])) checked($inputs['enabledusern'], 1); ?>>
                                                    <label for="enabling-usern">Enable User Notifications</label></p>
                                                <sup><?php _e('Check this to allways notify user on replies', 'st_options'); ?></sup>
                                            </td>
                                        </tr>
                                </table>
                            </div>
                        </div>
                        <p class="submit">
                            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes')?>" />
                        </p>
                    </form>
                </div>
                <?php
                break;
            case 'marketoapi':
                ?>
                <div class="welcome-panel-column-container">
                    <form id="settings-form" name="form1" method="post" action="">
                        <div class="bootstrap-wrapper">
                            <div class="table table-stripped">
                                <table>
                                    <?php wp_nonce_field( 'stoptionsnonce', 'stoptions' );

                                    $option_name = '_st_options_marketoapi';
                                    $inputs = get_option($option_name);?>
                                    <tr class="stoptions-general">
                                        <p>Here enter your TAB-2 details</p>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <p class="submit">
                            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes')?>" />
                        </p>
                    </form>
                </div>
                <?php break;
        }
        ?>


    </div>
</div><!-- end wrap -->