=== ATEAM EVENTS PRO ===
Contributors: ateamdigital
Tags: events, event calendar, event management, events plugin, calendar
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A professional events management plugin for WordPress. Create and showcase events with beautiful layouts.

== Description ==

**ATEAM EVENTS PRO** is a full-featured events management plugin that lets you create, manage, and display events on your WordPress site with a stunning, modern interface.

= Features =

* **Event Management** – Create events with name, date, time, location, and featured images
* **Custom Post Type** – Events are a dedicated post type with full WordPress integration
* **Event Categories** – Organize events into categories for easy filtering
* **Multiple Layouts** – Grid, list, and calendar display options
* **Responsive Design** – Beautiful on all devices
* **Single Event Page** – Dedicated event page with hero image, details sidebar, and share buttons
* **Virtual Events** – Support for online/virtual events with join links
* **Multi-day Events** – Support for events spanning multiple days
* **All-day Events** – Toggle for all-day events
* **Add to Calendar** – One-click Google Calendar integration
* **Social Sharing** – Built-in Facebook, Twitter, WhatsApp, and copy link sharing
* **Admin Dashboard** – Beautiful dashboard with stats and upcoming events overview
* **Sidebar Widget** – Display upcoming events in any widget area
* **Customizable Colors** – Match your brand with custom primary/secondary colors
* **Date/Time Formats** – Configurable date and time display formats
* **SEO Ready** – Clean markup with proper heading hierarchy

= Shortcodes =

* `[ateam_events]` – Display events in a responsive grid
  * Parameters: `count`, `category`, `columns`, `show_past`, `order`
* `[ateam_events_hero_slider]` – Premium full-width sliding events layout
  * Parameters: `count`, `category`
* `[ateam_events_list]` – Display events in a compact list
  * Parameters: `count`, `category`, `show_past`
* `[ateam_events_calendar]` – Display a monthly calendar
  * Parameters: `month`, `year`

= Examples =

`[ateam_events count="6" columns="3"]`
`[ateam_events_list count="5" show_past="yes"]`
`[ateam_events_calendar month="12" year="2024"]`

== Installation ==

1. Upload the `ateam-events-pro` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **ATeam Events** in the admin menu to create your first event
4. Use shortcodes to display events on any page

== Changelog ==

= 1.0.0 =
* Initial release
* Custom post type for events
* Event categories taxonomy
* Meta boxes for date, time, and location
* Grid, list, and calendar shortcodes
* Single event template with hero section
* Archive template with category filters
* Admin dashboard with stats
* Settings page with color customization
* Sidebar widget for upcoming events
* Social sharing and Google Calendar integration
