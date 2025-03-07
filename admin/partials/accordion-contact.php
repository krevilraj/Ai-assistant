<div class="postbox custom-field-box closed">
    <div class="postbox-header">
        <h2>Contact form 7</h2>
    </div>
    <div class="inside">
        <p>if you select the form and click the one of the button it will replace the text.</p>
        <?php
        // ✅ Fetch all Contact Form 7 forms
        $forms = get_posts([
            'post_type'   => 'wpcf7_contact_form',
            'numberposts' => -1
        ]);

        if ($forms) {
            echo '<div id="cf7-buttons-container">';
            foreach ($forms as $form) {
                $shortcode = '[contact-form-7 id="' . $form->ID . '" title="' . esc_attr($form->post_title) . '"]';
                echo '<button class="cf7-shortcode-btn" data-shortcode="' . esc_attr($shortcode) . '">' . esc_html($form->post_title) . '</button> ';
            }
            echo '</div>';
        } else {
            echo '<p>No Contact Form 7 forms found.</p>';
        }
        ?>
    </div>
</div>