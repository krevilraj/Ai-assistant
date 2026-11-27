# AI Assistant for WordPress Developers

**Turn AI-generated code into real WordPress themes, templates and features, right from the WordPress dashboard.**

AI Assistant is a developer-focused WordPress plugin. You generate code or HTML with the AI tool you already use (ChatGPT, Claude, Copilot…), and AI Assistant gives you the editor, snippet library and one-click automation to apply it correctly inside WordPress: no FTP, no file manager, no copy-paste into the wrong file.

> **No AI API calls.** The plugin does not connect to OpenAI or any other AI service. You choose which AI tools you use and what code you apply.

[Project page & screenshots](https://rajkumarsharma.com.np/project/ai-assistant-for-wordpress-developer/) · [Author](https://rajkumarsharma.com.np/)

---

## Who is it for?

- **WordPress developers** using AI to speed up custom theme work
- **Agencies** that want a safer, repeatable way to apply AI-generated code
- **Freelancers** who build custom themes and need automation around templates, CPTs and menus
- **Technical power users** who want more control over theme files

If you already ask AI to "write my `header.php`" or "create a CPT for Portfolio", this plugin is the workflow for applying that code inside WordPress.

---

## Features

### 1. Smart theme editor (CodeMirror)
- Syntax highlighting, line numbers and code folding
- File browser for the active theme, with one-click load and save
- Text search inside files
- Light and dark editor themes

### 2. Snippet library: WordPress, CSS and JavaScript
- **WordPress PHP:** `the_title()`, `the_permalink()`, the Loop, `WP_Query`, menus, logo, ACF field calls and more
- **CSS:** utility blocks and layout helpers
- **JavaScript:** common patterns and helpers

Click a snippet and it is inserted at the cursor, ready to customise.

### 3. Floating "Spark" button and assistant popup
A floating button and an admin-bar item open a tabbed popup (Tasks, Contact Form 7, ACF, Customizer, Miscellaneous) where you can:
- Create a new theme from a clean base template
- Create page templates and link them to new pages
- **Correct header, footer or page templates** by pasting static HTML from your AI tool
- Detect static menus and convert them into WordPress menus

### 4. One-click developer tasks
- Create a theme from a starter structure
- Create **custom post types** with labels, supports, archives and template stubs
- Create menus and assign them to theme locations
- Create and remove **user roles** based on existing roles
- Create template parts in the theme
- Set a page as the homepage
- Reset permalinks and flush rewrite rules
- Toggle `WP_DEBUG` in `wp-config.php`
- Change the site admin email

### 5. Customizer builder
- Add Customizer sections and settings without hand-writing the API code
- Choose from the available field types and set the text domain
- Generate the Customizer code into the theme

### 6. Contact Form 7 helpers
- Toolbar of CF7 field tags (text, email, URL, number, date, dropdown, checkbox, radio, file, submit…)
- Convert form tags into mail-template placeholders
- Quick workflow for translating default form messages for multilingual sites

### 7. Advanced Custom Fields (ACF) helpers
- Fetch the custom fields used on a given URL
- Work with ACF JSON field-group definitions
- Save ACF JSON and location rules where WordPress loads them automatically
- Create and apply custom fields to templates

### 8. Live CSS sync (optional)
A REST endpoint, protected by an API key, that lets an external live-CSS editor push stylesheet changes to the site:

| Method | Endpoint |
|---|---|
| `GET` | `/wp-json/live-css-sync/v1/test` |
| `POST` | `/wp-json/live-css-sync/v1/update` |

The key is stored in the `live_css_sync_api_key` option.

---

## Typical workflow

1. **Ask your AI tool for code or HTML.** For example: "Create header markup for my site", "Generate a CPT for Projects" or "Give me an ACF field group JSON for client details".
2. **Open AI Assistant in WordPress.** Open `header.php`, `functions.php` or any template in the editor, or open the popup for tasks like *Create CPT*, *Correct Header* or *Create Theme*.
3. **Paste and apply.** Paste HTML into *Correct Header/Footer/Page*, fill in the CPT form, or paste ACF JSON into the ACF helper and save.
4. **Review and refine.** Use the snippet library and editor to add loops, menus and ACF field calls.

The result is AI-generated code turned into working WordPress features, with far less manual editing.

---

## Installation

1. Download or clone this repository into `wp-content/plugins/`:
   ```bash
   cd wp-content/plugins
   git clone https://github.com/krevilraj/Ai-assistant.git ai-assistant
   ```
2. Activate **AI Assistant** in **Plugins** in the WordPress admin.
3. Open the assistant from the floating Spark button or the admin bar.

### Recommended plugins
On activation, the plugin suggests these (via TGM Plugin Activation):
- Contact Form 7
- Classic Editor
- Custom Post Type UI
- Post Duplicator

ACF and the ACF Repeater field are bundled in `plugin_files/` for the ACF helpers.

---

## Project structure

```
ai_assistant.php        Plugin bootstrap
admin/                  Admin UI: editor, popup, partials, snippets, CSS/JS
  pages/                Theme editor and create-theme screens
  partials/             Popup tabs (tasks, ACF, CF7, customizer, misc)
  js/snippet/           WordPress PHP, CSS and JS snippet libraries
includes/               Core classes: loader, i18n, activation, live CSS REST API
theme_template/         Base theme used by "Create theme"
plugin_files/           Bundled ACF and ACF Repeater
languages/              Translation template (.pot)
```

---

## Important notes

The plugin **edits theme files directly**, so:
- Use a **child theme** where possible
- Keep **backups or version control** (Git)
- Test on a **staging site** before production
- Use it only on sites where you are an administrator

---

## License

GPL-2.0-or-later. See [LICENSE.txt](LICENSE.txt).

## Author

**Rajkumar Sharma**, Senior WordPress / PHP Developer
[rajkumarsharma.com.np](https://rajkumarsharma.com.np/) · [krevilraj@gmail.com](mailto:krevilraj@gmail.com)

Also by the author: [Krevil User Registration & Login](https://wordpress.org/plugins/krevil-user-registration-login/) on WordPress.org.
