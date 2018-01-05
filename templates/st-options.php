<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


if (!current_user_can('manage_options')) {
    wp_die(_e('You are not authorized to view this page.',ST_TEXT_DOMAIN));
}

// Get the current tab
$current = empty( $_GET['tab'] ) ? "nots" : $_GET['tab'];

// Set the Tabs Array
$tabs = array(
        'main' => 'Main Options',
        'nots'   => 'Notifications',
        'slack'  => 'Slack'
);

if ( isset ( $_REQUEST['tab'] ) )
    $tab = $_REQUEST['tab'];
else
    $tab = 'license';

if (isset( $_POST['stoptions'] ) && wp_verify_nonce( $_POST['stoptions'], 'stoptionsnonce' )) {

    $option_name = '_st_options';

    $toupdate = get_option($option_name);
    $inputs = isset($_POST['inputs']) ? $_POST['inputs'] : "";
    var_dump($toupdate);
    if(isset($inputs)) {
        foreach ($toupdate as $key => $value) {
            if (!isset($inputs[$key]) && $toupdate[$key] == 1) {
                unset($toupdate[$key]);
                unset($inputs[$key]);
                continue;
            }
            if (!isset($toupdate[$key]) && $inputs[$key] == 1) {
                $toupdate[$key] = 1;
                unset($inputs[$key]);
                continue;
            }
        }
        foreach ($inputs as $key => $value) {
            if (isset($inputs[$key]) && $inputs[$key] != 1) {
                $toupdate[$key] = $value;
            }
        }
 /*           if (!isset($inputs[$key]) && $toupdate[$key] == 1) {
                    echo $key;
                    die;
                    unset($toupdate[$key]);
                continue;
            }
            if (!isset($toupdate[$key]) && $inputs[$key] == 1) {
                    $toupdate[$key] = 1;
                continue;
            }
            if (isset($inputs[$key]) && $inputs[$key] != 1) {
                $toupdate[$key] = $value;
            }
        } */
    }

    $update = update_option($option_name, $toupdate);

    if(!is_wp_error($update)) {
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
                echo "<a class='nav-tab$class' href='?post_type=ticketing&page=ticketing_options&tab=$tab'>$name</a>";
            }
            ?>
        </h2>

        <?php   if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
        else $tab = 'nots'; ?>

        <div class="welcome-panel-column-container">
            <form id="settings-form" name="form1" method="post" action=""> <?php
                wp_nonce_field( 'stoptionsnonce', 'stoptions' );
                $option_name = '_st_options';
                $inputs = get_option($option_name);
           switch ( $tab ){
           case 'main':
               ?>
                       <div class="bootstrap-wrapper">
                           <div class="table table-stripped">
                               <table>
                                   <tr class="stoptions-general">
                                       <td>
                                           <h2><?php _e('Dashboard Settings', ST_TEXT_DOMAIN); ?></h2>
                                       <p><input type="checkbox" value="1" id="enabling-nots" name="inputs[dashboard]" <?php if(isset($inputs['dashboard'])) checked($inputs['dashboard'], 1); ?>>
                                           <label for="enabling-nots"><?php _e('Show Tickets in Dashboard', ST_TEXT_DOMAIN); ?></label></p>
                                       <p><input type="checkbox" value="1" id="enabling-nots" name="inputs[dashboardnew]" <?php if(isset($inputs['dashboardnew'])) checked($inputs['dashboardnew'], 1); ?>>
                                               <label for="enabling-nots"><?php _e('Allow creation of new tickets in Dashboard', ST_TEXT_DOMAIN); ?></label></p>
                                       <p><input type="checkbox" value="1" id="enabling-nots" name="inputs[dashboardedit]" <?php if(isset($inputs['dashboardedit'])) checked($inputs['dashboardedit'], 1); ?>>
                                               <label for="enabling-nots"><?php _e('Allow answering of tickets in Dashboard', ST_TEXT_DOMAIN); ?></label></p>
                                       <hr>
                                          <h2><?php _e('Select user roles for filtering', ST_TEXT_DOMAIN); ?></h2>
                                           <p><label for="select-users"><?php _e('Select User role allowed to use the Ticketing System', ST_TEXT_DOMAIN); ?></label><br/>
                                               <select id="select-users" class="widefat" name="inputs[role]">
                                                   <?php wp_dropdown_roles( $inputs['role'] ); ?>
                                               </select><br/>
                                               <sup><?php _e('Default is subscriber'); ?></sup>
                                           </p>
                                           <p><label for="select-users"><?php _e('Select User role to admin the Ticketing System', ST_TEXT_DOMAIN); ?></label><br/>
                                               <select id="select-users" class="widefat" name="inputs[roleadmin]">
                                                   <?php wp_dropdown_roles( $inputs['roleadmin'] ); ?>
                                               </select><br/>
                                               <sup><?php _e('Default is administrator'); ?></sup>
                                           </p>
                                       </td>
                                   </tr>
                               </table>
                           </div>
                       </div>
               <?php break;
            case 'nots':
                ?>
                        <div class="bootstrap-wrapper">
                            <div class="table table-stripped">
                                <table class="stoptions-general">
                                        <tr>
                                            <td>
                                            <h1><span class="dashicons dashicons-email-alt"></span> Email Options</h1>
                                            <p><input type="checkbox" value="1" id="enabling-nots" name="inputs[enabled]" <?php if(isset($inputs['enabled'])) checked($inputs['enabled'], 1); ?>>
                                                <label for="enabling-nots">Enable e-mail Notifications</label></p>
                                            <p><label for="select-users"><?php _e('Select Users to notify', ST_TEXT_DOMAIN); ?></label><br/>
                                                <select id="select-users" class="widefat" name="input[users][]" multiple>
                                                <?php $users = get_users(array('role' => 'administrator'));
                                                foreach($users as $user) {?>
                                                     <option value="<?php echo $user->ID;?>" <?php if(isset($inputs['users']) && is_array($inputs['users'])) { if (in_array($user->ID, $inputs['users'])) echo "selected"; } ?>><?php echo $user->nickname; ?></option>
                                                <?php } ?>
                                                </select><br/>
                                                <sup><?php _e('Hold down the Ctrl (windows) / Command (Mac) button to select multiple options.', ST_TEXT_DOMAIN); ?></sup>
                                            </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h1><span class="dashicons dashicons-email"></span> User Notify</h1>
                                                <p><input type="checkbox" value="1" id="enabling-usern" name="inputs[enabledusern]" <?php if(isset($inputs['enabledusern'])) checked($inputs['enabledusern'], 1); ?>>
                                                    <label for="enabling-usern">Enable User Notifications</label></p>
                                                <sup><?php _e('Check this to allways notify user on replies', ST_TEXT_DOMAIN); ?></sup>
                                            </td>
                                        </tr>
                                </table>
                            </div>
                        </div>
                <?php
                break;
                case 'slack':
                ?>
                        <div class="bootstrap-wrapper">
                            <div class="table table-stripped">
                                <table>
                                    <tr class="stoptions-general">
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
                                </table>
                            </div>
                        </div>
                <?php break;
        }
        ?>
                <p class="submit">
                    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes')?>" />
                </p>
            </form>
        </div>

    </div>
</div><!-- end wrap -->