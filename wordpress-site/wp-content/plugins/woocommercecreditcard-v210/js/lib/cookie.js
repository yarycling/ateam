window.setCookie = function setCookie(key, value, expiry) {
    var expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 1000 * 60 * 60 * 24));
    document.cookie = key + "=" + escape(value) +
        ";domain=" + window.location.hostname +
        ";path=/" +
        ";expires=" + expires.toUTCString();
}

window.getCookie = function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

window.eraseCookie = function eraseCookie(key) {
    var keyValue = getCookie(key);
    setCookie(key, keyValue, '-1');
    if (getCookie(key) != null) {
        document.cookie = key + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=-99999999; path=/; domain=' + window.location.hostname;
    }
}