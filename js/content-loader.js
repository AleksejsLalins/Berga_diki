$(document).ready(function() {
    // Получаем текущий язык из URL (по умолчанию 'lv')
    var lang = getUrlParameter('lang') || 'lv';

    // Запрос на получение данных с сервера
    $.ajax({
        url: 'admin/get_sections.php', // Путь к PHP скрипту, который извлекает данные из базы данных
        type: 'GET',
        dataType: 'json',
        data: { lang: lang }, // Передаем параметр языка в запросе
        success: function(data) {
            // Обработка полученных данных и их отображение
            if (data.error) {
                console.log(data.error); // Выводим ошибку, если данные не найдены
            } else {
                updateContent(data);
            }
        },
        error: function(xhr, status, error) {
            console.log("Ошибка при загрузке данных: " + error);
        }
    });

    // Функция для обновления контента на странице
    function updateContent(sections) {
        sections.forEach(function(section) {
            switch (section.section_name) {
                case 'about':
                    $('#about').html(section.content);
                    break;
                case 'ponds':
                    $('#ponds').html(section.content);
                    break;
                case 'rules':
                    $('#rules').html(section.content);
                    break;
                case 'pricing':
                    $('#pricing').html(section.content);
                    break;
                case 'booking':
                    $('#booking').html(section.content);
                    break;
                case 'gallery':
                    $('#gallery').html(section.content);
                    break;
                case 'contacts':
                    $('#contacts').html(section.content);
                    break;
                default:
                    console.log("Неизвестный раздел: " + section.section_name);
            }
        });
    }

    // Функция для получения параметра из URL
    function getUrlParameter(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        } else {
            return decodeURIComponent(results[1]) || 0;
        }
    }
});
