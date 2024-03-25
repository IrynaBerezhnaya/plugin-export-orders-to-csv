<?php
/**
 * Callback function to register admin submenu.
 *
 * @since    1.0.3
 */

function ib_register_submenu() {
	add_submenu_page(
		'woocommerce',
		'Export Orders',
		'Export Orders',
		'manage_options',
		'order_export',
		'ib_add_submenu_callback'
	);
}
add_action('admin_menu', 'ib_register_submenu');


function ib_add_submenu_callback() {

	if ( class_exists( 'WC_Countries' ) ) {
		$countries_obj = new WC_Countries();
		$all_countries = $countries_obj->get_countries();

		$countries_list = array_map(function($code, $name) {
			return ['code' => $code, 'name' => $name];
		}, array_keys($all_countries), $all_countries);
	} else {
		$countries_list = [];
	}

	$countries_json = json_encode($countries_list);

	$order_statuses = wc_get_order_statuses();

	$statuses_json = json_encode(array_map(function($status, $key) {
		return ['key' => $key, 'status' => $status];
	}, $order_statuses, array_keys($order_statuses)));

	?>
    <div class="order-export">
        <div class="order-export__header"><h2><?php esc_html_e( 'Export Orders', 'woocommerce' ); ?></h2></div>
        <form id="exportOrdersForm" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="export_orders">
            <div class="order-export__item">
                <label for="date_from"><?php esc_html_e( 'Date Created from ', 'woocommerce' ); ?></label>
                <div>
                    <label><input type="date" id="date_from" name="date_from" max="<?php echo date('Y-m-d'); ?>" value="" /></label>
                    <label for="date_to"><?php esc_html_e( 'to', 'woocommerce' ); ?></label>
                    <label><input type="date" id="date_to" name="date_to" max="<?php echo date('Y-m-d'); ?>" value="" /></label>
                </div>
            </div>
            <br><br>
            <div id="countries" class="order-export__multiselect">
                <label class="typo__label"><?php esc_html_e( 'Select Countries', 'woocommerce' ); ?></label>
                <multiselect
                        v-model="selectedCountries"
                        :options="countries"
                        :multiple="true"
                        :custom-label="customLabel"
                        track-by="code" >
                </multiselect>
            </div>
            <input type="hidden" name="selected_countries" id="selected_countries">
            <br><br>
            <div id="order_statuses" class="order-export__multiselect">
                <label class="typo__label"><?php esc_html_e( 'Select Statuses', 'woocommerce' ); ?></label>
                <multiselect
                        v-model="selectedStatuses"
                        :options="statuses"
                        :multiple="true"
                        :custom-label="statusLabel"
                        track-by="key" >
                </multiselect>
            </div>
            <input type="hidden" name="selected_statuses" id="selected_statuses">
            <br><br>
            <div class="order-export__item">
                <label for="order_total"><?php esc_html_e( 'Order Total ', 'woocommerce' ); ?></label>
                <select id="order_total_operator" name="order_total_operator" class="order-export__total">
                    <option value="greater_than"> > </option>
                    <option value="less_than"> < </option>
                </select>
                <input type="number" id="order_total" name="order_total">
            </div>
            <br><br>
            <div id="metaInputsContainer">
                <div class="metaInputPair order-export__item">
                    <label for="meta_key[]"><?php esc_html_e( 'Meta Key : Value', 'woocommerce' ); ?></label>
                    <input type="text" id="meta_key" name="meta_key[]">

                    <label for="meta_value[]" class="delimiter"><?php esc_html_e( ' : ', 'woocommerce' ); ?></label>
                    <input type="text" id="meta_value" name="meta_value[]">
                </div>
            </div>
            <div class="order-export__item">
                <button type="button" class="meta-btn" id="addMetaPair"><?php esc_html_e( 'Add More', 'woocommerce' ); ?></button>
                <button type="button" class="meta-btn" id="removeMetaPair"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></button>
            </div>
            <br><br>
            <input type="submit" value="Export Orders to CSV" class="order-export__submit">
        </form>
    </div>

    <script>
        new Vue({
            components: {
                Multiselect: window.VueMultiselect.default
            },
            data: {
                selectedCountries: [],
                countries: <?php echo json_encode($countries_list); ?>,
                selectedStatuses: [],
                statuses: <?php echo $statuses_json; ?>
            },
            methods: {
                customLabel (country) {
                    return `${country.name}`;
                },
                updateHiddenInput() {
                    document.getElementById('selected_countries').value = JSON.stringify(this.selectedCountries.map(country => country.code));
                    document.getElementById('selected_statuses').value = JSON.stringify(this.selectedStatuses.map(status => status.key));
                },
                statusLabel(status) {
                    return `${status.status}`;
                }
            },
            watch: {
                selectedCountries: {
                    handler: function (newVal) {
                        this.updateHiddenInput();
                    },
                    deep: true
                },
                selectedStatuses: {
                    handler: function (newVal) {
                        this.updateHiddenInput();
                    },
                    deep: true
                }
            },
            created() {
                this.countries = <?php echo $countries_json; ?>;
                this.statuses = <?php echo $statuses_json; ?>;
            }
        }).$mount('#exportOrdersForm')

    </script>
	<?php
}



