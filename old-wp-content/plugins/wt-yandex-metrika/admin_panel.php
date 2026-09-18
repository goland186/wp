<?php

class WtYandexMetrikaAdmin
{
	var $script_mode_array = array(
		'admin_not_display' => 'Отключать во время авторизации администратора сайта'
		);

	var $script_position_array = array(
		'header' => 'Ближе к началу страницы',
		'footer' => 'Ближе к концу страницы'
		);

	function __construct(){

		// Добавляем страницу настроек в панель администратора
	    add_action('admin_menu', array(&$this, 'admin_menu'));

	    //Добавляем в описание плагина ссылку на справку.
	    add_filter('plugin_row_meta', 'WtYandexMetrikaAdmin::plugin_row_meta', 10, 2);

	    add_action('admin_init', array(&$this, 'plugin_settings'));
	}

	function admin_menu()
	{
	    // Добавляем в меню "Настройки" страницу настроек плагина
		add_options_page(
			'Настройка счетчика "Яндекс Метрика"', 
			'WT Яндекс Метрика', 
			'manage_options', 
			'wt_yandex_metrika/setting.php',
			array(&$this, 'options_page_output')
			);

	}

	// Добавление ссылок к описанию плагина
	public static function plugin_row_meta($meta, $file) {
        if ($file == WtYandexMetrika::basename()) {
        	// Ссылка на страницу справки
            $meta[] = '<a href="options-general.php?page=wt_yandex_metrika/setting.php">Настройки</a>';
        }
        return $meta;
    }

    /**
	 * Создаем страницу настроек плагина
	 */
	
	function options_page_output(){
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title() ?></h2>

			<form action="options.php" method="POST">
				<?php
					settings_fields( 'wt_yandex_metrika_group' );     // скрытые защитные поля
					do_settings_sections( 'wt_yandex_metrika_page' ); // секции с настройками (опциями).
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Регистрируем настройки.
	 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
	 */
	function plugin_settings(){
		
		// параметры: $option_group, $option_name, $sanitize_callback
		register_setting( 'wt_yandex_metrika_group', 'wt_yandex_metrika', array(&$this, 'sanitize_callback') );

		// параметры: $id, $title, $callback, $page
		add_settings_section( 'wt_yandex_metrika', '', array(&$this, 'display_setting_info') , 'wt_yandex_metrika_page' ); 

		$field_params = array(
			'type'      => 'textarea', 
			'id'        => 'script',
			'label_for' => 'script' 
		);
		add_settings_field( 'script', 'Код счетчика', array(&$this, 'display_settings'), 'wt_yandex_metrika_page', 'wt_yandex_metrika', $field_params );
	 
	 	$field_params = array(
			'type'      => 'select', 
			'id'        => 'position',
			'label_for' => 'position' ,
			'desc'      => 'Выберите место расположения счетчика. Яндекс рекомендует распологать счетчик ближе к началу страницы.', // описание
			'vals' => $this->script_position_array
			);
		add_settings_field( 'position', 'Расположение счетчика', array(&$this, 'display_settings'), 'wt_yandex_metrika_page', 'wt_yandex_metrika', $field_params );
	 	 
		$field_params = array(
			'type'      => 'checkbox-group', 
			'id'        => 'mode',
			'label_for' => 'mode' ,
			'desc'      => 'Выберите режим вывода кода счетчика.', 
			'vals' => $this->script_mode_array
			);
		add_settings_field( 'mode', 'Режим работы счетчика', array(&$this, 'display_settings'), 'wt_yandex_metrika_page', 'wt_yandex_metrika', $field_params );

        $field_params = array(
            'type'      => 'checkbox',
            'id'        => 'activate_admin_panel',
            'label_for' => 'activate_admin_panel' ,
            'desc'      => 'Активировать счетчик в панели администратора.',
        );
        add_settings_field( 'activate_admin_panel', '', array(&$this, 'display_settings'), 'wt_yandex_metrika_page', 'wt_yandex_metrika', $field_params );
	}

	// Поясняющее сообщение для секции тестирования и отладки
	function display_setting_info(){
		echo '<p>Для работы счетчика "Яндекс Метрика" вам необходимо получить на <a href="https://metrika.yandex.ru"  target="_blank">сайте "Яндекс Метрика"</a> код, и вставить его ниже в одноименное поле.</p>';
	}

	/*
	 * Функция отображения полей ввода
	 * Здесь задаётся HTML и PHP, выводящий поля
	 */
	function display_settings($args) {
		extract( $args );
	 
		$option_name = 'wt_yandex_metrika';
	 
		$o = get_option( $option_name );
	 
		switch ( $type ) {  
			case 'text':  
				$o[$id] = esc_attr( stripslashes($o[$id]) );
				echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$o[$id]' />";  
				echo (isset($args['desc'])) ? '<br /><span class="description">'.$args['desc'].'</span>' : "";  
			break;
			case 'textarea':  
				$o[$id] = esc_attr( stripslashes($o[$id]) );
				echo "<textarea class='code large-text' cols='30' rows='10' type='text' id='$id' name='" . $option_name . "[$id]'>$o[$id]</textarea>";  
				echo (isset($args['desc'])) ? '<br /><span class="description">'.$args['desc'].'</span>' : ""; 
			break;
			case 'checkbox':
				$checked = (!empty($o[$id]) && $o[$id] == 'on') ? " checked='checked'" :  '';
				echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' $checked /> ";  
				echo (isset($args['desc'])) ? $args['desc'] : "";
				echo "</label>";  
			break;
			case 'checkbox-group':
				echo '<ul style="margin-top: 10px;">';
				foreach($vals as $v=>$l){
					echo '<li>';
					$checked = (isset($o[$id]) && $o[$id] == $v) ? " checked='checked'" :  ''; 
					echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' value='$v' $checked /> ";
					echo ($l != '') ? $l : "";
					echo "</label>";  
					echo '</li>';
				}
				echo '<ul>';
				
			break;
			case 'select':
				echo "<select id='$id' name='" . $option_name . "[$id]'>";
				foreach($vals as $v=>$l){
					$selected = ($o[$id] == $v) ? "selected='selected'" : '';  
					echo "<option value='$v' $selected>$l</option>";
				}
				echo "</select>"; 
				echo (isset($args['desc'])) ? '<br /><span class="description">'.$args['desc'].'</span>' : ""; 
			break;
			case 'radio':
				echo "<fieldset>";
				foreach($vals as $v=>$l){
					$checked = ($o[$id] == $v) ? "checked='checked'" : '';  
					echo "<label><input type='radio' name='" . $option_name . "[$id]' value='$v' $checked />$l</label><br />";
				}
				echo "</fieldset>";  
			break;
			case 'info':  
				echo '<p>'.$text.'</p>';
			break; 
		}
	}

	## Очистка данных
	function sanitize_callback( $options ){ 
		// очищаем
		foreach( $options as $name => & $val ){
			if( $name == 'input' )
				$val = strip_tags( $val );

			if( $name == 'checkbox' )
				$val = intval( $val );
		}

		//die(print_r( $options ));

		return $options;
	}
}
?>