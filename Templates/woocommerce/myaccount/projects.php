<?php

$userId = get_current_user_id();

$userProjects = getProjectsForUser($userId);

function getProjectsForUser($userId)
{
    global $wpdb;
    $table_name = PPI_USER_PROJECTS_TABLE;
    return $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $userId;");
}
?>

<html>

<head>
    <style>
        table,
        th,
        td {
            border: 1px solid lightgrey !important;
        }

        thead th {
            text-align: center;
        }

        table td a {
            color: #006ad0 !important;
            text-decoration: underline;
        }

        .project-line .actions {
            user-select: none;
            position: relative;
        }

        .ppi-btn-warning {
            background-color: red !important;
        }

        .ppi-btn-disabled {
            background-color: grey !important;
            cursor: not-allowed !important;
        }

        .ppi-btn-disabled:active {
            pointer-events: none;
        }

        .ordered {
            display: none;
            background-color: white;
            border: 1px solid lightgrey;
            color: black;
            z-index: 999;
            padding: 5px 10px;
            border-radius: 10px;
            position: absolute;
            top: 30px;
            left: 50px;
        }

        #overlay {
            height: 100vh;
            width: 100vw;
            position: fixed;
            left: 0;
            top: 0;
            display: none;
        }

        .modal {
            background: white;
            position: absolute;
            padding: 18px;
            border-radius: 25px;
            border: 1px solid black;
            left: 50%;
            top: 50%;
            margin: auto;
            transform: translate(-50%, -50%);
            right: auto !important;
            bottom: auto !important;
            align-items: center;

        }

        .modal h4,
        form,
        input {
            display: inline-block;
            line-height: 1.05;
        }

        .modal h4 {
            margin: 5px;
        }

        .ppi-input-invalid {
            border: 1px solid red;
        }
    </style>

</head>

<body>
    <table class="shop-table">
        <thead>
            <th>Name</th>
            <th>Product</th>
            <th>Created</th>
            <th>Actions</th>
        </thead>
        <tbody>
            <?php foreach ($userProjects as $project) : ?>
                <tr class="project-line" id="<?php echo $project->project_id; ?>">
                    <td id="project-name"><?php echo $project->name; ?></td>
                    <td id="variant-id" data-variant-id="<?= $project->product_id; ?>">
                        <?php $product = wc_get_product($project->product_id);
                        echo '<a href="' . get_permalink($project->product_id) . '">' . $product->get_title() . '</a>';  ?>
                    </td>
                    <td><?php $date = new DateTime($project->created);
                        echo $date->format('Y-m-d'); ?></td>
                    <td class="actions">
                        <button id="edit-project" class=" woocommerce-button button <?= $project->ordered ? 'ppi-btn-disabled' : '' ?>">Edit</button>
                        <?= $project->ordered ? '<div class="ordered">This project has already been ordered,<br>and cannot be modified.</div>' : '' ?>
                        <button id="rename-project" class=" woocommerce-button button">Rename</button>
                        <button id="add-project-to-cart" class="woocommerce-button button">Order</button>
                        <button id="duplicate-project" class=" woocommerce-button button">Duplicate</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="modal" id="name-modal">
        <h4>Please enter a name</h4>
        <form action="">
            <label for="project-name"></label>
            <input id="new-project-name" name="project-name" type="text" required>
            <button id="save-name" class="woocommerce-button button">Save name</button>
            <button id="close-modal" name="close" class="woocommerce-button button">X</button>
        </form>
    </div>
    <div id="overlay"></div>
</body>

</html>