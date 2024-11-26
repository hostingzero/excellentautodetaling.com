<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body"  name ="invoice_settings" label="invoice" data-tab_name="invoice_settings">
	<span slot="label">
		<i class="material-icons-round">receipt</i>
		<?php esc_html_e( 'Invoice', 'bookingpress-invoice' ); ?>
	</span>
	<div class="bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card bpa-invoice-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Invoice', 'bookingpress-invoice' ); ?></h1>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_save_invoice_settings('invoice_setting_form','invoice_setting')" :disabled="is_disabled" >					
					  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-invoice' ); ?></span>
					  <div class="bpa-btn--loader__circles">				    
						  <div></div>
						  <div></div>
						  <div></div>
					  </div>
					</el-button>
					
					<el-popover class="bpa-invoice-settings-popover" width="400" trigger="click" popper-class="bpa-is__container">
						<div class="bpa-is__wrapper">
							<div class="bpa-is__title">
								<el-row type="flex" align="middle">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<h3 class="bpa-page-heading"><?php esc_html_e('Invoice Settings', 'bookingpress-invoice'); ?></h3>
									</el-col>
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="bpa-ish__right">
										<el-popconfirm 
											confirm-button-text='<?php esc_html_e( 'Approve', 'bookingpress-invoice' ); ?>' 
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-invoice' ); ?>' 
											icon="false" 
											title="<?php esc_html_e( 'Are you sure you want to reset invoice counter?', 'bookingpress-invoice' ); ?>" 
											@confirm="bookingpress_reset_invoice_counter()" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--primary" 
											cancel-button-type="bpa-btn bpa-btn__small">							
											<el-button class="bpa-btn bpa-btn__medium" type="text" slot="reference" >
												<?php esc_html_e( 'Reset Counter', 'bookingpress-invoice' ); ?>							
											</el-button>
										</el-popconfirm>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-is__body">
								<el-form id="invoice_setting_form" class="invoice_setting_form" :rules="rules_invoice" ref="invoice_setting_form" :model="invoice_setting_form" @submit.native.prevent>									
									<el-row type="flex" align="middle" class="bpa-is__body-item-row bpa-is__body-swtich-row-prefix">
										<el-col :xs="20" :sm="20" :md="20" :lg="20" :xl="20">
											<div class="bpa-is__body-swtich-col-title">
												<span class="bpa-form-label"><?php esc_html_e( 'Invoice prefix/suffix', 'bookingpress-invoice' ); ?></span>
											</div>											
										</el-col>
										<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4" class="bpa-is__body-switch">
											<el-form-item prop="bookingpress_invoice_suffix_prefix">
												<el-switch class="bpa-swtich-control" v-model="invoice_setting_form.bookingpress_invoice_suffix_prefix"></el-switch>
											</el-form-item>
										</el-col>										
									</el-row>	
									<el-row type="flex" class="bpa-is__body-item-row" v-if="invoice_setting_form.bookingpress_invoice_suffix_prefix == true">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<el-form-item prop="bookingpress_invoice_suffix">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Invoice Suffix', 'bookingpress-invoice' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="invoice_setting_form.bookingpress_invoice_suffix"
													placeholder="<?php esc_html_e( 'Enter Invoice Suffix', 'bookingpress-invoice' ); ?>">
												</el-input>
											</el-form-item>
										</el-col>
									</el-row>				
									<el-row type="flex" class="bpa-is__body-item-row" v-if="invoice_setting_form.bookingpress_invoice_suffix_prefix == true">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<el-form-item prop="bookingpress_invoice_prefix">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Invoice Prefix', 'bookingpress-invoice' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="invoice_setting_form.bookingpress_invoice_prefix" placeholder="<?php esc_html_e( 'Enter Invoice Prefix', 'bookingpress-invoice' ); ?>"></el-input>		
											</el-form-item>						
										</el-col>
									</el-row>
									<el-row class="bpa-is__body-item-row" type="flex" v-if="invoice_setting_form.bookingpress_invoice_suffix_prefix == true">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">				
											<el-form-item prop="bookingpress_minimum_invoice_length">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Minimum Invoice Length', 'bookingpress-invoice' ); ?></span>
												</template>
												<el-input-number class="bpa-form-control bpa-form-control--number" v-model="invoice_setting_form.bookingpress_minimum_invoice_length" ></el-input>		
											</el-form-item>
										</el-col>
									</el-row>
									<?php /*
									<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
										<el-col :xs="12" :sm="12" :md="12" :lg="22" :xl="22">
											<h4> <?php esc_html_e( 'Hide discount Raw', 'bookingpress-invoice' ); ?></h4>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="02" :xl="02">
											<el-form-item prop="bookingpress_hide_discount_raw">
												<el-switch class="bpa-swtich-control" v-model="invoice_setting_form.bookingpress_hide_discount_raw"></el-switch>
											</el-form-item>
										</el-col>
									</el-row>
									<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
										<el-col :xs="12" :sm="12" :md="12" :lg="22" :xl="22">
											<h4> <?php esc_html_e( 'Hide Tax Raw', 'bookingpress-invoice' ); ?></h4>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="02" :xl="02">
											<el-form-item prop="bookingpress_hide_tax_raw">
												<el-switch class="bpa-swtich-control" v-model="invoice_setting_form.bookingpress_hide_tax_raw"></el-switch>
											</el-form-item>
										</el-col>
									</el-row> */ ?>
									<el-row class="bpa-is__body-item-row" type="flex">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >				
											<el-form-item prop="bookingpress_minimum_invoice_length">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Invoice due days', 'bookingpress-invoice' ); ?></span>
												</template>
												<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :step="1" :max="365" step-strictly v-model="invoice_setting_form.bookingpress_invoice_due_days" ></el-input>		
											</el-form-item>						
										</el-col>
									</el-row>				
									<el-row class="bpa-is__body-item-row" type="flex">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<el-form-item prop="bookingpress_font_options_list">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Font', 'bookingpress-invoice' ); ?></span>
												</template>
												<el-select class="bpa-form-control" v-model="invoice_setting_form.bookingpress_selected_font"
												popper-class="bpa-el-select--invoice-popper">
													<el-option v-for="font_data in bookingpress_font_options_list" :value="font_data.value" :label="font_data.text">
														<span>{{ font_data.text }}</span>
													</el-option>
												</el-select>
											</el-form-item>
										</el-col>
									</el-row>
								</el-form>
							</div>
							<div class="bpa-invoice-settings-footer bpa-hw-right-btn-group">
								<el-button class="bpa-btn bpa-btn__medium" @click="bookingpress_close_invoice_settings_popover()"><?php esc_html_e('Cancel', 'bookingpress-invoice'); ?></el-button>
								<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="bookingpress_close_invoice_settings_popover()"><?php esc_html_e('Update', 'bookingpress-invoice'); ?></el-button>
							</div>
						</div>
						<el-button slot="reference" class="bpa-btn">
							<span class="material-icons-round">settings</span>
							<?php esc_html_e('Settings', 'bookingpress-invoice'); ?>
						</el-button>
					</el-popover>

					<el-button class="bpa-btn bpa-invoice-preview-btn" @click="bookingpress_open_preview_modal">
						<span class="material-icons-round">preview</span>
						<?php esc_html_e( 'Invoice Preview', 'bookingpress-invoice' ); ?>
					</el-button>										
					<?php do_action('bookingpress_invoice_setting_header_extra_button'); ?>

				</div>
			</el-col>
		</el-row>
		<div class="bpa-gs--tabs-pb__content-body bpa-invoice__content-body">			
			<el-form id="invoice_setting_form" :rules="rules_invoice" ref="invoice_setting_form" :model="invoice_setting_form" @submit.native.prevent>
				<div class="bpa-gs__cb--item bpa-icb--template-builder">
                    <el-row :gutter="24">
                        <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="18">
                            <div class="bpa-invoice-template-builder">
								
								<?php
									$bookingpress_invoice_html_content = get_option('bookingpress_invoice_html_format');
									if(empty($bookingpress_invoice_html_content)){
										$bookingpress_invoice_html_content = '
<html>
	<head>
		<style>@import url(https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap);body{margin:0;padding:0;font-family:Poppins,sans-serif}.page{width:700px;min-height:600px;padding:20px;margin:0 auto;background:#fff}</style>
	</head>
	<body>
		<div style="font-family:Poppins,sans-serif;padding:20px">
			<div>
				<table width="800" style="border-spacing:0;border-collapse:collapse">
					<tr>
						<td colspan="2" style="width:120px;padding-bottom:20px;font-size:16px;font-weight:400">{company_logo}</td>
						<td colspan="2" style="font-size:16px;line-height:20px;color:#202c45" align="right"><b>INVOICE</b></td>
					</tr>
					<tr>
						<td colspan="2" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45">
							<div style="width:200px">{company_name}<br>{company_address}<br>{company_website}<br>{company_phone}</div>
						</td>
						<td colspan="2" align="right" style="padding-bottom:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45" colspan="2">Invoice: #{invoice_number}<br>Date: {invoice_date}<br>Due Date: {invoice_due_date}</td>
					</tr>
					<tr>
						<td colspan="4">
							<hr>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="padding-bottom:16px;padding-top:16px;font-size:15px;line-height:24px;font-weight:400;color:#202c45">
							<div style="width:200px"><b>BILL TO:</b><br><br>{customer_fullname}<br>{customer_firstname}<br>{customer_lastname}<br>{customer_email}<br>{customer_phone}</div>
						</td>
					</tr>
				</table>
				<table style="border-spacing:0;border:1px solid #dce4f5;border-collapse:collapse;margin-top:32px" width="800">
					<tr>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Service</b></td>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Date</b></td>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Staff</b></td>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;color:#202c45"><b>Price</b></td>
					</tr>
					<tr>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{service_name}</td>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{appointment_date}</td>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">{staffmember_name}</td>
						<td style="width:200px;border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{service_price}</td>
					</tr>
					<tr>
						<td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Subtotal</td>
						<td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{subtotal_amt}</td>
					</tr>
					<tr>
						<td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Discount</td>
						<td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{discount_amt}</td>
					</tr>
					<tr>
						<td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45">Tax</td>
						<td colspan="2" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:14px;font-weight:400;color:#202c45" align="right">{tax_amt}</td>
					</tr>
					<tr>
						<td colspan="3" align="right" style="border-bottom:1px solid #dce4f5;border-right:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>Total</b></td>
						<td colspan="2" align="right" style="border-bottom:1px solid #dce4f5;padding:6px 12px;font-size:15px;color:#202c45"><b>{total_amt}</b></td>
					</tr>
				</table>
				<table width="800" style="margin-top:20px">
					<tr>
						<td style="padding:6px 12px;font-size:15px;color:#202c45" align="center"><b>THANK YOU FOR VISIT</b></td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>';
									}

									$bookingpress_invoice_editor_settings = array(
										'wpautop' => true,
                                    	'media_buttons' => false,
										'textarea_name' => 'bookingpress_invoice_template_builder',
										'textarea_rows' => '42',
										'tinymce' => false,
									);

									wp_editor(stripslashes($bookingpress_invoice_html_content), 'bookingpress_invoice_template_builder', $bookingpress_invoice_editor_settings);
								?>
                            </div>
                        </el-col>
                        <el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="06">
                            <div class="bpa-invoice-template-tags-container">
                                <div class="bpa-gs__cb--item-heading">
                                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Tags', 'bookingpress-invoice'); ?></h4>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div v-for="invoice_tag_data in bookingpress_invoice_tag_list">
										<span class="bpa-tags--item-sub-heading" v-if="invoice_tag_data.tag_details != ''">{{ invoice_tag_data.group_tag_name }}</span>
										<span v-if="invoice_tag_data.group_tag_name == 'custom fields'">                                        
                                        	<span class="bpa-tags--item-body" @click="bookingpress_insert_tag(item.tag_value)" v-for="item in invoice_tag_data.tag_details" >{{ item.tag_name }}</span>
										</span>
										<span v-else>
											<span class="bpa-tags--item-body" @click="bookingpress_insert_tag(item.tag_name)" v-for="item in invoice_tag_data.tag_details" >{{ item.tag_name }}</span>
										</span>
									</div>
                                </div>
                            </div>
                        </el-col>
                    </el-row>
				</div>
			<el-form>						
		</div>	
	</div>



	<el-dialog custom-class="bpa-dialog bpa-dialog--invoice-preview" :visible.sync="bookingpress_invoice_preview_modal">
		<div class="bpa-dialog-heading">
			<el-row type="flex">
				<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
					<h1 class="bpa-page-heading"><?php esc_html_e('Invoice Preview', 'bookingpress-invoice'); ?> </h1>
				</el-col>
				<el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
					<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">
						<el-button class="bpa-btn" @click="bookingpress_close_preview_modal">
							<?php esc_html_e( 'Cancel', 'bookingpress-invoice' ); ?>
						</el-button>
					</div>
				</el-col>
			</el-row>
		</div>
		<div class="bpa-dialog-body">
			<div class="bpa-invoice-preview-container" v-html="bookingpress_invoice_preview_html"></div>
		</div>
	</el-dialog>

</el-tab-pane>
<?php do_action('bookingpress_invoice_setting_view_bottom'); ?>