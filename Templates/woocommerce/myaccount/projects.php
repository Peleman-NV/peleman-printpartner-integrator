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

        .ppi-btn-warning {
            background-color: red !important;
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
                <tr class="project-line">
                    <input type="hidden" name="project_id" value="<?php echo $project->project_id; ?>">
                    <td><?php echo $project->name; ?></td>
                    <td><?php $product = wc_get_product($project->product_id);
                        echo '<a href="' . get_permalink($project->product_id) . '">' . $product->get_title() . '</a>';  ?></td>
                    <td><?php $date = new DateTime($project->created);
                        echo $date->format('Y-m-d'); ?></td>
                    <td><button id="edit-project" class="woocommerce-button button">Edit</button>
                        <button id="rename-project" class="woocommerce-button button">Rename</button>
                        <button id="add-project-to-cart" class="woocommerce-button button">Order</button>
                        <button id="delete-project" class="woocommerce-button button ppi-btn-warning">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
<script type="text/javascript">
    (function($) {
        ('use strict');
        $(function() {

            $('.project-line').on('click', e => {
                let action = e.target.id;
                let projectId = e.currentTarget.children[0].value;

                switch (action) {
                    case 'edit-project':
                        editProject(projectId);
                        break;
                    case 'rename-project':
                        renameProject(projectId);
                        break;
                    case 'add-project-to-cart':
                        orderProject(projectId);
                        break;
                    case 'delete-project':
                        deleteProject(projectId);
                        break;
                }
            });

            function editProject(projectId) {
                /**
                 * redirect to Imaxel with ?
                 * backURL projects page -> Imaxel label is Add to cart though
                 */
                console.log('Edit ' + projectId);
            }

            function renameProject(projectId) {
                console.log('Rename ' + projectId);
                const name = prompt('New project name?');
                console.log(name);

            }

            function orderProject(projectId) {
                /**
                 * create project
                 * read JSON
                 * add to cart
                 */
                console.log('Order ' + projectId);
            }

            function deleteProject(projectId) {
                const confirmDelete = confirm('Are you sure you want to delete project ' + projectId);
                if (confirmDelete) {
                    console.log('Delete ' + projectId);
                }
                console.log('Not deleting project ' + projectId);

            }
        });
    })(jQuery);
</script>

</html>