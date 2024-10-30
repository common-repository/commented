<?php
/**
 * Plugin Name: Commented
 * Description: Comment on web & mobile projects intuitively, collaborate seamlessly!
 * Version: 1.0.0
 * Author: Commented
 * Author URI: https://commented.io
 *
 * @version   1.0.0
 * @package   Commented
 * @author    Efe Kahyaoğlu <efe.kahyaoglu@digieggs.com>
 * @copyright 2023-2024 Commented
 * @license   GPL-2.0-or-later https://spdx.org/licenses/GPL-2.0-or-later.html
 *
 */

// NOTES FOR DEVELOPERS
// 1. Every function name should start with commented prefix to avoid conflicts with other plugins.
// 2. We are using the WordPress Settings API to create the settings page. You can find the documentation here: https://developer.wordpress.org/plugins/settings/settings-api/
// 3. We are using the builtin WordPress admin notices to display the activation notice. You can find the documentation here: https://developer.wordpress.org/reference/hooks/admin_notices/
// 4. This plugin is developed really quickly. Please feel free to refactor the code and make it better. We are open to any suggestions.
// 5. There is a flicker issue when the page is mounted. This is due wp tries to move the notice under an h1 element (https://core.trac.wordpress.org/ticket/45186). At the moment without using the builtin WordPress admin notices, we can't fix this issue.

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function commented_setting_init()
{
    register_setting("commented", "commented_options");
    add_settings_section(
        "commented_section_activate",
        __("Commented", "commented"),
        "",
        "commented"
    );

    add_settings_field(
        "commented_field_activate",
        __("commented", "commented"),
        "commented_field_active_cb",
        "commented",
        "commented_section_activate",
        array(
            "label_for" => "commented_field_activate",
            "class" => "hidden",
            "commented_custom_data" => "commented_custom",
        )
    );
}

function commented_options_page_html()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    ?>
    <div class="wrap">
        <form action="options.php" method="post">
            <?php
            settings_fields("commented");
            do_settings_sections("commented");
            ?>
        </form>
        <h1 class="how-to-use-section__title">
            How to use Commented?
        </h1>
        <ol>
            <li>
                Go to your website
            </li>
            <li>
                Login to your Commented account. If you don’t have an Commented account yet, please <a
                    href="https://app.commented.io" target="_blank">click here</a>
            </li>
            <li>
                Click on commenting activation icon.
            </li>
            <li>
                Simply click where you want to leave a comment.
            </li>
            <li>
                You can annotate on the image if you need
            </li>
            <li>
                You can improve texts using AI enhancement feature.
            </li>
            <li>
                Also, you can list the comments on your website or visit your dashboard for more details.
            </li>
        </ol>
    </div>
    <?php
}

function commented_field_active_cb($args)
{
    $commented_options = get_option("commented_options");
    $commented_label_for = esc_attr($args["label_for"]);
    $commented_data_custom = esc_attr($args["commented_custom_data"]);
    $commented_activated = isset($commented_options["commented_field_activate"]) && $commented_options["commented_field_activate"] == "on";

    ?>
    <input type="hidden" id="<?php echo esc_html($commented_label_for) ?>" data-custom="<?php echo esc_html($commented_data_custom) ?>"
        name="commented_options[<?php echo esc_html($commented_label_for) ?>]" value="<?php echo $commented_activated ? "off" : "on" ?>" />
    <?php
}

function commented_admin_notice()
{
    $commented_options = get_option("commented_options");
    $commented_activated = isset($commented_options["commented_field_activate"]) && $commented_options["commented_field_activate"] == "on";
    global $pagenow;

    // Get current page with nonce sanitization
    $commented_page = filter_input(INPUT_GET, "page", FILTER_SANITIZE_STRING);

    // Only display the notice on the commented page
    if ($pagenow !== "admin.php" || $commented_page !== "commented") {
        return;
    }
    
    ?>
    <div class="admin-notice__container notice <?php echo $commented_activated ? "notice-success" : "notice-warning" ?>">
        <h3 class="admin-notice__title">
            <?php echo $commented_activated ? "Activated" : "Activation" ?>
        </h3>
        <p class="admin-notice__text">
            <?php echo $commented_activated ? "Commented is active on your website." : "Start using Commented on your website by simply activating it." ?>
            If you don’t have an Commented account yet, please <a href="https://app.commented.io" target="_blank">click
                here</a>
        </p>
        <button class="button <?php echo $commented_activated ? "button-outline-primary" : "button-primary" ?>">
            <?php echo $commented_activated ? "Deactivate" : "Click here to activate" ?>
        </button>
    </div>
    <?php
}

function commented_options_page()
{
    $commented_logo = '<svg width="40" height="40" viewBox="0 0 40 40" fill="transparent" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5 29.5H20C25.2467 29.5 29.5 25.2467 29.5 20C29.5 14.7533 25.2467 10.5 20 10.5C14.7533 10.5 10.5 14.7533 10.5 20V29.5ZM20 4C11.1634 4 4 11.1634 4 20V35.5789C4 36 4 36 4.42105 36H20C28.8366 36 36 28.8366 36 20C36 11.1634 28.8366 4 20 4Z" fill="transparent"/>
    </svg>
    ';

    $commented_encoded_logo = base64_encode($commented_logo);

    add_menu_page(
        "commented",
        "Commented",
        "manage_options",
        "commented",
        "commented_options_page_html",
        "data:image/svg+xml;base64,$commented_encoded_logo",
        20
    );
}

function commented_handle_inject_script()
{
    $commented_options = get_option("commented_options");
    if (isset($commented_options["commented_field_activate"]) && $commented_options["commented_field_activate"] == "on") {
        wp_enqueue_script("commented", "https://cdn.commented.io/latest.js", [], "1.0.0", false);
    }
}

function commented_load_styles()
{
    wp_enqueue_style('commented', plugins_url('commented.css', __FILE__), [], '1.0.0');
}


add_action('admin_enqueue_scripts', 'commented_load_styles');
add_action("admin_init", "commented_setting_init");
add_action("admin_menu", "commented_options_page");
add_action("admin_notices", "commented_admin_notice");
add_action("wp_enqueue_scripts", "commented_handle_inject_script");
