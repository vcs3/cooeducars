<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The block settings.
 */

defined('MOODLE_INTERNAL') || die();

class block_cb_report_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // Show Block as Card.
        $options = array(
            '0' => 'No',
            '1' => 'Yes',
        );
        $mform->addElement('select', 'config_show_as_card', get_string('show_as_card', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_show_as_card', '0');
        $mform->setType('config_show_as_card', PARAM_RAW); // Not needed for select elements.

		// Escolher o eixo da disciplina
		
		 $options = array(
            'eixo1' => 'Eixo 1 - cor verde',
            'eixo2' => 'Eixo 2 - cor azul',
			'eixo3' => 'Eixo 3- cor vermelha',
        );
		 $mform->addElement('select', 'config_eixo', get_string('eixo', 'block_purity_courses_pro'), $options);
		 $mform->setDefault('config_eixo', 'C3ED2B');
		  $mform->setType('config_show_eixo', PARAM_RAW); 

        // Display Header option.
        $options = array(
            '0' => 'Hide',
            '1' => 'Show',
        );
        $mform->addElement('select', 'config_show_header', get_string('show_header', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_show_header', '1');
        $mform->setType('config_show_header', PARAM_RAW); // Not needed for select elements.

        // Set a custom title.
        $mform->addElement('text', 'config_custom_title', get_string('custom_title', 'block_purity_courses_pro'));
        $mform->setDefault('config_custom_title', '');
        $mform->setType('config_custom_title', PARAM_RAW);


        // Behaviour options.
        $options = array(
            'static' => 'Static',
            'slider' => 'Slider',
        );
        $mform->addElement('select', 'config_behaviour', get_string('behaviour', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_item_per_row', 'slider');
        $mform->setType('config_item_per_row', PARAM_RAW); // Not needed for select elements.

        // Items per row option.
        $options = array(
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
        );
        $mform->addElement('select', 'config_items_per_row', get_string('items_per_row', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_items_per_row', '4');
        $mform->setType('config_items_per_row', PARAM_RAW); // Not needed for select elements.

        // Navigation options.
        $options = array(
            'arrows' => 'Arrows',
            'dots' => 'Dots',
            'both' => 'Both',
            'none' => 'None',
        );
        $mform->addElement('select', 'config_navigation', get_string('navigation', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_navigation', 'arrows');
        $mform->setType('config_navigation', PARAM_RAW); // Not needed for select elements.

        // Autoplay options.
        $options = array(
            'true' => 'Enabled',
            'false' => 'Disabled',
        );
        $mform->addElement('select', 'config_autoplay', get_string('autoplay', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_autoplay', 'true');
        $mform->setType('config_autoplay', PARAM_RAW); // Not needed for select elements.

        // Autoplay Interval.
        $mform->addElement('text', 'config_autoplay_interval', get_string('autoplay_interval', 'block_purity_courses_pro'));
        $mform->setDefault('config_autoplay_interval', '6000');
        $mform->setType('config_autoplay_interval', PARAM_RAW);

        // Pause on Hover.
        $options = array(
            'true' => 'Enabled',
            'false' => 'Disabled',
        );
        $mform->addElement('select', 'config_pause_hover', get_string('pause_hover', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_pause_hover', 'true');
        $mform->setType('config_pause_hover', PARAM_RAW); // Not needed for select elements.


        // Show Image options.
        $options = array(
            '0' => 'Hide',
            '1' => 'Show',
        );
        $mform->addElement('select', 'config_show_image', get_string('show_image', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_show_image', '1');
        $mform->setType('config_show_image', PARAM_RAW); // Not needed for select elements.

        // Set Image height.
        $mform->addElement('text', 'config_image_height', get_string('image_height', 'block_purity_courses_pro'));
        $mform->setDefault('config_image_height', '200px');
        $mform->setType('config_image_height', PARAM_TEXT);

        // Show Title options.
        $options = array(
            '0' => 'Hide',
            '1' => 'Show Full Name',
            '2' => 'Show Short Name',
        );
        $mform->addElement('select', 'config_show_title', get_string('show_title', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_show_title', '1');
        $mform->setType('config_show_title', PARAM_RAW); // Not needed for select elements.

        // Set Title characters limit.
        $mform->addElement('text', 'config_title_limit', get_string('title_limit', 'block_purity_courses_pro'));
        $mform->setDefault('config_title_limit', '50');
        $mform->setType('config_title_limit', PARAM_TEXT);


        // Show Date option.
        $options = array(
            '0' => 'Esconder',
            '1' => 'Mostrar Data de Início do curso',
       
        );
        $mform->addElement('select', 'config_show_date', get_string('show_date', 'block_purity_courses_pro'), $options);
        $mform->setDefault('config_show_date', '2');
        $mform->setType('config_show_date', PARAM_RAW); // Not needed for select elements.


           // Courses
           $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
        );
        $mform->addElement('course', 'config_course1', get_string('courses'), $options);
        $mform->addRule('config_course1', get_string('required'), 'required', null, 'client', false, false);


      // Courses
      $options = array(
        'multiple' => true,
        'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
    );
    $mform->addElement('course', 'config_course2', get_string('courses'), $options);
    $mform->addRule('config_course2', get_string('required'), 'required', null, 'client', false, false);
    
         // Courses
         $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
        );
        $mform->addElement('course', 'config_course3', get_string('courses'), $options);
        $mform->addRule('config_course3', get_string('required'), 'required', null, 'client', false, false);
    
             // Courses
             $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
            );
            $mform->addElement('course', 'config_course4', get_string('courses'), $options);
            $mform->addRule('config_course4', get_string('required'), 'required', null, 'client', false, false);

                 // Courses
           $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
        );
        $mform->addElement('course', 'config_course5', get_string('courses'), $options);
        $mform->addRule('config_course5', get_string('required'), 'required', null, 'client', false, false);

             // Courses
             $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
            );
            $mform->addElement('course', 'config_course6', get_string('courses'), $options);
            $mform->addRule('config_course6', get_string('required'), 'required', null, 'client', false, false);

                 // Courses
           $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
        );
        $mform->addElement('course', 'config_course7', get_string('courses'), $options);
        $mform->addRule('config_course7', get_string('required'), 'required', null, 'client', false, false);

             // Courses
             $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
            );
            $mform->addElement('course', 'config_course8', get_string('courses'), $options);
            $mform->addRule('config_course8', get_string('required'), 'required', null, 'client', false, false);

                 // Courses
           $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_courses', 'block_purity_courses_pro'),
        );
        $mform->addElement('course', 'config_course9', get_string('courses'), $options);
        $mform->addRule('config_course9', get_string('required'), 'required', null, 'client', false, false);
      

// Categorias
        $displaylist = core_course_category::make_categories_list('moodle/course:changecategory');
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_categories', 'block_purity_courses_pro'),
        );
        $mform->addElement('autocomplete', 'config_categories', get_string('coursecategory'), $displaylist,$options);
        $mform->addRule('config_categories', get_string('required'), 'required', null, 'client', false, false);
       // $mform->addHelpButton('config_categories', 'coursecategory');
 
    }
}