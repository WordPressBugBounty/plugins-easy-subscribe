<?php

use Devnet\EasySubscribe\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$esub_class = 'esub__disabled';
?>

<div class="esub-subscribers-wrap">

    <h2> <?php 
esc_html_e( 'Subscribers', 'easy-subscribe' );
?> </h2>

    <div class="esub-subscribers-info"></div>

    <div class="esub-subscribers-header">

        <form id="esub-subscribers-filter" action="/" method="get" class="esub-subscribers-form">

            <div class="esub-subscribers-form-group">

                <span class="esub-subscribers-form-title"><strong><?php 
esc_html_e( 'Filter:', 'easy-subscribe' );
?></strong></span>

                <div class="esub-subscribers-input">
                    <label><?php 
esc_html_e( 'List', 'easy-subscribe' );
?></label>
                    <select name="esub_subscribers_list"></select>
                </div>

                <div class="esub-subscribers-input">
                    <label><?php 
esc_html_e( 'Date', 'easy-subscribe' );
?></label>
                    <input id="esub-subscribers-date" type="text" name="esub_subscribers_date">
                </div>

                <div class="esub-subscribers-input esub-subscribers-input--limit">
                    <label><?php 
esc_html_e( 'Show results', 'easy-subscribe' );
?></label>
                    <div class="esub-subscribers-limit-controls">
                        <input id="esub-subscribers-limit-results" type="number" name="esub_subscribers_limit" step="1" min="1" value="1000" placeholder="<?php 
esc_attr_e( 'All', 'easy-subscribe' );
?>">
                        <button id="esub-subscribers-show-all" class="esub-button esub-button--secondary" type="button"><?php 
esc_html_e( 'All', 'easy-subscribe' );
?></button>
                    </div>
                </div>
            </div>

        </form>

        <form id="esub-subscribers-csv" action="/" class="esub-subscribers-form esub-customize-csv">
            <div class="esub-subscribers-form-group">

                <span class="esub-subscribers-form-title"><strong><?php 
esc_html_e( 'Customize CSV:', 'easy-subscribe' );
?></strong></span>

                <div class="esub-subscribers-input <?php 
echo esc_attr( $esub_class );
?>">
                    <label> <?php 
esc_html_e( 'Delimiter: ', 'easy-subscribe' );
?></label>
                    <input type="text" name="esub_customize_csv_delimiter" value="," maxlength="1" size="1" required>
                </div>

                <div class="esub-subscribers-input esub-checkbox <?php 
echo esc_attr( $esub_class );
?>">
                    <label> <?php 
esc_html_e( 'Name: ', 'easy-subscribe' );
?></label>
                    <input type="checkbox" name="esub_customize_csv_name_field" value="1">
                </div>

                <div class="esub-subscribers-input esub-checkbox <?php 
echo esc_attr( $esub_class );
?>">
                    <label> <?php 
esc_html_e( 'Last name: ', 'easy-subscribe' );
?></label>
                    <input type="checkbox" name="esub_customize_csv_last_name_field" value="1">
                </div>

                <div class="esub-subscribers-input esub-checkbox <?php 
echo esc_attr( $esub_class );
?>">
                    <label> <?php 
esc_html_e( 'Email: ', 'easy-subscribe' );
?></label>
                    <input type="checkbox" name="esub_customize_csv_email_field" value="1" checked>
                </div>

                <button id="esub-subscribers-download" class="esub-button"><?php 
esc_html_e( 'Download CSV', 'easy-subscribe' );
?></button>


            </div>
        </form>

    </div>

    <div id="esub-subscribers-table"></div>


    <button id="esub-subscribers-delete" class="esub-button disabled"><?php 
esc_html_e( 'Delete selected', 'easy-subscribe' );
?></button>

    <div class="esub-subscribers-delete-old <?php 
echo esc_attr( $esub_class );
?>">
        <label for="esub-delete-old-data"><?php 
esc_html_e( 'Delete entries older than', 'easy-subscribe' );
?></label>
        <select id="esub-delete-old-data">
            <option value=""><?php 
esc_html_e( '-- Select --', 'easy-subscribe' );
?></option>
            <option value="30_days"><?php 
esc_html_e( '30 days', 'easy-subscribe' );
?></option>
            <option value="3_months"><?php 
esc_html_e( '3 months', 'easy-subscribe' );
?></option>
            <option value="6_months"><?php 
esc_html_e( '6 months', 'easy-subscribe' );
?></option>
            <option value="12_months"><?php 
esc_html_e( '12 months', 'easy-subscribe' );
?></option>
        </select>
        <button id="esub-delete-old-data-button" class="esub-button" disabled><?php 
esc_html_e( 'Delete now', 'easy-subscribe' );
?></button>
        <span class="esub-delete-old-data-status" role="status"></span>
    </div>


</div>
