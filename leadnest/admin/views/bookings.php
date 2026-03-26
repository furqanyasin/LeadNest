<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$options  = LeadNest_DB::get_options();
$site_key = $options['site_key'];

// Filter
$filter_status  = sanitize_text_field( wp_unslash( $_GET['status'] ?? 'all' ) );
$valid_statuses = array( 'all', 'pending', 'confirmed', 'cancelled', 'completed' );
if ( ! in_array( $filter_status, $valid_statuses, true ) ) {
    $filter_status = 'all';
}

// Count per status
$status_counts = array();
foreach ( array( 'pending', 'confirmed', 'cancelled', 'completed' ) as $s ) {
    $status_counts[ $s ] = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}leadnest_bookings WHERE status = %s", $s )
    );
}
$status_counts['all'] = array_sum( $status_counts );

// Fetch bookings
if ( $filter_status === 'all' ) {
    $bookings = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}leadnest_bookings ORDER BY booking_date DESC, booking_time DESC LIMIT 100"
    );
} else {
    $bookings = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}leadnest_bookings WHERE status = %s ORDER BY booking_date DESC, booking_time DESC LIMIT 100",
            $filter_status
        )
    );
}

// Availability
$availability = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}leadnest_availability WHERE site_key = %s ORDER BY day_of_week ASC",
        $site_key
    )
);

$days = array(
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
);

// Build lookup by day_of_week
$avail_by_day = array();
foreach ( $availability as $slot ) {
    $avail_by_day[ (int) $slot->day_of_week ] = $slot;
}

