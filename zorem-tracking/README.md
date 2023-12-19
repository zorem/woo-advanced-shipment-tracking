# Integration Guide

## Step 1: Download and Set Up the SDK
1. Download the SDK Zip File from our [official website](link_to_sdk).
2. Unzip the downloaded file and copy the contents into your plugin's main folder.
3. Rename the copied folder to "zorem-tracking".
4. This SDK will provide the necessary functionality for integrating with our services.

## Step 2: Initialize Plugin Instance
Within your plugin's codebase, choose a unique function name that will allow easy access to your plugin's instance.

## Step 3: Configure Admin Menu
Choose how your plugin's main settings page should be accessible:
- **Top-Level Menu:**
  - Select "A custom top-level admin menu."
  - Specify a unique menu slug (e.g., "my-plugin-settings").
- **Sub Menu:**
  - Choose a suitable parent menu (settings, tools, users, plugins, appearance, media, posts, comments, or dashboard).
  - Define a submenu slug (e.g., "my-plugin-settings").
- **No Settings Page:**
  - Choose this option if your plugin doesn't require a settings page.

## Step 4: Add the Snippet Code
Copy the provided code and paste it right after the plugin's header comment in your main plugin's PHP file.
```php
if ( ! function_exists( 'zorem_tracking' ) ) {
    function zorem_tracking() {
        require_once dirname(__FILE__) . '/zorem-tracking/zorem-tracking.php';
        $plugin_name = "Plugin Name";
        $plugin_slug = "plugin-slug";
        $user_id = "10";
        $setting_page_type = "top-level";
        $setting_page_location =  "A submenu under other plugin's top level menu";
        $parent_menu_type = "";
        $menu_slug = "menu-slug";
        $plugin_id = "7";
        $zorem_tracking = WC_Trackers::get_instance( $plugin_name, $plugin_slug, $user_id,
            $setting_page_type, $setting_page_location, $parent_menu_type,  $menu_slug, $plugin_id );

        return $zorem_tracking;
    }
    zorem_tracking();
}

```

## Step 5: Plugin Activation and Connection
1. Reactivate your plugin to ensure the recent changes take effect.
2. After activation, your plugin will display an Opt-In screen.
3. Click the "Allow & Continue" button on the Opt-In screen to establish the initial connection to our API, enabling your plugin to interact with our services seamlessly.
