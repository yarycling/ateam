(function ($) {

    var symbols = {
        'AED': 'د.إ',
        'AFN': '؋',
        'ALL': 'L',
        'AMD': '֏',
        'ANG': 'ƒ',
        'AOA': 'Kz',
        'ARS': '$',
        'AUD': '$',
        'AWG': 'ƒ',
        'AZN': '₼',
        'BAM': 'KM',
        'BBD': '$',
        'BDT': '৳',
        'BGN': 'лв',
        'BHD': '.د.ب',
        'BIF': 'FBu',
        'BMD': '$',
        'BND': '$',
        'BOB': '$b',
        'BRL': 'R$',
        'BSD': '$',
        'BTC': '฿',
        'BTN': 'Nu.',
        'BWP': 'P',
        'BYR': 'Br',
        'BYN': 'Br',
        'BZD': 'BZ$',
        'CAD': '$',
        'CDF': 'FC',
        'CHF': 'CHF',
        'CLP': '$',
        'CNY': '¥',
        'COP': '$',
        'CRC': '₡',
        'CUC': '$',
        'CUP': '₱',
        'CVE': '$',
        'CZK': 'Kč',
        'DJF': 'Fdj',
        'DKK': 'kr',
        'DOP': 'RD$',
        'DZD': 'دج',
        'EEK': 'kr',
        'EGP': '£',
        'ERN': 'Nfk',
        'ETB': 'Br',
        'ETH': 'Ξ',
        'EUR': '€',
        'FJD': '$',
        'FKP': '£',
        'GBP': '£',
        'GEL': '₾',
        'GGP': '£',
        'GHC': '₵',
        'GHS': 'GH₵',
        'GIP': '£',
        'GMD': 'D',
        'GNF': 'FG',
        'GTQ': 'Q',
        'GYD': '$',
        'HKD': '$',
        'HNL': 'L',
        'HRK': 'kn',
        'HTG': 'G',
        'HUF': 'Ft',
        'IDR': 'Rp',
        'ILS': '₪',
        'IMP': '£',
        'INR': '₹',
        'IQD': 'ع.د',
        'IRR': '﷼',
        'ISK': 'kr',
        'JEP': '£',
        'JMD': 'J$',
        'JOD': 'JD',
        'JPY': '¥',
        'KES': 'KSh',
        'KGS': 'лв',
        'KHR': '៛',
        'KMF': 'CF',
        'KPW': '₩',
        'KRW': '₩',
        'KWD': 'KD',
        'KYD': '$',
        'KZT': 'лв',
        'LAK': '₭',
        'LBP': '£',
        'LKR': '₨',
        'LRD': '$',
        'LSL': 'M',
        'LTC': 'Ł',
        'LTL': 'Lt',
        'LVL': 'Ls',
        'LYD': 'LD',
        'MAD': 'MAD',
        'MDL': 'lei',
        'MGA': 'Ar',
        'MKD': 'ден',
        'MMK': 'K',
        'MNT': '₮',
        'MOP': 'MOP$',
        'MRO': 'UM',
        'MRU': 'UM',
        'MUR': '₨',
        'MVR': 'Rf',
        'MWK': 'MK',
        'MXN': '$',
        'MYR': 'RM',
        'MZN': 'MT',
        'NAD': '$',
        'NGN': '₦',
        'NIO': 'C$',
        'NOK': 'kr',
        'NPR': '₨',
        'NZD': '$',
        'OMR': '﷼',
        'PAB': 'B/.',
        'PEN': 'S/.',
        'PGK': 'K',
        'PHP': '₱',
        'PKR': '₨',
        'PLN': 'zł',
        'PYG': 'Gs',
        'QAR': '﷼',
        'RMB': '￥',
        'RON': 'lei',
        'RSD': 'Дин.',
        'RUB': '₽',
        'RWF': 'R₣',
        'SAR': '﷼',
        'SBD': '$',
        'SCR': '₨',
        'SDG': 'ج.س.',
        'SEK': 'kr',
        'SGD': '$',
        'SHP': '£',
        'SLL': 'Le',
        'SOS': 'S',
        'SRD': '$',
        'SSP': '£',
        'STD': 'Db',
        'STN': 'Db',
        'SVC': '$',
        'SYP': '£',
        'SZL': 'E',
        'THB': '฿',
        'TJS': 'SM',
        'TMT': 'T',
        'TND': 'د.ت',
        'TOP': 'T$',
        'TRL': '₤',
        'TRY': '₺',
        'TTD': 'TT$',
        'TVD': '$',
        'TWD': 'NT$',
        'TZS': 'TSh',
        'UAH': '₴',
        'UGX': 'USh',
        'USD': '$',
        'UYU': '$U',
        'UZS': 'лв',
        'VEF': 'Bs',
        'VND': '₫',
        'VUV': 'VT',
        'WST': 'WS$',
        'XAF': 'FCFA',
        'XBT': 'Ƀ',
        'XCD': '$',
        'XOF': 'CFA',
        'XPF': '₣',
        'YER': '﷼',
        'ZAR': 'R',
        'ZWD': 'Z$'
    };

    window.downloadURI = function downloadURI(uri, name) {
        let link = document.createElement("a");
        link.download = name;
        link.href = uri;
        link.click();
    }

    window.downloader = function downloader(data, type, name) {
        let blob = new Blob([data], {
            type
        });
        let url = window.URL.createObjectURL(blob);
        downloadURI(url, name);
        window.URL.revokeObjectURL(url);
    }

    window.get_locale = function get_locale() {
        return (navigator.languages && navigator.languages.length)
            ? navigator.languages[0]
            : navigator.language;
    }

    window.money_format = function money_format(value, currency) {
        var _currency = currency.toUpperCase();
        var _value = new Intl.NumberFormat(get_locale(), {
            style: 'currency',
            currency: _currency,
            currencyDisplay: 'symbol',
        }).format(value);
        var _symbol = symbols[_currency];
        if (_symbol === undefined) {
            _symbol = '$'; // default symbol if no currency matched to a symbol
        }
        var _symbol2 = _symbol.replace(/[^\W]/g, '');
        if (_symbol2.length !== 0) {
            _symbol = _symbol2; // strip letters from the symbol if non-destructive
        }
        var _amount = $.trim(_value.replace(/[^\d.,]/g, ''));
        return {
            'symbol': _symbol,
            'amount': _amount,
            'currency': _currency,
            'desc': _symbol + _amount + ' ' + _currency,
        }
    }

    // standardize styling custom buttons. Must have a specific layout!
    window.style_button = function style_button(data) {
        var settings = $.extend({
            color: '#007cba',
            height: '30px',
            id: '',
            margin: '',
            width: '',
            pointer_events: 'all',
        }, data);
        $('#' + settings.id).attr('style', '\
            display: flex !important;\
            justify-content: center !important;\
            align-items: center !important;\
            width: ' + settings.width + ' !important;\
            height: ' + settings.height + ' !important;\
            background: ' + settings.color + ' !important;\
            margin: ' + settings.margin + ' !important;\
            border-color: ' + settings.color + ' !important;\
            pointer-events: ' + settings.pointer_events + ' !important;\
            border-radius: ' + settings['border-radius'] + '!important;\
        ');
        $('div#' + settings.id + '_icon').attr('style', '\
            display: flex !important;\
            align-items: center !important;\
        ');
        $('div#' + settings.id + '_text').attr('style', '\
            align-items: center !important;\
            display: flex !important;\
            width: 100% !important;\
            justify-content: center !important;\
        ');
        $('#' + settings.id).on('mouseenter', function (e) {
            $(this).css({
                'box-shadow': 'inset 0 0 0 1000px rgba(0,0,0,.2)',
            });
        }).on('mouseleave', function (e) {
            $(this).css({
                'box-shadow': 'none',
            });
        });
    }

})(jQuery);