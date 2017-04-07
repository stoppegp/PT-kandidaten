<?php


class PT_kandidaten {

    private static $wk;
    private static $orte;
    private static $kandidaten;
    private static $kandidatenNW;
    private static $wahlen = array("btw" => "Bundestagswahl");
    private static $addJS = "";

    static function getWk($wahl) {
        if (!in_array($wahl, array_keys(self::$wahlen))) return false;
        if (!is_array(self::$wk[$wahl])) { 
            $data = file_get_contents(plugin_dir_path(__FILE__)."wk-".$wahl.".json");
            self::$wk[$wahl] = JSON_decode($data);
        }
        return self::$wk[$wahl];
    }
    static function getOrte($wahl) {
        if (!in_array($wahl, array_keys(self::$wahlen))) return false;
        if (!is_array(self::$orte[$wahl])) { 
            $data = file_get_contents(plugin_dir_path(__FILE__)."orte-".$wahl.".json");
            self::$orte[$wahl] = JSON_decode($data);
        }
        return self::$orte[$wahl];
    }

    static function getKandidaten($wahl) {
        if (!in_array($wahl, array_keys(self::$wahlen))) return false;

        if (!is_array(self::$kandidaten)) { 
            $args = array(
                'post_type'=> 'pt_kandidaten'
                );              

            $the_query = new WP_Query( $args );
            $data = $the_query->posts;
            $wks = array();
            $uus = array();
            foreach ($data as $key => $val) {
                self::$kandidaten[$key]['name'] = $val->post_title;
                foreach (array_keys(self::$wahlen) as $wahl0) {
                    $wk = get_post_meta($val->ID, '_wk_'.$wahl0, true);
                    if (isset($wk) && ($wk != 0) && ($wk != "")) {
                        $wks[$wahl0] = $wk;
                        self::$kandidatenNW[$wahl0][$wk] = &self::$kandidaten[$key];
                    }
                    $uu = get_post_meta($val->ID, '_uu_'.$wahl0, true);
                    if (isset($uu) && ($uu != "")) {
                        $uus[$wahl0] = $uu;
                    }
                }
                self::$kandidaten[$key]['wk'] = $wks;
                self::$kandidaten[$key]['uu'] = $uus;
            }
        }
        return self::$kandidatenNW[$wahl];
    }

    function create_posttype() {
        register_post_type( 'pt_kandidaten',
            array(
              'labels' => array(
                'name' => __( 'Kandidaten' ),
                'singular_name' => __( 'Kandidat' ),
                'add_new'   => __( 'Neuen Kandidaten hinzufügen' )
              ),
              'public' => false,
              'show_ui' => true,
              'rewrite' => array('slug' => 'kandidat'),
              'supports' => array('title', 'thumbnail'),
              'register_meta_box_cb' => array(__CLASS__, 'posttype_add_fields')
            )
        );
        add_action( 'save_post_pt_kandidaten', array(__CLASS__, 'posttype_save_data'));
    }
    
    function posttype_add_fields() {
        foreach (self::$wahlen as $wahl => $wahlname) {
    	    add_meta_box('pt_kandidaten_wk_'.$wahl, 'Wahlkreis '.$wahlname, array(__CLASS__, 'posttype_add_fieldcontent'), 'pt_kandidaten', 'normal', 'high', array("wahl" => $wahl));
    	    add_meta_box('pt_kandidaten_uu_'.$wahl, 'Link zum Unterstützerformular '.$wahlname, array(__CLASS__, 'posttype_add_fieldcontent_uu'), 'pt_kandidaten', 'normal', 'high', array("wahl" => $wahl));
        }
    }
    function posttype_add_fieldcontent($post, $args) {
        $wahl = $args['args']['wahl'];

        wp_nonce_field( basename( __FILE__ ), "pt_kandidaten_wk_".$wahl."_nonce" );

        $curval = get_post_meta($post->ID, '_wk_'.$wahl, true);

        $content = "<select name=\"pt_kandidaten_wk_".$wahl."\">";
        $content .= "<option value=\"0\">– kein WK –</option>";
        foreach (self::getWk($wahl) as $wknr => $wkname) {
            $content .= "<option ".selected($curval, $wknr, false)." value=\"".$wknr."\">".$wknr." – ".$wkname."</option>";
        }
        $content .= "</select>";
        echo $content;
    }
    function posttype_add_fieldcontent_uu($post, $args) {
        $wahl = $args['args']['wahl'];

        wp_nonce_field( basename( __FILE__ ), "pt_kandidaten_uu_".$wahl."_nonce" );

        $curval = get_post_meta($post->ID, '_uu_'.$wahl, true);

        $content = "<input type=\"text\" name=\"pt_kandidaten_uu_".$wahl."\" value=\"".esc_attr($curval)."\">";
        echo $content;
    }

