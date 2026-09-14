#!/bin/bash
# WPOPT Theme Setup Script for WP-CLI
# Run this script from the WordPress root directory

echo "Setting up WPOPT theme..."

# Activate theme
wp theme activate wpopt

# Check if Contact Form 7 is installed
if wp plugin is-installed contact-form-7; then
    echo "Contact Form 7 is installed."
    
    # Activate if not active
    if ! wp plugin is-active contact-form-7; then
        wp plugin activate contact-form-7
    fi
    
    # Create intake form
    FORM_ID=$(wp post create --post_type=wpcf7_contact_form --post_status=publish --post_title="Website Intake Form" --porcelain 2>/dev/null)
    
    if [ ! -z "$FORM_ID" ]; then
        echo "Created Contact Form 7 form with ID: $FORM_ID"
        echo "Please configure the form manually in WordPress admin:"
        echo "1. Go to Contact > Contact Forms"
        echo "2. Edit the 'Website Intake Form'"
        echo "3. Use the form code from contact-form-7-intake.txt"
        echo "4. Update mail settings to send to info@alexseif.com"
        echo "5. Update the shortcode in templates/page-intake.html with: [contact-form-7 id=\"$FORM_ID\"]"
    fi
else
    echo "Contact Form 7 is not installed."
    echo "Installing Contact Form 7..."
    wp plugin install contact-form-7 --activate
    echo "Please create the intake form manually using the template in contact-form-7-intake.txt"
fi

# Pages will be created automatically via the theme activation hook
echo "Pages will be created automatically when theme is activated."

echo "Setup complete!"
echo ""
echo "Next steps:"
echo "1. Configure the Contact Form 7 intake form"
echo "2. Visit your site to see the new theme"
echo "3. Customize content as needed"

