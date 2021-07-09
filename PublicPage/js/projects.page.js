(function ($) {
    ('use strict');
    $(function () {
        $('.project-line .actions button').on('click', handleProjectAction);
        $('#close-modal').on('click', closeModal);
        $('#project-name').on('keyup', e => {
            $('#project-name').removeClass('ppi-input-invalid');
        });

        function showModal() {
            $('.modal').css('display', 'flex');
            $('#overlay').css('display', 'block');
            $('#project-name').val('');
        }

        function closeModal(e) {
            e.preventDefault();
            $('.modal').css('display', 'none');
            $('#overlay').css('display', 'none');
            projectId = undefined;
        }

        let projectId;
        function handleProjectAction(e) {
            let action = e.target.id;
            projectId = $(this).parent().parent().attr('id');

            if (action === 'rename-project') {
                showModal();
                console.log(projectId);
                $('#save-name').on('click', e => {
                    e.preventDefault();
                    if ($('#project-name').val() === '') {
                        $('#project-name').addClass('ppi-input-invalid');
                        projectId = undefined;

                        return false;
                    }
                    const name = $('#project-name').val();
                    $('#project-name').val('');
                    //const projectId = $(this).parent().parent().attr('id');
                    performAjaxCall(action, projectId, name);
                    $('.modal').css('display', 'none');
                    $('#overlay').css('display', 'none');
                });
            } else {
                const projectId = $(this).parent().parent().attr('id');
                performAjaxCall(action, projectId);
            }
        }

        function performAjaxCall(projectAction, projectId, name = null) {
            const data = {
                projectAction: projectAction,
                projectId: projectId,
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
            setTimeout(e => location.reload(), 500);
        }
    });
})(jQuery);