    function posttype_save_data( $post_id ){
        // Check the user's permissions.
        if ( ! current_user_can( 'edit_post', $post_id ) ){
            return;
        }

        foreach (self::$wahlen as $wahl => $wahlname) {
            // verify meta box nonce
            if ( !isset( $_POST["pt_kandidaten_wk_".$wahl."_nonce"] ) || !wp_verify_nonce( $_POST["pt_kandidaten_wk_".$wahl."_nonce"], basename( __FILE__ ) ) ){
	            continue;
            }
            if ( isset( $_POST['pt_kandidaten_wk_'.$wahl] ) ) {
	            update_post_meta( $post_id, '_wk_'.$wahl, sanitize_text_field( $_POST['pt_kandidaten_wk_'.$wahl] ) );
            }
        }

        foreach (self::$wahlen as $wahl => $wahlname) {
            // verify meta box nonce
            if ( !isset( $_POST["pt_kandidaten_uu_".$wahl."_nonce"] ) || !wp_verify_nonce( $_POST["pt_kandidaten_uu_".$wahl."_nonce"], basename( __FILE__ ) ) ){
	            continue;
            }
            if ( isset( $_POST['pt_kandidaten_uu_'.$wahl] ) ) {
	            update_post_meta( $post_id, '_uu_'.$wahl, sanitize_text_field( $_POST['pt_kandidaten_uu_'.$wahl] ) );
            }
        }
    }


    function incJS() {
        $content = "<script type=\"text/javascript\">var wk = []; var orte = []; var kandidaten = [];";
        foreach (self::$wahlen as $wahl => $wahlname) {
            $content .= "\nwk['".$wahl."'] = ".JSON_encode(self::getWk($wahl)).";";
            $content .= "\norte['".$wahl."'] = ".JSON_encode(self::getOrte($wahl)).";";
            $content .= "\nkandidaten['".$wahl."'] = ".JSON_encode(self::getKandidaten($wahl)).";";
        }
        $content .= "\n".self::$addJS;
        $content .= "</script>";
        echo $content;
        include("js.php");
    }

    function shortcodeWKF($atts) {
        reset(self::$wahlen);
        if (isset($atts['wahl']) && in_array($atts['wahl'], array_keys(self::$wahlen))) $wahl = $atts['wahl']; else $wahl = key(self::$wahlen);
        if (isset($atts['start'])) $start = $atts['start']; else $start = "";
        if (isset($atts['text_nok'])) $text_nok = $atts['text_nok']; else $text_nok = "Im Wahlkreis <strong>{wknr} – {wkname}</strong> wurde leider noch kein Kandidat aufgestellt.";
        if (isset($atts['text_nouu'])) $text_nouu = $atts['text_nouu']; else $text_nouu = "Im Wahlkreis <strong>{wknr} – {wkname}</strong> kandidiert <strong>{kandidat}</strong>. Es sind aber leider noch keine Unterstützerformulare verfügbar.";
        if (isset($atts['text_uu'])) $text_uu = $atts['text_uu']; else $text_uu = "Im Wahlkreis <strong>{wknr} – {wkname}</strong> kandidiert <strong>{kandidat}</strong>. Du kannst eine <a href=\"{uu}\">Unterstützeruntschrift</a> abgeben.";
        $content = "<div class=\"pt-kandidaten-wkf\" data-wahl=\"".esc_attr($wahl)."\" data-start=\"".esc_attr($start)."\" data-text-nok=\"".esc_attr($text_nok)."\" data-text-nouu=\"".esc_attr($text_nouu)."\" data-text-uu=\"".esc_attr($text_uu)."\"></div>";
        return $content;
    }

}

add_action( 'init', array('PT_kandidaten', 'create_posttype') );
add_action( 'wp_footer', array('PT_kandidaten', 'incJS') );
add_shortcode( "pt-kandidaten-wkf", array("PT_kandidaten", "shortcodeWKF"));
?>
