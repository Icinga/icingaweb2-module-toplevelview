;(function (Icinga) {

    var Toplevelview = function (module) {
        this.module = module;
        this.initialize();
    };

    Toplevelview.prototype = {
        initialize: function () {
            this.module.on('click', '.tlv-view-tree .tlv-tree-node', this.processTreeNodeClick);
            this.module.on('click', 'div[href].action', this.buttonClick, this);
            this.module.on('rendered', this.rendered);
        },

        rendered: function (ev) {
            this.collapseOnLoad(ev);
            var $container = $(ev.currentTarget);
            $container.find('.codemirror').each(function (i, el) {
                var mode = el.getAttribute('data-codemirror-mode');
                var editor = CodeMirror.fromTextArea(el, {
                    lineNumbers: true,
                    mode: mode,
                    foldGutter: true,
                    gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
                });
                // avoid entering tab chars
                editor.setOption('extraKeys', {
                    Tab: function(cm) {
                        var spaces = new Array(cm.getOption('indentUnit') + 1).join(' ');
                        cm.replaceSelection(spaces);
                    },
                    'Ctrl-F': 'findPersistent'
                });
            });
        },

        processTreeNodeClick: function (event) {
            event.stopPropagation();
            var $el = $(event.currentTarget);
            var $parent = $el.parents('.tlv-tree-node');
            var $all = $el.find('.tlv-tree-node');
            if (($parent.length === 0 && $all.hasClass('tlv-collapsed')) || $el.hasClass('tlv-collapsed')) {
                $el.removeClass('tlv-collapsed');
                $all.removeClass('tlv-collapsed');
            } else {
                $el.addClass('tlv-collapsed');
                $all.addClass('tlv-collapsed');
            }
        },

        collapseOnLoad: function (event) {
            var $el = $(event.currentTarget);
            $el.find('.tlv-view-tree .tlv-tree-node.tlv-collapsible.ok').addClass('tlv-collapsed');
        },

        buttonClick: function (event) {
            event.stopPropagation();
            var $el = $(event.currentTarget);
            var $links = $el.find('a[href]');
            if ($links.length > 0) {
                $links[0].click();
            }
        }
    };

    Icinga.availableModules.toplevelview = Toplevelview;
}(Icinga));
