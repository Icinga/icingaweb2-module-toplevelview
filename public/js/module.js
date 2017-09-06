;(function (Icinga) {

    var Toplevelview = function (module) {
        this.module = module;
        this.initialize();
    };

    Toplevelview.prototype = {
        initialize: function () {
            var addCSSRule = function (sheet, selector, rules, index) {
                if ('insertRule' in sheet) {
                    sheet.insertRule(selector + '{' + rules + '}', index);
                } else if ('addRule' in sheet) {
                    sheet.addRule(selector, rules, index);
                } else {
                    Icinga.module.icinga.logger.debug('Can\'t insert CSS rule');
                }
            };

            var sheet = (function () {
                var style = document.createElement('style');
                // WebKit hack
                style.appendChild(document.createTextNode(''));
                document.head.appendChild(style);
                return style.sheet;
            })();

            addCSSRule(
                sheet,
                '#layout.twocols.wide-layout #col1.module-toplevelview,'
                + '#layout.twocols.wide-layout #col1.module-toplevelview ~ #col2',
                'width: 50%',
                0
            );
        }
    };

    Icinga.availableModules.toplevelview = Toplevelview;
}(Icinga));
