<?php
if (!function_exists("PT_main_menu")) {
	function PT_main_menu() {
		global $PT_infos;
		echo '<div class="wrap">';
		if (is_array($PT_infos) && (count($PT_infos) > 0)) {
			foreach ($PT_infos as $val) {
				echo '<h2>'.$val['name'].'</h2>';
				echo '<p>'.$val['desc'].'</p>';
				
			}
		}
		echo '</div>';
	}
}