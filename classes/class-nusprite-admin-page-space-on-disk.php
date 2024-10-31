<?php
if (!class_exists('Nusprite_Admin_Page_Space_On_Disk')) {

    class Nusprite_Admin_Page_Space_On_Disk {

        static function _display() {
            global $wpdb;

            require_once('class-nusprite-version.php');

            if (isset($_POST['nusprite_delete_old_files'])) {
                $start_time = time();
                while (true) {
                    $finished = Nusprite_Version::delete_versions(true);
                    $remaining_time = ini_get('max_execution_time') - 1 - (time() - $start_time);
                    if ($finished || $remaining_time < 10) {
                        break;
                    }
                }
            }

            if (isset($_POST['nusprite_set_expiration']) && isset($_POST['nb_days'])) {
                Nusprite_Version::set_expiration((int) $_POST['nb_days']);
            }
            $nb_days = Nusprite_Version::get_expiration();

            Nusprite::hydrate_Sprites();
            ?>
            <h1><?php __("Space on disk", 'nusprite') ?></h1>
            <?php
            $WHERE = " WHERE DATE_ADD(date_insert, INTERVAL " . $nb_days . " DAY)<now()";
            if (Nusprite::$Sprites) {
                $exclude_current_files = array();
                foreach (Nusprite::$Sprites as $Sprite) {
                    $exclude_current_files[] = $Sprite->sprite_slug . '#' . $Sprite->current_version;
                }
                $WHERE .= " AND concat(sprite_slug,'#',version) not in ('" . implode("','", $exclude_current_files) . "')";
            }

            $sql = "SELECT count(*) as nb,sum(filesize) as size FROM `" . $wpdb->prefix . "nusprite_versions`" . $WHERE . ";";
            $row = $wpdb->get_row($sql);

            $nb_folders = $wpdb->get_var("SELECT count(distinct path) FROM `" . $wpdb->prefix . "nusprite_versions`" . $WHERE . ";");
            ?>

            <h2><?php _e("Expiration", 'nusprite') ?></h2>
            <p><i><?php _e("Define how long <b>old versions</b> of files must remain on server.", 'nusprite') ?></i></p>

            <form method="post">
                <label><input type="text" name="nb_days" value="<?php echo $nb_days ?>" size="2" /> <?php _e("days", 'nusprite') ?></label>
                <button type="submit" name="nusprite_set_expiration" class="button button-primary"><?php _e("Define", 'nusprite') ?></button>
            </form>            

            <h2><?php _e("Currently on disk", 'nusprite') ?></h2>

            <div class="nusprite-table-wrapper">
                <table>
                    <tr>
                        <th><?php printf(__("Number of <b>old versions</b> older than %d days", 'nusprite'), $nb_days) ?></th>
                        <td><b><?php echo $row->nb ?></b></td>
                    </tr>
                    <tr>
                        <th><?php _e("Number of folders", 'nusprite') ?></th>
                        <td><b><?php echo $nb_folders ?></b></td>
                    </tr>
                    <tr>
                        <th><?php _e("Space on disk", 'nusprite') ?></th>
                        <td><b><?php echo Nusprite_Admin::formatBytes($row->size) ?></b></td>
                    </tr>
                </table>
            </div>

            <h2><?php _e("Deletion of old files via Cron", 'nusprite') ?></h2>

            <p><?php printf(__("Nusprite schedulded a WP-Cron task to delete %d old files each hour.", 'nusprite'), 10) ?></p>
            <?php
            if (defined('DISABLE_WP_CRON')) {
                Nusprite_Admin::nag(sprintf(__("As <b>%s</b> is defined, this task will run only if a server cron job is scheduled.", 'nusprite'), 'DISABLE_WP_CRON'));
            }
            if ($row->size) {
                ?>
                <h2><?php _e("Manually", 'nusprite') ?></h2>
                <form method="post">
                    <button type="submit" name="nusprite_delete_old_files" class="button button-primary"><?php printf(__("Delete files older than %d days right now", 'nusprite'), $nb_days) ?></button> 
                    <p><i><?php _e("As much files as possible will be deleted", 'nusprite') ?></i></p>
                </form>            
                <?php
            }
        }

    }

}