$status_labels = array(
    'pending'   => array( 'label' => 'Pending',   'class' => 'ln-badge-new' ),
    'confirmed' => array( 'label' => 'Confirmed', 'class' => 'ln-badge-qualified' ),
    'cancelled' => array( 'label' => 'Cancelled', 'class' => 'ln-badge-closed' ),
    'completed' => array( 'label' => 'Completed', 'class' => 'ln-badge-contacted' ),
);
?>
<div class="wrap ln-wrap">
    <h1 class="ln-page-title">
        <span class="dashicons dashicons-calendar-alt"></span>
        <?php esc_html_e( 'Bookings', 'leadnest' ); ?>
    </h1>

    <!-- Availability Settings -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2><?php esc_html_e( 'Weekly Availability', 'leadnest' ); ?></h2>
        </div>
        <div class="ln-card-body">
            <p class="description" style="margin-bottom:16px;">
                <?php esc_html_e( 'Set the days and hours you are available for appointments. The bot will offer only these slots to visitors.', 'leadnest' ); ?>
            </p>
            <form id="ln-availability-form" method="post">
                <table class="ln-table ln-availability-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><?php esc_html_e( 'On', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Day', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Start Time', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'End Time', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $days as $dow => $day_name ) :
                            $slot   = isset( $avail_by_day[ $dow ] ) ? $avail_by_day[ $dow ] : null;
                            $active = $slot && $slot->active;
                            $start  = $slot ? substr( $slot->start_time, 0, 5 ) : '09:00';
                            $end    = $slot ? substr( $slot->end_time,   0, 5 ) : '17:00';
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="avail_active[]"
                                       value="<?php echo esc_attr( $dow ); ?>"
                                       <?php checked( $active ); ?>
                                       class="ln-avail-toggle"
                                       data-day="<?php echo esc_attr( $dow ); ?>">
                            </td>
                            <td><strong><?php echo esc_html( $day_name ); ?></strong></td>
                            <td>
                                <input type="time" name="avail_start[<?php echo esc_attr( $dow ); ?>]"
                                       value="<?php echo esc_attr( $start ); ?>"
                                       class="ln-avail-time <?php echo $active ? '' : 'ln-avail-disabled'; ?>">
                            </td>
                            <td>
                                <input type="time" name="avail_end[<?php echo esc_attr( $dow ); ?>]"
                                       value="<?php echo esc_attr( $end ); ?>"
                                       class="ln-avail-time <?php echo $active ? '' : 'ln-avail-disabled'; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="ln-form-table" style="margin-top:16px;">
                    <table class="ln-form-table">
                        <tr>
                            <th><?php esc_html_e( 'Appointment Duration', 'leadnest' ); ?></th>
                            <td>
                                <input type="number" name="booking_duration" class="small-text"
                                       value="<?php echo esc_attr( $options['booking_duration'] ); ?>"
                                       min="15" max="480" step="15">
                                <?php esc_html_e( 'minutes', 'leadnest' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Buffer Between Bookings', 'leadnest' ); ?></th>
                            <td>
                                <input type="number" name="booking_buffer" class="small-text"
                                       value="<?php echo esc_attr( $options['booking_buffer'] ); ?>"
                                       min="0" max="120" step="5">
                                <?php esc_html_e( 'minutes', 'leadnest' ); ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Max Bookings Per Day', 'leadnest' ); ?></th>
                            <td>
                                <input type="number" name="booking_max_per_day" class="small-text"
                                       value="<?php echo esc_attr( $options['booking_max_per_day'] ); ?>"
                                       min="1" max="50">
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Confirmation Message', 'leadnest' ); ?></th>
                            <td>
                                <textarea name="booking_confirmation_message" rows="3" class="large-text"
                                          style="max-width:460px;"><?php echo esc_textarea( $options['booking_confirmation_message'] ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Shown to visitors after booking is confirmed.', 'leadnest' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="ln-form-actions">
                    <button type="submit" id="ln-availability-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save Availability', 'leadnest' ); ?>
                    </button>
                    <span id="ln-availability-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Google Calendar Integration -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <span class="dashicons dashicons-calendar" style="color:#4285F4;"></span>
                <?php esc_html_e( 'Google Calendar Sync', 'leadnest' ); ?>
            </h2>
            <?php if ( class_exists( 'LeadNest_GCal' ) && LeadNest_GCal::is_connected() ) : ?>
                <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Connected', 'leadnest' ); ?></span>
            <?php else : ?>
                <span class="ln-badge ln-badge-closed"><?php esc_html_e( 'Not Connected', 'leadnest' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ln-card-body">
            <p class="description" style="margin-bottom:16px;">
                <?php esc_html_e( 'Automatically create Google Calendar events when bookings are confirmed.', 'leadnest' ); ?>
            </p>
            <form id="ln-gcal-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Client ID', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="gcal_client_id"
                                   value="<?php echo esc_attr( $options['gcal_client_id'] ?? '' ); ?>"
                                   class="regular-text" placeholder="xxxx.apps.googleusercontent.com">
                            <p class="description"><?php esc_html_e( 'From Google Cloud Console → APIs & Services → Credentials.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Client Secret', 'leadnest' ); ?></th>
                        <td>
                            <input type="password" name="gcal_client_secret"
                                   value="<?php echo esc_attr( $options['gcal_client_secret'] ?? '' ); ?>"
                                   class="regular-text" autocomplete="new-password">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Calendar ID', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="gcal_calendar_id"
                                   value="<?php echo esc_attr( $options['gcal_calendar_id'] ?? 'primary' ); ?>"
                                   class="regular-text" placeholder="primary">
                            <p class="description"><?php esc_html_e( 'Use "primary" for the main calendar, or a specific calendar ID.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <?php if ( ! empty( $options['gcal_client_id'] ) && ! empty( $options['gcal_client_secret'] ) ) : ?>
                    <tr>
                        <th><?php esc_html_e( 'Authorization', 'leadnest' ); ?></th>
                        <td>
                            <?php if ( class_exists( 'LeadNest_GCal' ) && LeadNest_GCal::is_connected() ) : ?>
                                <span style="color:#16a34a;font-weight:600;">&#10003; <?php esc_html_e( 'Connected to Google Calendar', 'leadnest' ); ?></span>
                            <?php else : ?>
                                <a href="<?php echo esc_url( LeadNest_GCal::get_auth_url() ); ?>" class="button button-secondary">
                                    <?php esc_html_e( 'Connect Google Calendar', 'leadnest' ); ?>
                                </a>
                                <p class="description"><?php esc_html_e( 'Click to authorize LeadNest to access your Google Calendar.', 'leadnest' ); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-gcal-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save Google Calendar Settings', 'leadnest' ); ?>
                    </button>
                    <span id="ln-gcal-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- SMS Reminders (Twilio) -->
    <div class="ln-card">
        <div class="ln-card-header">
            <h2>
                <span class="dashicons dashicons-phone" style="color:#F22F46;"></span>
                <?php esc_html_e( 'SMS Reminders (Twilio)', 'leadnest' ); ?>
            </h2>
            <?php if ( class_exists( 'LeadNest_SMS' ) && LeadNest_SMS::is_configured() ) : ?>
                <span class="ln-badge ln-badge-qualified"><?php esc_html_e( 'Configured', 'leadnest' ); ?></span>
            <?php else : ?>
                <span class="ln-badge ln-badge-closed"><?php esc_html_e( 'Not Configured', 'leadnest' ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ln-card-body">
            <p class="description" style="margin-bottom:16px;">
                <?php esc_html_e( 'Send SMS reminders to visitors before their appointment time via Twilio.', 'leadnest' ); ?>
            </p>
            <form id="ln-twilio-form" method="post">
                <table class="ln-form-table">
                    <tr>
                        <th><?php esc_html_e( 'Twilio Account SID', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="twilio_sid"
                                   value="<?php echo esc_attr( $options['twilio_sid'] ?? '' ); ?>"
                                   class="regular-text" placeholder="ACxxxxxxxxxx">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Twilio Auth Token', 'leadnest' ); ?></th>
                        <td>
                            <input type="password" name="twilio_token"
                                   value="<?php echo esc_attr( $options['twilio_token'] ?? '' ); ?>"
                                   class="regular-text" autocomplete="new-password">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Twilio Phone Number', 'leadnest' ); ?></th>
                        <td>
                            <input type="text" name="twilio_phone"
                                   value="<?php echo esc_attr( $options['twilio_phone'] ?? '' ); ?>"
                                   class="regular-text" placeholder="+15551234567">
                            <p class="description"><?php esc_html_e( 'Your Twilio phone number in E.164 format.', 'leadnest' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'SMS Reminders', 'leadnest' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sms_reminder_enabled" value="1"
                                       <?php checked( ! empty( $options['sms_reminder_enabled'] ) ); ?>>
                                <?php esc_html_e( 'Send SMS reminders before appointments', 'leadnest' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Remind Before', 'leadnest' ); ?></th>
                        <td>
                            <input type="number" name="sms_reminder_hours" class="small-text"
                                   value="<?php echo esc_attr( $options['sms_reminder_hours'] ?? 24 ); ?>"
                                   min="1" max="72">
                            <?php esc_html_e( 'hours before appointment', 'leadnest' ); ?>
                        </td>
                    </tr>
                </table>
                <div class="ln-form-actions">
                    <button type="submit" id="ln-twilio-save-btn" class="button button-primary">
                        <?php esc_html_e( 'Save Twilio Settings', 'leadnest' ); ?>
                    </button>
                    <span id="ln-twilio-save-status" class="ln-save-status"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="ln-status-tabs">
        <?php
        $tab_labels = array(
            'all'       => __( 'All', 'leadnest' ),
            'pending'   => __( 'Pending', 'leadnest' ),
            'confirmed' => __( 'Confirmed', 'leadnest' ),
            'cancelled' => __( 'Cancelled', 'leadnest' ),
            'completed' => __( 'Completed', 'leadnest' ),
        );
        foreach ( $tab_labels as $status_key => $label ) :
            $active  = $filter_status === $status_key ? 'ln-tab-active' : '';
            $tab_url = admin_url( 'admin.php?page=leadnest-bookings' . ( $status_key !== 'all' ? '&status=' . $status_key : '' ) );
        ?>
        <a href="<?php echo esc_url( $tab_url ); ?>" class="ln-status-tab <?php echo esc_attr( $active ); ?>">
            <?php echo esc_html( $label ); ?>
            <span class="ln-tab-count"><?php echo esc_html( $status_counts[ $status_key ] ); ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="ln-card">
        <div class="ln-card-body" style="padding:0;">
            <?php if ( empty( $bookings ) ) : ?>
                <div class="ln-empty-state">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <h3><?php esc_html_e( 'No bookings yet', 'leadnest' ); ?></h3>
                    <p><?php esc_html_e( 'Appointments booked via the chatbot will appear here.', 'leadnest' ); ?></p>
                </div>
            <?php else : ?>
                <table class="ln-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Contact', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Date & Time', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Service', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'leadnest' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'leadnest' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $bookings as $booking ) :
                            $status_info = $status_labels[ $booking->status ] ?? array( 'label' => ucfirst( $booking->status ), 'class' => '' );
                        ?>
                        <tr id="ln-booking-row-<?php echo esc_attr( $booking->id ); ?>">
                            <td>
                                <strong><?php echo esc_html( $booking->name ?: '—' ); ?></strong>
                            </td>
                            <td style="font-size:12px;">
                                <?php if ( $booking->email ) : ?>
                                    <a href="mailto:<?php echo esc_attr( $booking->email ); ?>"><?php echo esc_html( $booking->email ); ?></a><br>
                                <?php endif; ?>
                                <?php echo esc_html( $booking->phone ?: '' ); ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html( wp_date( 'D M j, Y', strtotime( $booking->booking_date ) ) ); ?></strong><br>
                                <span style="color:#64748b;"><?php echo esc_html( substr( $booking->booking_time, 0, 5 ) ); ?></span>
                            </td>
                            <td><?php echo esc_html( $booking->service_type ?: '—' ); ?></td>
                            <td>
                                <select class="ln-booking-status-select" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                    <?php foreach ( $status_labels as $sval => $sinfo ) : ?>
                                    <option value="<?php echo esc_attr( $sval ); ?>" <?php selected( $booking->status, $sval ); ?>>
                                        <?php echo esc_html( $sinfo['label'] ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="ln-table-actions">
                                <button class="button button-small ln-delete-booking-btn"
                                        data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                    <?php esc_html_e( 'Delete', 'leadnest' ); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery( function( $ ) {
    // Toggle time inputs active/disabled when checkbox changes
    $( '.ln-avail-toggle' ).on( 'change', function() {
        var day     = $( this ).data( 'day' );
        var enabled = $( this ).is( ':checked' );
        $( 'input.ln-avail-time[name="avail_start[' + day + ']"], input.ln-avail-time[name="avail_end[' + day + ']"]' )
            .toggleClass( 'ln-avail-disabled', ! enabled )
            .prop( 'disabled', ! enabled );
    } );

    // Init disabled state
    $( '.ln-avail-toggle' ).each( function() {
        var day     = $( this ).data( 'day' );
        var enabled = $( this ).is( ':checked' );
        if ( ! enabled ) {
            $( 'input.ln-avail-time[name="avail_start[' + day + ']"], input.ln-avail-time[name="avail_end[' + day + ']"]' )
                .addClass( 'ln-avail-disabled' )
                .prop( 'disabled', true );
        }
    } );
} );
</script>
