document.addEventListener('DOMContentLoaded', function () {
    $(document).on('click', '.error-dump a', function (e) {
        e.preventDefault();

        let parentNode = $(this).parent();
        if (!$(parentNode).hasClass('show')) {
            $(parentNode).addClass('show');
            $(parentNode).find('a').text('Скрыть');
        } else {
            $(parentNode).removeClass('show');
            $(parentNode).find('a').text('Показать');
        }
    });

    $(document).on('click', '.switch-btn', function() {
        $(this).toggleClass('switch-on');

        let inputName = $(this).data('input');
        let value = $(this).hasClass('switch-on') ? 1 : 0;

        $('input[name="' + inputName + '"]').val(value);
    });
});

const RdnErrorLog = {
    openModal: function (data, id, title) {
        const popup = BX.PopupWindowManager.create(
            "rdn_error_log_detail_" + id,
            null,
            {
                content: data,
                width: 900, // ширина окна
                height: 700, // высота окна
                zIndex: 100, // z-index
                closeIcon: {
                    opacity: 1
                },
                titleBar: title,
                closeByEsc: true, // закрытие окна по esc
                darkMode: false, // окно будет светлым или темным
                autoHide: false, // закрытие при клике вне окна
                draggable: true, // можно двигать или нет
                resizable: true, // можно ресайзить
                min_height: 100, // минимальная высота окна
                min_width: 100, // минимальная ширина окна
                lightShadow: true, // использовать светлую тень у окна
                overlay: {
                    backgroundColor: 'black',
                    opacity: 500
                },
            });

        popup.show();
    },
    showDetail: (id) => {
        BX.ajax.runAction('rdn:error.AjaxController.getDetailData', {
            data: {
                id: id
            }
        }).then(function (response) {
                RdnErrorLog.openModal(response.data.html, id, 'Подробно');
            },
            function (response) {

                let data = '';
                response.errors.forEach(function (item) {
                    data += '<p>' + item.message + '</p>';
                });

                RdnErrorLog.openModal('<div class="internal-error">' + data + '</div>',  id, 'Ошибка!');
            });
    }
};
