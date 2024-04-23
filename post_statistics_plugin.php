<?php

/*
    Plugin Name: Post Statistics
    Description: This plugin counts words, characters and required time to read a post
    Version: 1.0.0
    Author: Arafin Mridul
    Author URI: https://www.linkedin.com/in/arafinmridul
*/

class WordCountPlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'adminSettings'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
    }
    function ifWrap($content)
    {
        if (
            is_main_query() and is_single() and
            (
                get_option('wcp_wordcount', '1') or get_option('wcp_charcount', '1') or get_option('wcp_readtime', '1')
            )
        ) {
            return $this->createHTML($content);
        }
        return $content;
    }
    function createHTML($content)
    {
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

        // get word count because word count and read time will need it
        if (get_option('wcp_wordcount', '1') or get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }
        if (get_option('wcp_wordcount', '1')) {
            $html .= 'This post has ' . $wordCount . ' words.<br>';
        }
        if (get_option('wcp_charcount', '1')) {
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>';
        }
        if (get_option('wcp_readtime', '1')) {
            $html .= 'You need  ' . round($wordCount / 180) . ' minute(s) to read this post.<br>';
        }

        $html .= '</p>';

        // Showing the statistics
        if (get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }
    function settings()
    {
        add_settings_section('wcp_first_section', null, null, 'word-count-slug');
        // Display Location
        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-slug', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0')); // wp built-in functions

        // Headline Text
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-slug', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics')); // wp built-in functions

        // Word Count Check
        add_settings_field('wcp_wordcount', 'Word Count', array($this, 'wordcountHTML'), 'word-count-slug', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')); // wp built-in functions

        // Character Count Check
        add_settings_field('wcp_charcount', 'Character Count', array($this, 'charcountHTML'), 'word-count-slug', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_charcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')); // wp built-in functions

        // Read Time Check
        add_settings_field('wcp_readtime', 'Read Time', array($this, 'readtimeHTML'), 'word-count-slug', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')); // wp built-in functions
    }
    function sanitizeLocation($input)
    {
        if ($input != '0' and $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Inappropriate Display Location!');
            return get_option('wcp_location');
        }
        return $input;
    }
    function readtimeHTML()
    { ?>
        <input type="checkbox" name="wcp_readtime" value="1" <?php checked(get_option('wcp_readtime'), '1') ?>>
    <?php }
    function charcountHTML()
    { ?>
        <input type="checkbox" name="wcp_charcount" value="1" <?php checked(get_option('wcp_charcount'), '1') ?>>
    <?php }
    function wordcountHTML()
    { ?>
        <input type="checkbox" name="wcp_wordcount" value="1" <?php checked(get_option('wcp_wordcount'), '1') ?>>
    <?php }
    function headlineHTML()
    { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
    <?php }
    function locationHTML()
    { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of post</option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>Ending of post</option>
        </select>
    <?php }
    function adminSettings()
    {
        add_options_page('Word Count Title', 'Settings Title', 'manage_options', 'word-count-slug', array($this, 'settingsHTML'));
    }
    function settingsHTML()
    { ?>
        <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('wordcountplugin'); // group name
                do_settings_sections('word-count-slug');
                submit_button();
                ?>
            </form>
        </div>
    <?php }

}

$wordCountPlugin = new WordCountPlugin();



