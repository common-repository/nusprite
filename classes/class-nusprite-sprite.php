<?php

if (!class_exists('Nusprite_Sprite')) {

    class Nusprite_Sprite {

        public $sprite_slug = '';
        public $current_version = null;
        public $new_version = null;
        public $current_path = '';
        public $current_url = '';
        public $current_width = 0;
        public $current_height = 0;
        public $current_filesize = 0;
        public $posts_nuspriteitem = null;
        public $Items = null;
        public $key2id_item = array();
        public $max_width_type = 'widest'; // 'widest', 'narrowest' OR (integer)
        public $max_width_value = '';
        public $custom_post_type = '';
        public $post_meta_image = '';
        public $post_meta_credits = '';
        public $post_meta_extra = '';
        public $bg_preview = '';
        public $current_fingerprint = '';
        public $item_image_size = 'full';
        public $versioning = false;
        public $regenerate_on_save_post = false;
        public $regenerate_nucss_on_regenerate_nusprite = false;
        public $new_width = 1;
        public $new_height = 1;
        public $max_width = 0;
        public $security_margins = 2; // around each item

        function __construct($sprite_slug, $row = false) {
            $this->sprite_slug = $sprite_slug;
            if ($row) {
                $this->max_width_type = $row['max_width_type'];
                $this->max_width_value = $row['max_width_value'];
                $this->custom_post_type = $row['custom_post_type'];
                $this->post_meta_image = $row['post_meta_image'];
                $this->post_meta_credits = $row['post_meta_credits'];
                $this->post_meta_extra = $row['post_meta_extra'];
                $this->item_image_size = $row['item_image_size'];
                $this->versioning = (bool) $row['versioning'];
                $this->regenerate_on_save_post = (bool) $row['regenerate_on_save_post'];
                $this->regenerate_nucss_on_regenerate_nusprite = $row['regenerate_nucss_on_regenerate_nusprite'];
                $this->current_version = $row['current_version'];
                $this->current_fingerprint = $row['current_fingerprint'];
                $this->current_url = $row['current_url'];
                $this->current_path = $row['current_path'];
                $this->current_width = (int) $row['current_width'];
                $this->current_height = (int) $row['current_height'];
                $this->current_filesize = (int) $row['current_filesize'];
                $this->security_margins = (int) $row['security_margins'];
                $this->bg_preview = $row['bg_preview'];
            }
        }

        function hydrate_Items($rehydratable = false) {
            if ($rehydratable == false && $this->Items !== null) {
                return;
            }
            global $wpdb;
            $this->Items = array();
            $rows_items = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "nusprite_items` WHERE sprite_slug='" . $this->sprite_slug . "';", ARRAY_A);
            if (!$rows_items) {
                return;
            }
            require_once('class-nusprite-item.php');
            foreach ($rows_items as $row) {
                $Item = new Nusprite_Item($row, $this);
                $this->Items[$row['item_slug']] = $Item;
            }
        }

        function create() {
            global $wpdb;
            $wpdb->query("INSERT INTO `" . $wpdb->prefix . "nusprite_sprites` SET sprite_slug='" . addslashes($this->sprite_slug) . "';");
        }

        function delete() {
            global $wpdb;
            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "nusprite_items` WHERE sprite_slug='" . addslashes($this->sprite_slug) . "';");
            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "nusprite_sprites` WHERE sprite_slug='" . addslashes($this->sprite_slug) . "';");
        }

        function update($data) {
            global $wpdb;
            $SET = [];
            foreach ($data as $field => $value) {
                $this->$field = $value;
                if ($value === null) {
                    $SET[] = $field . "=null";
                } else {
                    $SET[] = $field . "='" . addslashes($value) . "'";
                }
            }
            if ($SET) {
                $wpdb->query("UPDATE `" . $wpdb->base_prefix . "nusprite_sprites` SET " . implode(",", $SET) . " WHERE sprite_slug='" . addslashes($this->sprite_slug) . "';");
            }
        }

        function regenerate() {
            $this->new_version = $this->current_version;
            if ($this->versioning) {
                $this->new_version++;
            }

            $this->hydrate_posts_nuspriteitem();

            $res = '';

            // 1. define max-width

            foreach ($this->posts_nuspriteitem as $post_item) {


                if ($this->post_meta_image == '') {
                    // featured image
                    $attachment_id = get_post_thumbnail_id($post_item);
                } else {
                    // custom field image
                    $attachment_id = get_field($this->post_meta_image, $post_item, false);
                }
                $data = wp_get_attachment_metadata($attachment_id);
                if ($data == false) {
                    // no attachment !
                    continue;
                }
                list($img_src, $img_width, $img_height, $void) = wp_get_attachment_image_src($attachment_id, $this->item_image_size);


                $post_item->nusc_src = $img_src;
                $post_item->nusc_width = $img_width;
                $post_item->nusc_height = $img_height;
                $post_item->nusc_path = get_attached_file($attachment_id);
                $post_item->nusc_attachment_id = $attachment_id;
                $post_item->credits = '';
                if ($this->post_meta_credits) {
                    $post_item->credits = (string) get_field($this->post_meta_credits, $attachment_id);
                }
                $extra = array();
                if ($this->post_meta_extra) {
                    $post_meta_extras=explode(',',$this->post_meta_extra);
                    foreach ($post_meta_extras as $post_meta_extra) {
                        $value=(string) get_field($post_meta_extra, $attachment_id);
                        $extra[$post_meta_extra]=$value;
                    }
                }
 $post_item->extra=serialize($extra);                

                $img_width_with_security_margins = $img_width + 2 * $this->security_margins;
                switch ($this->max_width_type) {
                    case 'widest' :
                        $this->max_width += $img_width_with_security_margins;
                        break;
                    case 'narrowest' :
                        if ($img_width_with_security_margins > $this->max_width) {
                            $this->max_width = $img_width_with_security_margins;
                        }
                        break;
                    default :
                        $this->max_width = max($this->max_width, $this->max_width_value, $img_width_with_security_margins);
                }

                $key = str_pad($img_height, 5, 0, STR_PAD_LEFT) . '_' . $post_item->ID;

                $this->key2id_item[$key] = $post_item->ID;
            }

            krsort($this->key2id_item);

            $this->not_yet_used = $this->key2id_item;
            $this->fill_pan(0, 0, $this->max_width, null);

            $this->generate_image_file();

            if ($this->regenerate_nucss_on_regenerate_nusprite) {
                $Stylesheet = Nucss::get($this->regenerate_nucss_on_regenerate_nusprite);
                if ($Stylesheet) {
                    $Stylesheet->regenerate();
                }
            }

            // crédits

            $credits = "";
            foreach ($this->Items as $Item) {
                if ($Item->credits != '') {
                    $credits .= $Item->format('<div><div style="width:$width;height:$height;max-width:100%;max-height:75vh;overflow:auto"><div style="background:#f0f0f0 url($url) $pos_x $pos_y no-repeat;width:$width;height:$height"></div></div><p>$credits</p></div>');
                }
            }
            $credits_path = Nusprite::get_basedir('credits-' . $this->sprite_slug . '.html');
            if ($credits) {
                $credits = '<div class="nusprite-credits">' . $credits . '</div>';
            }
            file_put_contents($credits_path, $credits);
            return $res;
        }

        public function fill_pan($x, $y, $width, $height = null) {

            $found = false;

            // 1. Search image that fits pan

            foreach ($this->not_yet_used as $clef => $ID) {
                $post_item = $this->posts_nuspriteitem[$ID];
                $img_width = $post_item->nusc_width;
                $img_height = $post_item->nusc_height;

                $img_width_with_security_margins = $img_width + 2 * $this->security_margins;
                $img_height_with_security_margins = $img_height + 2 * $this->security_margins;

                if (
                        ($height === null || $img_height_with_security_margins <= $height) &&
                        ($img_width_with_security_margins <= $width)
                ) {
                    // it fits !
                    $post_item->nusc_pos_x = $x + $this->security_margins;
                    $post_item->nusc_pos_y = $y + $this->security_margins;
                    $post_item->nusc_width = $img_width;
                    $post_item->nusc_height = $img_height;

                    $this->new_width = max($this->new_width, $x + $img_width_with_security_margins);
                    $this->new_height = max($this->new_height, $y + $img_height_with_security_margins);
                    unset($this->not_yet_used[$clef]);

                    $found = true;

                    break;
                }
            }

            if ($found) {

                // 2. Fill remaining pan (right positioned)

                $x_right = $x + $img_width_with_security_margins;
                $y_right = $y;
                $width_right = $width - $img_width_with_security_margins;
                $height_right = $img_height_with_security_margins;

                $this->fill_pan($x_right, $y_right, $width_right, $height_right);

                // 3. Fill remaining (bottom positioned)

                $x_bottom = $x;
                $y_bottom = $y + $img_height_with_security_margins;
                $width_bottom = $width;
                $height_bottom = $height;
                if ($height !== null) {
                    $height_bottom = $height - $img_height_with_security_margins;
                }

                $this->fill_pan($x_bottom, $y_bottom, $width_bottom, $height_bottom);
            }
        }

        /*
         * source = http://php.net/manual/fr/function.imagecreatefromjpeg.php
         */

        function setMemoryForImage() {

            $imageInfo = [
                0 => $this->new_width,
                1 => $this->new_height,
                'bits' => 8,
                'channels' => 3 /* image RGB */
            ];


            $MB = Pow(1024, 2);   // number of bytes in 1M
            $K64 = Pow(2, 16);    // number of bytes in 64K
            $TWEAKFACTOR = 1.8;   // Or whatever works for you
            $memoryNeeded = round(( $imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + $K64
                    ) * $TWEAKFACTOR
            );
            $memoryHave = memory_get_usage();
            $memoryLimitMB = (integer) ini_get('memory_limit');
            $memoryLimit = 8 * $MB;

            if (function_exists('memory_get_usage') && $memoryHave + $memoryNeeded > $memoryLimit
            ) {
                $GLOBALS["newLimit"] = $memoryLimitMB + ceil(( $memoryHave + $memoryNeeded - $memoryLimit
                                ) / $MB
                );
                ini_set('memory_limit', $GLOBALS["newLimit"] . 'M');
            }
        }

        public function generate_image_file() {
            global $wpdb;
            require_once('class-nusprite-item.php');

            $md5 = md5($this->sprite_slug . $this->new_version);

            $folders = '/' . $md5[0] . '/' . $md5[1] . '/' . $md5[2];
            $filename = $this->sprite_slug . '-' . $this->new_version . '.png';
            $newimg_url = Nusprite::get_baseurl($folders, $filename);
            $newimg_path = Nusprite::get_basedir($folders, $filename, true);

            $this->setMemoryForImage();
            $background = imagecreatetruecolor($this->new_width, $this->new_height);

            imagealphablending($background, false);
            imagesavealpha($background, true);
            $col = imagecolorallocatealpha($background, 255, 255, 255, 127);
            imagefill($background, 0, 0, $col);

            ob_start();
            imagepng($background);
            $fingerprint = md5(ob_get_clean());

            /*
              // uncomment this block to activate fingerprint functionality
              if ($fingerprint == $this->current_fingerprint) {
              // image is the same as before => do nothing
              Nusprite_Admin::nag(__("Image is the same as before", 'nusprite'));
              imagedestroy($background);
              return;
              }
             */
            $this->current_fingerprint = $fingerprint;

            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "nusprite_items` WHERE sprite_slug='" . addslashes($this->sprite_slug) . "';");
            $this->Items = array();
            foreach ($this->posts_nuspriteitem as $post_item) {
                if (empty($post_item->nusc_width)) {
                    continue;
                }
                switch (pathinfo($post_item->nusc_path, PATHINFO_EXTENSION)) {
                    case 'png' :
                        $tmp = imagecreatefrompng($post_item->nusc_path);
                        break;
                    case 'jpg' : case 'jpeg' :
                        $tmp = imagecreatefromjpeg($post_item->nusc_path);
                        break;
                }
                imagecopy($background, $tmp, $post_item->nusc_pos_x, $post_item->nusc_pos_y, 0, 0, $post_item->nusc_width, $post_item->nusc_height);
                imagedestroy($tmp);
//            foreach ($rows_items as $row) {
//            foreach ($this->posts_nuspriteitem as $post_item) {
                $Item = new Nusprite_Item(array(
                    'item_slug' => $post_item->post_name,
                    'sprite_slug' => $this->sprite_slug,
                    'post_id' => $post_item->ID,
                    'pos_x' => $post_item->nusc_pos_x,
                    'pos_y' => $post_item->nusc_pos_y,
                    'width' => $post_item->nusc_width,
                    'height' => $post_item->nusc_height,
                    'credits' => $post_item->credits,
                    'extra' => $post_item->extra
                        ), $this);
                $Item->insert();
                $this->Items[$post_item->post_name] = $Item;
            }

            imagepng($background, $newimg_path);
            imagedestroy($background);

            clearstatcache();
            $filesize = filesize($newimg_path);

            $this->update(array(
                'current_version' => $this->new_version,
                'current_fingerprint' => $this->current_fingerprint,
                'current_url' => $newimg_url,
                'current_width' => $this->new_width,
                'current_height' => $this->new_height,
                'current_filesize' => $filesize,
                'current_path' => substr($md5, 0, 3),
            ));
            if ($this->versioning) {
                $wpdb->query("INSERT IGNORE INTO `" . $wpdb->prefix . "nusprite_versions` 
                SET 
                    sprite_slug='" . addslashes($this->sprite_slug) . "',             
                    version=" . $this->new_version . ",
                    path='" . $md5[0] . $md5[1] . $md5[2] . "',
                    filesize='" . $filesize . "',
                    date_insert=now()
            ;");
            } else {
                $wpdb->query("UPDATE `" . $wpdb->prefix . "nusprite_versions`
                SET 
                    filesize='" . $filesize . "',
                    date_insert=now()                    
                WHERE 
                    sprite_slug='" . addslashes($this->sprite_slug) . "'
                    AND version='" . $this->new_version . "'
;");
            }
        }

        function get_real_post_type() {
            if ($this->custom_post_type != '') {
                return $this->custom_post_type;
            } else {
                return 'nusci_' . $this->sprite_slug;
            }
        }

        function hydrate_posts_nuspriteitem() {
            if ($this->posts_nuspriteitem !== null) {
                return;
            }
            $this->posts_nuspriteitem = array();
            $args = array(
                'post_type' => $this->get_real_post_type(),
                'posts_per_page' => -1
            );
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()) :
                while ($the_query->have_posts()) :
                    $the_query->the_post();
                    $this->posts_nuspriteitem[$the_query->post->ID] = $the_query->post;
                endwhile;
                wp_reset_postdata();
            else:
            endif;
        }

    }

}