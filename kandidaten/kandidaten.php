<?php


class PT_kandidaten {

    private static $wk;
    private static $orte;
    private static $kandidaten;
    private static $kandidatenNW;
    private static $wahlen = array("btw" => "Bundestagswahl");
    private static $incJS = array();

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
                'post_type'=> 'pt_kandidaten',
                'posts_per_page' => -1
                );              

            $the_query = new WP_Query( $args );
            $data = $the_query->posts;
            foreach ($data as $key => $val) {
                $wks = array();
                $uus = array();
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

    function my_admin_menu() { 
        add_submenu_page('edit.php?post_type=pt_kandidaten', 'Informationen', 'Informationen', 'manage_options', 'pt_kandidaten_menu', array(__CLASS__, "my_admin_menu_content")); 
    }

    function my_admin_menu_content() { 
        if (!current_user_can('manage_options'))
        {
          wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        echo '<div class="wrap">';

        // header

        echo "<h2>" . __( 'Kandidaten', 'pt_kandidaten' ) . "</h2>";
?>
    <p>Dieses Plugin stellt eine tabellarische Übersicht von Kandidaten sowie einen Wahlkreisfindet zur Verfügung.</p>
<h3>Tabelle</h3>
<p>Die tabellarische Übersicht kann mit dem Shortcode <pre>[pt-kandidaten-tabelle wahl="btw" sort="wknr" uu="true"]</pre> eingebunden werden. Der Parameter "sort" akzeptiert die Werte "wknr" sowie "name". Der optionale Parameter "uu" aktiviert eine zusätzliche Spalte für Unterstützerformulare.</p>
<h3>Wahlkreisfinder</h3>
<p>Der Wahlkreisfinder wird über den Shortcode <pre>[pt-kandidaten-wkf wahl="btw"]</pre> eingebunden.</p> <p>Mit dem zusätzlichen Parameter "start" kann ein bestimmtes Gebiet vorausgewählt werden. <code>[pt-kandidaten-wkf wahl="btw" start="Baden-Württemberg"]</code> deaktiviert beispielsweise die Länderauswahl und zeigt direkt die Untergebiete von Baden-Württemberg an.</p>
<p>Mit den Parametern "text_nok", "text_uu" sowie "text_nouu" können verschiedene Texte vorgegeben werden. Dabei werden folgende Variablen ersetzt:<ul><li><code>{wknr}</code> – Die Nummer des Wahlkreises</li><li><code>{wkname}</code> – Der Name des Wahlkreises</li><li><code>{kandidat}</code> – Der Name des Kandidaten</li><li><code>{uu}</code> – Der Link zum Unterstützerformular</li></ul></p>
<?php        

        echo "</div>";
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
              'menu_icon'   => 'dashicons-admin-users',
              'register_meta_box_cb' => array(__CLASS__, 'posttype_add_fields')
            )
        );
        add_action( 'save_post_pt_kandidaten', array(__CLASS__, 'posttype_save_data'));
        add_filter('manage_pt_kandidaten_posts_columns' , array(__CLASS__, 'add_columns'));
        add_action( 'manage_posts_custom_column' , array(__CLASS__, 'custom_columns'), 10, 2 );
    }
    
        function add_columns($columns) {
            return array_merge($columns, 
                      array('wk' => __('Wahlkreis')));
        }

    function custom_columns( $column, $post_id ) {
	    switch ( $column ) {
		    case 'wk':
                foreach (array_keys(self::$wahlen) as $wahl) {
                    $wknames = self::getWk($wahl);
                    $wknr = get_post_meta( $post_id, '_wk_'.$wahl, true ); 
                    if ($wknr && ($wknr !== 0)) $wk[] = $wknr." – ".$wknames->$wknr;
            
                }
			    echo implode(", ", $wk);
			    break;
	    }
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
        if (count(self::$incJS) == 0) return;
        $content = "<script type=\"text/javascript\">var wk = []; var orte = []; var kandidaten = [];";
        foreach (array_keys(self::$incJS) as $wahl) {
            $content .= "\nwk['".$wahl."'] = ".JSON_encode(self::getWk($wahl)).";";
            $content .= "\norte['".$wahl."'] = ".JSON_encode(self::getOrte($wahl)).";";
            $content .= "\nkandidaten['".$wahl."'] = ".JSON_encode(self::getKandidaten($wahl)).";";
        }
        $content .= "</script>";
        echo $content;
        include("js.php");
    }

    function shortcodeWKF($atts) {
        reset(self::$wahlen);
        if (isset($atts['wahl']) && in_array($atts['wahl'], array_keys(self::$wahlen))) $wahl = $atts['wahl']; else $wahl = key(self::$wahlen);
        self::$incJS[$wahl] = true;
        if (isset($atts['start'])) $start = $atts['start']; else $start = "";
        if (isset($atts['text_nok'])) $text_nok = $atts['text_nok']; else $text_nok = "Im Wahlkreis <strong>{wknr} – {wkname}</strong> wurde leider noch kein Kandidat aufgestellt.";
        if (isset($atts['text_nouu'])) $text_nouu = $atts['text_nouu']; else $text_nouu = "Im Wahlkreis <strong>{wknr} – {wkname}</strong> kandidiert <strong>{kandidat}</strong>. Es sind aber leider noch keine Unterstützerformulare verfügbar.";
        if (isset($atts['text_uu'])) $text_uu = $atts['text_uu']; else $text_uu = "Im Wahlkreis <strong>{wknr} – {wkname}</strong> kandidiert <strong>{kandidat}</strong>. Du kannst eine <a href=\"{uu}\">Unterstützeruntschrift</a> abgeben.";
        $content = "<div class=\"pt-kandidaten-wkf\" data-wahl=\"".esc_attr($wahl)."\" data-start=\"".esc_attr($start)."\" data-text-nok=\"".esc_attr($text_nok)."\" data-text-nouu=\"".esc_attr($text_nouu)."\" data-text-uu=\"".esc_attr($text_uu)."\"></div>";
        return $content;
    }

    function shortcodeTabelle($atts) {
        reset(self::$wahlen);
        if (isset($atts['wahl']) && in_array($atts['wahl'], array_keys(self::$wahlen))) $wahl = $atts['wahl']; else $wahl = key(self::$wahlen);
        $wknames = self::getWk($wahl);

        $content = "<table><tr><th>Wahlkreis</th><th>Kandidat</th>";   
        if (isset($atts['uu'])) $content .= "<th></th>";
        $content .= "</tr>";

        $kandidaten = array_values(self::getKandidaten($wahl));

        if (isset($atts['sort'])) {        
            switch ($atts['sort']) {
                case 'name':
                    foreach ($kandidaten as $k) {
                        $sar[] = $k['name'];
                    }
                    break;
                case 'wknr':
                default:
                    $sar = array_keys(self::getKandidaten($wahl));
            }
        } else {
            $sar = array_keys(self::getKandidaten($wahl));
        }
        array_multisort($sar, $kandidaten);
        foreach ($kandidaten as $kandidat) {
            $wk = $kandidat['wk'][$wahl];
            $wkname = $wknames->$wk;
        $content .= "<tr>
<td>{$wk} – <strong>{$wkname}</strong></td>
<td>{$kandidat['name']}</td>";
            if (isset($atts['uu'])) {
                if (isset($kandidat['uu'][$wahl])) $content .= "<td><a href=\"".esc_attr($kandidat['uu'][$wahl])."\">Unterstützerformular für $wkname</a></td>";
                else $content .= "<td><small>Noch kein Formular verfügbar</small></td>";
            }
            $content .= "</tr>";
        }
        $content .= "</table>";

        return $content;
    }

}

add_action( 'init', array('PT_kandidaten', 'create_posttype') );
add_action( 'wp_footer', array('PT_kandidaten', 'incJS') );
add_shortcode( "pt-kandidaten-wkf", array("PT_kandidaten", "shortcodeWKF"));
add_shortcode( "pt-kandidaten-tabelle", array("PT_kandidaten", "shortcodeTabelle"));
add_action('admin_menu', array("PT_kandidaten", 'my_admin_menu')); 

?>
