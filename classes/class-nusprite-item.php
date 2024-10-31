<?php

if (!class_exists('Nusprite_Item')) {

    class Nusprite_Item {

        public $Sprite = null;
        public $Post = null;
        public $post_id = 0;
        public $item_slug = 'not_found';
        public $pos_x = 0;
        public $pos_y = 0;
        public $width = 0;
        public $height = 0;
        public $credits = '';
        public $extra = '';

        /*
         * is_string(mixed) => item_slug
         * is_array(mixed) => row
         */

        function __construct($mixed = false, $Sprite = false) {
            if ($Sprite) {
                $this->Sprite = $Sprite;
            }
            if (is_array($mixed)) {
                $this->item_slug = $mixed['item_slug'];
                $this->post_id = $mixed['post_id'];
                $this->pos_x = $mixed['pos_x'];
                $this->pos_y = $mixed['pos_y'];
                $this->width = $mixed['width'];
                $this->height = $mixed['height'];
                $this->credits = $mixed['credits'];
                $this->extra = $mixed['extra'];
            } elseif (is_string($mixed)) {
                $this->item_slug = $mixed;
            }
        }

        function width($unit = 'px') {
            return $this->width . $unit;
        }

        function height($unit = 'px') {
            return $this->height . $unit;
        }

        function pos_x($unit = 'px') {
            if ($this->pos_x == 0) {
                return "0";
            }
            return '-' . $this->pos_x . $unit;
        }

        function pos_y($unit = 'px') {
            if ($this->pos_y == 0) {
                return "0";
            }
            return '-' . $this->pos_y . $unit;
        }

        function url() {
            if ($this->Sprite === null) {
                return '#not_found';
            } else {
                return $this->Sprite->current_url;
            }
        }

        function post() {
            if ($this->Post === null) {
                $this->Post = get_post($this->post_id);
            }
            return $this->Post;
        }

        function get_vars() {
            return array(
                'post_id' => $this->post_id,
                'slug' => $this->item_slug,
                'url' => $this->url(),
                'width' => $this->width(),
                'height' => $this->height(),
                'pos_x' => $this->pos_x(),
                'pos_y' => $this->pos_y(),
                'credits' => $this->credits,
                'extra' => $this->extra,
            );
        }

        function format($s) {
        echo $this->sformat($s);
        }
        function sformat($s) {
            $vars = $this->get_vars();
            return preg_replace_callback('#\$([a-zA-Z_]+)#', function($matches) use ($vars) {
                $var = $matches[1];
                if (isset($vars[$var])) {
                    return $vars[$var];
                } else {
                    if (substr($var, 0, 6) == 'extra_' && !empty($vars['extra'])) {
                        $key = substr($var, 6);
                        $unserialized = unserialize($vars['extra']);
                        if (isset($unserialized[$key])) {
                            return $unserialized[$key];
                        }
                    }
                    // unknown var...
                    return $var;
                }
            }, $s);
        }

        function style($args = array()) {
            $color = 'transparent';
            if (!empty($args['color'])) {
                $color = $args['color'];
            }
            $repeat = 'no-repeat';
            if (!empty($args['repeat'])) {
                $repeat = $args['repeat'];
            }
            $res = 'width:' . $this->width();
            $res .= ';height:' . $this->height();
            $res .= ';background:transparent';
            $res .= ' url(' . $this->url() . ')';
            $res .= ' ' . $this->pos_x();
            $res .= ' ' . $this->pos_y();
            $res .= ' ' . $repeat;
            return $res;
        }

        function html_div($args = []) {
            return '<div style="' . $this->style($args) . '"></div>';
        }

        function insert() {
            global $wpdb;
            $wpdb->query("INSERT INTO `" . $wpdb->prefix . "nusprite_items` SET 
                    item_slug='" . addslashes($this->item_slug) . "',
                    sprite_slug='" . addslashes($this->Sprite->sprite_slug) . "',
                    post_id='" . $this->post_id . "',
                    pos_x='" . $this->pos_x . "',
                    pos_y='" . $this->pos_y . "',
                    width='" . $this->width . "',
                    height='" . $this->height . "',
                    credits='" . addslashes($this->credits) . "',
                    extra='" . addslashes($this->extra) . "'
;");
        }

    }

}