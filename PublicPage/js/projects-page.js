(function ($) {
    ('use strict');
    $(function () {
        $('.project-line .actions button').on('click', handleProjectAction);
        $('.project-line .actions .ppi-btn-disabled').hover(displayAlreadyOrderedWarning);
        $('#close-modal').on('click', closeModal);
        $('#project-name').on('keyup', e => {
            $('#project-name').removeClass('ppi-input-invalid');
        });

        let projectId;
        let variantId;

        function displayAlreadyOrderedWarning(e) {
            e.preventDefault();
            $(this).siblings('.ordered').toggle();

        }

        function showModal(element) {
            const currentName = element.parent().parent().children('#project-name').html();
            console.log(currentName);
            $('.modal').css('display', 'flex');
            $('#overlay').css('display', 'block');
            $('#new-project-name').val(currentName);
        }

        function closeModal(e) {
            e.preventDefault();
            $('.modal').css('display', 'none');
            $('#overlay').css('display', 'none');
            projectId = undefined;
        }

        function handleProjectAction(e) {
            let action = e.target.id;
            projectId = $(this).parent().parent().attr('id');

            if (action === 'rename-project') {
                showModal($(this));
                $('#save-name').on('click', e => {
                    e.preventDefault();
                    if ($('#new-project-name').val() === '') {
                        $('#new-project-name').addClass('ppi-input-invalid');
                        projectId = undefined;

                        return false;
                    }
                    const name = $('#new-project-name').val();
                    $('#new-project-name').val('');
                    performAjaxCall(action, projectId, name);
                    $('.modal').css('display', 'none');
                    $('#overlay').css('display', 'none');
                });
            } else if (action === 'add-project-to-cart') {
                    variantId = $(this).parent().parent().children('#variant-id').data('variantId');
                    performAjaxCall(action, projectId, null, variantId);
            } else {
                const projectId = $(this).parent().parent().attr('id');
                performAjaxCall(action, projectId);
            }
        }

        function performAjaxCall(projectAction, projectId, name = null, variantId = null) {
            const data = {
                projectAction: projectAction,
                projectId: projectId,
                variantId: variantId,
                name: name,
                action: 'handle_project_action',
                _ajax_nonce: ppi_project_action_object.nonce,
            };

            $.ajax({
                url: ppi_project_action_object.ajax_url,
                method: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        switch (response.action) {
                            case 'edit-project':
                            case 'add-project-to-cart':
                                location.href = response.redirectUrl;
                                break;
                            // case 'add-project-to-cart':
                            //     location.href = response.redirectUrl;
                            //     break;
                            case 'rename-project':
                                setTimeout(e => location.reload(), 500);
                                break;
                            default:
                                break;
                        }
                    }
                    if (response.status === 'error') {
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log({ jqXHR });
                    console.error(
                        'Something went wrong:\n' +
                            jqXHR.status +
                            ': ' +
                            jqXHR.statusText +
                            '\nTextstatus: ' +
                            textStatus +
                            '\nError thrown: ' +
                            errorThrown
                    );
                },
            });
        }
    });
})(jQuery);
