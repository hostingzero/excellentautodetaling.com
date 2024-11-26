<?php


$entry_id = sanitize_text_field($_GET['entry_id']);
$datas = json_decode(get_post_meta($entry_id, 'rform-entri-data', true), true);
$entry = get_post($entry_id);
$form_id = get_post_meta($entry_id, 'rform-entri-form-id', true);
$form_name = get_the_title($form_id);
$pageID = get_post_meta($entry_id, 'rform-entri-referal', true);
$pageUrl = get_permalink($pageID);

?>

<div>
    <h2><?php echo esc_html($entry->post_title) ?></h2>
    <div class="body-container p-3">
        <div class="data-container p-3">
            <div class="data-header w-100 border-bottom">
                <h5>Data</h5>
            </div>
            <div class="data-body">
                <?php
                foreach ($datas as $key => $value) :
                    $label = ucwords(str_replace(['-', '_'], ' ', $key))
                ?>
                    <div class="p-0 mb-3 bg-white">
                        <h6 class="info-header py-2 px-1 m-0"><?php echo esc_html($label) ?></h6>
                        <span class="py-2 px-1 text-wrap"><?php echo (is_array($value)) ? esc_html(implode(' , ' , $value)) : esc_html($value) ?></span>
                    </div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
        <div class="sidebar">
            <div class="bg-white py-3 px-2">
                <div class="card-header text-center">
                    <h5>INFO</h5>
                </div>
                <div class="px-1">
                    <div class="p-0 mb-3 bg-white">
                        <h6 class="info-header py-2 px-1 m-0">Form Name</h6>
                        <span class="py-2 px-1"><?php echo esc_html($form_name) ?></span>
                    </div>
                    <div class="p-0 mb-3 bg-white">
                        <h6 class="info-header py-2 px-1 m-0">Entry ID</h6>
                        <span class="py-2 px-1"><?php echo esc_html($entry_id) ?></span>
                    </div>
                    <div class="p-0 bg-white">
                        <h6 class="info-header py-2 px-1 m-0">Referal Page</h6>
                        <a href="<?php echo esc_url($pageUrl) ?>" class="py-2 px-1"><?php echo esc_html(get_the_title($pageID)) ?></a>
                    </div>
                </div>
            </div>
            <div class="bg-white py-3 px-2">
                Entry Date : <?php echo esc_html($entry->post_date) ?>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f0f0f1;
    }

    .body-container {
        display: flex;
        flex-direction: row;
        justify-content: center;
        width: 100%;
        justify-content: space-between;
        masonry-auto-flow: auto;
    }

    .data-container {
        width: calc(80% - 10px);
        background-color: white;
        height: fit-content;
    }

    .sidebar {
        width: calc(20% - 10px);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-header {
        background-color: #a7d3fc;
    }

    @media only screen and (max-width : 782px) {
        .body-container {
            flex-direction: column;
            gap: 1rem;
        }

        .data-container {
            width: 100%;
        }

        .sidebar {
            width: 100%;
        }
    }
</style>