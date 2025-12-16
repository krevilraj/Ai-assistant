jQuery(function ($) {
    const $form = $("#ai-theme-generator-form");

    // -----------------------------
    // Helpers
    // -----------------------------
    const isNewThemeMode = () =>
        $('input[name="ai_tg_target_mode"]:checked').val() === "new";

    const slugify = (str) =>
        (str || "")
            .toString()
            .trim()
            .toLowerCase()
            .replace(/['"]/g, "")
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/-+/g, "-")
            .replace(/^-|-$/g, "");

    const showError = ($field, msg) => {
        $field.addClass("ai-field-error");
        const $wrap = $field.closest(".ai-field");
        let $m = $wrap.find(".ai-field-error-msg");
        if (!$m.length) $m = $('<div class="ai-field-error-msg"></div>').appendTo($wrap);
        $m.text(msg).show();
    };

    const clearError = ($field) => {
        $field.removeClass("ai-field-error");
        $field.closest(".ai-field").find(".ai-field-error-msg").hide().text("");
    };

    const clearAllErrors = () => {
        $(".ai-field-error").removeClass("ai-field-error");
        $(".ai-field-error-msg").hide().text("");
    };

    // -----------------------------
    // Toggle UI
    // -----------------------------
    const toggleNewThemeFields = () => {
        $(".ai-tg-new-theme-options").toggle(isNewThemeMode());
        clearAllErrors();
    };

    toggleNewThemeFields();
    $(document).on("change", 'input[name="ai_tg_target_mode"]', toggleNewThemeFields);

    // -----------------------------
    // Auto-fill slug + text-domain (until user edits)
    // -----------------------------
    const $name = $("#ai_tg_theme_name");
    const $slug = $("#ai_tg_theme_slug");
    const $domain = $("#ai_tg_text_domain");

    $slug.data("touched", false);
    $domain.data("touched", false);

    $slug.on("input", () => $slug.data("touched", true));
    $domain.on("input", () => $domain.data("touched", true));

    $name.on("input", function () {
        const s = slugify($(this).val());
        if (!$slug.data("touched")) $slug.val(s);
        if (!$domain.data("touched")) $domain.val(s);
    });

    // -----------------------------
    // Validation
    // -----------------------------
    const validateZip = () => {
        const $zip = $("#ai_tg_zip");
        const files = $zip[0]?.files || [];

        if (!files.length) {
            showError($zip, "Please select a ZIP file.");
            return false;
        }

        const file = files[0];
        const ok = (file.name || "").toLowerCase().endsWith(".zip");
        if (!ok) {
            showError($zip, "Only .zip files are allowed.");
            return false;
        }

        clearError($zip);
        return true;
    };

    const validateNewTheme = () => {
        let ok = true;

        const nameVal = ($name.val() || "").trim();
        const domainVal = ($domain.val() || "").trim();
        const slugVal = ($slug.val() || "").trim();

        // Theme Name required
        if (!nameVal) {
            showError($name, "Theme name is required.");
            ok = false;
        } else {
            clearError($name);
        }

        // Text domain required + simple format
        if (!domainVal) {
            showError($domain, "Text domain is required.");
            ok = false;
        } else if (!/^[a-z0-9-]+$/.test(domainVal)) {
            showError($domain, "Use lowercase letters, numbers, and hyphens only.");
            ok = false;
        } else {
            clearError($domain);
        }

        // Slug optional but if present must be valid
        if (slugVal && !/^[a-z0-9-]+$/.test(slugVal)) {
            showError($slug, "Use lowercase letters, numbers, and hyphens only.");
            ok = false;
        } else {
            clearError($slug);
        }

        return ok;
    };

    // Submit
    $form.on("submit", function (e) {
        clearAllErrors();

        let ok = validateZip();
        if (isNewThemeMode()) ok = validateNewTheme() && ok;

        if (!ok) {
            e.preventDefault();
            const $first = $(".ai-field-error").first();
            if ($first.length) {
                $("html, body").animate({ scrollTop: $first.offset().top - 120 }, 200);
                $first.trigger("focus");
            }
        }
    });

    // Clear error as user types/changes
    $(document).on("input change", ".ai-field :input", function () {
        clearError($(this));
    });
});


(function($){
    function slugify(str){
        return (str || "")
            .toString()
            .trim()
            .toLowerCase()
            .replace(/['"]/g, "")
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/-+/g, "-")
            .replace(/^-|-$/g, "");
    }

    $(document).ready(function () {

        // Mark slug as "touched" if user edits it manually
        $("#ai-tg-pages-table").on("input", ".ai-page-slug", function () {
            $(this).data("touched", true);
        });

        // Auto-update slug when title changes (only if slug not touched)
        $("#ai-tg-pages-table").on("input", ".ai-page-title", function () {
            const $row = $(this).closest(".ai-page-row");
            const $slug = $row.find(".ai-page-slug");

            if ($slug.data("touched")) return;

            $slug.val(slugify($(this).val()));
        });

    });
})(jQuery);
