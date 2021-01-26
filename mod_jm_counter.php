<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Counter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Counter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Counter. If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$version = new JVersion;
$jversion = '3';
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
		$jversion = '2.5';
}

$doc = JFactory::getDocument();

$moduleId = $module->id;
$id = 'jmm-counter-' . $moduleId;

$data = trim( $params->get('items') );

$json_data = ( !empty($data) ) ? json_decode($data) : false;

if ($json_data === false) {
	echo JText::_('MOD_JM_COUNTER_NO_ITEMS');
	return false;
}

$field_pattern = '#^jform\[params\]\[([a-zA-Z0-9\_\-]+)\]#i';

$output_data = array();
foreach ($json_data as $item) {
	$item_obj = new stdClass();
	foreach($item as $field) {
		if (preg_match($field_pattern, $field->name, $matches)) {
			$attr = $matches[1];
			if (isset($item_obj->$attr)) {
				if (is_array($item_obj->$attr)) {
					$temp = $item_obj->$attr;
					$temp[] = $field->value;
					$item_obj->$attr = $temp;
				} else {
					$temp = array($item_obj->$attr);
					$temp[] = $field->value;
					$item_obj->$attr = $temp;
				}
			} else {
				$item_obj->$attr = $field->value;
			}
		}
	}
	$output_data[] = $item_obj;
}

$elements = count($output_data);

if( $elements < 1 ) {
	echo JText::_('MOD_JM_COUNTER_NO_ITEMS');
	return false;
}

$load_fa = $params->get('load_fontawesome', 0);

if( $load_fa == 1 ) {
	$doc->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
}

$theme = $params->get('theme', 1);
$theme_class = ( $theme == 1 ) ? 'default' : 'override';

if( $theme == 1 ) { //default
	$doc->addStyleSheet(JURI::root(true).'/modules/mod_jm_counter/assets/default.css');
}

$style = '';
$i_s = 0;

foreach($output_data as $item) {
	$i_s++;
	if( !empty($item->color) ) {
		$style .= '#' . $id . ' .item-' . $i_s . ' .jmm-icon {'
						. 'color: ' . $item->color . ';'
						. '}';
	}
}

if( !empty($style) ) {
	$doc->addStyleDeclaration($style);
}


JHtml::_('jquery.framework', true);
$doc->addScript(JURI::root(true).'/modules/mod_jm_counter/assets/jquery.countTo.js');
$doc->addScript(JURI::root(true).'/modules/mod_jm_counter/assets/jquery.waypoints.min.js');

$opt = '';
$format = $params->get('format', 0);
$separator = $params->get('separator', '.');

if( $format ) {
	$opt .= '{
		formatter: function (value, options) {
			return value.toFixed(options.decimals).toString().split(".").join("'. $separator .'").replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1'. $separator .'");
		}
	}';
} else {
	$sep_fn = ( $separator != '.' ) ? '.toString().replace(".", "'. $separator .'")' : '';
	$opt .= '{
		formatter: function (value, options) {
			return value.toFixed(options.decimals)' . $sep_fn . ';
		}
	}';
}

$doc->addScriptDeclaration('
	jQuery(document).ready(function(){
		var waypoint = jQuery(\'#' . $id . '\').waypoint(function() {
			jQuery(\'#' . $id . ' .jmm-timer\').countTo(' . $opt . ');
			this.destroy();
		}, {
			offset: \'bottom-in-view\'
		});
	});
');

$span_size = $params->get('span_size', '1');

$mod_class_suffix = $params->get('moduleclass_sfx', '');

require JModuleHelper::getLayoutPath('mod_jm_counter', $params->get('layout', 'default'));

?>
