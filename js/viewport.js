// viewport.js 
// Запрещаем зум на мобильных устройствах, если ширина экрана меньше 768 пикселей, на пк зум разрешаем

if (window.innerWidth <= 768) {
    var meta = document.createElement('meta');
    meta.name = "viewport";
    meta.content = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
    document.head.appendChild(meta);
} else {
    var meta = document.createElement('meta');
    meta.name = "viewport";
    meta.content = "width=device-width, initial-scale=1.0";
    document.head.appendChild(meta);
}
