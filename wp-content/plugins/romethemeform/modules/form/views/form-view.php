<?php
require_once \RomeThemeForm::module_dir() . 'form/form.php';
$index = 0;
$rtform = new WP_Query(['post_type' => 'romethemeform_form']);

?>


<div class="w-100 p-3">
    <div class="d-flex flex-column gap-1 mb-3">
        <h2>Forms</h2>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal">Add New</button>
        </div>
    </div>
    <div class="w-100">
        <table class="table shadow table-sm">
            <thead class="bg-white">
                <tr>
                    <td class="text-center" scope="col">No</td>
                    <td scope="col">Title</td>
                    <td scope="col">Shortcode</td>
                    <td scope="col">Entries</td>
                    <td scope="col">Author</td>
                    <td scope="col">Date</td>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($rtform->have_posts()) {
                    while ($rtform->have_posts()) {
                        $index = $index + 1;
                        $no = (string) $index;
                        $rtform->the_post();
                        $id_post =  intval(get_the_ID());
                        $delete = get_delete_post_link($id_post, '', false);
                        $edit_link = get_edit_post_link($id_post, 'display');
                        $edit_elementor = str_replace('action=edit', 'action=elementor', $edit_link);
                        $status = (get_post_status($id_post) == 'publish') ? 'Published' : 'Draft';
                        $entries = \RomethemeForm\Form\Form::count_entries($id_post);
                        $shortcode = get_post_meta($id_post, 'rtform_shortcode', true);
                        $success_msg = get_post_meta($id_post, 'rtform_form_success_message', true);
                        $f = "export_entries(' " . $id_post . " ',' " . get_the_title() . " ')";
                        echo '<tr>';
                        echo '<td class="text-center">' . esc_html__($no, 'romethemeform') . '</td>';
                        echo '<td><div>' . esc_html(get_the_title());
                        echo '</div>';
                        echo '<smal style="font-size: 13px;">
                        <a type="button" class="link" data-bs-toggle="modal" 
                        data-bs-target="#formUpdate" data-form-id="' . $id_post . '" 
                        data-form-name="' . esc_attr(get_the_title()) . '" 
                        data-form-entry="' . esc_attr(get_post_meta($id_post, "rtform_form_entry_title", true)) . '"
                        data-form-restricted ="' . esc_attr(get_post_meta($id_post, "rtform_form_restricted", true)) . '"
                        data-form-msg-success="' . esc_attr($success_msg) . '"
                        >
                        Edit</a>&nbsp;|&nbsp; <a class="link" href="' . esc_url($edit_elementor) . '">Edit Form</a> &nbsp;|&nbsp;<a class="link-danger" href="' . esc_url($delete) . '">Trash</a></small>';
                        echo '</td>';
                        echo '<td>' . esc_html($shortcode) . '</td>';
                        echo '<td>
                        <a class="btn btn-outline-primary" href="' . esc_url(admin_url("admin.php?page=romethemeform-entries&rform_id=" . $id_post)) . '" type="button" 
                        >' . esc_html($entries) . '</a>
                        <a type="button" class="btn btn-outline-success" onclick="' . esc_attr($f) . '">Export CSV</a>
                        </td>';
                        echo '<td>' . esc_html(get_the_author()) . '</td>';
                        echo '<td><small>' . esc_html($status) . '</small><br><small>' . esc_html(get_the_date('Y/m/d') . ' at ' . get_the_date('H:i a')) . '</small></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td class="text-center" colspan="6">' . esc_html('No Data') . '</td></tr>';
                }

                ?>
            </tbody>
            <tfoot>
                <tr class="bg-white">
                    <td scope="col"></td>
                    <td scope="col">Title</td>
                    <td scope="col">Shortcode</td>
                    <td scope="col">Entries</td>
                    <td scope="col">Author</td>
                    <td scope="col">Date</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="modal fade" id="formModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index:99999">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="w-100" id="rtform-add-form" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add Form</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input id="action" name="action" type="text" value="rtformnewform" hidden>
                    <nav>
                        <div class="nav nav-pills mb-3" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-general" type="button" role="tab" aria-controls="nav-general" aria-selected="true">General</button>
                            <button class="nav-link" id="nav-confirmation-tab" data-bs-toggle="tab" data-bs-target="#nav-confirmation" type="button" role="tab" aria-controls="nav-confirmation" aria-selected="false">Confirmation</button>
                            <button class="nav-link" id="nav-notification-tab" data-bs-toggle="tab" data-bs-target="#nav-notification" type="button" role="tab" aria-controls="nav-notification" aria-selected="false">Notification</button>
                        </div>
                    </nav>
                    <div class="tab-content p-3" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
                            <label for="form-name">Form Name</label>
                            <input type="text" name="form-name" id="form-name" class="form-control p-2" placeholder="Enter Form Name">
                            <h5 class="my-3">Settings</h5>
                            <hr>
                            <div class="mb-3">
                                <label for="success-message" class="form-label">Success Message</label>
                                <input type="text" class="form-control p-2" id="success-message" name="success-message" value="Thank you! Form submitted successfully.">
                            </div>
                            <div class="mb-3">
                                <label for="entry-name" class="form-label">Entry Title</label>
                                <input type="text" class="form-control p-2" id="entry-name" name="entry-name" value="Entry #">
                            </div>
                            <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                <span>
                                    <p class="m-0">Require Login</p>
                                    <p class="fw-light fst-italic text-black-50">Without login, user can't submit the form.</p>
                                </span>
                                <label class="switch">
                                    <input name="require-login" id="switch" type="checkbox" value="true">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-confirmation" role="tabpanel" aria-labelledby="nav-confirmation-tab" tabindex="0">
                            <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                <span>
                                    <h5 class="m-0">Confirmation mail to user</h5>
                                </span>
                                <label class="switch">
                                    <input name="confirmation" id="switch_confirmation" type="checkbox" value="true">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="fw-light fst-italic text-black-50">Want to send a submission copy to user by email? <strong style="color:black;">Active this one.The form must have at least one Email widget and it should be required.</strong></p>
                            <div id="confirmation_form">
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Email Subject</label>
                                    <input type="text" class="form-control p-2" name="email_subject" id="email_subject" placeholder="Enter Email Subject Here">
                                </div>
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Email From</label>
                                    <input type="email" class="form-control p-2" name="email_from" id="email_from" placeholder="mail@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Email Reply To</label>
                                    <input type="text" class="form-control p-2" name="email_replyto" id="email_replyto" placeholder="mail@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="thks_mssg" class="form-label">Thankyou Message</label>
                                    <textarea class="form-control" id="thks_msg" name="tks_msg" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-notification" role="tabpanel" aria-labelledby="nav-notification-tab" tabindex="0">
                            <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                <span>
                                    <h5 class="m-0">Notification mail to Admin</h5>
                                </span>
                                <label class="switch">
                                    <input name="notification" id="switch_notification" type="checkbox" value="true">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="fw-light fst-italic text-black-50">Want to send a submission copy to admin by email? <strong style="color:black;">Active this one.</strong></p>
                            <div id="notification_form">
                                <div class="mb-3">
                                    <label for="notif_subject" class="form-label">Email Subject</label>
                                    <input type="text" class="form-control p-2" name="notif_subject" id="notif_subject" placeholder="Enter Email Subject Here">
                                </div>
                                <div class="mb-3">
                                    <label for="notif_email_to" class="form-label">Email From</label>
                                    <input type="email" class="form-control p-2" name="notif_email_from" id="notif_email_from" placeholder="mail@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="notif_email_to" class="form-label">Email To</label>
                                    <input type="text" class="form-control p-2" name="notif_email_to" id="notif_email_to" placeholder="mail@example.com">
                                    <span class="fw-light fst-italic text-black-50">Enter admin email where you want to send mail. <strong style="color:black">for multiple email addresses please use "," separator.</strong></span>
                                </div>
                                <div class="mb-3">
                                    <label for="thks_mssg" class="form-label">Admin Note</label>
                                    <textarea class="form-control" id="adm_msg" name="adm_msg" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="close-btn" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="rform-save-button" type="button" class="btn btn-primary rform-save-btn">Save & Edit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="formUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true" style="z-index: 99999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="w-100" id="rtform-update-form" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="updateLabel">Update Form</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input id="action" name="action" type="text" value="rtformupdate" hidden>
                    <input type="text" name="id" id="id" hidden>
                    <nav>
                        <div class="nav nav-pills mb-3" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-update-general" type="button" role="tab" aria-controls="nav-general" aria-selected="true">General</button>
                            <button class="nav-link" id="nav-confirmation-tab" data-bs-toggle="tab" data-bs-target="#nav-update-confirmation" type="button" role="tab" aria-controls="nav-confirmation" aria-selected="false">Confirmation</button>
                            <button class="nav-link" id="nav-notification-tab" data-bs-toggle="tab" data-bs-target="#nav-update-notification" type="button" role="tab" aria-controls="nav-notification" aria-selected="false">Notification</button>
                        </div>
                    </nav>
                    <div class="tab-content p-3" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-update-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
                            <label for="form-name">Form Name</label>
                            <input type="text" name="form-name" id="form-name" class="form-control p-2" placeholder="Enter Form Name">
                            <h5 class="my-3">Settings</h5>
                            <hr>
                            <div class="mb-3">
                                <label for="success-message" class="form-label">Success Message</label>
                                <input type="text" class="form-control p-2" id="success-message" name="success-message" value="Thank you! Form submitted successfully.">
                            </div>
                            <div class="mb-3">
                                <label for="entry-name" class="form-label">Entry Title</label>
                                <input type="text" class="form-control p-2" id="entry-name" name="entry-name" value="Entry #">
                            </div>
                            <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                <span>
                                    <p class="m-0">Require Login</p>
                                    <p class="fw-light fst-italic text-black-50">Without login, user can't submit the form.</p>
                                </span>
                                <label class="switch">
                                    <input name="require-login" id="switch" type="checkbox" value="true">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-update-confirmation" role="tabpanel" aria-labelledby="nav-confirmation-tab" tabindex="0">
                            <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                <span>
                                    <h5 class="m-0">Confirmation mail to user</h5>
                                </span>
                                <label class="switch">
                                    <input name="confirmation" id="update_switch_confirmation" type="checkbox" value="true">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="fw-light fst-italic text-black-50">Want to send a submission copy to user by email? <strong style="color:black;">Active this one.The form must have at least one Email widget and it should be required.</strong></p>
                            <div id="update_confirmation_form">
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Email Subject</label>
                                    <input type="text" class="form-control p-2" name="email_subject" id="update_email_subject" placeholder="Enter Email Subject Here">
                                </div>
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Email From</label>
                                    <input type="email" class="form-control p-2" name="email_from" id="update_email_from" placeholder="mail@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Email Reply To</label>
                                    <input type="text" class="form-control p-2" name="email_replyto" id="update_email_replyto" placeholder="mail@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="thks_mssg" class="form-label">Thankyou Message</label>
                                    <textarea class="form-control" id="update_thks_msg" name="tks_msg" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-update-notification" role="tabpanel" aria-labelledby="nav-notification-tab" tabindex="0">
                            <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                <span>
                                    <h5 class="m-0">Notification mail to Admin</h5>
                                </span>
                                <label class="switch">
                                    <input name="notification" id="update_switch_notification" type="checkbox" value="true">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="fw-light fst-italic text-black-50">Want to send a submission copy to admin by email? <strong style="color:black;">Active this one.</strong></p>
                            <div id="update_notification_form">
                                <div class="mb-3">
                                    <label for="notif_subject" class="form-label">Email Subject</label>
                                    <input type="text" class="form-control p-2" name="notif_subject" id="update_notif_subject" placeholder="Enter Email Subject Here">
                                </div>
                                <div class="mb-3">
                                    <label for="notif_email_from" class="form-label">Email From</label>
                                    <input type="email" class="form-control p-2" name="notif_email_from" id="update_notif_email_from" placeholder="mail@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="notif_email_to" class="form-label">Email To</label>
                                    <input type="text" class="form-control p-2" name="notif_email_to" id="update_notif_email_to" placeholder="mail@example.com">
                                    <span class="fw-light fst-italic text-black-50">Enter admin email where you want to send mail. <strong style="color:black">for multiple email addresses please use "," separator.</strong></span>
                                </div>
                                <div class="mb-3">
                                    <label for="thks_mssg" class="form-label">Admin Note</label>
                                    <textarea class="form-control" id="update_adm_msg" name="adm_msg" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="close-btn" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="rform-update-button" type="button" class="btn btn-primary rform-save-btn">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="rform-editor-modal" class="rform-modal">
    <div class="rform-modal-content">
        <div class="elementor-editor-header-iframe">
            <div class="rform-editor-header">
                <svg width="30" height="30" id="eohpCl3PVjW1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 300 300" shape-rendering="geometricPrecision" text-rendering="geometricPrecision">
                    <g transform="matrix(.11326 0 0-.113381-20.251951 319.628716)">
                        <path d="M372,2749c-46-14-109-80-122-128-7-27-10-384-8-1148l3-1108l24-38c13-21,42-50,64-65l41-27h1131h1131l41,27c22,15,51,44,64,65l24,38v812v813l-383,382-382,383-798,2c-485,1-810-2-830-8Zm1500-932c211-120,337-197,335-206-2-14-262-170-285-170-7-1-102,50-212,113l-200,115-200-115c-110-63-204-114-209-114-21,0-292,163-288,174c6,19,691,407,707,400c8-3,167-92,352-197Zm-151-319c82-46,148-86,149-89c0-3-12-11-27-18-26-12-20-16,183-131c115-66,210-123,212-128c3-9-277-172-296-172-7,0-107,54-222,120l-210,120-208-120c-115-66-215-120-223-120-24,1-284,155-286,170-2,10,125,88,380,232c210,120,386,218,391,218s76-37,157-82Z" transform="matrix(1.00378 0 0 1.013853-5.68208-20.7254)" fill="#f0f0f1" />
                    </g>
                    <path d="M199.680417,24.709473v75.9h76.5l-76.5-75.9Z" transform="matrix(1.075983 0 0 1.177621-4.45472-23.399398)" fill="#a1a1a1" stroke="#3f5787" stroke-width="0.6" />
                </svg>
                <strong>ROMETHEMEFORM</strong>
            </div>
            <button id="rform-save-editor-btn" class="elementor-button elementor-button-success elementor-modal-iframe-btn-control"><?php echo esc_html__('SAVE & CLOSE', 'romethemeform') ?></button>
        </div>
        <div class="elementor-editor-container">
            <iframe class="ifr-editor" id="rform-elementor-editor" src="" frameborder="0"></iframe>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f0f0f1;
    }

    .rform-modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 99999;
        /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.6);
        /* Black w/ opacity */
    }

    .rform-modal-content {
        display: flex;
        gap: 5px;
        flex-direction: column;
        background-color: #34383c;
        margin: auto;
        /* 15% from the top and centered */
        width: 80%;
        /* Could be more or less, depending on screen size */
        height: 90%;
        box-shadow: 0px 0px 49px -19px rgba(0, 0, 0, 0.82);
        -webkit-box-shadow: 0px 0px 49px -19px rgba(0, 0, 0, 0.82);
        -moz-box-shadow: 0px 0px 49px -19px rgba(0, 0, 0, 0.82);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .ifr-editor {
        height: 100%;
        width: 100%;
    }

    .ifr-editor[src] {
        background-color: #34383c;
    }

    /* The Close Button */
    .close {
        color: rgb(255, 255, 255);
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .elementor-editor-container {
        width: 100%;
        height: 100%;
    }

    .flex-direction-col {
        display: flex;
        flex-direction: column;
    }

    .elementor-modal-iframe-btn-control {
        padding: 15px;
    }

    .elementor-editor-header-iframe {
        display: flex;
        justify-content: space-between;
        padding: 5px;
    }

    .edit-form-wrapper {
        padding: 5px;
        display: flex;
        justify-content: center;
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .rform-editor-header {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        align-items: center;
        padding-inline: 1rem;
    }

    .rform-editor-header>strong {
        font-size: 1rem;
        color: white;
    }
</style>
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 25px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 17px;
        width: 17px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .rform-save-btn {
        width: 8rem;
    }

    body {
        background-color: #f0f0f1;
    }
</style>