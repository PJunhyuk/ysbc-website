<div id="woocommerce_events_data" class="panel woocommerce_options_panel">
    <div class="options_group">
            <p class="form-field">
                <label><?php _e('Event:', 'woocommerce-events'); ?></label>
                <select name="WooCommerceEventsEvent" id="WooCommerceEventsEvent">
                    <option value="">Please select...</option>
                    <?php foreach($events as $event) :?>
                        <?php if( $event->has_child() ): ?>
                        <option value="<?php echo $event->ID; ?>"><?php echo $event->post_title; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>   
                   
            </p>
    </div>
</div>    
